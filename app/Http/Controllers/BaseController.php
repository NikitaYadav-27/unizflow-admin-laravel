<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelController;
use App\Traits\FileUploadTrait;

 
abstract class BaseController extends LaravelController
{
    use AuthorizesRequests, ValidatesRequests, FileUploadTrait;
}
