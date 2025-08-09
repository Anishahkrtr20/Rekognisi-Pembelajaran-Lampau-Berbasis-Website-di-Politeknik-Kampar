<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\Prodi;
use App\Models\Asesor;
use Illuminate\Support\Facades\Session;


class UsersController extends Controller
{
    //
    public function index(){
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        // inner join. users.status = role.id
        $user = User::join('role', 'users.status', '=', 'role.id')
                ->select('users.*','users.id as id_user', 'role.nama_role' )
                ->get(); 

        // untuk memanggil data role di tambah data
        $rolee = Role::where('role.id','!=', '3')->get();

        $prodii = Prodi::all();

        $title = "Data User | SI-RPL";
        $page = "Data User";
        return view('Admin.user.index', compact('title', 'page', 'name', 'user', 'rolee', 'prodii'));
    }

    public function store(Request $request)
    {
        // Validasi data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'status' => 'required|integer',
            'nama_asesor' => 'required_if:status,2|string|nullable',
            'jk' => 'required_if:status,2|string|nullable',
            'prodi_id' => 'required_if:status,2|exists:prodi,id|nullable',
            'alamat' => 'required_if:status,2|string|nullable',
            'no_telepon' => 'required_if:status,2|string|nullable',
        ]);

        // Jika validasi lolos, simpan data user
        $user = new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->status = $validated['status'];
        $user->is_active = 0;
        $user->save();

        // Simpan data asesor jika role adalah asesor
        if ($validated['status'] == 2) { // Asesor
            $asesor = new Asesor();
            $asesor->user_id = $user->id; // Foreign key ke tabel users
            $asesor->nama_asesor = $validated['nama_asesor'];
            $asesor->jk = $validated['jk'];
            $asesor->prodi_id = $validated['prodi_id'];
            $asesor->alamat = $validated['alamat'];
            $asesor->no_telepon = $validated['no_telepon'];
            $asesor->save();
        }

        // Redirect dengan pesan sukses
        Session::flash('success', 'Data User berhasil ditambahkan');
        return redirect()->route('user')->with('success', 'Data User berhasil ditambahkan');
    }
    
    public function edituser($id){
        $user = User::find($id);
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();
        
        $rolee = Role::where('role.id','!=', '3')->get();

        $prodii = Prodi::all();

        // $asesor = Asesor::all();
        if ($user && $user->status == 2) {
            $user = User::join('asesor', 'users.id', '=', 'asesor.user_id')
                        ->select('users.*', 'asesor.id as id_asesor', 'asesor.*')
                        ->where('users.id', $id)
                        ->first();
        }

        // $user = User::join('asesor', 'users.id', '=', 'asesor.user_id')
        //             ->select('users.*','users.id as id_user', 'asesor.*' )
        //             ->where('users.id', $id)
        //             ->first(); 

        $title = "Data User | SI-RPL";
        $page = "Data User";
        return view('Admin.user.edit', compact('title', 'page', 'name', 'user', 'rolee', 'prodii'));
    }

    public function updateuser(Request $request,$id){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable',
            'status' => 'required|exists:role,id',
        ]);

        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->email = $request->email;

        if($request->filled('password')){
            $user->password = Hash::make($request->password);
        }

        $user->status = $request->status;

        $user->save();

        if ($user->status != 1) {
            $asesor = Asesor::where('user_id', $user->id)->first();
            if (!$asesor) {
                $asesor = new Asesor();
                $asesor->user_id = $user->id; // Foreign key to users
            }
            $asesor->nama_asesor = $request->input('nama_asesor');
            $asesor->jk = $request->input('jk');
            $asesor->prodi_id = $request->input('prodi_id');
            $asesor->alamat = $request->input('alamat');
            $asesor->no_telepon = $request->input('no_telepon');
            
            $asesor->save();
        }

        return redirect()->route('user')->with('success', 'User Updated Successfully');
    }

    public function status(Request $request, $id){
        $user = User::find($request->id);

        if($user){
            $user->is_active = !$user->is_active;
            $user->save();
            return redirect()->route('user')->with('success', 'User Status Updated Successfully');
        }
        return redirect()->route('user')->with('error', 'User Not Found');
    }

    public function hapus($id){
        $user = User::findOrFail($id);  // Temukan User berdasarkan ID atau berikan 404 jika tidak ditemukan.
        
        $user->delete();

        if ($user->status != 1) {
            $asesor = Asesor::where('user_id', $user->id)->first();
            
            $asesor->delete();
        }

        return redirect()->route('user')->with('success', 'User deleted successfully');
    }

    public function checkName(Request $request)
    {
        $name = $request->input('name');
        $exists = User::where('name', $name)->exists(); // Check if the name exists in the User table

        return response()->json(['exists' => $exists]); // Return JSON response
    }

    public function editPendaftar($id){
        $user = User::find($id);
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();
        
        $title = "Data Edit Pendaftar | SI-RPL";
        $page = "Data Edit Pendaftar";
        return view('Admin.user.pendaftar', compact('title', 'page', 'user','name'));
    }

    public function updatePendaftar(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'password' => 'required|string|min:8|confirmed', // Ensures password matches confirmation
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Update the password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Redirect or return response
        return redirect()->route('user')->with('success', 'Password updated successfully!');
    }
}
