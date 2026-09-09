<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * NFR Hak Akses — otorisasi diperiksa di level controller melalui
 * Policy/Gate, selain di level route dan tampilan Blade.
 */
abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;
}
