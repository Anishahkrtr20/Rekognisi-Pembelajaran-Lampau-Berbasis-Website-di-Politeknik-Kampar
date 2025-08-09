<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Daftar;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Log;
use App\Notifications\UserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class DaftarController extends Controller
{
    //
    public function index(){
        $title = "Daftar SIRPL Politeknik Kampar";
        return view('Daftar/index', compact('title'));
    }

    // public function store(Request $request){
    //     // Validasi input
    //     $request->validate([
    //         'nama' => 'required|string|max:255',
    //         'tempat_lahir' => 'required|string|max:255',
    //         'tanggal_lahir' => 'required|date',
    //         'jenis_kelamin' => 'required|string',
    //         'kebangsaan' => 'required|string',
    //         'no_hp' => 'required|string|max:15',
    //         'alamat' => 'required|string',
    //         'email' => 'required|string|email|max:255|unique:users,email',
    //         'username' => 'required|string|max:255|unique:users,name',
    //         'password' => 'required|string',
    //     ]);

    //     // Simpan data ke tabel user
    //     $user = new User();
    //     $user-> name = $request->input('username');
    //     $user-> email = $request->input('email');
    //     $user-> password = bcrypt($request->input('password'));
    //     $user-> is_active = 0;
    //     $user-> status = 3;
    //     $user-> remember_token = Str::random(60);
    //     $user-> save();

    //     $activationUrl = route('user.activate', ['token' => $user->remember_token]);

    //     // $user = User::create([
    //     //         'name' => $request->input('name'),
    //     //         'email' => $request->input('email'),
    //     //         'password' => bcrypt($request->password), //hash password sebelum di simpan
    //     //         'is_active'=> 0,
    //     //         'status' => 3
    //     //     ]);
            
    //     // Buat entri baru dalam tabel daftar
    //     $daftar = new Daftar();
    //     $daftar->user_id = $user->id; // Ambil id dari user
    //     $daftar->nama = $request->input('nama');
    //     $daftar->tempat_lahir = $request->input('tempat_lahir');
    //     $daftar->tanggal_lahir = $request->input('tanggal_lahir');
    //     $daftar->jenis_kelamin = $request->input('jenis_kelamin');
    //     $daftar->kebangsaan = $request->input('kebangsaan');
    //     $daftar->no_hp = $request->input('no_hp');
    //     $daftar->alamat = $request->input('alamat');
    //     $daftar->status = 1;
        
    //     $user->notify(new UserRegistered($user, $activationUrl, $daftar));
    //     // Simpan data ke database
    //     $daftar->save();
    //     // Redirect atau response sesuai kebutuhan
    //     return redirect()->route('login')->with('success', 'Data berhasil disimpan!');

    // }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|string',
            'kebangsaan' => 'required|string',
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users,email',
            'username' => 'required|string|max:255|unique:users,name',
            'password' => 'required|string',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
        ]);

        DB::beginTransaction();

        try {
            $user = new User();
            $user->name = $request->input('username');
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password'));
            $user->is_active = 0;
            $user->status = 3;
            $user->remember_token = Str::random(60);
            $user->save();

            $activationUrl = route('user.activate', ['token' => $user->remember_token]);

            $daftar = new Daftar();
            $daftar->user_id = $user->id;
            $daftar->nama = $request->input('nama');
            $daftar->tempat_lahir = $request->input('tempat_lahir');
            $daftar->tanggal_lahir = $request->input('tanggal_lahir');
            $daftar->jenis_kelamin = $request->input('jenis_kelamin');
            $daftar->kebangsaan = $request->input('kebangsaan');
            $daftar->no_hp = $request->input('no_hp');
            $daftar->alamat = $request->input('alamat');
            $daftar->status = 1;
            $daftar->save();

            try {
                $user->notify(new UserRegistered($user, $activationUrl, $daftar));
            } catch (\Exception $notificationException) {
                // Log::error('Gagal mengirim notifikasi: ' . $notificationException->getMessage());
                return redirect()->route('register')->with('error', 'Data berhasil disimpan, tetapi notifikasi gagal dikirim.');
            }

            DB::commit();

            return redirect()->route('login')->with('success', 'Data berhasil disimpan, silahkan cek Email atau folder SPAM untuk verify akun!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error saat menyimpan data: ' . $e->getMessage());
            // return redirect()->route('register')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            return redirect()->route('register')->with('error', 'Registrasi gagal! Terjadi kesalahan saat menyimpan data. Silakan coba lagi.' . $e->getMessage());
        }
    }

    public function activate($token)
    {
        $user = User::where('remember_token', $token)->firstOrFail();

        $user->update([
            'is_active' => 1,
            'remember_token' => null
        ]);

        return redirect()->route('login')->with('success', 'Akun Sudah Diaktifkan');
    }
}
