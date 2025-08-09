<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Daftar;
use App\Models\Matkul;
use App\Models\student_Matkul;
use App\Models\student_pilihBukti;
use App\Models\student_profile;
use App\Models\student_uploads;
use App\Models\SubMatkul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class buktiRekognisiController extends Controller
{
    //
    public function index()
    {
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        $title = "Bukti RPL | SI-RPL";
        $page = "Bukti RPL";

        // Get the 'daftar' entry related to the currently logged-in user
        $daftar = Daftar::where('user_id', $name->id)->first();

        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        // Check if the 'daftar' record exists for the current user
        if ($daftar) {
            // If 'daftar' exists, filter 'student_upload' by 'daftar_id'
            $student_upload = student_uploads::where('daftar_id', $daftar->id)->get();
            
            // If no uploads are found, return an empty collection
            if ($student_upload->isEmpty()) {
                $student_upload = collect();  // Return an empty collection
            }
        } else {
            // If 'daftar' does not exist, return an empty collection
            $student_upload = collect();  // Return an empty collection
        }

        $mahasiswa = student_uploads::where('daftar_id', $daftar->id)->first();

        return view('Pendaftar.bukti.index', compact('page', 'title', 'name', 'daftar', 'student_upload','mahasiswa','avatar'));
    }

    public function store(Request $request){
        
        $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login\
        $daftar = Daftar::where('user_id', $name->id)->first();
        // dd($daftar);
        
        $fileRules = match ($request->jenis) {
            'gambar' => 'required|mimes:jpg,jpeg,png|max:3072',
            'video' => 'required|mimes:mp4,avi,mov|max:10240', // Maksimum 10 MB untuk video
            'document' => 'required|mimes:pdf|max:3072',
            default => 'required|mimes:pdf|max:3072', // fallback
        };
        $validated = $request->validate([
            'daftar_id' => 'required|string',     
            'kode_bukti' => 'required|string',                   
            'nama_bukti' => 'required|string',        
            'jenis' => 'required|string',        
            'file' => $fileRules,
            'keterangan' => 'required|string|max:255',           
            // 'status' => 'required|string',
            // 'editable' => 'required|string',
        ]);

        $certificate = $request->file('file');
        $filename = time() . '_' . $certificate->getClientOriginalName();
        $filePath = '/sertifikat/student_bukti/' . $filename;
        $certificate->move(public_path('sertifikat/student_bukti'), $filename);

        $upload = new student_uploads;
        $upload->daftar_id = $daftar->id;
        $upload->kode_bukti = $validated['kode_bukti'];
        $upload->nama_bukti = $validated['nama_bukti'];
        $upload->jenis = $validated['jenis'];
        // $upload->file = $validated['file'];
        $upload->file = $filePath;
        $upload->keterangan = $validated['keterangan'];
        $upload->status = 1;
        $upload->editable = 0;

        $upload->save();

        return redirect()->route('indexBuktiRpl')->with(['success', 'Data Bukti berhasil di tambahkan',
                                                'alert', 'Data Bukti dihapus']);
    }

    public function getLastKodeBukti()
    {
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        // Ambil data pendaftar berdasarkan user login
        $daftar = Daftar::where('user_id', $name->id)->first();

        if (!$daftar) {
            // Handle if 'daftar' is not found, maybe show an error or proceed
            return response()->json(['error' => 'Pendaftar tidak ditemukan.'], 404);
        }

        // Cari record terakhir berdasarkan daftar_id yang sedang login
        $lastRecord = student_uploads::where('daftar_id', $daftar->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        // Jika tidak ada data sebelumnya, set kode_bukti pertama menjadi 'bukti-001'
        if ($lastRecord) {
            $lastKode = $lastRecord->kode_bukti;
            // Ambil nomor urut terakhir dan tambahkan 1
            $lastKodeNum = (int) substr($lastKode, -3); // Ambil 3 digit terakhir dari kode_bukti
            $newKode = 'Bukti-' . str_pad($lastKodeNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // Jika tidak ada record sebelumnya, mulai dengan 'Bukti-001'
            $newKode = 'Bukti-001';
        }

        return response()->json(['newKode' => $newKode]);
    }

    public function editBuktiRpl($id){
        $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login\

        $title = "Butki RPL | SI-RPL";
        $page = "Edit Butki RPL";
        
        // Ambil data daftar berdasarkan user_id yang login
        $daftar = Daftar::where('user_id', $name->id)->first();
        // dd($daftar);
        // dd($id);
        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        $student_upload = student_uploads::find($id);

        return view('Pendaftar.bukti.edit', compact('daftar', 'page', 'title','name','student_upload','avatar'));
    }

    public function updateBukti(Request $request, $id)
    {
        $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login
        $daftar = Daftar::where('user_id', $name->id)->first();
        
        // Validasi tipe file berdasarkan jenis yang dipilih
        $fileRules = match ($request->jenis) {
            'gambar' => 'required|mimes:jpg,jpeg,png|max:3072',
            'video' => 'required|mimes:mp4,avi,mov|max:10240', // Maksimum 10 MB untuk video
            'document' => 'required|mimes:pdf|max:3072',
            default => 'required|mimes:pdf|max:3072', // fallback
        };
        $validated = $request->validate([
            'daftar_id' => 'required|string',     
            'kode_bukti' => 'required|string',                   
            'nama_bukti' => 'required|string',        
            'jenis' => 'required|string',        
            'file' => $fileRules,
            'keterangan' => 'required|string|max:255',           
        ]);

        $upload = student_uploads::findOrFail($id);

        // Hapus file lama jika ada file baru yang diupload
        if ($request->hasFile('file')) {
            $oldFilePath = public_path($upload->file);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Upload file baru
            $certificate = $request->file('file');
            $filename = time() . '_' . $certificate->getClientOriginalName();
            $filePath = '/sertifikat/student_bukti/' . $filename;
            $certificate->move(public_path('sertifikat/student_bukti'), $filename);
            
            // Set file path yang baru
            $upload->file = $filePath;
        }

        // Update data lainnya
        $upload->daftar_id = $daftar->id;
        $upload->kode_bukti = $validated['kode_bukti'];
        $upload->nama_bukti = $validated['nama_bukti'];
        $upload->jenis = $validated['jenis'];
        $upload->keterangan = $validated['keterangan'];
        $upload->status = 1;
        $upload->editable = 0;

        $upload->save();

        return redirect()->route('indexBuktiRpl')->with('success', 'Data Bukti berhasil di Edit');
    }

    public function buktiRplHapus($id)
    {
        // Mencari data upload berdasarkan id
        $student_upload = student_uploads::find($id);

        // Cek jika data ditemukan
        if ($student_upload) {
            // Ambil path file yang tersimpan di database
            $filePath = public_path($student_upload->file);

            // Cek apakah file ada dan jika ada, hapus file
            if (file_exists($filePath)) {
                unlink($filePath); // Hapus file dari server
            }

            // Hapus data dari tabel
            $student_upload->delete();

            // Redirect dengan pesan sukses
            return redirect()->route('indexBuktiRpl')->with('success', 'Data bukti berhasil dihapus.');
        }

        // Jika data tidak ditemukan
        return redirect()->route('indexBuktiRpl')->with('error', 'Data bukti tidak ditemukan.');
    }

    // Controller Pilih Bukti

    public function indexPilihBukti(Request $request)
    {
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        $title = "Pilih Bukti Rekognisi | SI-RPL";
        $page = "Pilih Bukti Rekognisi";

        // Ambil data pendaftar berdasarkan user login
        $daftar = Daftar::where('user_id', $name->id)->first();

        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        if (!$daftar) {
            // Handle if 'daftar' is not found, maybe show an error or proceed
            return redirect()->route('someErrorPage');
        }

        // Fetch student_pilihBukti for the logged-in user based on daftar_id
        $student_pilihBukti = student_pilihBukti::with(['matkul.cpl', 'student_uploads'])
            ->where('daftar_id', $daftar->id)
            ->orderBy('cpl_id')
            ->get()
            ->groupBy('matkul_id') // Grouping by matkul_id
            ->map(function ($group) {
                $groupData = $group->first(); // Take the first item to display the matkul only once
                
                // Implode bukti names and file links into a single string
                $groupData->nama_bukti = implode("\n", $group->pluck('student_uploads.nama_bukti')->toArray());
                $groupData->file_bukti = implode(', ', $group->pluck('student_uploads.file')->toArray());
                
                return $groupData;
            });

        // Ambil data CPL berdasarkan daftar_id dan prodi_id
        $dataCpl = student_Matkul::where('daftar_id', $daftar->id)
            ->with('cpl') // Ensure CPL is loaded through the relationship
            ->get()
            ->pluck('cpl') // Only take the CPL data
            ->unique('id') // Remove duplicates
            ->values();

        // Ambil data Mata Kuliah terkait CPL yang dipilih
        $dataMatkul = Matkul::whereIn('id', student_Matkul::where('daftar_id', $daftar->id)
            ->pluck('matkul_id'))
            ->get();

        // Get all available bukti
        $pilihBukti = student_uploads::where('daftar_id', $daftar->id)->get();

        // Get all selected matkul_ids
        $selectedMatkul = student_pilihBukti::where('daftar_id', $daftar->id)
            ->pluck('matkul_id')
            ->toArray();

        // For dropdown selection
        $selectedMatkul = $request->old('matkul_id', $selectedMatkul);

        $mahasiswa = student_pilihBukti::where('daftar_id', $daftar->id)->first();

        return view('Pendaftar.pilihBukti.index', compact('title', 'page', 'name', 'dataCpl', 'dataMatkul', 'selectedMatkul', 'daftar', 'student_pilihBukti', 'pilihBukti','mahasiswa','avatar'));
    }

    public function storePilihBukti(Request $request)
    {
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        $daftar = Daftar::where('user_id', $name->id)->first();

        $validated = $request->validate([
            'daftar_id' => 'required|exists:daftar,id',
            'cpl_id' => 'required|exists:cpl,id',
            'matkul_id' => 'required|exists:matkul,id',
            'bukti_id' => 'required|array',
            'bukti_id.*' => 'exists:student_uploads,id', // Validasi setiap elemen dalam array bukti_id
        ]);

        // Ambil data asesor assessment yang sudah ada untuk daftar_id dan prodi_id tertentu
        $existingPilihBukti = student_pilihBukti::where('cpl_id', $validated['cpl_id'])
                                                ->where('matkul_id', $validated['matkul_id'])
                                                ->get();

        // Simpan atau perbarui data asesor yang dicentang
        foreach ($validated['bukti_id'] as $buktiId) {
            $student_pilihBukti = $existingPilihBukti->firstWhere('bukti_id', $buktiId);

            if ($student_pilihBukti) {
                // Jika data sudah ada, hanya pastikan status tetap dan simpan
                $student_pilihBukti->save();
            } else {
                // Jika data belum ada, buat data baru
                $student_pilihBukti = new student_pilihBukti();
                $student_pilihBukti->daftar_id = $daftar->id;
                $student_pilihBukti->cpl_id = $validated['cpl_id'];
                $student_pilihBukti->matkul_id = $validated['matkul_id'];
                // $student_pilihBukti->asesor_id = $asesorId;
                $student_pilihBukti->bukti_id = $buktiId;
                $student_pilihBukti->status = 1;
                $student_pilihBukti->editable = 0;
                $student_pilihBukti->save();
            }
        }

        // Hapus data asesor yang tidak dicentang
        $buktiIdsChecked = $validated['bukti_id']; // Asesor ID yang dicentang
        $existingPilihBukti->whereNotIn('bukti_id', $buktiIdsChecked)->each(function ($student_pilihBukti) {
            $student_pilihBukti->delete();
        });

        return redirect()->route('indexPilihBukti')->with('success', 'Data bukti berhasil di tambahkan.');
    }

    public function pilihBuktiHapus($id)
    {
        // Find the selected record by ID
        $student_pilihBukti = student_pilihBukti::findOrFail($id);

        // Get the matkul_id and daftar_id from the found record
        $matkul_id = $student_pilihBukti->matkul_id;
        $daftar_id = $student_pilihBukti->daftar_id;

        // Delete all records that have the same matkul_id and daftar_id
        student_pilihBukti::where('matkul_id', $matkul_id)
                        ->where('daftar_id', $daftar_id)
                        ->delete();

        // Redirect to the index page with a success message
        return redirect()->route('indexPilihBukti')->with('success', 'Data Bukti yang dipilih berhasil dihapus.');
    }
}
