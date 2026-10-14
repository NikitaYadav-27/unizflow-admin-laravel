<?php
namespace App\Http\Controllers;
use App\Libraries\Recaptcha;
use App\Libraries\Validators;
use App\Models\Admin;

class Auth extends BaseController
{
    private const OTP_EXPIRY_SECONDS = 300;
    private const OTP_RESEND_DELAY = 120;
    public function login()
    {
        $session = session();
        if ($session->get('admin_id')) {
            return redirect('/admin/dashboard');
        }
        return view('auth/login');
    }

    public function login_admin()
    {
        $session = session();
        $input = (object) json_decode(request()->getContent());
        // $result = Recaptcha::verifyV3Captcha($input->captcha);
        // if (!($result->success && $result->score > 0.7)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Unable to resolve captcha.',
        //     ], 401);
        // }
        
        $email = trim($input->email);
        $password = trim($input->password);
        $lat = $input->location->lat ?? null;
        $lon = $input->location->lon ?? null;
        $browser = $input->browser;
        $device = $input->device;
        $os = $input->os;

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

        $admin = new Admin();
        $adminData = $admin->where('email', strtolower($email))->first();
        if ($adminData) {
            //check for login attempts exceeded more than 3 in 15 minutes

            $loginAttempt = new \App\Models\LoginAttempt();
            $loginAttempt->where('admin_id', $adminData['id'])
                ->where('created_at', '>=', date("Y-m-d H:i:s", strtotime("-15 minutes")))
                ->where('verified', -1);

            $loginAttemptCount = $loginAttempt->count();
            if ($loginAttemptCount >= 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Too many login attempts. Please try again after 15 Minutes.',
                ], 429);
            }

            //add login Attempt 
            $loginAttempt = new \App\Models\LoginAttempt();
            $loginAttempt->create([
                "ip" => request()->ip(),
                "browser" => $browser,
                "device" => $device,
                "os" => $os,
                "lat" => $lat,
                "log" => $lon,
                "session_id" => session_id(),
                "admin_id" => $adminData['id'],
                "verified" => 0
            ]);

            $loginAttemptId = $loginAttempt->getConnection()->getPdo()->lastInsertId();
        }

        if (!$adminData || !password_verify($password, $adminData['password'])) {
            //login attempt failed
            if($adminData){
              $loginAttempt->where('id', $loginAttemptId)->update([
                  "verified" => -1,
                  "admin_id" => $adminData['id']
              ]);
           }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Credentials',
            ], 401);

        }

        //login attempt verified
        $loginAttempt->where('id', $loginAttemptId)->update([
            "verified" => 1,
            "admin_id" => $adminData['id']
        ]);

        if ($adminData['active'] == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }
        
        //insert logs to login_logs table
        $session_id = session_id();
        $login = new \App\Models\Login();
        $login->create([
            "admin_id" => $adminData['id'],
            "ip" => request()->ip(),
            "browser" => $browser,
            "device" => $device,
            "os" => $os,
            "lat" => $lat,
            "lon" => $lon,
            "session_id" => $session_id,
            "verified" => 0,
            "email_verified" => 0,
            "phone_verified" => 0
        ]);
        
        $browserToken = request()->cookie('browser_token');
        if(strlen($browserToken) > 5){
            $loginBrowserModel = new \App\Models\LoginBrowser();
            $browser = $loginBrowserModel->findBrowserToken($browserToken);
            if(isset($browser['admin_id']) && $browser['admin_id'] == $adminData['id']){
                //update login log
                $login->where("session_id",$session_id)->update([
                   "phone_verified" => $browser['phone_verified'],
                   "email_verified" => $browser['email_verified']
                ]);
                
                session()->put('admin_id', $adminData['id']);
                
                $admin = new \App\Models\Admin();
                $admin->where('id', $adminData['id'])->update([
                   "current_session_id" => session_id()
                ]);
                
                return response()->json([
                   'status' => 'success',
                   'otpRequired' => false,
                   'message' => 'Login Successful !',
                ], 200);
                
            }
        }
        

        //send otp to email & phone
        $emailOtp = random_int(111111, 999999);
        $session->put('email_otp', $emailOtp);
        $session->put('otp_expires', time() + self::OTP_EXPIRY_SECONDS);

        //send email
        //send email
        \Illuminate\Support\Facades\Mail::raw("Your OTP code for login is: $emailOtp", function ($message) use ($adminData) {
            $message->to($adminData['email'])
                    ->subject('Your OTP for Login');
        });
        
        return response()->json([
            'status' => 'success',
            'otpRequired' => true,
            'message' => 'Login OTP sent successfully',
        ], 200);

    }

    public function verify_login()
    {
        //check if there is login log is present in login_logs with current session id within 15 minutes
        $session = session();
        $login = new \App\Models\Login();
        $loginLog = $login->where('session_id', session_id())->orderBy('id', 'DESC')->where('verified', 0)
        ->where('created_at', '>=', date("Y-m-d H:i:s", strtotime("-15 minutes")))
        ->first();
        if (!$loginLog) {
            return redirect('/admin/login');
        }
        $admin = new Admin();
        $adminData = $admin->where('id', $loginLog['admin_id'])->first();
        return view('auth/verify_login',[ "email" => $adminData['email'], "phone" => $adminData['phone'] ]);
    }

    public function verify_login_otp(){
        //captcha verification
        $input = (object) json_decode(request()->getContent());
        // $result = Recaptcha::verifyV3Captcha($input->captcha);
        // if (!($result->success && $result->score > 0.7)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Unable to resolve captcha.',
        //     ], 401);
        // }
        $session = session();

        //check if there is login log is present in login_logs with current session id within 15 minutes
        $login = new \App\Models\Login();
        $loginLog = $login->where('session_id', session_id())->orderBy('id', 'DESC')->where('verified', 0)
            ->where('created_at', '>=', date("Y-m-d H:i:s", strtotime("-15 minutes")))
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

        //check if otp matching with email otp .

        if (
            !ctype_digit($input->otp) || strlen($input->otp) != 6
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please Enter valid OTP',
            ], 400);
        }
        if ($session->get("otp_expires") < time()) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP Expired. Please resend OTP.',
            ], 400);
        }
        if ($input->otp != $session->get("email_otp")) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please Enter valid OTP',
            ], 400);
        }

        $emailVerified = $input->otp == $session->get("email_otp") ? 1 : 0;
        $phoneVerified = 0;


        //login attempt verified
        $login->where('id', $loginLog['id'])->update([
            "verified" => 1,
            "email_verified" => $emailVerified,
            "phone_verified" => $phoneVerified
        ]);
        
        $admin = new \App\Models\Admin();
        $admin->where('id', $loginLog['admin_id'])->update([
           "current_session_id" => session_id()
        ]);
        
        $loginBrowserModel = new \App\Models\LoginBrowser();
        $browserToken = substr(bin2hex(random_bytes(30)), 0, 30);
        $loginBrowserModel->create([
           "admin_id" => $loginLog['admin_id'],
           "browser_token" => $browserToken,
           "phone_verified" => $phoneVerified,
           "email_verified" => $emailVerified
        ]);
        
        session()->put('admin_id', $loginLog['admin_id']);
        session()->forget("email_otp");
        session()->forget("phone_otp");
        session()->forget("otp_expires");
        return response()->json([
            'status' => 'success',
            'message' => 'Login Successful',
        ], 200)->cookie("browser_token", $browserToken, 1440, "/", null, true, true);
    }

    public function forgot_password()
    {
        $session = session();
        if ($session->get('admin_id')) {
            return redirect('/admin/dashboard');
        }
        return view('auth/forgot_password');
    }

    public function logout()
    {
        $session = session();
        $session->flush();
        return redirect('/admin/login');
    }

    public function ad_login()
    {
        $adToken = request()->query('ad_token');
        if (!empty($adToken) && strlen($adToken) > 10) {
            $userModel = new \App\Models\User();
            $user = $userModel->where("ad_token", $adToken)->first();
            if ($user) {
                $partnerUrl = env("partnerURL") ?? env("frontendURL") ?? "http://localhost:8001/";
                $parsedPort = parse_url($partnerUrl, PHP_URL_PORT);
                $parsedHost = parse_url($partnerUrl, PHP_URL_HOST);
                // If partnerUrl points back to the current admin server (port 8000), redirect to default partner port 8001
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