<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\asesor_rekap;
use App\Models\asesor_vatm;
use App\Models\asesorAssesment;
use App\Models\Daftar;
use App\Models\Matkul;
use App\Models\student_asessment;
use App\Models\student_biodata;
use App\Models\student_Matkul;
use App\Models\student_profile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class infoAssestmentController extends Controller
{
    //
    public function index()
    {
        $daftar = Daftar::with(['student_matkul'])->get(); // Data pendaftar
        // dd($daftar);

        $name = \DB::table('users')->where('email', Auth::user()->email)->first();
        // dd($name);

        $title = "Info Assestment | SI-RPL";
        $page = "Informasi Assestment";

        // Load data asesorAssesment beserta relasi ke daftar, asesor, dan student_matkul
        $asesorAssement = asesorAssesment::with(['daftar', 'asesor', 'student_matkul'])->get();
        // dd($asesorAssement);

        $daftar_id = Daftar::where('user_id', $name->id)->first();
        // dd($daftar_id);

        return view('Admin.assestment.index', compact('daftar', 'page', 'title', 'name', 'asesorAssement'));
    }

    public function asesor($id)
    {
        // Ambil data pendaftar berdasarkan ID
        $daftar = Daftar::find($id);

        // Ambil data studentMatkul berdasarkan daftar_id
        $studentMatkul = student_Matkul::where('daftar_id', $daftar->id)->first();

        // Validasi jika data studentMatkul tidak ditemukan
        if (!$studentMatkul) {
            return redirect()->back()->withErrors(['error' => 'Data prodi untuk pendaftar ini tidak ditemukan']);
        }

        // Filter asesor berdasarkan prodi_id
        $asesor = Asesor::where('prodi_id', $studentMatkul->prodi_id)->get();

        $asesorAssement = asesorAssesment::where('daftar_id', $daftar->id)->get();

        $name = \DB::table('users')->where('email', Auth::user()->email)->first();


        $title = "Info Assessment | SI-RPL";
        $page = "Info Assessment";

        // Return ke view dengan data yang sesuai
        return view('Admin.assestment.asesor', compact('daftar', 'page', 'title', 'name', 'asesor', 'studentMatkul','asesorAssement'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'daftar_id' => 'required|exists:daftar,id',
            'prodi_id' => 'required|exists:prodi,id',
            'asesor_id' => 'required|array', // Pastikan asesor_id adalah array
            'asesor_id.*' => 'exists:asesor,id', // Setiap id dalam array harus ada di tabel asesor
        ]);

        // Hitung jumlah asesor yang sudah ada untuk daftar_id dan prodi_id tertentu
        $existingAsesorCount = asesorAssesment::where('daftar_id', $validated['daftar_id'])
                                            ->where('prodi_id', $validated['prodi_id'])
                                            ->count();

        // Hitung jumlah asesor_id yang akan disimpan
        $newAsesorCount = count($validated['asesor_id']);

        // Validasi batas maksimum 2 asesor_id
        if ($existingAsesorCount + $newAsesorCount > 3) {
            return redirect()->route('infoAssestment')
                            ->with('error', 'Maksimal hanya dapat menyimpan 2 asesor untuk setiap daftar.');
        }

        // Ambil data asesor assessment yang sudah ada untuk daftar_id dan prodi_id tertentu
        $existingAsesorAssessments = asesorAssesment::where('daftar_id', $validated['daftar_id'])
                                                    ->where('prodi_id', $validated['prodi_id'])
                                                    ->get();

        // Simpan atau perbarui data asesor yang dicentang
        foreach ($validated['asesor_id'] as $asesorId) {
            $asesorAssement = $existingAsesorAssessments->firstWhere('asesor_id', $asesorId);

            if ($asesorAssement) {
                // Jika data sudah ada, hanya pastikan status tetap dan simpan
                $asesorAssement->status = 1; // Atur status sesuai kebutuhan
                $asesorAssement->save();
            } else {
                // Jika data belum ada, buat data baru
                $asesorAssement = new asesorAssesment;
                $asesorAssement->daftar_id = $validated['daftar_id'];
                $asesorAssement->prodi_id = $validated['prodi_id'];
                $asesorAssement->asesor_id = $asesorId;
                $asesorAssement->status = 1; // Atur status sesuai kebutuhan
                $asesorAssement->save();
            }
        }

        // Hapus data asesor yang tidak dicentang
        $asesorIdsChecked = $validated['asesor_id']; // Asesor ID yang dicentang
        $existingAsesorAssessments->whereNotIn('asesor_id', $asesorIdsChecked)->each(function ($asesorAssement) {
            $asesorAssement->delete();
        });

        // Redirect dengan pesan sukses
        return redirect()->route('infoAssestment')->with('success', 'Data Asesor Assesment berhasil diperbarui');
    }

    // public function indexHasil($id){
    //     // $daftarid = Daftar::where('user_id', $name->id)->first();
    //     $daftarid = Daftar::find($id);
    //     // dd($daftarid);

    //     $email = User::where('id', $daftarid->user_id)->first();
    //     // dd($email);

    //     $student_assesment = student_asessment::where('daftar_id', $daftarid->id)->first();

    //     $student_matkul = student_Matkul::with(['prodi'])
    //                     ->where('daftar_id', $daftarid->id)
    //                     ->first();
    //     // dd($student_matkul);
        
    //     // dd($daftarid);

    //     $asesor_rekap = asesor_rekap::where('daftar_id',$daftarid->id)
    //                 ->where('status_kirim',1)
    //                 ->first();
    //     // dd($asesor_rekap);

    //     // Periksa apakah $asesor_rekap ditemukan
    //     if (!$asesor_rekap) {
    //         return redirect()->back()->with('error', 'Data Belum selesai dinilai');
    //     }

    //     $query = DB::table('asesor_vatm as a')
    //         ->join('matkul as m', 'a.matkul_id', '=', 'm.id')
    //         ->join('cpl as c', 'm.cpl_id', '=', 'c.id')
    //         ->join('prodi as p', 'p.id', '=', 'c.prodi_id')
    //         ->leftJoin('asesor_rekap as ar', function ($join) {
    //             $join->on('ar.daftar_id', '=', 'a.daftar_id')
    //                 ->on('ar.matkul_id', '=', 'a.matkul_id');
    //         })
    //         ->select(
    //             'a.daftar_id',
    //             'm.id as matkul_id',
    //             'm.kode_matkul',
    //             'm.sks',
    //             'm.nama_matkul',
    //             'c.id as cpl_id',
    //             'c.cpl as cpl_name',
    //             DB::raw('
    //                 CASE
    //                     WHEN COUNT(DISTINCT a.asesor_id) = 1 THEN
    //                         CASE
    //                             WHEN GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id) = "A" THEN "A"
    //                             WHEN GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id) = "B" THEN "B"
    //                             WHEN GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id) = "C" THEN "C"
    //                             ELSE "D"
    //                         END
    //                     ELSE
    //                         CASE
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "A"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "A" THEN "A"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "A"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "B" THEN "B"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "A"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "C" THEN "C" 
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "A"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "D" THEN "D" 
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "B"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "B" THEN "B"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "B"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "C" THEN "C"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "B"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "D" THEN "D"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "C"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "B" THEN "C"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "C"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "C" THEN "C"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "C"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "D" THEN "D"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "D"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "A" THEN "D"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "D"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "B" THEN "D"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "D"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "C" THEN "D"
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "D"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "D" THEN "D"
    //                             ELSE "di pertimbangkan"
    //                         END
    //                 END AS hasil_rekap_akhir
    //             '),
    //             DB::raw('
    //                 CASE
    //                     WHEN COUNT(DISTINCT a.asesor_id) = 1 THEN
    //                         CASE
    //                             WHEN GROUP_CONCAT(DISTINCT ar.status_lulus ORDER BY a.asesor_id) = "lulus" THEN "lulus"
    //                             ELSE "tidak lulus"
    //                         END
    //                     ELSE
    //                         CASE
    //                             WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.status_lulus ORDER BY a.asesor_id), ",", 1), ",", -1) = "lulus"
    //                                 AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.status_lulus ORDER BY a.asesor_id), ",", 2), ",", -1) = "lulus" THEN "lulus"
    //                             ELSE "tidak lulus"
    //                         END
    //                 END AS status_akhir
    //             ')
    //         )
    //         ->where('a.daftar_id', $daftarid->id) // Change this as necessary
    //         ->groupBy('a.daftar_id','m.sks', 'm.id', 'm.kode_matkul', 'm.nama_matkul', 'c.id', 'c.cpl')
    //         ->orderBy('c.cpl', 'desc')
    //         ->get();
    //     // dd($query);

    //     // Retrieve the pilihCpmk data with related matkul
    //     $matkul = Matkul::where('prodi_id', $student_matkul->prodi_id)->get();

    //     // Calculate the total sks for the required courses
    //     $totalSksWajib = $matkul->sum(function($item) {
    //         return $item->sks;
    //     });
    //     // dd($totalSksWajib);

    //     // $totalSks = $query->sum('sks');
    //     $totalSks = $query->where('status_akhir', 'lulus')->sum('sks');

    //     $data = $query->map(function($item)use ($totalSks, $totalSksWajib){
    //         return [
    //             'cpl_name' => $item->cpl_name,
    //             'matkul' => $item->nama_matkul,
    //             'sks' => $item->sks,
    //             'nilai' => $item->hasil_rekap_akhir, // Assuming this field contains the final result
    //             'status_akhir' => $item->status_akhir, // Assuming this field contains the final status
    //             'sks_rekognisi' => $totalSks,
    //             'sks_wajib' => $totalSksWajib,
    //         ];
    //     })->toArray();

    //     // dd($data);
    //     // Load the view and pass data
    //     // $pdf = Pdf::loadView('Pendaftar.hasil.hasil', $data);
    //     $pdf = Pdf::loadView('Admin.assestment.download', compact('data','email', 'daftarid', 'student_assesment', 'student_matkul'));

    //     // Return the generated PDF for download
    //     return $pdf->download('Hasil_RPL.pdf');
    // }

    public function indexHasil($id){
        $daftar = Daftar::find($id);
        // dd($daftar);
        $name = User::where('id', $daftar->user_id)->first();

        $asesorAssesment = asesorAssesment::where('daftar_id', $daftar->id)->first();

        // dd($asesorAssesment);

        $asesor_vatm = asesor_vatm::where('daftar_id',$daftar->id)->first();
        // dd($asesor_vatm);

        $asesor_editable = asesor_rekap::where('daftar_id',$daftar->id)->first();
        // dd($asesor_rekap);

        $asesor_rekap = asesor_rekap::where('daftar_id', $daftar->id)->get();

        // Initialize the status result variable
        $statusResult = 0;

        // Check if any status_kirim is 0, if so set $statusResult to 0
        if ($asesor_rekap->contains('status_kirim', 0)) {
            $statusResult = 0;
        }
        // Check if all status_kirim are 1, if so set $statusResult to 1
        elseif ($asesor_rekap->every(function ($item) {
            return $item->status_kirim === 1;
        })) {
            $statusResult = 1;
        }
        // If any status_kirim is 1 but not all are 1, set $statusResult to 0
        else {
            $statusResult = 0;
        }

        $mahasiswa = student_biodata::where('daftar_id', $daftar->id)->first();

        $title = "Hasil Rekognisi | SI-RPL";
        $page = "Hasil Rekognisi";

        // Mengirim ke view
        return view('Admin.assestment.hasil', compact('title', 'page', 'name','asesorAssesment','asesor_vatm','statusResult','asesor_editable','mahasiswa','daftar'));
    }

    public function hasil($id)
    {
        // $user = Auth::user();
        // $name = DB::table('users')->where('email', $user->email)->first();
        // // dd($name);

        // $daftarid = Daftar::where('user_id', $name->id)->first();
        // // dd($daftarid);

        $daftarid = Daftar::find($id);
        // dd($daftar);
        $name = User::where('id', $daftarid->user_id)->first();

        $student_assesment = student_asessment::where('daftar_id', $daftarid->id)->first();

        $student_matkul = student_Matkul::with(['prodi'])
                        ->where('daftar_id', $daftarid->id)
                        ->first();
        // dd($student_matkul);
        
        // dd($daftarid);


        $query = DB::table('asesor_vatm as a')
            ->join('matkul as m', 'a.matkul_id', '=', 'm.id')
            ->join('cpl as c', 'm.cpl_id', '=', 'c.id')
            ->join('prodi as p', 'p.id', '=', 'c.prodi_id')
            ->leftJoin('asesor_rekap as ar', function ($join) {
                $join->on('ar.daftar_id', '=', 'a.daftar_id')
                    ->on('ar.matkul_id', '=', 'a.matkul_id');
            })
            ->select(
                'a.daftar_id',
                'm.id as matkul_id',
                'm.kode_matkul',
                'm.sks',
                'm.nama_matkul',
                'c.id as cpl_id',
                'c.cpl as cpl_name',
                DB::raw('
                    CASE
                        WHEN COUNT(DISTINCT a.asesor_id) = 1 THEN
                            CASE
                                WHEN GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id) = "A" THEN "A"
                                WHEN GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id) = "B" THEN "B"
                                WHEN GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id) = "C" THEN "C"
                                ELSE "D"
                            END
                        ELSE
                            CASE
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "A"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "A" THEN "A"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "A"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "B" THEN "B"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "A"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "C" THEN "C" 
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "A"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "D" THEN "D" 
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "B"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "B" THEN "B"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "B"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "C" THEN "C"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "B"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "D" THEN "D"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "C"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "B" THEN "C"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "C"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "C" THEN "C"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "C"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "D" THEN "D"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "D"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "A" THEN "D"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "D"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "B" THEN "D"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "D"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "C" THEN "D"
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "D"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "D" THEN "D"
                                ELSE "di pertimbangkan"
                            END
                    END AS hasil_rekap_akhir
                '),
                DB::raw('
                    CASE
                        WHEN COUNT(DISTINCT a.asesor_id) = 1 THEN
                            CASE
                                WHEN GROUP_CONCAT(DISTINCT ar.status_lulus ORDER BY a.asesor_id) = "lulus" THEN "lulus"
                                ELSE "tidak lulus"
                            END
                        ELSE
                            CASE
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.status_lulus ORDER BY a.asesor_id), ",", 1), ",", -1) = "lulus"
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.status_lulus ORDER BY a.asesor_id), ",", 2), ",", -1) = "lulus" THEN "lulus"
                                ELSE "tidak lulus"
                            END
                    END AS status_akhir
                ')
            )
            ->where('a.daftar_id', $daftarid->id) // Change this as necessary
            ->groupBy('a.daftar_id','m.sks', 'm.id', 'm.kode_matkul', 'm.nama_matkul', 'c.id', 'c.cpl')
            ->orderBy('c.cpl', 'desc')
            ->get();
        // dd($query);

        // Retrieve the pilihCpmk data with related matkul
        $matkul = Matkul::where('prodi_id', $student_matkul->prodi_id)->get();

        // Calculate the total sks for the required courses
        $totalSksWajib = $matkul->sum(function($item) {
            return $item->sks;
        });
        // dd($totalSksWajib);

        // $totalSks = $query->sum('sks');
        // $totalSks = $query->where('status_akhir', 'lulus')->sum('sks');
        $totalSks = $query->where('status_akhir', 'lulus')
                ->unique('kode_matkul') // Pastikan hanya satu matkul_id yang dihitung
                ->sum('sks');

        $data = $query->map(function($item)use ($totalSks, $totalSksWajib){
            return [
                'cpl_name' => $item->cpl_name,
                'matkul' => $item->nama_matkul,
                'sks' => $item->sks,
                'nilai' => $item->hasil_rekap_akhir, // Assuming this field contains the final result
                'status_akhir' => $item->status_akhir, // Assuming this field contains the final status
                'sks_rekognisi' => $totalSks,
                'sks_wajib' => $totalSksWajib,
            ];
        })->toArray();

        // dd($data);
        // Load the view and pass data
        // $pdf = Pdf::loadView('Pendaftar.hasil.hasil', $data);
        $pdf = Pdf::loadView('Admin.assestment.download', compact('data', 'daftarid', 'name', 'student_assesment', 'student_matkul'));

        // Return the generated PDF for download
        return $pdf->download('Hasil_RPL.pdf');
    }
}
