<?php

namespace App\Http\Controllers;

use App\Libraries\Recaptcha;
use App\Libraries\Validators;
use App\Models\Admin;
use App\Models\Login;
use App\Models\LoginAttempt;
use App\Models\LoginBrowser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Auth extends BaseController
{
    private const OTP_EXPIRY_SECONDS = 300;
    private const OTP_RESEND_DELAY = 120;

    public function login(Request $request)
    {
        if ($request->session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    public function login_admin(Request $request)
    {
        $session = $request->session();
        $input = (object) $request->all();

        $email = trim($input->email ?? '');
        $password = trim($input->password ?? '');
        if (app()->environment('local')) {
            $lat = $input->location['lat'] ?? $input->location->lat ?? 26.9124;
            $lon = $input->location['lon'] ?? $input->location->lon ?? 75.7873;
        } else {
            $lat = $input->location['lat'] ?? $input->location->lat ?? null;
            $lon = $input->location['lon'] ?? $input->location->lon ?? null;
        }
        $browser = $input->browser ?? '';
        $device = $input->device ?? '';
        $os = $input->os ?? '';

        if (!Validators::email($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Email',
            ], 400);
        }

        if (!Validators::password($password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Password',
            ], 400);
        }

        if (!in_array($browser, ["Unknown Browser", "Chrome", "Firefox", "Safari", "Internet Explorer"])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Browser',
            ], 400);
        }

        if (!in_array($os, ["Unknown OS", "Windows", "Linux", "macOS", "Android", "iOS"])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Operating System',
            ], 400);
        }

        $adminData = Admin::where('email', strtolower($email))->first();
        $loginAttemptId = null;

        if ($adminData) {
            $loginAttemptCount = LoginAttempt::where('admin_id', $adminData->id)
                ->where('created_at', '>=', now()->subMinutes(15))
                ->where('verified', -1)
                ->count();

            if ($loginAttemptCount >= 5) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Too many login attempts. Please try again after 15 Minutes.',
                ], 429);
            }

            $createdAttempt = LoginAttempt::create([
                "ip" => $request->ip(),
                "browser" => $browser,
                "device" => $device,
                "os" => $os,
                "lat" => $lat,
                "log" => $lon,
                "session_id" => $session->getId(),
                "admin_id" => $adminData->id,
                "verified" => 0,
            ]);

            $loginAttemptId = $createdAttempt->id;
        }

        if (!$adminData || !Hash::check($password, $adminData->password)) {
            if ($adminData && $loginAttemptId) {
                LoginAttempt::where('id', $loginAttemptId)->update([
                    "verified" => -1,
                    "admin_id" => $adminData->id,
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Credentials',
            ], 401);
        }

        LoginAttempt::where('id', $loginAttemptId)->update([
            "verified" => 1,
            "admin_id" => $adminData->id,
        ]);

        LoginAttempt::where('admin_id', $adminData->id)
            ->where('verified', -1)
            ->update(["verified" => 2]);

        if ($adminData->active == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        $sessionId = $session->getId();
        Login::create([
            "admin_id" => $adminData->id,
            "ip" => $request->ip(),
            "browser" => $browser,
            "device" => $device,
            "os" => $os,
            "lat" => $lat,
            "lon" => $lon,
            "session_id" => $sessionId,
            "verified" => 0,
            "email_verified" => 0,
            "phone_verified" => 0,
        ]);

        $browserToken = $request->cookie('browser_token');
        if (strlen($browserToken ?? '') > 5) {
            $browser = LoginBrowser::findBrowserToken($browserToken);
            if (isset($browser['admin_id']) && $browser['admin_id'] == $adminData->id) {
                Login::where("session_id", $sessionId)->update([
                    "phone_verified" => $browser['phone_verified'],
                    "email_verified" => $browser['email_verified'],
                ]);

                $session->put('admin_id', $adminData->id);

                Admin::where('id', $adminData->id)->update([
                    "current_session_id" => $sessionId,
                ]);

                return response()->json([
                    'status' => 'success',
                    'otpRequired' => false,
                    'message' => 'Login Successful !',
                ], 200);
            }
        }

        if (app()->environment('local')) {
            $emailOtp = 654321;
        } else {
            $emailOtp = random_int(111111, 999999);
        }
        $session->put('email_otp', $emailOtp);
        $session->put('otp_expires', now()->addSeconds(self::OTP_EXPIRY_SECONDS)->timestamp);

        Mail::raw("Your OTP code for login is: $emailOtp", function ($message) use ($adminData) {
            $message->to($adminData->email)
                ->subject('Your OTP for Login');
        });

        return response()->json([
            'status' => 'success',
            'otpRequired' => true,
            'message' => 'Login OTP sent successfully',
        ], 200);
    }

    public function verify_login(Request $request)
    {
        $sessionId = $request->session()->getId();
        $loginLog = Login::where('session_id', $sessionId)
            ->orderBy('id', 'DESC')
            ->where('verified', 0)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if (!$loginLog) {
            return redirect()->route('admin.login');
        }

        $adminData = Admin::find($loginLog->admin_id);
        return view('auth.verify_login', [
            "email" => $adminData->email ?? '',
            "phone" => $adminData->phone ?? '',
        ]);
    }

    public function verify_login_otp(Request $request)
    {
        $session = $request->session();
        $input = (object) $request->all();
        $sessionId = $session->getId();

        $loginLog = Login::where('session_id', $sessionId)
            ->orderBy('id', 'DESC')
            ->where('verified', 0)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if (!$loginLog) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login Session not found',
            ], 401);
        }

        if (!$session->get('email_otp')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login Session not found',
            ], 401);
        }

        $otp = $input->otp ?? '';
        if (!ctype_digit((string) $otp) || strlen((string) $otp) != 6) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please Enter valid OTP',
            ], 400);
        }

        if ($session->get("otp_expires") < now()->timestamp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP Expired. Please resend OTP.',
            ], 400);
        }

        if ($otp != $session->get("email_otp")) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please Enter valid OTP',
            ], 400);
        }

        $emailVerified = ($otp == $session->get("email_otp")) ? 1 : 0;
        $phoneVerified = 0;

        Login::where('id', $loginLog->id)->update([
            "verified" => 1,
            "email_verified" => $emailVerified,
            "phone_verified" => $phoneVerified,
        ]);

        Admin::where('id', $loginLog->admin_id)->update([
            "current_session_id" => $sessionId,
        ]);

        $browserToken = Str::random(30);
        LoginBrowser::create([
            "admin_id" => $loginLog->admin_id,
            "browser_token" => $browserToken,
            "phone_verified" => $phoneVerified,
            "email_verified" => $emailVerified,
        ]);

        $session->put('admin_id', $loginLog->admin_id);
        $session->forget("email_otp");
        $session->forget("phone_otp");
        $session->forget("otp_expires");

        return response()->json([
            'status' => 'success',
            'message' => 'Login Successful',
        ], 200)->cookie("browser_token", $browserToken, 1440, "/", null, true, true);
    }

    public function forgot_password(Request $request)
    {
        if ($request->session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.forgot_password');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('admin.login');
    }

    public function ad_login(Request $request)
    {
        $adToken = $request->query('ad_token');
        if (!empty($adToken) && strlen($adToken) > 10) {
            $user = User::where("ad_token", $adToken)->first();
            if ($user) {
                $partnerUrl = config('app.partner_url', env("partnerURL") ?? env("frontendURL") ?? "http://localhost:8001/");
                $parsedPort = parse_url($partnerUrl, PHP_URL_PORT);
                $parsedHost = parse_url($partnerUrl, PHP_URL_HOST);
                if (($parsedHost === 'localhost' || $parsedHost === '127.0.0.1') && ($parsedPort == 8000 || empty($parsedPort))) {
                    $partnerUrl = "http://localhost:8001/";
                }
                $targetUrl = rtrim($partnerUrl, '/') . '/auth/login/token?ad_token=' . urlencode($adToken);
                return redirect($targetUrl);
            }
        }
        return response("Invalid or expired login token.", 403);
    }
}
