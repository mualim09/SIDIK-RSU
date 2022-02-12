<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\TenKesehatan;
use App\Models\Pasien;
use App\Models\Fakulta;
use App\Models\Prodi;
use App\Models\Category;
use App\Models\Apoteker;
use App\Models\Kategori_tenkesehatan;
use App\Models\RekamMedik;
use App\Models\Notification;
use App\Models\ResepObat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Events\MedicalRecordSent;
use App\Charts\RekamMedikBarChart;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function adm_dashboard()
    {
        $pasiens = DB::table('pasiens')->count();
        $nakes = DB::table('tenkesehatans')->count();
        $rekammedik = DB::table('rekam_mediks')->count();
        $apoteker = DB::table('apotekers')->count();

        $api = url('admin/dashboard/');

        $chart = new RekamMedikBarChart;

        return view('admin.dashboard', compact('pasiens', 'nakes', 'rekammedik', 'apoteker', 'chart'));
    }

    public function chartBarAjax(Request $request)
    {
        $year = $request->has('year') ? $request->year : date('Y');
        $rekammedik = RekamMedik::select(DB::raw("COUNT(*) as count"))
            ->whereYear('rekammedik_created_at', $year)
            ->groupBy(DB::raw("Month(rekammedik_created_at)"))
            ->pluck('count')
        ;

        $chart = new RekamMedikBarChart;
        $chart->dataset('Jumlah Pasien', 'bar', $rekammedik)->options([
            'fill' => 'true',
            'borderColor' => '#51C1C0',
        ]);

        return $chart->api();
    }

    public function adm_profil($user_id)
    {
        $admins = Admin::where('user_id', $user_id)->first();
        $age = Carbon::parse($admins->tgl_lhr_admin)->diff(Carbon::now())->y;

        return view('admin.admin_profil', compact('admins', 'age'));
    }

    public function adm_edit($user_id)
    {
        $admins = Admin::where('user_id', '=', $user_id)->first();
        $age = Carbon::parse($admins->tgl_lhr_admin)->diff(Carbon::now())->y;

        return view('admin.manajemen.edit.edit_profil', compact('admins', 'age'));
    }

    public function adm_update(Request $request, $user_id)
    {
        $admins = Admin::where('user_id', '=', $user_id)->first();

        $admins->update([
            'nama_admin' => $request->input('nama'),
            'jk_admin' => $request->input('jk'),
            'tempat_lhr_admin' => $request->input('tempat_lhr'),
            'tgl_lhr_admin' => $request->input('tgl_lhr'),
            'no_hp_admin' => $request->input('no_hp'),
            'alamat_admin' => $request->input('alamat'),
        ]);

        return redirect()->route('adm_profil', $admins->user_id);
    }

    public function adm_edit_userpw($user_id)
    {
        $admins = User::where('id', '=', $user_id)->first();

        return view('admin.manajemen.edit.edit_userpw', compact('admins'));
    }

    public function adm_update_userpw(Request $request, $user_id)
    {
        $admins = User::where('id', '=', $user_id)->first();

        $admins->update([
            'username' => $request->input('username'),
            'password' => Hash::make($request->get('password')),
        ]);

        return redirect()->route('adm_profil', $admins->id);    
    }

    public function adm_jadwal()
    {
        $jadwals = Jadwal::get();
        
        return view('admin.admin_jadwal', compact('jadwals'));
    }

    public function adm_jadwal_edit($id)
    {
        $jadwals = Jadwal::where('id', $id)->first();
        $tenkes = Tenkesehatan::all();
        
        return view('admin.manajemen.edit.edit_jadwal', compact('jadwals', 'tenkes'));
    }

    public function adm_jadwal_update(Request $request, $id)
    {
        $jadwals = Jadwal::find($id);
        $tenkes = DB::table('jadwal_tenkesehatan')->where('jadwal_id', $id)->get();
        
        if(Request()->tenkes1) {
            if(Request()->tenkes2) {
                $tenkes1 = Request()->tenkes1;
                $tenkes2 = Request()->tenkes2;
                
                foreach ($tenkes as $t) {
                    $jadwals->tenkesehatan()->detach($t);
                }
                $jadwals->tenkesehatan()->attach([$tenkes1, $tenkes2]);
            }
            else {
                $tenkes1 = Request()->tenkes1;
                $tenkes2 = null;
                foreach ($tenkes as $t) {
                    $jadwals->tenkesehatan()->detach($t);
                }
                $jadwals->tenkesehatan()->attach([$tenkes1, $tenkes2]);
            }
        }
        else {
            if(Request()->tenkes2) {
                $tenkes1 = null;
                $tenkes2 = Request()->tenkes2;
                foreach ($tenkes as $t) {
                    $jadwals->tenkesehatan()->detach($t);
                }
                $jadwals->tenkesehatan()->attach([$tenkes1, $tenkes2]);
            }
            else {
                $tenkes1 = null;
                $tenkes2 = null;
                foreach ($tenkes as $t) {
                    $jadwals->tenkesehatan()->detach($t);
                }
                $jadwals->tenkesehatan()->attach([$tenkes1, $tenkes2]);
            }
        }

        $jadwal = Jadwal::where('id', $id)->first();
        $jadwal->update([
            'pagi_s' => $request->input('pagi_s'),
            'pagi_n' => $request->input('pagi_n'),
            'siang_s' => $request->input('siang_s'),
            'siang_n' => $request->input('siang_n'),
        ]);

        #dd($jadwals, $id);
        return redirect()->route('adm_jadwal', $id);
    }

    //------------rekap rekam medik-------------//
    public function adm_rekap_rekam_medik()
    {
        $pasien = Pasien::all();
        $rekammedik = RekamMedik::all();//dd($rekammedik);  

        return view('admin.rekap_rekam_medik', compact('pasien', 'rekammedik'));
    }
    public function filterRekamMedik()
    {
        $columns = [
            'nama_pasien',
            'rekammedik_created_at',
            'nama_kategori',
            'nama_tenkes',
            ''
        ];
        $orderBy = $columns[request()->input("order.0.column")];
        $data = DB::table('rekam_mediks as rk')
        ->join('tenkesehatans as t', 't.id', 'rk.tenkesehatan_id')
        ->join('pasiens as p', 'p.id', 'rk.pasien_id')
        ->join('categories as c', 'c.id', 'p.category_id')
        ->join('diagnosas as d', 'rk.diagnosa_id', 'd.id')
        ->select('rk.id', 'rk.pasien_id', 'rk.tenkesehatan_id', 'rk.diagnosa_id', 'rk.suhu', 'rk.tensi', 'rk.keluhan', 'rk.keterangan', 'rk.rekammedik_created_at',
            't.nama_tenkes',
            'p.nama_pasien', 
            'c.nama_kategori', 
            'd.nama_diagnosa', 'd.kode_diagnosa')
        ;

        if(request()->input("search.value")) {
            $data = $data->where(function($query){
                $query->whereRaw('LOWER(nama_pasien) like ?', ['%'.strtolower(request()->input("search.value")).'%'])
                ->orWhereRaw('LOWER(rekammedik_created_at) like ?', ['%'.strtolower(request()->input("search.value")).'%'])
                ->orWhereRaw('LOWER(nama_kategori) like ?', ['%'.strtolower(request()->input("search.value")).'%'])
                ->orWhereRaw('LOWER(nama_tenkes) like ?', ['%'.strtolower(request()->input("search.value")).'%']);
            });
        }

        if(request()->input('mulai') and request()->input('habis')) {
            $data = $data->whereBetween('rekammedik_created_at', [request()->input('mulai'), request()->input('habis')])
            ->orWhereDate('rekammedik_created_at', request()->input('habis'));
        }

        $recordsFiltered = $data->get()->count();
        $data = $data
            ->skip(request()->input('start'))
            ->take(request()->input('length'))
            ->orderBy($orderBy, request()->input("order.0.dir"))
            ->get();
        $recordsTotal = $data->count();

        return response()->json([
            'draw' => request()->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    //-----------filter grafik bar--------------//
    public function filterGrafikBar()
    {
        $data = DB::table('rekam_mediks')
        ->whereYear('rekammedik_created_at', request()->input('tahun'))
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->rekammedik_created_at)->format('m');
        });

        $usermcount = [];
        $userArr = [];

        foreach ($data as $key => $value) {
            $usermcount[(int)$key] = count($value);
        }

        for ($i=1; $i <= 12 ; $i++) { 
            if(!empty($usermcount[$i])) {
                $userArr[$i]['count'] = $usermcount[$i];
            }
            else {
                $userArr[$i]['count'] = 0;
            }
        }
        
        return json_encode(compact('userArr'));
    }

    //-----------function manajemen----------//
    public function adm_man_datapasien()
    {
        $pasiens = Pasien::all();
        
        return view('admin.manajemen.man_datapasien', compact('pasiens'));
    }
    public function adm_man_dataapoteker()
    {
        $apotekers = Apoteker::all();

        return view('admin.manajemen.man_dataapoteker', compact('apotekers'));
    }
    public function adm_man_datanakes()
    {
        $tenkes = Tenkesehatan::all();
        
        return view('admin.manajemen.man_datanakes', compact('tenkes'));
    }
    public function adm_man_datarekammedik()
    {
        $pasiens = Pasien::get();

        return view('admin.manajemen.man_rekammedik', compact('pasiens'));
    }

    //------------Tambah data------------//
    #Tambah data pasien
    public function adm_man_datapasien_tambah()
    {
        $users = User::all()->last();
        $fakultas = Fakulta::all();
        $prodis = Prodi::all();
        $category = Category::all();

        return view('admin.manajemen.add.add_datapasien', compact('users', 'fakultas', 'prodis', 'category'));
    }
    public function adm_man_datapasien_add(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'tempat_lhr' => 'required',
            'tgl_lhr' => 'required',
            'no_hp' => 'required',
            'alamat' => 'required',
            'jk' => 'required',
            'category_id' => 'required',
        ]);

        if($validated)
        {
            User::create([
                'role_id' => '2',
                'username' => Str::lower(Str::random(6)),
                'password' => Hash::make(12345678),
                'user_created_at' => Carbon::now(),
                'user_updated_at' => Carbon::now(),
            ]);
            
            if(Request()->fakulta_id <> ""){
                if(Request()->prodi_id <> ""){
                    Pasien::create([
                        'user_id' => $request->input('user_id'),
                        'category_id' => $request->input('category_id'),
                        'fakulta_id' => $request->input('fakulta_id'),
                        'prodi_id' => $request->input('prodi_id'),
                        'nama_pasien' => $request->input('nama'),
                        'tempat_lhr_pasien' => $request->input('tempat_lhr'),
                        'tgl_lhr_pasien' => $request->input('tgl_lhr'),
                        'no_hp_pasien' => $request->input('no_hp'),
                        'alamat_pasien' => $request->input('alamat'),
                        'jk_pasien' => $request->input('jk'),
                        'pasien_created_at' => Carbon::now(),
                        'pasien_updated_at' => Carbon::now(),
                    ]);
                }
                else {
                    Pasien::create([
                        'user_id' => $request->input('user_id'),
                        'category_id' => $request->input('category_id'),
                        'fakulta_id' => $request->input('fakulta_id'),
                        'prodi_id' => '1',
                        'nama_pasien' => $request->input('nama'),
                        'tempat_lhr_pasien' => $request->input('tempat_lhr'),
                        'tgl_lhr_pasien' => $request->input('tgl_lhr'),
                        'no_hp_pasien' => $request->input('no_hp'),
                        'alamat_pasien' => $request->input('alamat'),
                        'jk_pasien' => $request->input('jk'),
                        'pasien_created_at' => Carbon::now(),
                        'pasien_updated_at' => Carbon::now(),
                    ]);
                }
            }
            else {
                Pasien::create([
                    'user_id' => $request->input('user_id'),
                    'category_id' => $request->input('category_id'),
                    'fakulta_id' => '1',
                    'prodi_id' => '1',
                    'nama_pasien' => $request->input('nama'),
                    'tempat_lhr_pasien' => $request->input('tempat_lhr'),
                    'tgl_lhr_pasien' => $request->input('tgl_lhr'),
                    'no_hp_pasien' => $request->input('no_hp'),
                    'alamat_pasien' => $request->input('alamat'),
                    'jk_pasien' => $request->input('jk'),
                    'pasien_created_at' => Carbon::now(),
                    'pasien_updated_at' => Carbon::now(),
                ]);
            }
        }

        return redirect()->route('adm_man_datapasien')->with(['success' => 'Data berhasil ditambahkan!']);
    }

    #Tambah data apoteker
    public function adm_man_dataapoteker_tambah()
    {
        $users = User::all()->last();

        return view('admin.manajemen.add.add_dataapoteker', compact('users'));
    }
    public function adm_man_dataapoteker_add(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'tempat_lhr' => 'required',
            'tgl_lhr' => 'required',
            'no_hp' => 'required',
            'alamat' => 'required',
            'jk' => 'required',
        ]);

        if($validated)
        {
            User::create([
                'role_id' => '4',
                'username' => Str::lower(Str::random(6)),
                'password' => Hash::make(12345678),
            ]);
            Apoteker::create([
                'user_id' => $request->input('user_id'),
                'nama_apoteker' => $request->input('nama'),
                'tempat_lhr_apoteker' => $request->input('tempat_lhr'),
                'tgl_lhr_apoteker' => $request->input('tgl_lhr'),
                'nohp_apoteker' => $request->input('no_hp'),
                'alamat_apoteker' => $request->input('alamat'),
                'jk_apoteker' => $request->input('jk'),
                'apoteker_created_at' => Carbon::now(),
                'apoteker_updated_at' => Carbon::now(),
            ]);
        }

        return redirect()->route('adm_man_dataapoteker')->with(['success' => 'Data berhasil ditambahkan!']);
    }

    #Tambah data nakes
    public function adm_man_datanakes_tambah()
    {
        $users = User::all()->last();

        return view('admin.manajemen.add.add_datanakes', compact('users'));
    }
    public function adm_man_datanakes_add(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'tempat_lhr' => 'required',
            'tgl_lhr' => 'required',
            'no_hp' => 'required',
            'alamat' => 'required',
            'jk' => 'required',
        ]);

        if($validated)
        {
            User::create([
                'role_id' => '3',
                'username' => Str::lower(Str::random(6)),
                'password' => Hash::make(12345678),
            ]);
            Tenkesehatan::create([
                'user_id' => $request->input('user_id'),
                'nama_tenkes' => $request->input('nama'),
                'tempat_lhr_tenkes' => $request->input('tempat_lhr'),
                'tgl_lhr_tenkes' => $request->input('tgl_lhr'),
                'nohp_tenkes' => $request->input('no_hp'),
                'alamat_tenkes' => $request->input('alamat'),
                'jk_tenkes' => $request->input('jk'),
                'tenkes_created_at' => Carbon::now(),
                'tenkes_updated_at' => Carbon::now(),
            ]);
        }

        return redirect()->route('adm_man_datanakes')->with(['success' => 'Data berhasil ditambahkan!']);
    }

    //--------------Edit data-------------//
    #Edit data pasien
    public function adm_man_datapasien_edit($id)
    {
        $pasiens = Pasien::where('id', $id)->first();
        $fakultas = Fakulta::all();
        $prodis = Prodi::all();
        $category = Category::all();
        
        return view('admin.manajemen.edit.edit_datapasien', compact('pasiens', 'fakultas', 'prodis', 'category'));
    }
    public function adm_man_datapasien_update(Request $request, $id)
    {
        $pasiens = Pasien::where('id', $id);

        if(Request()->fakulta_id <> ""){
            if(Request()->prodi_id <> ""){
                $pasiens->update([
                    'category_id' => $request->input('category_id'),
                    'fakulta_id' => $request->input('fakulta_id'),
                    'prodi_id' => $request->input('prodi_id'),
                    'nama_pasien' => $request->input('nama'),
                    'tempat_lhr_pasien' => $request->input('tempat_lhr'),
                    'tgl_lhr_pasien' => $request->input('tgl_lhr'),
                    'no_hp_pasien' => $request->input('no_hp'),
                    'alamat_pasien' => $request->input('alamat'),
                    'jk_pasien' => $request->input('jk'),
                ]);
            }
            else {
                $pasiens->update([
                    'category_id' => $request->input('category_id'),
                    'fakulta_id' => $request->input('fakulta_id'),
                    'prodi_id' => '1',
                    'nama_pasien' => $request->input('nama'),
                    'tempat_lhr_pasien' => $request->input('tempat_lhr'),
                    'tgl_lhr_pasien' => $request->input('tgl_lhr'),
                    'no_hp_pasien' => $request->input('no_hp'),
                    'alamat_pasien' => $request->input('alamat'),
                    'jk_pasien' => $request->input('jk'),
                ]);
            }
        }
        else {
            $pasiens->update([
                    'category_id' => $request->input('category_id'),
                    'fakulta_id' => '1',
                    'prodi_id' => '1',
                    'nama_pasien' => $request->input('nama'),
                    'tempat_lhr_pasien' => $request->input('tempat_lhr'),
                    'tgl_lhr_pasien' => $request->input('tgl_lhr'),
                    'no_hp_pasien' => $request->input('no_hp'),
                    'alamat_pasien' => $request->input('alamat'),
                    'jk_pasien' => $request->input('jk'),
                ]);
        }
        return redirect()->route('adm_man_datapasien')->with(['success' => 'Data berhasil diubah!']);
    }

    #Edit data apoteker
    public function adm_man_dataapoteker_edit($id)
    {
        $apoteker = Apoteker::where('id', $id)->first();

        return view('admin.manajemen.edit.edit_dataapoteker', compact('apoteker'));
    }
    public function adm_man_dataapoteker_update(Request $request, $id)
    {
        $apoteker = Apoteker::where('id', $id);

        $apoteker->update([
            'nama_apoteker' => $request->input('nama'),
            'tempat_lhr_apoteker' => $request->input('tempat_lhr'),
            'tgl_lhr_apoteker' => $request->input('tgl_lhr'),
            'nohp_apoteker' => $request->input('no_hp'),
            'alamat_apoteker' => $request->input('alamat'),
            'jk_apoteker' => $request->input('jk'),
        ]);

        return redirect()->route('adm_man_dataapoteker')->with(['success' => 'Data berhasil diubah!']);
    }

    #Edit data nakes
    public function adm_man_datanakes_edit($id)
    {
        $tenkes = Tenkesehatan::where('id', $id)->first();
        $katenkes = Kategori_tenkesehatan::all();

        return view('admin.manajemen.edit.edit_datanakes', compact('tenkes', 'katenkes'));
    }
    public function adm_man_datanakes_update(Request $request, $id)
    {
        $tenkes = Tenkesehatan::where('id', $id)->first();
        $tenkes->update([
            'nama_tenkes' => $request->input('nama'),
            'kategori_tenkesehatan_id_tenkes' => $request->input('kts'),
            'tempat_lhr_tenkes' => $request->input('tempat_lhr'),
            'tgl_lhr_tenkes' => $request->input('tgl_lhr'),
            'nohp_tenkes' => $request->input('no_hp'),
            'alamat_tenkes' => $request->input('alamat'),
            'jk_tenkes' => $request->input('jk'),
        ]);

        return redirect()->route('adm_man_datanakes')->with(['success' => 'Data berhasil diubah!']);
    }

    #Edit data rekam medik
    public function edit_datarekammedik($id)
    {
        $pasien = Pasien::find($id);
        $nakes = Tenkesehatan::all();

        return view('admin.manajemen.edit.edit_datarekammedik', compact('pasien', 'nakes'));
    }
    public function kirim_datarekammedik(Request $request, $id)
    {
        $validated = $request->validate([
            'nakes_id' => 'required',
        ]);
        
        $tenkes = Tenkesehatan::find(Request()->nakes_id);

        if($validated) {
            $rekammedik = RekamMedik::create([
                'pasien_id' => $id,
                'tenkesehatan_id' => Request()->nakes_id,
                'suhu' => Request()->suhu,
                'tensi' => Request()->tensi,
            ]);

            $rkm = RekamMedik::all()->last();
            $notif = Notification::create([
                'rekam_medik_id' => $rkm->id,
                'user_id' => $tenkes->user->id,
                'isi' => 'Pasien ingin berobat!',
            ]);

            MedicalRecordSent::dispatch($rekammedik, $notif);
        }

        return redirect()->route('adm_man_datarekammedik')->with(['success' => 'Data berhasil dikirim!']);
    }

    //---------------Delete data---------//
    public function delete_datapasien($id)
    {
        $pasiens = Pasien::where('id', $id)->first();
        $users = User::where('id', $pasiens->user_id)->first();
        $pasiens->delete();
        $users->delete();

        return redirect()->route('adm_man_datapasien')->with(['success' => 'Data berhasil dihapus!']);
    }
    public function delete_dataapoteker($id)
    {
        $apoteker = Apoteker::where('id', $id)->first();
        $users = User::where('id', $apoteker->user_id)->first();
        $apoteker->delete();
        $users->delete();

        return redirect()->route('adm_man_dataapoteker')->with(['success' => 'Data berhasil dihapus!']);
    }
    public function delete_datanakes($id)
    {
        // $jadwal = Jadwal::where('tenkesehatan_id', $id)->get();
        $jadwal = DB::table('jadwal_tenkesehatan')->where('tenkesehatan_id', $id)->update(['tenkesehatan_id' => null]);
        $tenkes = Tenkesehatan::where('id', $id)->first();
        $users = User::where('id', $tenkes->user_id)->first();

        $tenkes->delete();
        $users->delete();

        return redirect()->route('adm_man_datanakes')->with(['success' => 'Data berhasil dihapus!']);
    }
}