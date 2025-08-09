<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prodi;
use App\Models\Asesor;
use App\Models\asesor_rekap;
use App\Models\asesor_vatm;
use App\Models\asesorAssesment;
use App\Models\Daftar;
use App\Models\Matkul;
use App\Models\student_pilihBukti;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


use Illuminate\Support\Facades\Session;

class AsesorController extends Controller
{
    //
    public function index(){
        $asesorId = Asesor::where('user_id', Auth::user()->id)->first()->id;
        // dd($asesorId);
        // Get the user's information based on email
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        // Filter asesorAssesment by relevant daftar_id and asesor_id
        $dataStudent = asesorAssesment::with(['daftar.user', 'student_biodata', 'asesor_vatm', 'asesor_rekap'])
                                    ->where('asesor_id', $asesorId) // Only get records with this asesor_id
                                    ->get();
        // dd($dataStudent);

        $countStudent = DB::table('daftar')
                    ->join('users', 'daftar.user_id', '=', 'users.id')
                    ->where('users.is_active', '1')
                    ->count();
        // dd($countStudent);

        $countAsessment = asesor_rekap::where('status_kirim', '1')
                    ->distinct('daftar_id')
                    ->count('daftar_id');
        // dd($countAsessment);

        // Set the page title and other view variables
        $title = "Data Asesor | SI-RPL";
        $page = "Dashboard";

        return view('Asesor.index', compact('page', 'title', 'name','dataStudent','asesorId','countStudent','countAsessment'));
    }

    public function indexVatm(){
        // Fetch the current user's asesor_id (you can adjust this based on your authentication)

        // $asesorId = Auth::user()->id;
        $asesorId = Asesor::where('user_id', Auth::user()->id)->first()->id;
        // dd($asesorId);

        // Get the user's information based on email
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();
        // dd($name);

        // Filter asesorAssesment by relevant daftar_id and asesor_id
        $dataStudent = asesorAssesment::with(['daftar', 'student_biodata', 'asesor_vatm'])
                                    ->where('asesor_id', $asesorId) // Only get records with this asesor_id
                                    ->get();

        // Debugging: check the data
        // dd($dataStudent);

        $daftar = asesorAssesment::where('asesor_id',$asesorId)->first();
        // dd($daftar);

        // $student = asesor_rekap::where('daftar_id', $daftar->daftar_id)
        //     ->where('asesor_id', $asesorId)
        //     ->first();

        // $checkStatus = asesor_rekap::where('daftar_id', $daftar->daftar_id)->get();
        
        // Set the page title and other view variables
        $title = "Data Asesor | SI-RPL";
        $page = "Dashboard";

        // Return the view with the data
        return view('Asesor.penilaian.index', compact('page', 'title', 'name', 'dataStudent','asesorId'));
    }

    public function indexStoreVatm($daftar_id)
    {
        // Ambil user yang sedang login
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();

        // Ambil ID asesor berdasarkan user login
        $asesor = Asesor::where('user_id', $name->id)->first();
        $asesor_id = $asesor ? $asesor->id : null;

        if (!$asesor_id) {
            return redirect()->back()->with('error', 'Asesor data not found for the logged-in user.');
        }

        $title = "Store Asesor | SI-RPL";
        $page = "Dashboard";

        $daftar = daftar::find($daftar_id);

        // Ambil data asesor_assesment berdasarkan daftar_id dan asesor_id
        $asesor_assesment = asesorAssesment::where('daftar_id', $daftar_id)
            ->where('asesor_id', $asesor_id)
            ->first();
            
        if (!$asesor_assesment) {
            return redirect()->back()->with('error', 'Assessment data not found.');
        }

        $prodi_id = $asesor_assesment->prodi_id;
        $daftarid = $asesor_assesment->daftar_id;

        $checkStatus = asesor_rekap::where('daftar_id', $daftarid)
                ->where('asesor_id', $asesor_id)
                ->where('status_kirim', 1)
                ->first();
        // dd($checkStatus);

        $dataStudent = \DB::table('asesor_assesment')
            ->join('student_pilihBukti', 'asesor_assesment.daftar_id', '=', 'student_pilihBukti.daftar_id')
            ->join('student_uploads', 'student_pilihBukti.bukti_id', '=', 'student_uploads.id')
            ->leftJoin('pilihCpmk', 'asesor_assesment.prodi_id', '=', 'pilihCpmk.id')
            ->leftJoin('matkul', 'student_pilihBukti.matkul_id', '=', 'matkul.id')
            ->leftJoin('cpl', 'matkul.cpl_id', '=', 'cpl.id')
            ->leftJoin('prodi', 'prodi.id', '=', 'cpl.prodi_id')
            ->leftJoin('asesor_vatm', function ($join) use ($asesor_id) {
                $join->on('asesor_vatm.daftar_id', '=', 'asesor_assesment.daftar_id')
                    ->on('asesor_vatm.matkul_id', '=', 'student_pilihBukti.matkul_id')
                    ->on('asesor_vatm.asesor_id', '=', \DB::raw($asesor_id)); // Filter sesuai asesor_id
            })
            ->select(
                'asesor_assesment.daftar_id',
                'asesor_assesment.asesor_id',
                'student_pilihBukti.bukti_id', 
                'matkul.id as matkul_id',
                'cpl.id as cpl_id',
                'matkul.kode_matkul',
                'matkul.nama_matkul',
                'student_uploads.file as bukti_file',
                'student_uploads.nama_bukti as nama_bukti',
                'cpl.cpl as cpl_name',
                'asesor_vatm.hasil as vatm_result',
                'asesor_vatm.asesor_id as vatm_asesor_id'
            )
            ->where('asesor_assesment.daftar_id', '=', $daftarid)
            ->where('prodi.id', '=', $prodi_id)
            ->distinct()
            ->get();

            // dd($dataStudent);

        $dataStudentGroup = $dataStudent->unique(function ($item) {
            return $item->matkul_id;
        })->map(function ($item) use ($dataStudent) {
            $item->vatm_result = $item->vatm_result ? explode(',', $item->vatm_result) : [];

            $buktiFiles = $dataStudent->where('matkul_id', $item->matkul_id)
                ->pluck('bukti_file')
                ->unique()
                ->toArray();

            $namaBukti = $dataStudent->where('matkul_id', $item->matkul_id)
                ->pluck('nama_bukti')
                ->unique()
                ->toArray();
            
            return [
                'daftar_id' => $item->daftar_id,
                'asesor_id' => $item->vatm_asesor_id ?? $item->asesor_id,
                'matkul_id' => $item->matkul_id,
                'cpl_id' => $item->cpl_id,
                'kode_matkul' => $item->kode_matkul,
                'nama_matkul' => $item->nama_matkul,
                'cpl_name' => $item->cpl_name,
                'vatm_result' => $item->vatm_result,
                'bukti_files' => $buktiFiles,
                'nama_bukti' => $namaBukti,
            ];
        });

        return view('Asesor.penilaian.store', compact('page', 'title', 'name', 'dataStudentGroup', 'daftar', 'daftar_id', 'asesor_assesment', 'asesor_id','checkStatus'));
    }

    public function store(Request $request, $daftar_id, $asesor_id)
    {
        // Validasi input
        $validated = $request->validate([
            'matkul_id' => 'required|array',
            'matkul_id.*' => 'required|integer',
            'assessment' => 'nullable|array',
            'assessment.*' => 'nullable|array',
            'assessment.*.*' => 'integer|in:1,2,3,4',
        ]);

        // Pastikan asesor yang login sesuai
        $asesor = Asesor::where('user_id', Auth::id())->first();

        if (!$asesor || $asesor->id != $asesor_id) {
            return redirect()->route('indexVatm')->with('error', 'Akses tidak valid untuk asesor ini.');
        }

        // Validasi daftar
        $daftar = daftar::find($daftar_id);

        if (!$daftar) {
            return redirect()->route('indexVatm')->with('error', 'Data pendaftaran tidak ditemukan.');
        }

        // Simpan atau hapus data berdasarkan checkbox yang dicentang
        DB::transaction(function () use ($validated, $daftar_id, $asesor_id) {
            foreach ($validated['matkul_id'] as $index => $matkulId) {
                // Cek apakah ada nilai assessment untuk matkul ini
                $assessmentValues = $validated['assessment'][$index] ?? null;

                if ($assessmentValues) {
                    // Ambil nilai terakhir yang dicentang
                    $lastSelectedAssessmentValue = end($assessmentValues);

                    // Simpan atau perbarui data
                    asesor_vatm::updateOrCreate(
                        [
                            'daftar_id' => $daftar_id,
                            'asesor_id' => $asesor_id,
                            'matkul_id' => $matkulId,
                        ],
                        [
                            'hasil' => $lastSelectedAssessmentValue,
                            'editable' => 1,
                        ]
                    );
                } else {
                // Jika tidak ada checkbox yang dicentang, hapus data dari asesor_vatm
                $asesorVatm = asesor_vatm::where([
                    'daftar_id' => $daftar_id,
                    'asesor_id' => $asesor_id,
                    'matkul_id' => $matkulId,
                ])->first();

                if ($asesorVatm) {
                    // Hapus data terkait di asesor_rekap berdasarkan asesor_id dan daftar_id
                    asesor_rekap::where([
                        'asesor_id' => $asesor_id,
                        'daftar_id' => $daftar_id,
                        'matkul_id' => $matkulId,
                    ])->delete();
                    
                    // Hapus data dari asesor_vatm berdasarkan asesor_id dan daftar_id
                    $asesorVatm->delete();
                    }
                }
            }
        });

        // Setelah transaksi selesai, redirect dengan pesan sukses
        return redirect()->route('indexStoreVatm', ['daftar_id' => $daftar_id, 'asesor_id' => $asesor_id])
                        ->with('success', 'Data berhasil disimpan.');
    }
}
