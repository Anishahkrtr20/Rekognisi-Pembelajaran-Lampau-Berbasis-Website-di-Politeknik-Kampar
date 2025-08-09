<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Prodi;

class ProdiController extends Controller
{
    //
    public function index(){
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        $title = "Data Prodi | SI-RPL";
        $page = "Data Prodi";
        
        // Mengambil data role dari database
        $prodi = Prodi::all(); // Menggunakan 'roles', bukan 'role'

        // Mengirim data ke view
        return view('Admin.prodi.index', compact('title', 'page', 'name','prodi'));
    }

    public function store(Request $request){
        $request->validate([
            'nama_prodi' => 'required'
        ]);

        Prodi::create([
            'nama_prodi'=> $request->input('nama_prodi')
        ]);

        return redirect()->route('prodi')->with('success', 'Data Prodi berhasil di tambahkan',);
    }

    public function edit(Request $request, $id){
        $prodi = Prodi::find($id);
        $title = "Edit Data Prodi | SI-RPL";
        $page = "Edit Data Prodi";
        $name = DB::table('users')->where('email', Auth::user()->email)->first();
        return view('Admin.prodi.edit', compact('title', 'page', 'name', 'prodi'));
    }

    public function update(Request $request, $id){
        $request->validate([
            'nama_prodi'
            ]);
            
            $prodi = Prodi::find($id);
            $prodi->nama_prodi = $request->input('nama_prodi');
            $prodi->save();
            return redirect()->route('prodi')->with('success', 'Data Prodi berhasil di update');
    }

    public function hapus($id){
        $prodi = Prodi::findOrFail($id);
        
        $prodi->delete();
        return redirect()->route('prodi')->with('success', 'Data Prodi berhasil di hapus');
    }
}
