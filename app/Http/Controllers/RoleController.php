<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        // Mengambil data user yang sedang login
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        $title = "Data Role | SI-RPL";
        $page = "Data Role";
        
        // Mengambil data role dari database
        $role = Role::all(); // Menggunakan 'roles', bukan 'role'

        // Mengirim data ke view
        return view('Admin.role.index', compact('title', 'page', 'name', 'role'));
    }

    public function store(Request $request)
    {
        // Validasi data yang masuk
//         $validate = $request -> validate([
// ``          'nama_role' => 'required'
//         ]);
        // $role = new Role;
        // $role -> nama_role = $validate([
        //     'nama_role'
        // ]);

        // $role -> save();

        // Validate data
        $request->validate([
            'nama_role' => 'required|string|max:255'
        ]);

        // Simpan data ke database
        Role::create([
            'nama_role' => $request->input('nama_role')
        ]);
        Session::flash('success', 'Email atau Password Salah');
        return redirect()->route('role')->with('success', 'Data Role berhasil di tambahkan');
    }

    public function hapus($id){
        $role = Role::findOrFail($id);  // Temukan role berdasarkan ID atau berikan 404 jika tidak ditemukan.

        $role->delete();

        return redirect()->route('role')->with('success', 'Role deleted successfully');
    }

    public function editview($id){
        $role = Role::find($id);
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        $title = "Data Role | SI-RPL";
        $page = "Data Role";
        return view('Admin.role.edit', compact('title', 'page', 'role', 'name'));
    }

    public function update(Request $request,$id){
        $role = Role::find($id);

        $role->update($request->all());

        return redirect()->route('role')->with('update', 'Data Role berhasil di Update');
    }
}
