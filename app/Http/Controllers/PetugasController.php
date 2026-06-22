<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petugas;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
    // halaman daftar petugas — bisa filter by nama, wilayah, dan status
    public function index(Request $request)
    {
        $query = Petugas::query();

        // filter pencarian nama
        if (!empty($request->search)) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        // filter wilayah kalau dipilih
        if (!empty($request->wilayah)) {
            $query->where('wilayah', $request->wilayah);
        }

        // filter status aktif/nonaktif
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        $petugas     = $query->paginate(5)->withQueryString();
        $wilayahList = Petugas::select('wilayah')->distinct()->get();

        return view('admin.datapetugas.datapetugas', compact('petugas', 'wilayahList'));
    }

    // form tambah petugas baru
    public function create()
    {
        return view('admin.datapetugas.create');
    }

    // simpan petugas baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'kode_petugas' => 'required',
            'nama'         => 'required',
            'wilayah'      => 'required',
            'status'       => 'required',
        ]);

        DB::beginTransaction();
        try {
            // kode_petugas di-uppercase supaya konsisten
            Petugas::create([
                'kode_petugas' => strtoupper($request->kode_petugas),
                'nama'         => $request->nama,
                'wilayah'      => $request->wilayah,
                'status'       => $request->status,
            ]);

            DB::commit();
            return redirect('/datapetugas')->with('success', 'Petugas berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // form edit — cek dulu datanya ada, kalau tidak langsung redirect
    public function edit($id)
    {
        $petugas = Petugas::find($id);

        if (!$petugas) {
            return redirect('/datapetugas')->with('error', 'Data tidak ditemukan');
        }

        return view('admin.datapetugas.edit', compact('petugas'));
    }

    // update data petugas yang sudah ada
    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_petugas' => 'required',
            'nama'         => 'required',
            'wilayah'      => 'required',
            'status'       => 'required',
        ]);

        DB::beginTransaction();
        try {
            $petugas = Petugas::findOrFail($id);
            $petugas->update([
                'kode_petugas' => $request->kode_petugas,
                'nama'         => $request->nama,
                'wilayah'      => $request->wilayah,
                'status'       => $request->status,
            ]);

            DB::commit();
            return redirect('/datapetugas')->with('success', 'Petugas berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // hapus petugas — pakai find biasa, tidak pakai findOrFail supaya bisa handle sendiri
    public function destroy($id)
    {
        $petugas = Petugas::find($id);

        if ($petugas) {
            $petugas->delete();
            return back()->with('success', 'Petugas berhasil dihapus');
        }

        return back()->with('error', 'Data tidak ditemukan');
    }
}