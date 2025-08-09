<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    //

    public function login()
    {
        $title = "Login SIRPL Politeknik Kampar";
        return view('Login/index', compact('title'));
    }

    public function loginaksi(Request $request)
    {
        // Validasi input form menggunakan Validator
        $validator = Validator::make($request->all(), [
            'username' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required|recaptcha',  // Menggunakan validasi reCAPTCHA
        ]);

        // Periksa apakah validasi gagal
        if ($validator->fails()) {
            return redirect('/login')
                ->with('error', 'Terjadi kesalahan Captcha. Silakan coba lagi.');
        }

        try {
            // Menyiapkan data untuk login
            $data = [
                'email' => $request->input('username'),
                'password' => $request->input('password')
            ];

            // Coba login dengan Auth
            if (Auth::attempt($data)) {
                $user = Auth::user();

                if ($user->is_active == 1) {
                    $request->session()->put('name', $user->name);

                    // Redirection berdasarkan status user
                    if ($user->status == 1) {
                        return redirect('admin')->with('name', $user->name);
                    } elseif ($user->status == 2) {
                        return redirect('asesor')->with('name', $user->name);
                    } else {
                        return redirect('pendaftar')->with('name', $user->name);
                    }
                } else {
                    // Jika akun tidak aktif
                    Auth::logout();
                    // Session::flash('error', 'Akun belum diaktifkan. Silakan hubungi administrator.');
                    return redirect('/login')->with('error', 'Akun belum diaktifkan. Silakan hubungi administrator.');
                }
            } else {
                // Jika email atau password salah
                // Session::flash('error', 'Email atau Password salah.');
                return redirect('/login')->with('error', 'Email atau Password salah.');
            }
        } catch (\Exception $e) {
            // Tangani kesalahan yang terjadi
            return redirect('/login')->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    public function logout(){
        Auth::logout();
        Session::flash('logout', 'Akun anda sudah logout.');
        return redirect('/login');
    }
    
}
