<?php

namespace App\Http\Controllers;

use App\Models\Daftar;
use App\Models\Matkul;
use App\Models\Prodi;
use App\Models\student_Matkul;
use App\Models\student_pilihBukti;
use App\Models\student_profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class matkulRekognisiController extends Controller
{
    public function index(){
        // Get the user data based on email
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        // Set the title and page
        $title = "Matkul RPL | SI-RPL";
        $page = "Matkul RPL";

        // Get all study programs (Prodi)
        $prodi = Prodi::all();

        // Check if the user has registered in 'Daftar' table
        $daftar = Daftar::where('user_id', $name->id)->first();
        
        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        $studentMatkul = student_Matkul::where('daftar_id', $daftar->id)->first();

        // dd($studentMatkul);

        // Set the selected program studi based on the student_matkul table, if available
        if ($studentMatkul) {
            $selectedProdiId = $studentMatkul->prodi_id; // Assuming 'prodi_id' is the column in student_matkul table
        } else {
            $selectedProdiId = null; // No data found, set to null
        }

        return view('Pendaftar.matkul.index', compact('page', 'title', 'name', 'prodi', 'daftar', 'selectedProdiId','avatar'));
    }

    public function matkulRpl($id){
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        $title = "Matkul RPL | SI-RPL";
        $page = "Matkul RPL";

        $matkul = Matkul::with(['cpl.prodi'])
                 ->where('prodi_id', $id)
                 ->orderBy('cpl_id') // Sorting by cpl_id
                 ->get();

        // dd($matkul);

        $prodi = Prodi::find($id);

        // dd($prodi);
        
        $daftar = Daftar::where('user_id', $name->id)->first();
        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        // $student_Matkul = student_Matkul::where('matkul_id', $matkul->id ?? null)->first();
        $student_Matkul = student_Matkul::where('daftar_id', $daftar->id)
                    ->pluck('matkul_id')
                    ->toArray();
        // dd($student_Matkul);
        session(['selected_program_studi' => $id]);

        $mahasiswa = student_Matkul::where('daftar_id', $daftar->id)->first();

        return view('Pendaftar.matkul.matkul', compact('page', 'title', 'name','matkul', 'prodi', 'daftar', 'student_Matkul','mahasiswa','avatar'));
    }

    public function store(Request $request)
    {
        // Validasi data yang diterima
        $request->validate([
            'daftar' => 'required|array',
            'daftar.*' => 'exists:daftar,id',
            'matkul' => 'array', // Matkul boleh kosong jika tidak ada yang dicentang
            'matkul.*' => 'exists:matkul,id',
        ]);

        // Ambil ID daftar pertama
        $daftarId = $request->daftar[0];

        // Ambil ID mata kuliah yang sudah ada di database untuk daftar ini
        $currentMatkul = student_Matkul::where('daftar_id', $daftarId)
                            ->pluck('matkul_id')
                            ->toArray();

        // ID mata kuliah yang baru dipilih
        $selectedMatkul = $request->input('matkul', []);

        // Tentukan mata kuliah yang perlu dihapus dan ditambahkan
        $toDelete = array_diff($currentMatkul, $selectedMatkul);
        $toInsert = array_diff($selectedMatkul, $currentMatkul);

        // Hapus data mata kuliah yang tidak dicentang
        if (!empty($toDelete)) {
            foreach ($toDelete as $matkulId) {
                // Hapus dari tabel student_Matkul
                student_Matkul::where('daftar_id', $daftarId)
                    ->where('matkul_id', $matkulId)
                    ->delete();

                // Periksa dan hapus dari tabel student_pilihBukti jika data dengan daftar_id, cpl_id, dan matkul_id sama
                $relatedData = student_pilihBukti::where('daftar_id', $daftarId)
                                    ->where('matkul_id', $matkulId)
                                    ->get();

                foreach ($relatedData as $data) {
                    student_pilihBukti::where('id', $data->id)->delete();
                }
            }
        }

        // Tambahkan data mata kuliah yang baru dicentang
        foreach ($toInsert as $matkulId) {
            // Ambil `cpl_id` dari relasi Matkul
            $matkul = Matkul::find($matkulId);

            if ($matkul) {
                student_Matkul::create([
                    'daftar_id' => $daftarId,
                    'prodi_id' => $matkul->prodi_id, // Ambil prodi_id dari relasi
                    'cpl_id' => $matkul->cpl_id, // Ambil cpl_id dari relasi
                    'matkul_id' => $matkulId,
                    'status' => 1,
                    'editable' => 0,
                ]);
            }
        }
        return redirect()->back()->with('success', 'RPL Mata Kuliah berhasil dipilih.');
    }
}
