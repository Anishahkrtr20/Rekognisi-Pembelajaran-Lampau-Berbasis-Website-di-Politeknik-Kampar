<?php

namespace App\Http\Controllers;

use App\Models\asesor_rekap;
use App\Models\asesor_vatm;
use App\Models\asesorAssesment;
use App\Models\Daftar;
use App\Models\Education;
use App\Models\Experiences;
use App\Models\student_asessment;
use App\Models\student_biodata;
use App\Models\student_Matkul;
use App\Models\student_pilihBukti;
use App\Models\student_profile;
use App\Models\student_uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class simpanPermanenController extends Controller
{
    //

    public function index(){
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        // dd($name);

        $daftar = Daftar::where('user_id', $name->id)->first();
        // dd($daftar);
        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        $biodata = student_biodata::where('daftar_id', $daftar->id)->first();
        // dd($biodata);

        $pendidikan = Education::where('daftar_id', $daftar->id)->first();
        // dd($pendidikan);

        $pengalaman = Experiences::where('daftar_id', $daftar->id)->first();

        $student_matkul = student_Matkul::where('daftar_id', $daftar->id)->first();

        // pengecekan semua data pilih Bukti
        $pilihBukti = student_pilihBukti::where('daftar_id', $daftar->id)->get();

        // Initialize the status result variable
        $statusResult = student_pilihBukti::where('daftar_id', $daftar->id)->first();

        // dd($statusResult);

        // pengecekan semua data Asessment Mandiri
        $asesmentMandiri = student_asessment::where('daftar_id', $daftar->id)->get();

        // Initialize the status result variable
        $statusAsesment = student_asessment::where('daftar_id', $daftar->id)->first();

        // dd($statusAsesment);

        $title = "Hasil Rekognisi | SI-RPL";
        $page = "Hasil Rekognisi";

        // Mengirim ke view
        return view('Pendaftar.simpan.index', compact('title', 'page', 'name','daftar','biodata', 'pendidikan','pengalaman','statusResult','statusAsesment','student_matkul','avatar'));
    }

    public function update(Request $request, $daftar_id)
    {
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();

        if (!$name) {
            return redirect()->back()->with('error', 'User not found');
        }

        $daftar = Daftar::where('user_id', $name->id)->first();
        if (!$daftar) {
            return redirect()->back()->with('error', 'Registration data not found');
        }

        // Retrieve related data
        $biodata = student_biodata::where('daftar_id', $daftar->id)->get();
        $pengalaman = Experiences::where('daftar_id', $daftar->id)->get();
        $student_matkul = student_Matkul::where('daftar_id', $daftar->id)->get();
        $pilihBukti = student_pilihBukti::where('daftar_id', $daftar->id)->get();
        $uploadBukti = student_uploads::where('daftar_id', $daftar->id)->get();
        $asesmentMandiri = student_asessment::where('daftar_id', $daftar->id)->get();

        // Update each collection
        foreach ($biodata as $item) {
            $item->editable = '1';
            $item->save();
        }

        foreach ($pengalaman as $item) {
            $item->editable = '1';
            $item->save();
        }

        foreach ($student_matkul as $item) {
            $item->editable = '1';
            $item->save();
        }

        foreach ($pilihBukti as $item) {
            $item->editable = '1';
            $item->save();
        }

        foreach ($uploadBukti as $item) {
            $item->editable = '1';
            $item->save();
        }

        foreach ($asesmentMandiri as $item) {
            $item->editable = '1';
            $item->save();
        }

        return redirect()->route('pendaftar')->with('success', 'Data successfully updated and submitted.');
    }
}
