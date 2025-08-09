<?php

namespace App\Http\Controllers;

use App\Models\Daftar;
use App\Models\student_biodata;
use App\Models\student_profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class profileController extends Controller
{
    //
    public function index(){

        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        $title = "Profile | SI-RPL";
        $page = "Profile Daftar";

        $biodata = Daftar::where('user_id', $name->id)->first();
        // dd($biodata);
        $avatar = student_profile::where('daftar_id', $biodata->id)->first();

        $mahasiswa = student_biodata::where('daftar_id', $biodata->id)->first();

        $student_biodata = student_biodata::where('daftar_id',$biodata->id)->first();

        return view('Pendaftar.profile.index', compact('page', 'title', 'name','biodata','avatar','mahasiswa','student_biodata'));
    }

    public function storeImage(Request $request, $id)
    {
        // Validasi file
        $request->validate([
            'daftar_id' => 'required|string',     
            'file' => 'required|image|mimes:jpeg,jpg,png|max:2048', // ukuran max 2MB
        ]);

        $profile = $request->file('file');
        $filename = time() . '_' . $profile->getClientOriginalName();
        $filePath = '/profile/' . $filename;

        $profile->move(public_path('profile/'), $filename);

        // Periksa apakah sudah ada data dengan daftar_id yang sama
        $existingProfile = student_profile::where('daftar_id', $id)->first();

        if ($existingProfile) {
            // Hapus file lama jika ada
            $oldFilePath = public_path($existingProfile->file);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Hapus data lama dari database
            $existingProfile->delete();
        }

        // Simpan data baru
        $upload = new student_profile;
        $upload->daftar_id = $request->input('daftar_id');
        $upload->file = $filePath;
        $upload->save();

        return redirect()->route('profile.Pendaftar')->with('success', 'Foto Profil berhasil ditambahkan');
    }

    public function updateDaftar(Request $request, $id)
    {
        // Validate incoming request data
        $request->validate([
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|string',
            'kebangsaan' => 'required|string',
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
        ]);

        // Retrieve the user based on the logged-in email
        $user = \DB::table('users')->where('email', Auth::user()->email)->first();

        // Retrieve the Daftar record by ID
        $daftar = Daftar::findOrFail($id);

        // Update the Daftar record
        $daftar->user_id = $user->id; // Set user_id from the authenticated user
        $daftar->nama = $request->input('nama');
        $daftar->tempat_lahir = $request->input('tempat_lahir');
        $daftar->tanggal_lahir = $request->input('tanggal_lahir');
        $daftar->jenis_kelamin = $request->input('jenis_kelamin');
        $daftar->kebangsaan = $request->input('kebangsaan');
        $daftar->no_hp = $request->input('no_hp');
        $daftar->alamat = $request->input('alamat');
        $daftar->status = 1;
        
        // Save the updated record
        $daftar->save();

        // Redirect back to the profile page with a success message
        return redirect()->route('profile.Pendaftar')->with('success', 'Data berhasil di Update!');
    }

    public function updateUser(Request $request, $id)
    {
        // Validate the form data
        $request->validate([
            'password_old' => 'required',
            'password' => 'required|confirmed|min:8', // You can adjust the min length as needed
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Check if the old password matches the one in the database
        if (!Hash::check($request->password_old, $user->password)) {
            // If passwords don't match, redirect back with an error message
            return redirect()->back()->with('error', 'Password yang anda masukkan tidak sesuai.');
        }

        // If passwords match, update the password with the new one
        $user->password = Hash::make($request->password);
        $user->updated_at = now();

        // Save the user
        $user->save();

        // Redirect to the profile page with a success message
        return redirect()->route('profile.Pendaftar')->with('success', 'Password successfully updated');
    }
}
