<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Matkul;
use App\Models\Prodi;
use App\Models\SubMatkul;



class SubMatkulController extends Controller
{
    //
    public function index(){
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        $title = "Data SubMatkul | SI-RPL";
        $page = "Data SubMatkul";

        // $submatkul = SubMatkul::with('Matkul')->get();
        $submatkul = SubMatkul::with(['Matkul.Cpl.Prodi'])->get(); // Mengambil semua data CPL bersama relasi Prodi
        
        // Kelompokkan SubMatkul berdasarkan kode matkul
        $groupedSubMatkul = $submatkul->groupBy(function ($item) {
            return optional($item->Matkul)->kode_matkul;
        });
        // dd($submatkul);

        $matkuls = Matkul::all();

        $prodi = Prodi::all();

        return view('Admin.submatkul.index', compact('title', 'page','prodi','name','submatkul', 'matkuls','groupedSubMatkul'));
    }

    public function store(Request $request){
         $validated = $request->validate([
            'sub_matkul' => 'required|string',
            'matkul_id' => 'required|exists:matkul,id',
            'prodi_id' => 'required|exists:prodi,id',
        ]);

        $submatkul = new SubMatkul;

        $submatkul->sub_matkul = $validated['sub_matkul'];
        $submatkul->matkul_id = $validated['matkul_id'];
        $submatkul->prodi_id = $validated['prodi_id'];

        $submatkul->save();

        Session::flash('success', 'Data SubMatkul berhasil diubah');
        return redirect()->route('submatkul')->with('success', 'Data SubMatkul berhasil di tambahkan');
        // var_dump($matkul);
    }

    public function edit(Request $request, $id){
        $submatkul = SubMatkul::find($id);
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();
        
        // $matkuls = Matkul::all();

        $prodi = Prodi::all();

        // dd($prodi);
        $submatkul1 = SubMatkul::with(['Matkul.Cpl.Prodi'])->get(); // Mengambil semua data CPL bersama relasi Prodi
        
        $title = "Edit Data SubMatkul | SI-RPL";
        $page = "Edit Data SubMatkul";
        return view('Admin.submatkul.edit', compact('title', 'page', 'name', 'submatkul','submatkul1','prodi'));
    }

    public function update(Request $request,$id){
        $request->validate([
            'prodi_id' => 'required|string',
            'sub_matkul' => 'required|string',
            'matkul_id' => 'required|string',
        ]);

        $submatkul = SubMatkul::findOrFail($id);

        $submatkul->prodi_id = $request->prodi_id;
        $submatkul->sub_matkul = $request->sub_matkul;
        $submatkul->matkul_id = $request->matkul_id;

        $submatkul->save();

        return redirect()->route('submatkul')->with('success', 'SubMatkul Updated Successfully');
    }

    public function destroy($id){
        $submatkul = SubMatkul::find($id);
        $submatkul->delete();
        return redirect()->route('submatkul')->with('success', 'Data Matkul berhasil di hapus');
    }
}
