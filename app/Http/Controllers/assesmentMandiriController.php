<?php

namespace App\Http\Controllers;

use App\Models\Daftar;
use App\Models\pilihCpmk;
use App\Models\student_asessment;
use App\Models\student_Matkul;
use App\Models\student_profile;
use App\Models\SubMatkul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class assesmentMandiriController extends Controller
{
    //
    public function index(Request $request){

        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        $title = "Assesment RPL | SI-RPL";
        $page = "Assesment RPL";

        // Ambil data pendaftar berdasarkan user login
        $daftar = Daftar::where('user_id', $name->id)->first();

        if (!$daftar) {
            // Handle if 'daftar' is not found, maybe show an error or proceed
            return redirect()->route('someErrorPage');
        }

        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        $selectedJalur = student_asessment::where('daftar_id', $daftar->id)->first();

        // Check if the selected jalur exists
        if ($selectedJalur) {
            // Get the 'jenis_rpl' based on selected jalur
            $jenisRpl = $selectedJalur->jenis_rpl;
        } else {
            // Default to null if no jalur is selected
            $jenisRpl = null;
        }

        return view('Pendaftar.assesment.index', compact('page', 'title', 'name','jenisRpl','avatar'));
    }

    // Controller
    public function transfer()
    {
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();
        // dd($name);
        // $user = Auth::user();

        // Ambil daftar_id pengguna yang sedang login
        $daftar = Daftar::where('user_id', $name->id)->first();

        // dd($daftar);

        if (!$daftar) {
            // Jika daftar_id tidak ditemukan, lanjutkan proses lainnya
            return redirect()->back()->with('error', 'Data pendaftar tidak ditemukan.');
        }

        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        $daftarId = $daftar->id;

        // Mengambil data student_matkul beserta relasi jika daftar_id cocok
        $studentMatkul = \DB::table('student_matkul')
            ->join('matkul', 'student_matkul.matkul_id', '=', 'matkul.id')
            ->join('cpl', 'matkul.cpl_id', '=', 'cpl.id')
            ->leftJoin('pilihCpmk', 'student_matkul.matkul_id', '=', 'pilihCpmk.matkul_id')
            ->leftJoin('submatkul', 'pilihCpmk.submatkul_id', '=', 'submatkul.id')
            ->leftJoin('student_asessment', function ($join) use ($daftarId) {
                $join->on('student_matkul.matkul_id', '=', 'student_asessment.matkul_id')
                    ->where('student_asessment.daftar_id', '=', $daftarId);
            })
            ->where('student_matkul.daftar_id', $daftarId) // Filter berdasarkan daftar_id
            ->select(
                'student_matkul.matkul_id',
                'matkul.kode_matkul',
                'matkul.nama_matkul',
                'cpl.cpl as cpl',
                \DB::raw('GROUP_CONCAT(submatkul.sub_matkul SEPARATOR ", ") as submatkul'),
                'student_asessment.pernyataan as pernyataan',
                'student_asessment.deskripsi as deskripsi'
            )
            ->groupBy(
                'student_matkul.matkul_id',
                'matkul.kode_matkul',
                'matkul.nama_matkul',
                'cpl.cpl',
                'student_asessment.pernyataan',
                'student_asessment.deskripsi'
            )
            ->get();

        if ($studentMatkul->isEmpty()) {
            // Jika tidak ada data yang relevan dengan daftar_id, lanjutkan proses lainnya
            return redirect()->back()->with('info', 'Tidak ada data yang relevan dengan daftar Anda.');
        }

        // Menyusun data ke dalam grup
        $studentMatkulGroup = $studentMatkul->map(function ($item) {
            return [
                'matkul_id' => $item->matkul_id,
                'kode_matkul' => $item->kode_matkul,
                'nama_matkul' => $item->nama_matkul,
                'cpl' => $item->cpl,
                'submatkul' => !empty($item->submatkul) ? explode(', ', $item->submatkul) : [],
                'pernyataan' => $item->pernyataan,
                'deskripsi' => $item->deskripsi,
            ];
        });

        $mahasiswa = student_asessment::where('daftar_id', $daftar->id)->first();

        // Pass data ke view
        $title = "Transfer Kredit | SI-RPL";
        $page = "Transfer Kredit";

        return view('Pendaftar.assesment.store', compact('name', 'page', 'title', 'studentMatkulGroup','mahasiswa','avatar'));
    }

    public function perolehan()
    {
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        // Ambil daftar_id pengguna yang sedang login
        $daftar = Daftar::where('user_id', $name->id)->first();

        // dd($daftar);

        if (!$daftar) {
            // Jika daftar_id tidak ditemukan, lanjutkan proses lainnya
            return redirect()->back()->with('error', 'Data pendaftar tidak ditemukan.');
        }

        $daftarId = $daftar->id;

        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        // Mengambil data student_matkul beserta relasi jika daftar_id cocok
        $studentMatkul = \DB::table('student_matkul')
            ->join('matkul', 'student_matkul.matkul_id', '=', 'matkul.id')
            ->join('cpl', 'matkul.cpl_id', '=', 'cpl.id')
            ->leftJoin('pilihCpmk', 'student_matkul.matkul_id', '=', 'pilihCpmk.matkul_id')
            ->leftJoin('submatkul', 'pilihCpmk.submatkul_id', '=', 'submatkul.id')
            ->leftJoin('student_asessment', function ($join) use ($daftarId) {
                $join->on('student_matkul.matkul_id', '=', 'student_asessment.matkul_id')
                    ->where('student_asessment.daftar_id', '=', $daftarId);
            })
            ->where('student_matkul.daftar_id', $daftarId) // Filter berdasarkan daftar_id
            ->select(
                'student_matkul.matkul_id',
                'matkul.kode_matkul',
                'matkul.nama_matkul',
                'cpl.cpl as cpl',
                \DB::raw('GROUP_CONCAT(submatkul.sub_matkul SEPARATOR ", ") as submatkul'),
                'student_asessment.pernyataan as pernyataan',
                'student_asessment.deskripsi as deskripsi'
            )
            ->groupBy(
                'student_matkul.matkul_id',
                'matkul.kode_matkul',
                'matkul.nama_matkul',
                'cpl.cpl',
                'student_asessment.pernyataan',
                'student_asessment.deskripsi'
            )
            ->get();

        if ($studentMatkul->isEmpty()) {
            // Jika tidak ada data yang relevan dengan daftar_id, lanjutkan proses lainnya
            return redirect()->back()->with('info', 'Tidak ada data yang relevan dengan daftar Anda.');
        }

        // Menyusun data ke dalam grup
        $studentMatkulGroup = $studentMatkul->map(function ($item) {
            return [
                'matkul_id' => $item->matkul_id,
                'kode_matkul' => $item->kode_matkul,
                'nama_matkul' => $item->nama_matkul,
                'cpl' => $item->cpl,
                'submatkul' => !empty($item->submatkul) ? explode(', ', $item->submatkul) : [],
                'pernyataan' => $item->pernyataan,
                'deskripsi' => $item->deskripsi,
            ];
        });

        $mahasiswa = student_asessment::where('daftar_id', $daftar->id)->first();

        // Pass data ke view
        $title = "Perolehan | SI-RPL";
        $page = "Perolehan";

        return view('Pendaftar.assesment.store', compact('name', 'page', 'title', 'studentMatkulGroup','mahasiswa','avatar'));
    }

    public function storeTransfer(Request $request)
    {
        try {
            $matkulIds = $request->input('matkul_id');
            $descriptions = $request->input('deskripsi');
            $statements = $request->input('pernyataan');
            $jenisRpl = $request->input('jenis_rpl');

            // Validasi input
            foreach ($matkulIds as $index => $matkulId) {
                if (empty($statements[$index])) {
                    return redirect()->route('assesment.transfer')
                        ->with('error', "Data Pernyataan Evaluasi Tidak boleh Kosong.");
                }

                if (empty($descriptions[$index])) {
                    return redirect()->route('assesment.transfer')
                        ->with('error', "Data Deskripsi untuk Mata Kuliah tidak boleh kosong.");
                }
            }

            // Mendapatkan user yang sedang login
            $user = Auth::user();
            $name = DB::table('users')->where('email', $user->email)->first();
            $daftar = Daftar::where('user_id', $name->id)->first();

            foreach ($matkulIds as $index => $matkulId) {
                $data = [
                    'daftar_id' => $daftar->id,
                    'jenis_rpl' => $jenisRpl,
                    'matkul_id' => $matkulId,
                    'deskripsi' => $descriptions[$index],
                    'pernyataan' => $statements[$index],
                    'status' => 1,
                    'editable' => 0,
                ];

                // Periksa apakah data dengan matkul_id sudah ada
                $existingRecord = DB::table('student_asessment')
                    ->where('matkul_id', $matkulId)
                    ->where('daftar_id', $daftar->id)
                    ->first();

                if ($existingRecord) {
                    // Update data jika sudah ada
                    DB::table('student_asessment')
                        ->where('id', $existingRecord->id)
                        ->update($data);
                } else {
                    // Buat data baru jika belum ada
                    DB::table('student_asessment')->insert($data);
                }
            }

            // Jika semua berhasil, tampilkan pesan sukses
            return redirect()->route('assesment.transfer')->with('success', 'Data berhasil disimpan atau diperbarui!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangkap pesan error SQL
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();

            // Pesan error khusus berdasarkan kode SQL
            $translatedMessage = match ($errorCode) {
                23000 => "Terjadi pelanggaran integritas data. Periksa input Anda dan coba lagi.",
                default => "Kesalahan database: $errorMessage"
            };

            return redirect()->route('assesment.transfer')->with('error', $translatedMessage);
        } catch (\Exception $e) {
            // Tangkap exception lain
            return redirect()->route('assesment.transfer')->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function storePerolehan(Request $request)
    {
        try {
            $matkulIds = $request->input('matkul_id');
            $descriptions = $request->input('deskripsi');
            $statements = $request->input('pernyataan');
            $jenisRpl = $request->input('jenis_rpl');

            // Validasi input
            foreach ($matkulIds as $index => $matkulId) {
                if (empty($statements[$index])) {
                    return redirect()->route('assesment.transfer')
                        ->with('error', "Data Pernyataan Evaluasi Tidak boleh Kosong.");
                }

                if (empty($descriptions[$index])) {
                    return redirect()->route('assesment.transfer')
                        ->with('error', "Data Deskripsi untuk Mata Kuliah tidak boleh kosong.");
                }
            }

            // Mendapatkan user yang sedang login
            $user = Auth::user();
            $name = DB::table('users')->where('email', $user->email)->first();
            $daftar = Daftar::where('user_id', $name->id)->first();

            foreach ($matkulIds as $index => $matkulId) {
                $data = [
                    'daftar_id' => $daftar->id,
                    'jenis_rpl' => $jenisRpl,
                    'matkul_id' => $matkulId,
                    'deskripsi' => $descriptions[$index],
                    'pernyataan' => $statements[$index],
                    'status' => 1,
                    'editable' => 0,
                ];

                // Periksa apakah data dengan matkul_id sudah ada
                $existingRecord = DB::table('student_asessment')
                    ->where('matkul_id', $matkulId)
                    ->where('daftar_id', $daftar->id)
                    ->first();

                if ($existingRecord) {
                    // Update data jika sudah ada
                    DB::table('student_asessment')
                        ->where('id', $existingRecord->id)
                        ->update($data);
                } else {
                    // Buat data baru jika belum ada
                    DB::table('student_asessment')->insert($data);
                }
            }

            // Jika semua berhasil, tampilkan pesan sukses
            return redirect()->route('assesment.perolehan')->with('success', 'Data berhasil disimpan atau diperbarui!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangkap pesan error SQL
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();

            // Pesan error khusus berdasarkan kode SQL
            $translatedMessage = match ($errorCode) {
                23000 => "Terjadi pelanggaran integritas data. Periksa input Anda dan coba lagi.",
                default => "Kesalahan database: $errorMessage"
            };

            return redirect()->route('assesment.perolehan')->with('error', $translatedMessage);
        } catch (\Exception $e) {
            // Tangkap exception lain
            return redirect()->route('assesment.perolehan')->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
