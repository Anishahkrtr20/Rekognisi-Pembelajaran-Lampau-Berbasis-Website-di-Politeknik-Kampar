<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\asesor_hasil;
use App\Models\asesor_rekap;
use App\Models\asesor_vatm;
use App\Models\asesorAssesment;
use App\Models\Daftar;
use App\Models\Experiences;
use App\Models\student_asessment;
use App\Models\student_biodata;
use App\Models\student_Matkul;
use App\Models\student_pilihBukti;
use App\Models\student_uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class asesorHasilRekognisi extends Controller
{
    //
    public function index(Request $request){
        // Ambil user yang sedang login
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();

        // Ambil ID asesor berdasarkan user login
        $asesor = Asesor::where('user_id', $name->id)->first();
        $asesor_id = $asesor ? $asesor->id : null;

        // Filter asesorAssesment by relevant daftar_id and asesor_id
        $dataStudent = asesorAssesment::with(['daftar', 'student_biodata', 'asesor_vatm', 'asesor_rekap'])
                                    ->where('asesor_id', $asesor_id) // Only get records with this asesor_id
                                    ->get();

        $daftar = asesorAssesment::where('asesor_id',$asesor->id)->first();
        // dd($daftar);

         // Check if daftar exists, else return empty or null value
        $daftar1 = $daftar ?: null;

        // $student = asesor_rekap::where('daftar_id', $daftar->daftar_id)
        //     ->where('asesor_id', $asesor_id)
        //     ->first();
        // dd($student);

        // $checkStatus = asesor_rekap::where('daftar_id', $daftar->daftar_id)->get();
        
        // Set the page title and other view variables
        $title = "Data Hasil Assesment | SI-RPL";
        $page = "Dashboard Hasil Assesment";

        return view('Asesor.hasil.index', compact('page', 'title', 'name', 'dataStudent','daftar','daftar1'));
    }

    public function indexHasilRekognisi($daftar_id)
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

        $asesor_rekap = asesor_rekap::where('daftar_id', $daftar_id)->first();

        if (!$asesor_rekap) {
            // Jika data tidak ditemukan, kembalikan ke halaman sebelumnya dengan pesan error
            return redirect()->back()->with('error', 'Penilaian belum dilakukan.');
        }

        $title = "Store Asesor | SI-RPL";
        $page = "Dashboard";
        $daftar = Daftar::find($daftar_id);

        // Ambil data asesor_assesment berdasarkan daftar_id dan asesor_id
        $asesor_assesment = asesorAssesment::where('daftar_id', $daftar_id)
            ->where('asesor_id', $asesor_id)
            ->first();

        if (!$asesor_assesment) {
            return redirect()->back()->with('error', 'Assessment data not found.');
        }

        $prodi_id = $asesor_assesment->prodi_id;
        $daftarid = $asesor_assesment->daftar_id;

        $dataStudent = DB::table('asesor_vatm')
            ->select(
                'asesor_vatm.daftar_id',
                'matkul.id as matkul_id',
                'matkul.kode_matkul',
                'matkul.nama_matkul',
                'cpl.id as cpl_id',
                'cpl.cpl as cpl_name',
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT asesor_vatm.asesor_id) = 1 THEN
                            GROUP_CONCAT(DISTINCT asesor_vatm.hasil ORDER BY asesor_vatm.asesor_id)
                        ELSE
                            SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT CONCAT(asesor_vatm.hasil) ORDER BY asesor_vatm.asesor_id), ',', 1)
                    END AS asesor_1"
                ),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT asesor_vatm.asesor_id) = 1 THEN
                            NULL
                        ELSE
                            SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT CONCAT(asesor_vatm.hasil) ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1)
                    END AS asesor_2"
                ),
                DB::raw("GROUP_CONCAT(DISTINCT student_asessment.jenis_rpl ORDER BY asesor_vatm.asesor_id) AS jenis_rpl"),
                DB::raw("GROUP_CONCAT(DISTINCT student_asessment.deskripsi ORDER BY asesor_vatm.asesor_id) AS deskripsi_student"),
                DB::raw("GROUP_CONCAT(DISTINCT student_asessment.pernyataan ORDER BY asesor_vatm.asesor_id) AS pernyataan_student"),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT asesor_vatm.asesor_id) = 1 THEN
                            GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id)
                        ELSE
                            SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1)
                    END AS hasil_rekap_1"
                ),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT asesor_vatm.asesor_id) = 1 THEN
                            NULL
                        ELSE
                            SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1)
                    END AS hasil_rekap_2"
                ),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT asesor_vatm.asesor_id) = 1 THEN
                            CASE
                                WHEN GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id) = 'A' THEN 'A'
                                WHEN GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id) = 'B' THEN 'B'
                                WHEN GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id) = 'C' THEN 'C'
                                ELSE 'D'
                            END
                        ELSE
                            CASE
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'A'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'A' THEN 'A'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'A'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'B' THEN 'B'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'A'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'C' THEN 'C' 
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'A'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'D' THEN 'D'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'B'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'A' THEN 'B' 
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'B'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'B' THEN 'B'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'B'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'C' THEN 'C'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'B'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'D' THEN 'D'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'C'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'A' THEN 'C'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'C'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'B' THEN 'C'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'C'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'C' THEN 'C'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'C'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'D' THEN 'D'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'D'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'A' THEN 'D'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'D'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'B' THEN 'D'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'D'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'C' THEN 'D'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'D'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.hasil_rekap ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'D' THEN 'D'
                                ELSE 'di pertimbangkan'
                            END
                    END AS hasil_rekap_akhir"
                ),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT asesor_vatm.asesor_id) = 1 THEN
                            GROUP_CONCAT(DISTINCT asesor_rekap.status_lulus ORDER BY asesor_vatm.asesor_id)
                        ELSE
                            SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.status_lulus ORDER BY asesor_vatm.asesor_id), ',', 1)
                    END AS status_lulus_1"
                ),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT asesor_vatm.asesor_id) = 1 THEN
                            NULL
                        ELSE
                            SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.status_lulus ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1)
                    END AS status_lulus_2"
                ),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT asesor_vatm.asesor_id) = 1 THEN
                            CASE
                                WHEN GROUP_CONCAT(DISTINCT asesor_rekap.status_lulus ORDER BY asesor_vatm.asesor_id) = 'lulus' THEN 'lulus'
                                ELSE 'tidak lulus'
                            END
                        ELSE
                            CASE
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.status_lulus ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'lulus'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.status_lulus ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'lulus' THEN 'lulus'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.status_lulus ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'lulus'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.status_lulus ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'tidak lulus' THEN 'tidak lulus'
                                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.status_lulus ORDER BY asesor_vatm.asesor_id), ',', 1), ',', -1) = 'tidak lulus'
                                    AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT asesor_rekap.status_lulus ORDER BY asesor_vatm.asesor_id), ',', 2), ',', -1) = 'tidak lulus' THEN 'tidak lulus'
                                ELSE 'tidak lulus'
                            END
                    END AS status_akhir"
                )
            )
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
            ->leftJoin('asesor_rekap', function ($join) use ($daftarid) {
                $join->on('asesor_rekap.daftar_id', '=', 'asesor_vatm.daftar_id')
                    ->on('asesor_rekap.matkul_id', '=', 'asesor_vatm.matkul_id')
                    ->on('asesor_rekap.daftar_id', '=', DB::raw($daftarid)); // Replace with your $daftarid value
            })
            ->where('asesor_vatm.daftar_id', '=', $daftarid) // Replace with your $daftarid value
            ->groupBy(
                'asesor_vatm.daftar_id',
                'matkul.id',
                'matkul.kode_matkul',
                'matkul.nama_matkul',
                'cpl.id',
                'cpl.cpl'
            )
            ->orderByDesc('pernyataan_student')
            ->get();
        // dd($dataStudent);

        return view('Asesor.hasil.hasil', compact('page', 'title', 'name', 'daftar', 'daftar_id', 'asesor_assesment', 'asesor_id','dataStudent'));
    }

    public function update(Request $request, $daftar_id, $asesor_id){
        // Ambil user yang sedang login
        $request->validate([
            'komen' => 'nullable|array',
        ]);
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();

        // Ambil ID asesor berdasarkan user login
        $asesor = Asesor::where('user_id', $name->id)->first();
        $asesor_id = $asesor ? $asesor->id : null;

        // Ambil data asesor_assesment berdasarkan daftar_id dan asesor_id
        $asesor_assesment = asesorAssesment::where('daftar_id', $daftar_id)
            ->where('asesor_id', $asesor_id)
            ->first();
        
        $student = asesorAssesment::where('asesor_id',$asesor->id)->first();
        // dd($student);

        $asesor_rekap = asesor_rekap::where('daftar_id', $daftar_id)->first();
        // dd($asesor_rekap);

        if (!$asesor_rekap) {
            // Jika data tidak ditemukan, kembalikan ke halaman sebelumnya dengan pesan error
            return redirect()->back()->with('error', 'Penilaian belum dilakukan.');
        }

        // dd($asesor_assesment);

        if (!$asesor_assesment) {
            return redirect()->back()->with('error', 'Assessment data not found.');
        }

        $daftarid = $asesor_assesment->daftar_id;

        $hasil_rekap = asesor_rekap::where('daftar_id', $daftarid)
                            ->where('asesor_id', $asesor_id)
                            ->get();

        // dd($asesor_rekap);

        // Loop untuk memproses data rekap
        // foreach ($asesor_rekap as $rekap) {
        //     // Proses data (contoh update status atau field lainnya)
        //     $rekap->status_kirim = '1';
        //     $rekap->komen = $request->komen;
        //     $rekap->save();
        // }
        // Process each record and update comments
        // foreach ($asesor_rekap as $index => $rekap) {
        //     if (isset($request->komen[$index])) {
        //         $rekap->status_kirim = '1'; // Mark as submitted
        //         $rekap->komen = $request->komen[$index] ?? null; // Save comment
        //         $rekap->save();
        //     }
        // }

        // Loop untuk memproses setiap data rekap
        foreach ($hasil_rekap as $index => $rekap) {
            // Simpan komentar atau nilai kosong jika tidak ada
            $rekap->status_kirim = '1'; // Tanda sebagai terkirim
            $rekap->komen = $request->komen[$index] ?? null; // Simpan nilai kosong jika tidak ada komentar
            $rekap->save();
        }
        return redirect()->route('indexHasil')->with('success', 'Nilai RPL Sending Successfully');
    }

    public function bukaSimpanPermanen(Request $request, $daftar_id, $asesor_id){
        $daftar = Daftar::where('id', $daftar_id)->first();
        if (!$daftar) {
            return redirect()->back()->with('error', 'Registration data not found');
        }

        // dd();

        // Retrieve related data
        $biodata = student_biodata::where('daftar_id', $daftar->id)->get();
        $pengalaman = Experiences::where('daftar_id', $daftar->id)->get();
        $student_matkul = student_Matkul::where('daftar_id', $daftar->id)->get();
        $pilihBukti = student_pilihBukti::where('daftar_id', $daftar->id)->get();
        $uploadBukti = student_uploads::where('daftar_id', $daftar->id)->get();
        $asesmentMandiri = student_asessment::where('daftar_id', $daftar->id)->get();
        $asesor_rekap = asesor_rekap::where('daftar_id', $daftar->id)
                            // ->where('asesor_id', $asesor_id)
                            ->get();
        $asesor_vatm = asesor_vatm::where('daftar_id', $daftar->id)
                            // ->where('asesor_id', $asesor_id)
                            ->get();
        // Update each collection
        foreach ($biodata as $item) {
            $item->editable = '0';
            $item->save();
        }

        foreach ($pengalaman as $item) {
            $item->editable = '0';
            $item->save();
        }

        foreach ($student_matkul as $item) {
            $item->editable = '0';
            $item->save();
        }

        foreach ($pilihBukti as $item) {
            $item->editable = '0';
            $item->save();
        }

        foreach ($uploadBukti as $item) {
            $item->editable = '0';
            $item->save();
        }

        foreach ($asesmentMandiri as $item) {
            $item->editable = '0';
            $item->save();
        }

        foreach ($asesor_rekap as $item) {
            $item->status_kirim = '0';
            $item->editable = '0';
            $item->save();
        }

        foreach ($asesor_vatm as $item) {
            $item->editable = '0';
            $item->save();
        }

        return redirect()->route('indexHasil')->with('success', 'Simpan Permanen Unlocked Successfully');
    }

}
