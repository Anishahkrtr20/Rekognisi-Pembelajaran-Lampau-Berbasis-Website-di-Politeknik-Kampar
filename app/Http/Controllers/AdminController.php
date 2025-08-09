<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\asesor_rekap;
use App\Models\asesorAssesment;
use App\Models\Daftar;
use App\Models\phoneContact;
use App\Models\student_biodata;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(){
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        $dataStudent = Daftar::with(['studentBiodata', 'asesor_vatm', 'asesor_rekap', 'user'])
                    ->select('daftar.*')
                    ->leftJoin('student_biodata', 'daftar.id', '=', 'student_biodata.daftar_id')
                    ->leftJoin('asesor_vatm', 'daftar.id', '=', 'asesor_vatm.daftar_id')
                    ->leftJoin('asesor_rekap', 'daftar.id', '=', 'asesor_rekap.daftar_id')
                    ->leftJoin('users', 'daftar.user_id', '=', 'users.id')
                    // ->groupBy('daftar.id')
                    ->distinct('daftar.id')
                    ->get();
        // dd($dataStudent);
                    
        // untuk Persen Progress
        $dataBiodata = Daftar::with([
            'studentBiodata.Experiences.student_pilihBukti.student_uploads.student_asessment','user','student_profile','student_matkul.prodi'
            ])
            ->select('daftar.*')
            ->leftJoin('student_biodata', 'daftar.id', '=', 'student_biodata.daftar_id')
            ->leftJoin('student_experience', 'daftar.id', '=', 'student_experience.daftar_id')
            ->leftJoin('student_matkul', 'daftar.id', '=', 'student_matkul.daftar_id')
            ->leftJoin('student_pilihbukti', 'daftar.id', '=', 'student_pilihbukti.daftar_id')
            ->leftJoin('student_uploads', 'daftar.id', '=', 'student_uploads.daftar_id')
            ->leftJoin('student_asessment', 'daftar.id', '=', 'student_asessment.daftar_id')
            ->leftJoin('student_profile', 'daftar.id', '=', 'student_profile.daftar_id')
            ->leftJoin('users', 'daftar.user_id', '=', 'users.id')
            // ->groupBy('daftar.id')
            ->distinct('daftar.id')
            ->get();
        // dd($dataBiodata);

        // Calculate progress for each student
        foreach ($dataBiodata as $item) {
            $totalCount = $item->studentBiodata->count();
            $completedCount = $item->studentBiodata->where('status', 1)->count();

            // Avoid division by zero
            $item->progress_percentage = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
        }
        // dd($item);

        $countStudent = DB::table('daftar')
                    ->join('users', 'daftar.user_id', '=', 'users.id')
                    ->where('users.is_active', '1')
                    ->count();
        // dd($countStudent);

        $countAsessment = asesor_rekap::where('status_kirim', '1')
                    ->distinct('daftar_id')
                    ->count('daftar_id');
                    
        $title = "Dashboard | SI-RPL";
        $page = "Dashboard";
        return view('Admin.index', compact('title', 'page', 'name','dataStudent','countStudent','countAsessment','dataBiodata'));
    }

    public function indexAsesor(){
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();
        $title = "Data Asesor | SI-RPL";
        $page = "Data Asesor";

        $asesor = Asesor::with(['prodi'])->get();
        return view('Admin.asesor.index', compact('title', 'page', 'name','asesor'));
    }

    public function contact(Request $request){
        // Validasi data input
        $request->validate([
            'phone_number' => 'required|string|max:15', // Sesuaikan validasi
        ]);

        // Perbarui data di tabel phone_contact
        DB::table('phone_contact')->updateOrInsert(
            ['id' => 1], // Pastikan ID sesuai, gunakan parameter lain jika ID tidak ada
            ['phone_number' => $request->phone_number, 'updated_at' => now()]
        );

        return redirect()->back()->with('success', 'Nomor Contact berhasil di tambahkan');
    }
}
