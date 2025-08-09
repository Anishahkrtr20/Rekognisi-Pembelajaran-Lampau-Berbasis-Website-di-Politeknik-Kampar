<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Matkul;
use App\Models\Prodi;


class MatkulController extends Controller
{
    //
    public function index(){
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        $title = "Data Matkul | SI-RPL";
        $page = "Data Matkul";

        // $matkul = Matkul::join('prodi', 'matkul.prodi_id', '=', 'prodi.id')
        //         ->select('matkul.*','matkul.id as id_matkul', 'prodi.*' )
        //         ->get(); 

        $matkul = Matkul::with(['Cpl.Prodi'])->get(); // Mengambil semua data CPL bersama relasi Prodi

        // dd($matkul);
        $cpl = Cpl::all();
        $prodi = Prodi::all();

        return view('Admin.matkul.index', compact('title', 'page', 'name','matkul', 'cpl', 'prodi'));
    }

    public function store(Request $request){
         $validated = $request->validate([
            'kode_matkul' => 'required',
            'nama_matkul' => 'required|string|max:255',
            'sks' => 'required|integer|max:999', // Adjusted max value assuming it should be up to 999 SKS
            'cpl_id' => 'required',
            'prodi_id' => 'required',
        ]);

        $matkul = new Matkul;
        $matkul->kode_matkul = $validated['kode_matkul'];
        $matkul->nama_matkul = $validated['nama_matkul'];
        $matkul->sks = $validated['sks'];
        $matkul->cpl_id = $validated['cpl_id'];
        $matkul->prodi_id = $validated['prodi_id'];
        
        $matkul->save();

        Session::flash('success', 'Data Matkul berhasil diubah');
        return redirect()->route('matkul')->with('success', 'Data Matkul berhasil di tambahkan');
        // var_dump($matkul);
    }

    public function edit(Request $request, $id){
        $matkul = Matkul::find($id);
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();
        
        $prodii = Prodi::all();

        $cpl = Cpl::all();

        $title = "Edit Data Matkul | SI-RPL";
        $page = "Edit Data Matkul";
        return view('Admin.matkul.edit', compact('title', 'page', 'name', 'matkul', 'prodii','cpl'));
    }

    public function update(Request $request,$id){
        $request->validate([
            'kode_matkul' => 'required',
            'nama_matkul' => 'required|string|max:255',
            'sks' => 'required|integer|max:999', // Adjusted max value assuming it should be up to 999 SKS
            'prodi_id' => 'required',
            'cpl_id' => 'required',
        ]);

        $matkul = Matkul::findOrFail($id);

        $matkul->kode_matkul = $request->kode_matkul;
        $matkul->nama_matkul = $request->nama_matkul;
        $matkul->sks = $request->sks;
        $matkul->cpl_id = $request->cpl_id;
        $matkul->prodi_id = $request->prodi_id;

        $matkul->save();

        return redirect()->route('matkul')->with('success', 'Matkul Updated Successfully');
    }

    public function hapus($id){
        $matkul = Matkul::find($id);
        $matkul->delete();
        return redirect()->route('matkul')->with('success', 'Data Matkul berhasil di hapus');
    }
}
