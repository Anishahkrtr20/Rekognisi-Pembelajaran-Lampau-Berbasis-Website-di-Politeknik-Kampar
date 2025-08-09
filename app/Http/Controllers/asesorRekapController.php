<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\asesor_rekap;
use App\Models\asesorAssesment;
use App\Models\Daftar;
use App\Models\Matkul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class asesorRekapController extends Controller
{
    //
    public function index(){
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();

        // Ambil ID asesor berdasarkan user login
        $asesor = Asesor::where('user_id', $name->id)->first();
        $asesor_id = $asesor ? $asesor->id : null;

        // dd($asesor_id);

        if (!$asesor_id) {
            return redirect()->back()->with('error', 'Asesor data not found for the logged-in user.');
        }

        // Filter asesorAssesment by relevant daftar_id and asesor_id
        $dataStudent = asesorAssesment::with(['daftar', 'student_biodata', 'asesor_vatm', 'asesor_rekap'])
                                    ->where('asesor_id', $asesor_id) // Only get records with this asesor_id
                                    ->get();
        // dd($dataStudent);
        // dd($dataStudent->toArray());

        $daftar = asesorAssesment::where('asesor_id',$asesor->id)->first();
        // dd($daftar);

        // $student = asesor_rekap::where('daftar_id', $daftar->daftar_id)
        //     ->where('asesor_id', $asesor_id)
        //     ->first();
        // dd($student);

        // $checkStatus = asesor_rekap::where('daftar_id', $daftar->daftar_id)->get();

        // Set the page title and other view variables\
        $title = "Data Asesor | SI-RPL";
        $page = "Dashboard";

        return view('Asesor.rekap.index', compact('page', 'title', 'name','dataStudent','asesor_id'));
    }

    public function indexStoreRekap($daftar_id)
    {
        // Ambil user yang sedang login
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();

        // Ambil ID asesor berdasarkan user login
        $asesor = Asesor::where('user_id', $name->id)->first();
        $asesor_id = $asesor ? $asesor->id : null;

        // dd($asesor_id);

        if (!$asesor_id) {
            return redirect()->back()->with('error', 'Asesor data not found for the logged-in user.');
        }

        $title = "Store Asesor | SI-RPL";
        $page = "Dashboard";

        $daftar = Daftar::find($daftar_id);

        // dd($daftar);

        // Ambil data asesor_assesment berdasarkan daftar_id dan asesor_id
        $asesor_assesment = asesorAssesment::where('daftar_id', $daftar_id)
            ->where('asesor_id', $asesor_id)
            ->first();

        // dd($asesor_assesment);

        if (!$asesor_assesment) {
            return redirect()->back()->with('error', 'Assessment data not found.');
        }

        $prodi_id = $asesor_assesment->prodi_id;
        $daftarid = $asesor_assesment->daftar_id;
        
        // dd($prodiId);

        $checkStatus = asesor_rekap::where('daftar_id', $daftarid)
                    ->where('asesor_id', $asesor_id)
                    ->where('status_kirim', 1)
                    ->first();

        $dataStudent = \DB::table('asesor_vatm')
                    ->join('asesor_assesment', function ($join) {
                        $join->on('asesor_assesment.daftar_id', '=', 'asesor_vatm.daftar_id')
                            ->on('asesor_assesment.asesor_id', '=', 'asesor_vatm.asesor_id');
                    })
                    ->join('matkul', 'asesor_vatm.matkul_id', '=', 'matkul.id')
                    ->join('cpl', 'matkul.cpl_id', '=', 'cpl.id')
                    ->join('prodi', 'prodi.id', '=', 'cpl.prodi_id')
                    ->leftJoin('student_asessment', function ($join) {
                        $join->on('student_asessment.daftar_id', '=', 'asesor_vatm.daftar_id')
                            ->on('student_asessment.matkul_id', '=', 'asesor_vatm.matkul_id');
                    })
                    ->leftJoin('asesor_rekap', function ($join) use ($daftarid, $asesor_id) {
                        $join->on('asesor_rekap.daftar_id', '=', 'asesor_vatm.daftar_id')
                            ->on('asesor_rekap.matkul_id', '=', 'asesor_vatm.matkul_id')
                            ->where('asesor_rekap.daftar_id', '=', $daftarid)
                            ->where('asesor_rekap.asesor_id', '=', $asesor_id);
                    })
                    ->select(
                        'asesor_vatm.daftar_id',
                        'asesor_vatm.asesor_id',
                        'asesor_vatm.hasil AS hasil_vatm',
                        'matkul.id AS matkul_id',
                        'cpl.id AS cpl_id',
                        'matkul.kode_matkul',
                        'matkul.nama_matkul',
                        'cpl.cpl AS cpl_name',
                        'student_asessment.jenis_rpl AS jenis_rpl',
                        'student_asessment.deskripsi AS deskripsi_student',
                        'student_asessment.pernyataan AS pernyataan_student',
                        'asesor_rekap.hasil_rekap AS hasil_rekap',
                        'asesor_rekap.status_lulus AS status_lulus',
                        'asesor_rekap.status_kirim AS status_kirim'
                    )
                    ->where('asesor_vatm.daftar_id', '=', $daftarid)
                    ->where('asesor_vatm.asesor_id', '=', $asesor_id)
                    ->groupBy(
                        'asesor_vatm.daftar_id',
                        'asesor_vatm.asesor_id',
                        'asesor_vatm.hasil',
                        'matkul.id',
                        'cpl.id',
                        'matkul.kode_matkul',
                        'matkul.nama_matkul',
                        'cpl.cpl',
                        'student_asessment.jenis_rpl',
                        'student_asessment.deskripsi',
                        'student_asessment.pernyataan',
                        'asesor_rekap.hasil_rekap',
                        'asesor_rekap.status_lulus',
                        'asesor_rekap.status_kirim'
                    )
                    ->distinct()
                    ->get()
                    ->sortBy('cpl_name');
        // dd($dataStudent);

        return view('Asesor.rekap.store', compact('page', 'title', 'name','daftar', 'daftar_id', 'asesor_assesment', 'asesor_id','dataStudent','checkStatus'));
    }

    public function store(Request $request, $daftar_id, $asesor_id)
    {
        // Validate the incoming data
        $validatedData = $request->validate([
            'matkul_id.*' => 'required|exists:matkul,id',
            'nilai.*.*' => 'required|in:A,B,C,D', // Ensure radio buttons are selected
        ]);

        // Check if any 'nilai' (radio buttons) are not selected
        $missingNilai = false;
            foreach ($request->matkul_id as $index => $matkul_id) {
                if (empty($request->nilai[$index][0])) {
                    $missingNilai = true;
                    break;
                }
            }

        // If any nilai is missing, redirect back with an error message
        if ($missingNilai) {
            return redirect()->back()->with('error', 'Semua Nilai harus di isi terlebih dahulu.');
        }

        // Loop through the submitted data to save each record
        foreach ($request->matkul_id as $index => $matkul_id) {
            $hasil_vatm = $request->hasil_vatm[$index] ?? null;
            $nilaiHuruf = $request->nilai[$index][0] ?? null; // Take the selected radio value
            $status_lulus = $nilaiHuruf === "D" ? "Tidak Lulus" : "Lulus";

            // Ambil hasil_vatm dari data yang dikirimkan, jika tersedia
            $hasil_vatm = $request->hasil_vatm[$index] ?? '-';

            // Create or update the asesor_rekap record
            \DB::table('asesor_rekap')->updateOrInsert(
                [
                    'asesor_id' => $asesor_id,
                    'daftar_id' => $daftar_id,
                    'matkul_id' => $matkul_id,
                    'hasil_vatm' => $hasil_vatm,
                ],
                [
                    'hasil_rekap' => $nilaiHuruf,
                    'status_lulus' => $status_lulus,
                    'editable' => 1, // Assuming 'editable' should be true by default
                    'status_kirim' => 0, // Assuming 'editable' should be true by default
                ]
            );
        }

        // Redirect back with a success message
        return redirect()->route('indexStoreRekap',['daftar_id' => $daftar_id, 'asesor_id' => $asesor_id])->with('success', 'Data successfully saved!');
    }
}
