<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petugas;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
    public function index(Request $request)
    {
        $query = Petugas::query();

        if (!empty($request->search)) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if (!empty($request->wilayah)) {
            $query->where('wilayah', $request->wilayah);
        }

        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        $petugas = $query->paginate(5)->withQueryString();
        $wilayahList = Petugas::select('wilayah')->distinct()->get();

        return view('admin.datapetugas.datapetugas', compact('petugas', 'wilayahList'));
    }

    public function create()
    {
        return view('admin.datapetugas.create');
    }

    public function store(Request $request)
    {
       $request->validate([
    'kode_petugas' => 'required',
    'nama'         => 'required',
    'wilayah'      => 'required',
    'status'       => 'required'
]);

        DB::beginTransaction();
        try {
            $petugas = Petugas::create([
    'kode_petugas' => strtoupper($request->kode_petugas),
    'nama'         => $request->nama,
    'wilayah'      => $request->wilayah,
    'status'       => $request->status
]);

            DB::commit();
            return redirect('/datapetugas')->with('success', 'Petugas berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $petugas = Petugas::find($id);

        if (!$petugas) {
            return redirect('/datapetugas')->with('error', 'Data tidak ditemukan');
        }

        return view('admin.datapetugas.edit', compact('petugas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_petugas' => 'required',
            'nama'         => 'required',
            'wilayah'      => 'required',
            'status'       => 'required'
        ]);

        DB::beginTransaction();
        try {
            $petugas = Petugas::findOrFail($id);
            $petugas->update([
                'kode_petugas' => $request->kode_petugas,
                'nama'         => $request->nama,
                'wilayah'      => $request->wilayah,
                'status'       => $request->status
            ]);

            DB::commit();
            return redirect('/datapetugas')->with('success', 'Petugas berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

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
