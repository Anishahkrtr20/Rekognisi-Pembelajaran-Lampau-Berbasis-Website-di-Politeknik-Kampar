<?php

namespace App\Http\Controllers;
use App\Models\Daftar;
use App\Models\Education;
use App\Models\student_asessment;
use App\Models\student_biodata;
use App\Models\student_Matkul;
use App\Models\student_pilihBukti;
use App\Models\student_uploads;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class InfoDaftarController extends Controller
{
    //
    public function index(){
        $pendaftar = Daftar::all();

        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        $title = "Info Pendaftar | SI-RPL";
        $page = "Info pendaftar";

        // dd($pendaftar);
        return view('Admin.pendaftar.index', compact('pendaftar', 'page', 'title', 'name'));
    }

    public function status($id){
        
        $pendaftar = Daftar::find($id);
        // $users = User::find($id);
        $user = User::where('id', $pendaftar->user_id)->first();
        // dd($user);

        $dataDiri = student_biodata::where('daftar_id', $id)->first();
        $education = Education::where('daftar_id', $id)->first();
        $student_matkul = student_Matkul::where('daftar_id', $id)->first();
        $student_upload = student_uploads::where('daftar_id', $id)->first();
        $studen_pilihBukti = student_pilihBukti::where('daftar_id', $id)->first();
        $student_asessment = student_asessment::where('daftar_id', $id)->first();

        $student = Daftar::with(['user'])->where('id', $id)->get();
        // dd($student);
        
        $title = "Info Pendaftar | SI-RPL";
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();
        $page = "Info Pendaftar";

        // dd($pendaftar);
        return view('Admin.pendaftar.status', compact('pendaftar','user','page', 'title', 'name','dataDiri','education','student_matkul','student_upload','studen_pilihBukti','student_asessment','student'));
    }
}
