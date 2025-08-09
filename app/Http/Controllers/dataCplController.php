<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Daftar;
use App\Models\Matkul;
use App\Models\pilihCpmk;
use App\Models\Prodi;
use App\Models\SubMatkul;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Console\Input\Input;

class dataCplController extends Controller
{
    //
    public function index(){
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        $title = "Data CPL | SI-RPL";
        $page = "Data CPL";
        
        // Mengambil data role dari database
        $prodii = Prodi::all(); 

        $cpl = Cpl::with(['prodi'])->get();

        // dd($cpl);

        // Mengirim data ke view
        return view('Admin.cpl.index', compact('title', 'page', 'name','prodii','cpl'));
    }

    // public function store(Request $request){
    //     $validated = $request->validate([
    //         'nama_cpl' => 'required|string',
    //         'kode_cpl' => 'required|string',
    //         'prodi_id' => 'required|exists:prodi,id',
    //     ]);

    //     $cpl = new Cpl;
    //     $cpl->cpl = $validated['nama_cpl'];
    //     $cpl->kode_cpl = $validated['kode_cpl'];
    //     $cpl->prodi_id = $validated['prodi_id'];
    //     $cpl->save();

    //     Session::flash('success', 'Data CPL berhasil diubah');
    //     return redirect()->route('cpl')->with('success', 'Data CPL berhasil di tambahkan');
    //     // var_dump($matkul);
    // }

    public function store(Request $request)
    {
        try {
            // Validasi data yang diterima
            $validated = $request->validate([
                'nama_cpl' => 'required|string|',
                'kode_cpl' => 'required|string|max:50',
                'prodi_id' => 'required|exists:prodi,id',
            ]);

            // Membuat instance baru untuk CPL
            $cpl = new Cpl;
            $cpl->cpl = $validated['nama_cpl'];
            $cpl->kode_cpl = $validated['kode_cpl'];
            $cpl->prodi_id = $validated['prodi_id'];
            $cpl->save();

            // Menampilkan pesan sukses jika berhasil
            Session::flash('success', 'Data CPL berhasil ditambahkan');
            return redirect()->route('cpl')->with('success', 'Data CPL berhasil ditambahkan');
        } catch (QueryException $e) {
            // Menangkap error terkait query ke database
            $errorMessage = "Terjadi kesalahan saat menyimpan data ke database. Silakan coba lagi atau hubungi administrator.";
            return redirect()->back()->withInput()->withErrors(['database_error' => $errorMessage]);
        } catch (\Exception $e) {
            // Menangkap error umum lainnya
            $errorMessage = "Terjadi kesalahan: " . $e->getMessage();
            return redirect()->back()->withInput()->withErrors(['general_error' => $errorMessage]);
        }
    }

    public function edit(Request $request, $id){
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        $title = "Edit Data CPL | SI-RPL";
        $page = "Edit Data CPL";
        
        // Mengambil data role dari database
        $prodii = Prodi::all();
        $cpl1 = Cpl::with(['prodi'])->first();

        $cpl = Cpl::find($id);
        $submatkul = SubMatkul::find($id);
        // Mengirim data ke view
        return view('Admin.cpl.edit', compact('title', 'page', 'name','cpl','cpl1','prodii','submatkul'));
    }

    public function update(Request $request, $id){
         $request->validate([
            'nama_cpl',
            'kode_cpl',
            'prodi_id',
        ]);
        
        $cpl = Cpl::find($id);
        $cpl->cpl = $request->input('nama_cpl');
        $cpl->kode_cpl = $request->input('kode_cpl');
        $cpl->prodi_id = $request->input('prodi_id');

        $cpl->save();

        return redirect()->route('cpl')->with('success', 'Data CPL berhasil di update');
    }

    public function hapus($id){
        $cpl = Cpl::findOrFail($id);
        
        $cpl->delete();
        return redirect()->route('cpl')->with('alert', 'Data CPL berhasil di hapus');
    }

    // Pilih CPLMK
    public function pilihCpmk(Request $request) {
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();
        $title = "Pilih Data CPMK | SI-RPL";
        $page = "Pilih Data CPMK";

        $daftar = Daftar::where('user_id', $name->id)->first();
        $prodi = Prodi::all();
        $dataCpl = cpl::all();
        $dataMatkul = Matkul::all();
        $dataSubmatkul = SubMatkul::all();
        $selectedMatkul = $request->old('matkul_id', []);

        // Mengambil data CPMK dan mengelompokkan berdasarkan cpl_id
        $pilihCpmk = pilihCpmk::with(['SubMatkul.Matkul.Cpl.Prodi'])->get()->groupBy('cpl_id');

        // dd($pilihCpmk);

        $pilihCpmkGrouped = $pilihCpmk->map(function ($items) {
            return [
                'cpl_id' => $items->first()->cpl_id ?? null, // Tambahkan CPL ID
                'prodi_id' => $items->first()->submatkul->matkul->cpl->prodi->id ?? '-', 
                'prodi' => $items->first()->submatkul->matkul->cpl->prodi->nama_prodi ?? '-', 
                'cpl' => $items->first()->submatkul->matkul->cpl->cpl ?? '-', 
                'kode_cpl' => $items->first()->submatkul->matkul->cpl->kode_cpl ?? '-', 
                'matkul' => $items->pluck('submatkul.matkul.nama_matkul')->unique()->join(', '), 
                'submatkul' => $items->pluck('submatkul.sub_matkul')->unique()->join('/n'), 
            ];
        });

        // dd($pilihCpmkGrouped);

        $pilihCpmkGrouped = $pilihCpmkGrouped->sortBy('prodi')->values();

        return view('Admin.pilihCpmk.index', compact('title', 'page', 'name', 'prodi', 'dataCpl', 'dataMatkul', 'dataSubmatkul', 'selectedMatkul', 'pilihCpmk', 'pilihCpmkGrouped'));
    }

    public function storeCpmk(Request $request)
    {
    // Validasi data
    $validatedData = $request->validate([
        'prodi_data' => 'required|array',
        'prodi_data.*.prodi_id' => 'required|exists:prodi,id',
        'prodi_data.*.cpl_data' => 'required|array',
        'prodi_data.*.cpl_data.*.cpl_id' => 'required|exists:cpl,id',
        'prodi_data.*.cpl_data.*.matkul_data' => 'required|array',
        'prodi_data.*.cpl_data.*.matkul_data.*.matkul_id' => 'required|exists:matkul,id',
        'prodi_data.*.cpl_data.*.matkul_data.*.submatkul_data' => 'required|array',
        'prodi_data.*.cpl_data.*.matkul_data.*.submatkul_data.*' => 'required|exists:submatkul,id',
    ]);

    // Simpan data ke database
    foreach ($validatedData['prodi_data'] as $prodiData) {
        $prodiId = $prodiData['prodi_id'];

        foreach ($prodiData['cpl_data'] as $cplData) {
            $cplId = $cplData['cpl_id'];

            foreach ($cplData['matkul_data'] as $matkulData) {
                $matkulId = $matkulData['matkul_id'];

                foreach ($matkulData['submatkul_data'] as $submatkulId) {
                    // Periksa apakah data sudah ada di database
                    $existingRecord = pilihCpmk::where('prodi_id', $prodiId)
                        ->where('cpl_id', $cplId)
                        ->where('matkul_id', $matkulId)
                        ->where('submatkul_id', $submatkulId)
                        ->first();

                    if ($existingRecord) {
                        // Jika sudah ada, tampilkan notifikasi dan hentikan proses
                        // return response()->json([
                        //     'success' => false,
                        //     'message' => 'Data CPL yang dipilih sudah dimasukkan',
                        // ]);
                        return redirect()->route('pilihCpmk')->with('error', 'Data CPL yang dipilih sudah dimasukkan');
                    }

                    // Simpan data baru jika belum ada
                    pilihCpmk::create([
                        'prodi_id' => $prodiId,
                        'cpl_id' => $cplId,
                        'matkul_id' => $matkulId,
                        'submatkul_id' => $submatkulId,
                    ]);
                }
            }
        }
    }

    Session::flash('success', 'Data pilih CPMK berhasil diubah');
    return redirect()->route('pilihCpmk')->with('success', 'Data pilih CPMK berhasil di tambahkan');

    return response()->json([
        'success' => true,
        'message' => 'Data pilih CPMK berhasil diubah',
        'data' => $request->all()
    ]);
    }

    public function hapusCpmk($cpl_id)
    {
        // Cari data berdasarkan CPL ID
       // Cari semua data berdasarkan CPL ID
        $pilihCpmk = PilihCpmk::where('cpl_id', $cpl_id)->get();

        if ($pilihCpmk->isEmpty()) {
            return redirect()->route('pilihCpmk')->with('error', 'Data tidak ditemukan.');
        }

        // Hapus semua data dengan CPL ID tersebut
        PilihCpmk::where('cpl_id', $cpl_id)->delete();
        // Redirect ke halaman dengan pesan berhasil
        return redirect()->route('pilihCpmk')->with('success', 'Semua data CPLMK tersebut berhasil dihapus.');
    }

    // Edit CPLMK

    public function editCpmk(Request $request, $prodi_id, $cpl_id)
    {
        // Ambil data user dan informasi tambahan
        $user = Auth::user();
        $name = DB::table('users')->where('email', $user->email)->first();

        // Judul dan halaman
        $title = "Edit Data CPMK | SI-RPL";
        $page = "Edit Data CPMK";

        // Ambil data dari tabel pilihCpmk
        $pilihCpmk = pilihCpmk::where('prodi_id', $prodi_id)
                            ->where('cpl_id', $cpl_id)
                            ->first();

        if (!$pilihCpmk) {
            return redirect()->back()->with('error', 'Data tidak ditemukan di tabel pilihCpmk.');
        }
        // dd($pilihCpmk);

        // Ambil satu instance Prodi dan CPL terkait
        $prodi1 = Prodi::find($pilihCpmk->prodi_id);
        $cpl = Cpl::find($pilihCpmk->cpl_id);

        if (!$prodi1 || !$cpl) {
            return redirect()->back()->with('error', 'Data Prodi atau CPL tidak ditemukan.');
        }

        // Ambil data terkait
        $prodi = Prodi::all();
        $dataCpl = Cpl::where('prodi_id', $prodi_id)->get();
        $dataMatkul = Matkul::where('prodi_id', $prodi_id)
                            ->where('cpl_id', $cpl_id)
                            ->get();
        $dataSubmatkul = SubMatkul::whereIn('matkul_id', $dataMatkul->pluck('id'))->get();

        // Ambil data pilihan yang sudah tersimpan
        $pilihCplmk = pilihCpmk::where('prodi_id', $prodi_id)
                            ->where('cpl_id', $cpl_id)
                            ->get();

        $selectedMatkul = $pilihCplmk->pluck('matkul_id')->toArray();
        $selectedSubmatkul = $pilihCplmk->pluck('submatkul_id')->toArray();

        // Tandai matkul dan submatkul yang sudah dipilih
        foreach ($dataMatkul as $matkul) {
            $matkul->checked = in_array($matkul->id, $selectedMatkul);
        }

        foreach ($dataSubmatkul as $submatkul) {
            $submatkul->checked = in_array($submatkul->id, $selectedSubmatkul);
        }

        // Kirim data ke view
        return view('Admin.pilihCpmk.edit', compact(
            'title', 
            'page', 
            'name', 
            'prodi1',
            'cpl',
            'prodi', 
            'dataCpl', 
            'dataMatkul', 
            'dataSubmatkul', 
            'selectedMatkul', 
            'selectedSubmatkul'
        ));
    }

    public function updateCplmk(Request $request, $prodi_id, $cpl_id){
    // Validasi data
    $validatedData = $request->validate([
        'prodi_data.*.prodi_id_hidden' => 'required|exists:prodi,id',
        'prodi_data.*.cpl_data.*.cpl_id_hidden' => 'required|exists:cpl,id',
        'matkul_data' => 'nullable|array',
        'matkul_data.*' => 'exists:matkul,id',
        'submatkul_data.*.*' => 'nullable|exists:submatkul,id',
    ]);

    try {
        DB::transaction(function () use ($validatedData, $prodi_id, $cpl_id, $request) {
            // Hapus data lama
            pilihCpmk::where('prodi_id', $prodi_id)
                ->where('cpl_id', $cpl_id)
                ->delete();

            foreach ($validatedData['matkul_data'] as $matkulId) {
                $submatkulIds = $request->input("submatkul_data.$matkulId", []);
                foreach ($submatkulIds as $submatkulId) {
                    pilihCpmk::updateOrCreate([
                        'prodi_id' => $prodi_id,
                        'cpl_id' => $cpl_id,
                        'matkul_id' => $matkulId,
                        'submatkul_id' => $submatkulId,
                    ]);
                }
            }
        });

        return redirect()->route('pilihCpmk')->with('success', 'Semua data CPLMK berhasil diperbarui.');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Terjadi kesalahan: ' . $e->getMessage());
    }
}
}
