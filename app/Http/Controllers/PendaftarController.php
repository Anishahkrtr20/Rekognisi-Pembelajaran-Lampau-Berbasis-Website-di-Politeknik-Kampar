<?php

namespace App\Http\Controllers;

use App\Models\asesor_rekap;
use App\Models\asesorAssesment;
use App\Models\student_biodata;
use App\Models\Daftar;
use App\Models\Education;
use App\Models\Experiences;
use App\Models\Matkul;
use App\Models\student_asessment;
use App\Models\student_Matkul;
use App\Models\student_pilihBukti;
use App\Models\student_profile;
use App\Models\student_uploads;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PendaftarController
{
    //
    public function index(){

        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        $title = "Info Assestment | SI-RPL";
        $page = "Informasi Assestment";

        $biodata = Daftar::where('user_id', $name->id)->first();
        // dd($biodata);
        $asesor_id = asesorAssesment::where('daftar_id', $biodata->id)->first();
        // dd($asesor_id);
        $comment = asesor_rekap::where('daftar_id', $biodata->id)->get();

        $hasUnsent = $comment->where('status_kirim', 0)->isNotEmpty();
        // dd($commment);

        $avatar = student_profile::where('daftar_id', $biodata->id)->first();

        // count MK
        $countMatkul = student_Matkul::where('daftar_id',$biodata->id)->count();

        // Count SKS yang dipilih
        $siswa = student_Matkul::where('daftar_id', $biodata->id)->pluck('matkul_id'); // Ambil daftar matkul_id
        $countSks = Matkul::whereIn('id', $siswa)->sum('sks'); // Hitung total SKS berdasarkan matkul_id

        // Count SKS total
        $countSksTotal = 0;
        $prodi = student_Matkul::where('daftar_id', $biodata->id)->first(); // Ambil daftar prodi_id
        if ($prodi && $prodi->prodi_id) {
            // Jika prodi_id ada, hitung total SKS berdasarkan prodi_id
            $countSksTotal = Matkul::where('prodi_id', $prodi->prodi_id)->sum('sks');
        } else {
            // Jika prodi_id kosong, lanjutkan proses tanpa error
            return view('Pendaftar.index', compact('page', 'title', 'name','avatar','countMatkul','countSks','countSksTotal','hasUnsent'));
        }

        // Count file bukti
        $countFile = student_uploads::where('daftar_id', $biodata->id)->where('file', '!=', null)->count();
        // dd($countFile);

        // Count pilih bukti
        $countPilihBukti = student_pilihBukti::where('daftar_id', $biodata->id)->count();
        // dd($countPilihBukti);

        // Count Assesment
        $countAsessment = student_asessment::where('daftar_id', $biodata->id)->count();

        // simpanPermanen
        $student_biodata = student_biodata::where('daftar_id',$biodata->id)->first();

        // Komen
        $results = DB::table('asesor_vatm as a')
            ->join('matkul as m', 'a.matkul_id', '=', 'm.id')
            ->join('cpl as c', 'm.cpl_id', '=', 'c.id')
            ->join('prodi as p', 'p.id', '=', 'c.prodi_id')
            ->leftJoin('asesor_rekap as ar', function ($join) {
                $join->on('ar.daftar_id', '=', 'a.daftar_id')
                    ->on('ar.matkul_id', '=', 'a.matkul_id');
            })
            ->where('a.daftar_id', 1) // Adjust as needed
            ->groupBy('a.daftar_id', 'm.id', 'm.kode_matkul', 'm.nama_matkul', 'c.id', 'c.cpl')
            ->select(
                'a.daftar_id',
                'm.id as matkul_id',
                'm.kode_matkul',
                'm.nama_matkul',
                'c.id as cpl_id',
                'c.cpl as cpl_name',
                DB::raw('CASE
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
                                AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "A" THEN "B"
                            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "B"
                                AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "B" THEN "B"
                            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "B"
                                AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "C" THEN "C"
                            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "B"
                                AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "D" THEN "D"
                            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 1), ",", -1) = "C"
                                AND SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT ar.hasil_rekap ORDER BY a.asesor_id), ",", 2), ",", -1) = "A" THEN "C"
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
                END AS hasil_rekap_akhir'),
                DB::raw('CASE
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
                END AS status_akhir'),
                DB::raw('GROUP_CONCAT(DISTINCT ar.komen ORDER BY a.asesor_id) AS komentar_asesor')
            )
            ->orderByDesc('c.cpl')
            ->get();
        // dd($results);


        return view('Pendaftar.index', compact('page', 'title', 'name','avatar','countMatkul','countSks','countSksTotal','countFile','countPilihBukti','countAsessment','student_biodata','hasUnsent','results'));
    }

    public function checkStatus(){
        return redirect()->route('pendaftar')->with('error', 'Kamu sudah melakukan simpan Permanen');
    }

    public function continueRegistration()
    {
        $name = \DB::table('users')->where('email', Auth::user()->email)->first();

        $daftarId = Daftar::where('user_id', $name->id)->first();
        // dd($daftarId);

        // Cek data di tabel student_biodata
        $biodata = student_biodata::where('daftar_id', $daftarId->id)->first();
        // dd($biodata);
        if (!$biodata) {
            return redirect()->route('biodata'); // Rute ke form biodata
        }

        // Cek data di tabel riwayat hidup
        $riwayatHidup = Education::where('daftar_id', $daftarId->id)->first();
        if (!$riwayatHidup) {
            return redirect()->route('riwayat'); // Rute ke form riwayat hidup
        }

        // Cek data di tabel matkul
        $matkul = student_Matkul::where('daftar_id', $daftarId->id)->first();
        if (!$matkul) {
            return redirect()->route('indexMatkulRpl'); // Rute ke form matkul
        }

        // Cek data di tabel upload bukti
        $uploadBukti = student_uploads::where('daftar_id', $daftarId->id)->first();
        if (!$uploadBukti) {
            return redirect()->route('indexBuktiRpl'); // Rute ke form upload bukti
        }

        // Cek data di tabel pilih bukti
        $pilihBukti = student_pilihBukti::where('daftar_id', $daftarId->id)->first();
        if (!$pilihBukti) {
            return redirect()->route('indexPilihBukti'); // Rute ke form pilih bukti
        }

        // Cek data di tabel assessment
        $assessment = student_asessment::where('daftar_id', $daftarId->id)->first();
        if (!$assessment) {
            return redirect()->route('asessmentMandiri'); // Rute ke form assessment
        }

        // Jika semua data sudah terisi, arahkan ke rute simpan permanen
        return redirect()->route('simpan.permanen'); // Rute ke simpan permanen
    }

    public function biodata() {
        $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login\

        $title = "Data Diri | SI-RPL";
        $page = "Data Diri";
        
        // Ambil data daftar berdasarkan user_id yang login
        $daftar = Daftar::where('user_id', $name->id)->first();
        // dd($daftar);
        $mahasiswa = student_biodata::where('daftar_id', $daftar->id)->first();
        // dd($mahasiswa);
        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        return view('Pendaftar.biodata.index', compact('daftar','mahasiswa', 'page', 'title','name','avatar'));
    }

    public function store(Request $request){
        // Validasi input
        $validated = $request->validate([
            'daftar_id' => 'required|string', // Mengharuskan nilai dan memastikan string
            'status_nikah' => 'required|string|max:15',
            'tahun_ajaran' => 'nullable|string|max:15',
            'nama_instansi' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:100',
            'alamat_instansi' => 'nullable|string|max:255',
            'divisi' => 'nullable|string|max:100',
            'status_pegawai' => 'nullable|string|max:100',
            'lama_bekerja' => 'nullable|string|max:50',
        ]);

        // Cek apakah nilai daftar_id ada atau tidak
        if (empty($validated['daftar_id'])) {
            // Jika daftar_id tidak ada, lanjutkan langkah selanjutnya
            // Misalnya, Anda bisa membuat objek mahasiswa baru tanpa mengisi daftar_id
            $mahasiswa = new student_biodata();
            // Isi data lain sesuai dengan yang diperlukan
            $mahasiswa->status_nikah = $validated['status_nikah'];
            $mahasiswa->tahun_ajaran = $validated['tahun_ajaran'];
            $mahasiswa->nama_instansi = $validated['nama_instansi'];
            $mahasiswa->jabatan = $validated['jabatan'];
            $mahasiswa->alamat_instansi = $validated['alamat_instansi'];
            $mahasiswa->divisi = $validated['divisi'];
            $mahasiswa->status_pegawai = $validated['status_pegawai'];
            $mahasiswa->lama_bekerja = $validated['lama_bekerja'];
            $mahasiswa->status = 1; // Menetapkan status
            $mahasiswa->editable = 0; // Menetapkan status
        } else {
            // Cek apakah data dengan daftar_id yang sama sudah ada
            $mahasiswa = student_biodata::where('daftar_id', $validated['daftar_id'])->first();

            // Jika $mahasiswa ditemukan
            if ($mahasiswa) {
                // Update data mahasiswa yang ada
                $mahasiswa->status_nikah = $validated['status_nikah'];
                $mahasiswa->tahun_ajaran = $validated['tahun_ajaran'];
                $mahasiswa->nama_instansi = $validated['nama_instansi'];
                $mahasiswa->jabatan = $validated['jabatan'];
                $mahasiswa->alamat_instansi = $validated['alamat_instansi'];
                $mahasiswa->divisi = $validated['divisi'];
                $mahasiswa->status_pegawai = $validated['status_pegawai'];
                $mahasiswa->lama_bekerja = $validated['lama_bekerja'];
            } else {
                // Jika tidak ada, buat objek baru
                $mahasiswa = new student_biodata();
                $mahasiswa->daftar_id = $validated['daftar_id']; // Mengisi daftar_id
                // Isi data lain seperti sebelumnya
                $mahasiswa->status_nikah = $validated['status_nikah'];
                $mahasiswa->tahun_ajaran = $validated['tahun_ajaran'];
                $mahasiswa->nama_instansi = $validated['nama_instansi'];
                $mahasiswa->jabatan = $validated['jabatan'];
                $mahasiswa->alamat_instansi = $validated['alamat_instansi'];
                $mahasiswa->divisi = $validated['divisi'];
                $mahasiswa->status_pegawai = $validated['status_pegawai'];
                $mahasiswa->lama_bekerja = $validated['lama_bekerja'];
                $mahasiswa->status = 1; // Menetapkan status
                $mahasiswa->editable = 0; // Menetapkan status
            }
        }

        // Simpan data ke database
        $mahasiswa->save();

        return redirect()->route('biodata')->with('success', 'Data berhasil disimpan!');
    }

    // --- Riwayat Hidup -----

    // Tampilan Index

    public function indexRiwayat(){
        // Ambil data user yang sedang login
        $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login
        // dd($name);

        // Judul halaman
        $title = "Riwayat Hidup | SI-RPL";
        $page = "Data Riwayat Hidup";

        // Ambil data daftar berdasarkan user_id dari user yang sedang login
        $daftar = Daftar::where('user_id', $name->id)->first();

        $avatar = student_profile::where('daftar_id', $daftar->id)->first();

        if ($daftar) {
            // Ambil data pendidikan dan pengalaman berdasarkan daftar_id
            $education = Education::where('daftar_id', $daftar->id)->get();
            $experience = Experiences::where('daftar_id', $daftar->id)->get();
        } else {
            // Jika tidak ada data daftar, inisialisasi dengan collection kosong
            $education = collect([]);
            $experience = collect([]);
        }

        $mahasiswa = Experiences::where('daftar_id', $daftar->id)->first();
        
        return view('Pendaftar.riwayat.index', compact('name', 'daftar', 'page', 'title', 'education', 'experience','mahasiswa','avatar'));
    }

        public function indexStore(){
            $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login\

            $title = "Riwayat Hidup | SI-RPL";
            $page = "Data Riwayat Hidup";
            
            // Ambil data daftar berdasarkan user_id yang login
            $daftar = Daftar::where('user_id', $name->id)->first();
            // dd($daftar);
            // $education = Education::first();
            // dd($education);
            $avatar = student_profile::where('daftar_id', $daftar->id)->first();

            $mahasiswa = Experiences::where('daftar_id', $daftar->id)->first();
            // dd($mahasiswa);

            return view('Pendaftar.riwayat.store', compact('daftar', 'page', 'title','name','mahasiswa','avatar'));
        }

        // Controller Simpan Ke database

        public function storeRiwayat(Request $request){
            $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login\

            $daftar = Daftar::where('user_id', $name->id)->first();
            // dd($daftar);

            $validated = $request->validate([
                'nama_sekolah' => 'required|string|max:50',
                'tahun_lulus' => 'required|date|max:255',
                'jurusan' => 'required|string|max:30', 
            ]);

            $education = new Education;
            $education->daftar_id = $daftar->id;
            $education->nama_sekolah = $validated['nama_sekolah'];
            $education->tahun_lulus = $validated['tahun_lulus'];
            $education->jurusan = $validated['jurusan'];
            $education->status = 1;
            $education->save();

            // $education = Education::all();
                
            // return view('Pendaftar.riwayat.index', compact('daftar', 'page', 'title','name', 'education'));
            return redirect()->route('riwayat')->with('success', 'Data pendidikan berhasil disimpan.');
        }

        public function storeExperience(Request $request){
            $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login\

            $daftar = Daftar::where('user_id', $name->id)->first();
            // dd($daftar);

            $validated = $request->validate([
                'jenis_pengalaman' => 'nullable|string|max:50',  
                'kegiatan' => 'nullable|string|max:50',  
                'tahun' => 'nullable|string|max:30',  
                'penyelenggara' => 'nullable|string|max:50',  
                'jangka_waktu' => 'nullable|string|max:30',
                'jabatan' => 'nullable|string|max:50',  
                'det_kegiatan' => 'nullable|string|max:255',  
                'file_sertifikat' => 'nullable|mimes:pdf|max:3072',  
            ]);

            $file = $request->file('file_sertifikat') ?? null;

            $experience = new Experiences;
            $experience->daftar_id = $daftar->id ?? null;
            $experience->jenis_pengalaman = $validated['jenis_pengalaman'] ?? null;
            $experience->kegiatan = $validated['kegiatan'] ?? null;
            $experience->tahun = $validated['tahun'] ?? null;
            $experience->penyelenggara = $validated['penyelenggara'] ?? null;
            $experience->jangka_waktu = $validated['jangka_waktu'] ?? null;
            $experience->jabatan = $validated['jabatan'] ?? null;
            $experience->det_kegiatan = $validated['det_kegiatan'] ?? null;
            $experience->status = 1 ?? null;
            $experience->editable = 0 ?? null;

            // $filename = time() . '_' . $file->getClientOriginalName();
            // $file->move(public_path('/sertifikat/riwayat_hidup'), $filename);
            // $experience->file_sertifikat = '/sertifikat/riwayat_hidup' . $filename;

            if ($file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('/sertifikat/riwayat_hidup'), $filename);
                $experience->file_sertifikat = '/sertifikat/riwayat_hidup/' . $filename;
            } else {
                // Jika tidak ada file, beri nilai default atau biarkan null
                $experience->file_sertifikat = null;
            }

            $experience->save();

            // return view('Pendaftar.riwayat.index', compact('daftar', 'page', 'title','name'));
            return redirect()->route('riwayat')->with('success', 'Data Pengalaman berhasil disimpan.');
        }

        // Controller tampilan Edit
        public function editRiwayat($id){
            $user = User::find($id);
            $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login\

            $title = "Riwayat Hidup | SI-RPL";
            $page = "Data Riwayat Hidup";
            
            // Ambil data daftar berdasarkan user_id yang login
            $daftar = Daftar::where('user_id', $name->id)->first();
            // dd($daftar);
            $avatar = student_profile::where('daftar_id', $daftar->id)->first();

            $education = Education::find($id);
            // dd($education);
            $experience = Experiences::first();

            return view('Pendaftar.riwayat.edit', compact('daftar', 'page', 'title','name','education','experience','avatar'));
        }

        public function editPengalaman($id){
            $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login\

            $title = "Riwayat Hidup | SI-RPL";
            $page = "Data Riwayat Hidup";
            
            // Ambil data daftar berdasarkan user_id yang login
            $daftar = Daftar::where('user_id', $name->id)->first();
            // dd($daftar);
            $avatar = student_profile::where('daftar_id', $daftar->id)->first();

            $education = Education::first();
            // dd($education);
            $experience = Experiences::find($id);

            return view('Pendaftar.riwayat.edit', compact('daftar', 'page', 'title','name','education','experience','avatar'));
        }

        // Controller Update Edit
        
        public function updateRiwayat(Request $request,$id){
            $title = "Riwayat Hidup | SI-RPL";
            $page = "Data Riwayat Hidup";
            $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login\
            
            if (!$name) {
                return redirect()->route('editRiwayat')->with('error', 'User tidak ditemukan.');
            }

            $daftar = Daftar::where('user_id', $name->id)->first();
            // dd($daftar);
            if (!$daftar) {
                return redirect()->route('editRiwayat')->with('error', 'Data daftar tidak ditemukan.');
            }
          

            $validated = $request->validate([
                'nama_sekolah' => 'required|string|max:50',
                'tahun_lulus' => 'required|date|max:255',
                'jurusan' => 'required|string|max:30', 
            ]);

            $education = Education::find($id);
            if (!$education) {
        // Jika data tidak ditemukan, buat data baru
        $education = new Education();
    }

            $education->daftar_id = $daftar->id;
            $education->nama_sekolah = $validated['nama_sekolah'];
            $education->tahun_lulus = $validated['tahun_lulus'];
            $education->jurusan = $validated['jurusan'];
            $education->status = 1;
            $education->save();

            return redirect()->route('riwayat')->with('success', 'Data pendidikan berhasil disimpan.');
        }

        public function updatePengalaman(Request $request, $id)
        {
            $title = "Riwayat Hidup | SI-RPL";
            $page = "Data Riwayat Hidup";
            $name = \DB::table('users')->where('email', Auth::user()->email)->first(); // Ambil data user yang login

            if (!$name) {
                return redirect()->route('editPengalaman')->with('error', 'User tidak ditemukan.');
            }

            $daftar = Daftar::where('user_id', $name->id)->first();

            if (!$daftar) {
                return redirect()->route('editPengalaman')->with('error', 'Data daftar tidak ditemukan.');
            }


            $validated = $request->validate([
                'jenis_pengalaman' => 'nullable|string|max:50',
                'kegiatan' => 'nullable|string|max:50',
                'tahun' => 'nullable|string|max:30',
                'penyelenggara' => 'nullable|string|max:50',
                'jangka_waktu' => 'nullable|string|max:30',
                'jabatan' => 'nullable|string|max:50',
                'det_kegiatan' => 'nullable|string|max:255',
                'file_sertifikat' => 'nullable|mimes:pdf|max:3072',
            ]);

            $experience = Experiences::find($id);

            if (!$experience) {
                // return redirect()->route('editPengalaman')->with('error', 'Data pendidikan tidak ditemukan.');
            $experience = new Experiences;
            }

            // Ambil file sertifikat baru dari request
            $file = $request->file('file_sertifikat');

            // Menyimpan file lama untuk dihapus jika ada file baru
            $oldFile = $experience->file_sertifikat;
            $experience->daftar_id = $daftar->id ?? null;
            $experience->jenis_pengalaman = $validated['jenis_pengalaman'] ?? null;
            $experience->kegiatan = $validated['kegiatan'] ?? null;
            $experience->tahun = $validated['tahun'] ?? null;
            $experience->penyelenggara = $validated['penyelenggara'] ?? null;
            $experience->jangka_waktu = $validated['jangka_waktu'] ?? null;
            $experience->jabatan = $validated['jabatan'] ?? null;
            $experience->det_kegiatan = $validated['det_kegiatan'] ?? null;
            $experience->status = 1 ?? null;
            $experience->editable = 0 ?? null;

            // Jika ada file baru yang diunggah
            if ($file) {
                // Menghapus file lama jika ada
                if (file_exists(public_path($oldFile))) {
                    unlink(public_path($oldFile)); // Hapus file lama dari server
                }

                // Menyimpan file baru ke server
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('/sertifikat/riwayat_hidup'), $filename);
                $experience->file_sertifikat = '/sertifikat/riwayat_hidup/' . $filename;
            }

            // Jika tidak ada file baru, tetap gunakan file lama
            // Tidak perlu perubahan apa-apa karena file_sertifikat tetap diset ke file lama

            $experience->save();

            return redirect()->route('riwayat')->with('success', 'Data pendidikan berhasil disimpan.');
        }


        // Controller Hapus

        public function hapusRiwayat($id){
            $education = Education::find($id);
            $education->delete();
            return redirect()->route('riwayat')->with('success', 'Data pendidikan berhasil disimpan.');
        }

        public function hapusPengalaman($id){
            // Mencari data pengalaman berdasarkan id
            $experience = Experiences::find($id);

            // Cek jika data ditemukan
            if ($experience) {
                // Ambil path file yang tersimpan di database
                $filePath = public_path($experience->file_sertifikat);

                // Cek apakah file ada dan jika ada, hapus file
                if (file_exists($filePath)) {
                    unlink($filePath); // Hapus file dari server
                }

                // Hapus data dari tabel
                $experience->delete();

                // Redirect dengan pesan sukses
                return redirect()->route('riwayat')->with('success', 'Data pengalaman berhasil dihapus.');
            }

            // Jika data tidak ditemukan
            return redirect()->route('riwayat')->with('error', 'Data pengalaman tidak ditemukan.');
        }
}