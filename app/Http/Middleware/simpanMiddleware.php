<?php

namespace App\Http\Middleware;

use App\Models\Daftar;
use App\Models\student_biodata;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class simpanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $user = Auth::user();
        $daftar = Daftar::where('user_id', $user->id)->first();

        // dd($daftar);

        if (!$daftar) {
            return redirect()->route('pendaftar')->with('error', 'Data Daftar not found.');
        }

        $student = student_biodata::where('editable', 1)
            ->where('daftar_id', $daftar->id)
            ->first();

        if ($student) {
            return redirect()->route('pendaftar')
                ->with('error', 'Kamu sudah melakukan simpan Permanen');
        }

        return $next($request);
    }
}
