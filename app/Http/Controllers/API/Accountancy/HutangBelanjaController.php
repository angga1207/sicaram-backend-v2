<?php

namespace App\Http\Controllers\API\Accountancy;

use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HutangBelanjaController extends Controller
{
    use JsonReturner;

    function getIndex(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = [];
            // $datas = DB::table('acc_hutang_belanja')
            //     ->when($request->instance, function ($query) use ($request) {
            //         return $query->where('instance_id', $request->instance);
            //     })
            //     ->when($request->year, function ($query) use ($request) {
            //         return $query->where('year', $request->year);
            //     })
            //     ->get();

            $arrPembayaran = DB::table('acc_htb_pembayaran_hutang')
                ->when($request->instance, function ($query) use ($request) {
                    return $query->where('instance_id', $request->instance);
                })
                ->when($request->year, function ($query) use ($request) {
                    return $query->where('year', $request->year);
                })
                ->where('deleted_at', null)
                ->get();
            $arrHutangBaru = DB::table('acc_htb_hutang_baru')
                ->when($request->instance, function ($query) use ($request) {
                    return $query->where('instance_id', $request->instance);
                })
                ->when($request->year, function ($query) use ($request) {
                    return $query->where('year', $request->year);
                })
                ->where('deleted_at', null)
                ->get();

            // merge data
            foreach ($arrPembayaran as $data) {
                $instance = DB::table('instances')->where('id', $data->instance_id)->first();
                $kodeRekening = DB::table('ref_kode_rekening_complete')->where('id', $data->kode_rekening_id)->first();
                $datas[] = [
                    'type' => 'pembayaran',
                    'id' => $data->id,
                    'instance_id' => $data->instance_id,
                    'instance_name' => $instance->name,
                    'periode_id' => $data->periode_id,
                    'year' => $data->year,
                    'kode_rekening_id' => $data->kode_rekening_id,
                    'kode_rekening_fullcode' => $kodeRekening->fullcode ?? null,
                    'kode_rekening_name' => $kodeRekening->name ?? null,
                    'nama_kegiatan' => $data->nama_kegiatan,
                    'pelaksana_pekerjaan' => $data->pelaksana_pekerjaan,
                    'nomor_kontrak' => $data->nomor_kontrak,
                    'tahun_kontrak' => $data->tahun_kontrak,

                    'kewajiban_tidak_terbayar' => $data->kewajiban_tidak_terbayar,
                    'kewajiban_tidak_terbayar_last_year' => $data->kewajiban_tidak_terbayar_last_year,
                    'nilai_kontrak' => 0,

                    'p1_nomor_sp2d' => $data->p1_nomor_sp2d,
                    'p1_tanggal' => $data->p1_tanggal,
                    'p1_jumlah' => $data->p1_jumlah,
                    'p2_nomor_sp2d' => $data->p2_nomor_sp2d,
                    'p2_tanggal' => $data->p2_tanggal,
                    'p2_jumlah' => $data->p2_jumlah,
                    'p3_nomor_sp2d' => 0,
                    'p3_tanggal' => 0,
                    'p3_jumlah' => 0,

                    'jumlah_pembayaran_hutang' => $data->jumlah_pembayaran_hutang,
                    'hutang_baru' => 0,
                    'sisa_hutang' => $data->sisa_hutang,

                    'pegawai' => $data->pegawai,
                    'persediaan' => $data->persediaan,
                    'perjadin' => $data->perjadin,
                    'jasa' => $data->jasa,
                    'pemeliharaan' => $data->pemeliharaan,
                    'uang_jasa_diserahkan' => $data->uang_jasa_diserahkan,
                    'hibah' => $data->hibah,

                    'aset_tetap_tanah' => $data->aset_tetap_tanah,
                    'aset_tetap_peralatan_mesin' => $data->aset_tetap_peralatan_mesin,
                    'aset_tetap_gedung_bangunan' => $data->aset_tetap_gedung_bangunan,
                    'aset_tetap_jalan_jaringan_irigasi' => $data->aset_tetap_jalan_jaringan_irigasi,
                    'aset_tetap_lainnya' => $data->aset_tetap_lainnya,
                    'konstruksi_dalam_pekerjaan' => $data->konstruksi_dalam_pekerjaan,
                    'aset_lain_lain' => $data->aset_lain_lain,

                    'beban' => $data->beban,
                    'jangka_pendek' => $data->jangka_pendek,
                    'total_hutang' => floatval($data->beban ?? 0) + floatval($data->jangka_pendek ?? 0),
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at
                ];
            }

            foreach ($arrHutangBaru as $data) {
                $instance = DB::table('instances')->where('id', $data->instance_id)->first();
                $kodeRekening = DB::table('ref_kode_rekening_complete')->where('id', $data->kode_rekening_id)->first();
                $datas[] = [
                    'type' => 'hutang_baru',
                    'id' => $data->id,
                    'instance_id' => $data->instance_id,
                    'instance_name' => $instance->name,
                    'periode_id' => $data->periode_id,
                    'year' => $data->year,
                    'kode_rekening_id' => $data->kode_rekening_id,
                    'kode_rekening_fullcode' => $kodeRekening->fullcode ?? null,
                    'kode_rekening_name' => $kodeRekening->name ?? null,
                    'nama_kegiatan' => $data->nama_kegiatan,
                    'pelaksana_pekerjaan' => $data->pelaksana_pekerjaan,
                    'nomor_kontrak' => $data->nomor_kontrak,
                    'tahun_kontrak' => $data->tahun_kontrak,

                    'kewajiban_tidak_terbayar' => 0,
                    'kewajiban_tidak_terbayar_last_year' => 0,
                    'nilai_kontrak' => $data->nilai_kontrak,

                    'p1_nomor_sp2d' => $data->p1_nomor_sp2d,
                    'p1_tanggal' => $data->p1_tanggal,
                    'p1_jumlah' => $data->p1_jumlah,
                    'p2_nomor_sp2d' => $data->p2_nomor_sp2d,
                    'p2_tanggal' => $data->p2_tanggal,
                    'p2_jumlah' => $data->p2_jumlah,
                    'p3_nomor_sp2d' => $data->p3_nomor_sp2d,
                    'p3_tanggal' => $data->p3_tanggal,
                    'p3_jumlah' => $data->p3_jumlah,

                    'jumlah_pembayaran_hutang' => $data->jumlah_pembayaran_hutang,
                    'hutang_baru' => $data->hutang_baru,
                    'sisa_hutang' => 0,

                    'pegawai' => $data->pegawai,
                    'persediaan' => $data->persediaan,
                    'perjadin' => $data->perjadin,
                    'jasa' => $data->jasa,
                    'pemeliharaan' => $data->pemeliharaan,
                    'uang_jasa_diserahkan' => $data->uang_jasa_diserahkan,
                    'hibah' => $data->hibah,

                    'aset_tetap_tanah' => $data->aset_tetap_tanah,
                    'aset_tetap_peralatan_mesin' => $data->aset_tetap_peralatan_mesin,
                    'aset_tetap_gedung_bangunan' => $data->aset_tetap_gedung_bangunan,
                    'aset_tetap_jalan_jaringan_irigasi' => $data->aset_tetap_jalan_jaringan_irigasi,
                    'aset_tetap_lainnya' => $data->aset_tetap_lainnya,
                    'konstruksi_dalam_pekerjaan' => $data->konstruksi_dalam_pekerjaan,
                    'aset_lain_lain' => $data->aset_lain_lain,

                    'beban' => $data->beban,
                    'jangka_pendek' => $data->jangka_pendek,
                    'total_hutang' => floatval($data->beban ?? 0) + floatval($data->jangka_pendek ?? 0),
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at
                ];
            }

            $datas = collect($datas)->sortBy('created_at')->values()->all();

            return $this->successResponse($datas);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    function storeData(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'required|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
        ], [], [
            'periode' => 'Periode',
            'year' => 'Tahun',
            'data.*.instance_id' => 'Perangkat Daerah'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateNow = now();
            foreach ($request->data as $input) {
                if ($input['instance_id']) {
                    if (!$input['id']) {
                        DB::table('acc_hutang_belanja')->insert([
                            'instance_id' => $input['instance_id'],
                            'periode_id' => $request->periode,
                            'year' => $request->year,

                            'kode_rekening_id' => $input['kode_rekening_id'],
                            'nama_kegiatan' => $input['nama_kegiatan'],
                            'pelaksana_pekerjaan' => $input['pelaksana_pekerjaan'],
                            'nomor_kontrak' => $input['nomor_kontrak'],
                            'tahun_kontrak' => $input['tahun_kontrak'],
                            'nilai_kontrak' => $input['nilai_kontrak'],
                            'kewajiban_tidak_terbayar' => $input['kewajiban_tidak_terbayar'],
                            'kewajiban_tidak_terbayar_last_year' => $input['kewajiban_tidak_terbayar_last_year'],
                            'p1_nomor_sp2d' => $input['p1_nomor_sp2d'],
                            'p1_tanggal' => $input['p1_tanggal'],
                            'p1_jumlah' => $input['p1_jumlah'],
                            'p2_nomor_sp2d' => $input['p2_nomor_sp2d'],
                            'p2_tanggal' => $input['p2_tanggal'],
                            'p2_jumlah' => $input['p2_jumlah'],
                            'p3_nomor_sp2d' => $input['p3_nomor_sp2d'],
                            'p3_tanggal' => $input['p3_tanggal'],
                            'p3_jumlah' => $input['p3_jumlah'],
                            'jumlah_pembayaran_hutang' => $input['jumlah_pembayaran_hutang'],
                            'hutang_baru' => $input['hutang_baru'],
                            'pegawai' => $input['pegawai'],
                            'persediaan' => $input['persediaan'],
                            'perjadin' => $input['perjadin'],
                            'jasa' => $input['jasa'],
                            'pemeliharaan' => $input['pemeliharaan'],
                            'hibah' => $input['hibah'],
                            'aset_tetap_tanah' => $input['aset_tetap_tanah'],
                            'aset_tetap_peralatan_mesin' => $input['aset_tetap_peralatan_mesin'],
                            'aset_tetap_gedung_bangunan' => $input['aset_tetap_gedung_bangunan'],
                            'aset_tetap_jalan_jaringan_irigasi' => $input['aset_tetap_jalan_jaringan_irigasi'],
                            'aset_tetap_lainnya' => $input['aset_tetap_lainnya'],
                            'konstruksi_dalam_pekerjaan' => $input['konstruksi_dalam_pekerjaan'],
                            'aset_lain_lain' => $input['aset_lain_lain'],

                            'created_at' => $dateNow,
                            'updated_at' => $dateNow
                        ]);
                    } else {
                        DB::table('acc_hutang_belanja')
                            ->where('id', $input['id'])
                            ->update([
                                'instance_id' => $input['instance_id'],
                                'periode_id' => $request->periode,
                                'year' => $request->year,

                                'kode_rekening_id' => $input['kode_rekening_id'],
                                'nama_kegiatan' => $input['nama_kegiatan'],
                                'pelaksana_pekerjaan' => $input['pelaksana_pekerjaan'],
                                'nomor_kontrak' => $input['nomor_kontrak'],
                                'tahun_kontrak' => $input['tahun_kontrak'],
                                'nilai_kontrak' => $input['nilai_kontrak'],
                                'kewajiban_tidak_terbayar' => $input['kewajiban_tidak_terbayar'],
                                'kewajiban_tidak_terbayar_last_year' => $input['kewajiban_tidak_terbayar_last_year'],
                                'p1_nomor_sp2d' => $input['p1_nomor_sp2d'],
                                'p1_tanggal' => $input['p1_tanggal'],
                                'p1_jumlah' => $input['p1_jumlah'],
                                'p2_nomor_sp2d' => $input['p2_nomor_sp2d'],
                                'p2_tanggal' => $input['p2_tanggal'],
                                'p2_jumlah' => $input['p2_jumlah'],
                                'p3_nomor_sp2d' => $input['p3_nomor_sp2d'],
                                'p3_tanggal' => $input['p3_tanggal'],
                                'p3_jumlah' => $input['p3_jumlah'],
                                'jumlah_pembayaran_hutang' => $input['jumlah_pembayaran_hutang'],
                                'hutang_baru' => $input['hutang_baru'],
                                'pegawai' => $input['pegawai'],
                                'persediaan' => $input['persediaan'],
                                'perjadin' => $input['perjadin'],
                                'jasa' => $input['jasa'],
                                'pemeliharaan' => $input['pemeliharaan'],
                                'hibah' => $input['hibah'],
                                'aset_tetap_tanah' => $input['aset_tetap_tanah'],
                                'aset_tetap_peralatan_mesin' => $input['aset_tetap_peralatan_mesin'],
                                'aset_tetap_gedung_bangunan' => $input['aset_tetap_gedung_bangunan'],
                                'aset_tetap_jalan_jaringan_irigasi' => $input['aset_tetap_jalan_jaringan_irigasi'],
                                'aset_tetap_lainnya' => $input['aset_tetap_lainnya'],
                                'konstruksi_dalam_pekerjaan' => $input['konstruksi_dalam_pekerjaan'],
                                'aset_lain_lain' => $input['aset_lain_lain'],

                                'updated_at' => $dateNow
                            ]);
                    }
                }
            }

            DB::commit();
            return $this->successResponse(null, 'Data Hutang Belanja berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function getPembayaranHutang(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }


        try {
            $datas = [];
            $arrData = DB::table('acc_htb_pembayaran_hutang')
                ->when($request->instance, function ($query) use ($request) {
                    return $query->where('instance_id', $request->instance);
                })
                ->when($request->year, function ($query) use ($request) {
                    return $query->where('year', $request->year);
                })
                ->where('deleted_at', null)
                ->get();
            foreach ($arrData as $data) {
                $instance = DB::table('instances')->where('id', $data->instance_id)->first();
                $kodeRekening = DB::table('ref_kode_rekening_complete')->where('id', $data->kode_rekening_id)->first();
                $datas[] = [
                    'id' => $data->id,
                    'instance_id' => $data->instance_id,
                    'instance_name' => $instance->name,
                    'periode_id' => $data->periode_id,
                    'year' => $data->year,
                    'kode_rekening_id' => $data->kode_rekening_id,
                    'kode_rekening_fullcode' => $kodeRekening->fullcode ?? null,
                    'kode_rekening_name' => $kodeRekening->name ?? null,
                    'nama_kegiatan' => $data->nama_kegiatan,
                    'pelaksana_pekerjaan' => $data->pelaksana_pekerjaan,
                    'nomor_kontrak' => $data->nomor_kontrak,
                    'tahun_kontrak' => $data->tahun_kontrak,
                    'kewajiban_tidak_terbayar' => $data->kewajiban_tidak_terbayar,
                    'kewajiban_tidak_terbayar_last_year' => $data->kewajiban_tidak_terbayar_last_year,
                    'p1_nomor_sp2d' => $data->p1_nomor_sp2d,
                    'p1_tanggal' => $data->p1_tanggal,
                    'p1_jumlah' => $data->p1_jumlah,
                    'p2_nomor_sp2d' => $data->p2_nomor_sp2d,
                    'p2_tanggal' => $data->p2_tanggal,
                    'p2_jumlah' => $data->p2_jumlah,
                    'jumlah_pembayaran_hutang' => $data->jumlah_pembayaran_hutang,
                    'sisa_hutang' => $data->sisa_hutang,
                    'pegawai' => $data->pegawai,
                    'persediaan' => $data->persediaan,
                    'perjadin' => $data->perjadin,
                    'jasa' => $data->jasa,
                    'pemeliharaan' => $data->pemeliharaan,
                    'uang_jasa_diserahkan' => $data->uang_jasa_diserahkan,
                    'hibah' => $data->hibah,
                    'aset_tetap_tanah' => $data->aset_tetap_tanah,
                    'aset_tetap_peralatan_mesin' => $data->aset_tetap_peralatan_mesin,
                    'aset_tetap_gedung_bangunan' => $data->aset_tetap_gedung_bangunan,
                    'aset_tetap_jalan_jaringan_irigasi' => $data->aset_tetap_jalan_jaringan_irigasi,
                    'aset_tetap_lainnya' => $data->aset_tetap_lainnya,
                    'konstruksi_dalam_pekerjaan' => $data->konstruksi_dalam_pekerjaan,
                    'aset_lain_lain' => $data->aset_lain_lain,
                    'beban' => $data->beban,
                    'jangka_pendek' => $data->jangka_pendek,
                    'total_hutang' => floatval($data->beban ?? 0) + floatval($data->jangka_pendek ?? 0),
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at
                ];
            }

            return $this->successResponse($datas);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    function storePembayaranHutang(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'required|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
        ], [], [
            'periode' => 'Periode',
            'year' => 'Tahun',
            'data.*.instance_id' => 'Perangkat Daerah'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateNow = now();
            foreach ($request->data as $input) {
                if ($input['instance_id']) {
                    if (!$input['id']) {
                        DB::table('acc_htb_pembayaran_hutang')->insert([
                            'instance_id' => $input['instance_id'],
                            'periode_id' => $request->periode,
                            'year' => $request->year,

                            'kode_rekening_id' => $input['kode_rekening_id'],
                            'nama_kegiatan' => $input['nama_kegiatan'],
                            'pelaksana_pekerjaan' => $input['pelaksana_pekerjaan'],
                            'nomor_kontrak' => $input['nomor_kontrak'],
                            'tahun_kontrak' => $input['tahun_kontrak'],
                            'kewajiban_tidak_terbayar' => $input['kewajiban_tidak_terbayar'],
                            'kewajiban_tidak_terbayar_last_year' => $input['kewajiban_tidak_terbayar_last_year'],
                            'p1_nomor_sp2d' => $input['p1_nomor_sp2d'],
                            'p1_tanggal' => $input['p1_tanggal'],
                            'p1_jumlah' => $input['p1_jumlah'],
                            'p2_nomor_sp2d' => $input['p2_nomor_sp2d'],
                            'p2_tanggal' => $input['p2_tanggal'],
                            'p2_jumlah' => $input['p2_jumlah'],
                            'jumlah_pembayaran_hutang' => $input['jumlah_pembayaran_hutang'],
                            'sisa_hutang' => $input['sisa_hutang'],

                            'pegawai' => $input['pegawai'],
                            'persediaan' => $input['persediaan'],
                            'perjadin' => $input['perjadin'],
                            'jasa' => $input['jasa'],
                            'pemeliharaan' => $input['pemeliharaan'],
                            'uang_jasa_diserahkan' => $input['uang_jasa_diserahkan'],
                            'hibah' => $input['hibah'],

                            'aset_tetap_tanah' => $input['aset_tetap_tanah'],
                            'aset_tetap_peralatan_mesin' => $input['aset_tetap_peralatan_mesin'],
                            'aset_tetap_gedung_bangunan' => $input['aset_tetap_gedung_bangunan'],
                            'aset_tetap_jalan_jaringan_irigasi' => $input['aset_tetap_jalan_jaringan_irigasi'],
                            'aset_tetap_lainnya' => $input['aset_tetap_lainnya'],
                            'konstruksi_dalam_pekerjaan' => $input['konstruksi_dalam_pekerjaan'],
                            'aset_lain_lain' => $input['aset_lain_lain'],

                            'beban' => $input['beban'],
                            'jangka_pendek' => $input['jangka_pendek'],

                            'created_at' => $dateNow,
                            'updated_at' => $dateNow
                        ]);
                    } else {
                        DB::table('acc_htb_pembayaran_hutang')
                            ->where('id', $input['id'])
                            ->update([
                                'kode_rekening_id' => $input['kode_rekening_id'],
                                'nama_kegiatan' => $input['nama_kegiatan'],
                                'pelaksana_pekerjaan' => $input['pelaksana_pekerjaan'],
                                'nomor_kontrak' => $input['nomor_kontrak'],
                                'tahun_kontrak' => $input['tahun_kontrak'],
                                'kewajiban_tidak_terbayar' => $input['kewajiban_tidak_terbayar'],
                                'kewajiban_tidak_terbayar_last_year' => $input['kewajiban_tidak_terbayar_last_year'],
                                'p1_nomor_sp2d' => $input['p1_nomor_sp2d'],
                                'p1_tanggal' => $input['p1_tanggal'],
                                'p1_jumlah' => $input['p1_jumlah'],
                                'p2_nomor_sp2d' => $input['p2_nomor_sp2d'],
                                'p2_tanggal' => $input['p2_tanggal'],
                                'p2_jumlah' => $input['p2_jumlah'],
                                'jumlah_pembayaran_hutang' => $input['jumlah_pembayaran_hutang'],
                                'sisa_hutang' => $input['sisa_hutang'],

                                'pegawai' => $input['pegawai'],
                                'persediaan' => $input['persediaan'],
                                'perjadin' => $input['perjadin'],
                                'jasa' => $input['jasa'],
                                'pemeliharaan' => $input['pemeliharaan'],
                                'uang_jasa_diserahkan' => $input['uang_jasa_diserahkan'],
                                'hibah' => $input['hibah'],

                                'aset_tetap_tanah' => $input['aset_tetap_tanah'],
                                'aset_tetap_peralatan_mesin' => $input['aset_tetap_peralatan_mesin'],
                                'aset_tetap_gedung_bangunan' => $input['aset_tetap_gedung_bangunan'],
                                'aset_tetap_jalan_jaringan_irigasi' => $input['aset_tetap_jalan_jaringan_irigasi'],
                                'aset_tetap_lainnya' => $input['aset_tetap_lainnya'],
                                'konstruksi_dalam_pekerjaan' => $input['konstruksi_dalam_pekerjaan'],
                                'aset_lain_lain' => $input['aset_lain_lain'],

                                'beban' => $input['beban'],
                                'jangka_pendek' => $input['jangka_pendek'],

                                'updated_at' => $dateNow
                            ]);
                    }
                }
            }

            DB::commit();
            return $this->successResponse(null, 'Data Hutang Belanja berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function deletePembayaranHutang(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_htb_pembayaran_hutang,id'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_htb_pembayaran_hutang')
                ->where('id', $request->id)
                // ->update([
                //     'deleted_at' => now(),
                //     'deleted_by' => auth()->id()
                // ]);
                ->delete();

            DB::commit();
            return $this->successResponse(null, 'Data Hutang Belanja berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function getHutangBaru(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }


        try {
            $datas = [];
            $arrData = DB::table('acc_htb_hutang_baru')
                ->when($request->instance, function ($query) use ($request) {
                    return $query->where('instance_id', $request->instance);
                })
                ->when($request->year, function ($query) use ($request) {
                    return $query->where('year', $request->year);
                })
                ->where('deleted_at', null)
                ->get();

            foreach ($arrData as $data) {
                $instance = DB::table('instances')->where('id', $data->instance_id)->first();
                $kodeRekening = DB::table('ref_kode_rekening_complete')->where('id', $data->kode_rekening_id)->first();
                $datas[] = [
                    'id' => $data->id,
                    'instance_id' => $data->instance_id,
                    'instance_name' => $instance->name,
                    'periode_id' => $data->periode_id,
                    'year' => $data->year,
                    'kode_rekening_id' => $data->kode_rekening_id,
                    'kode_rekening_fullcode' => $kodeRekening->fullcode ?? null,
                    'kode_rekening_name' => $kodeRekening->name ?? null,
                    'nama_kegiatan' => $data->nama_kegiatan,
                    'pelaksana_pekerjaan' => $data->pelaksana_pekerjaan,
                    'nomor_kontrak' => $data->nomor_kontrak,
                    'tahun_kontrak' => $data->tahun_kontrak,
                    'nilai_kontrak' => $data->nilai_kontrak,
                    'p1_nomor_sp2d' => $data->p1_nomor_sp2d,
                    'p1_tanggal' => $data->p1_tanggal,
                    'p1_jumlah' => $data->p1_jumlah ?? 0,
                    'p2_nomor_sp2d' => $data->p2_nomor_sp2d,
                    'p2_tanggal' => $data->p2_tanggal,
                    'p2_jumlah' => $data->p2_jumlah ?? 0,
                    'p3_nomor_sp2d' => $data->p3_nomor_sp2d,
                    'p3_tanggal' => $data->p3_tanggal,
                    'p3_jumlah' => $data->p3_jumlah ?? 0,
                    'p4_nomor_sp2d' => $data->p4_nomor_sp2d,
                    'p4_tanggal' => $data->p4_tanggal,
                    'p4_jumlah' => $data->p4_jumlah ?? 0,
                    'jumlah_pembayaran_hutang' => $data->jumlah_pembayaran_hutang,
                    'hutang_baru' => $data->hutang_baru,
                    'pegawai' => $data->pegawai,
                    'persediaan' => $data->persediaan,
                    'perjadin' => $data->perjadin,
                    'jasa' => $data->jasa,
                    'pemeliharaan' => $data->pemeliharaan,
                    'uang_jasa_diserahkan' => $data->uang_jasa_diserahkan,
                    'hibah' => $data->hibah,
                    'aset_tetap_tanah' => $data->aset_tetap_tanah,
                    'aset_tetap_peralatan_mesin' => $data->aset_tetap_peralatan_mesin,
                    'aset_tetap_gedung_bangunan' => $data->aset_tetap_gedung_bangunan,
                    'aset_tetap_jalan_jaringan_irigasi' => $data->aset_tetap_jalan_jaringan_irigasi,
                    'aset_tetap_lainnya' => $data->aset_tetap_lainnya,
                    'konstruksi_dalam_pekerjaan' => $data->konstruksi_dalam_pekerjaan,
                    'aset_lain_lain' => $data->aset_lain_lain,
                    'beban' => $data->beban,
                    'jangka_pendek' => $data->jangka_pendek,
                    'total_hutang' => floatval($data->beban ?? 0) + floatval($data->jangka_pendek ?? 0),
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at
                ];
            }

            return $this->successResponse($datas);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    function storeHutangBaru(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'required|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
        ], [], [
            'periode' => 'Periode',
            'year' => 'Tahun',
            'data.*.instance_id' => 'Perangkat Daerah'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateNow = now();
            foreach ($request->data as $input) {
                if ($input['instance_id']) {
                    if (!$input['id']) {
                        DB::table('acc_htb_hutang_baru')->insert([
                            'instance_id' => $input['instance_id'],
                            'periode_id' => $request->periode,
                            'year' => $request->year,

                            'kode_rekening_id' => $input['kode_rekening_id'],
                            'nama_kegiatan' => $input['nama_kegiatan'],
                            'pelaksana_pekerjaan' => $input['pelaksana_pekerjaan'],
                            'nomor_kontrak' => $input['nomor_kontrak'],
                            'tahun_kontrak' => $input['tahun_kontrak'],
                            'nilai_kontrak' => $input['nilai_kontrak'],
                            'p1_nomor_sp2d' => $input['p1_nomor_sp2d'],
                            'p1_tanggal' => $input['p1_tanggal'],
                            'p1_jumlah' => $input['p1_jumlah'] ?? 0,
                            'p2_nomor_sp2d' => $input['p2_nomor_sp2d'],
                            'p2_tanggal' => $input['p2_tanggal'],
                            'p2_jumlah' => $input['p2_jumlah'] ?? 0,
                            'p3_nomor_sp2d' => $input['p3_nomor_sp2d'],
                            'p3_tanggal' => $input['p3_tanggal'],
                            'p3_jumlah' => $input['p3_jumlah'] ?? 0,
                            'p4_nomor_sp2d' => $input['p4_nomor_sp2d'],
                            'p4_tanggal' => $input['p4_tanggal'],
                            'p4_jumlah' => $input['p4_jumlah'] ?? 0,
                            'jumlah_pembayaran_hutang' => $input['jumlah_pembayaran_hutang'],
                            'hutang_baru' => $input['hutang_baru'],

                            'pegawai' => $input['pegawai'],
                            'persediaan' => $input['persediaan'],
                            'perjadin' => $input['perjadin'],
                            'jasa' => $input['jasa'],
                            'pemeliharaan' => $input['pemeliharaan'],
                            'uang_jasa_diserahkan' => $input['uang_jasa_diserahkan'],
                            'hibah' => $input['hibah'],

                            'aset_tetap_tanah' => $input['aset_tetap_tanah'],
                            'aset_tetap_peralatan_mesin' => $input['aset_tetap_peralatan_mesin'],
                            'aset_tetap_gedung_bangunan' => $input['aset_tetap_gedung_bangunan'],
                            'aset_tetap_jalan_jaringan_irigasi' => $input['aset_tetap_jalan_jaringan_irigasi'],
                            'aset_tetap_lainnya' => $input['aset_tetap_lainnya'],
                            'konstruksi_dalam_pekerjaan' => $input['konstruksi_dalam_pekerjaan'],
                            'aset_lain_lain' => $input['aset_lain_lain'],

                            'beban' => $input['beban'],
                            'jangka_pendek' => $input['jangka_pendek'],

                            'created_at' => $dateNow,
                            'updated_at' => $dateNow
                        ]);
                    } else {
                        DB::table('acc_htb_hutang_baru')
                            ->where('id', $input['id'])
                            ->update([
                                'kode_rekening_id' => $input['kode_rekening_id'],
                                'nama_kegiatan' => $input['nama_kegiatan'],
                                'pelaksana_pekerjaan' => $input['pelaksana_pekerjaan'],
                                'nomor_kontrak' => $input['nomor_kontrak'],
                                'tahun_kontrak' => $input['tahun_kontrak'],
                                'nilai_kontrak' => $input['nilai_kontrak'],
                                'p1_nomor_sp2d' => $input['p1_nomor_sp2d'],
                                'p1_tanggal' => $input['p1_tanggal'],
                                'p1_jumlah' => $input['p1_jumlah'],
                                'p2_nomor_sp2d' => $input['p2_nomor_sp2d'],
                                'p2_tanggal' => $input['p2_tanggal'],
                                'p2_jumlah' => $input['p2_jumlah'],
                                'p3_nomor_sp2d' => $input['p3_nomor_sp2d'],
                                'p3_tanggal' => $input['p3_tanggal'],
                                'p3_jumlah' => $input['p3_jumlah'],
                                'p4_nomor_sp2d' => $input['p4_nomor_sp2d'],
                                'p4_tanggal' => $input['p4_tanggal'],
                                'p4_jumlah' => $input['p4_jumlah'],
                                'jumlah_pembayaran_hutang' => $input['jumlah_pembayaran_hutang'],
                                'hutang_baru' => $input['hutang_baru'],

                                'pegawai' => $input['pegawai'],
                                'persediaan' => $input['persediaan'],
                                'perjadin' => $input['perjadin'],
                                'jasa' => $input['jasa'],
                                'pemeliharaan' => $input['pemeliharaan'],
                                'uang_jasa_diserahkan' => $input['uang_jasa_diserahkan'],
                                'hibah' => $input['hibah'],

                                'aset_tetap_tanah' => $input['aset_tetap_tanah'],
                                'aset_tetap_peralatan_mesin' => $input['aset_tetap_peralatan_mesin'],
                                'aset_tetap_gedung_bangunan' => $input['aset_tetap_gedung_bangunan'],
                                'aset_tetap_jalan_jaringan_irigasi' => $input['aset_tetap_jalan_jaringan_irigasi'],
                                'aset_tetap_lainnya' => $input['aset_tetap_lainnya'],
                                'konstruksi_dalam_pekerjaan' => $input['konstruksi_dalam_pekerjaan'],
                                'aset_lain_lain' => $input['aset_lain_lain'],

                                'beban' => $input['beban'],
                                'jangka_pendek' => $input['jangka_pendek'],

                                'updated_at' => $dateNow
                            ]);
                    }
                }
            }

            DB::commit();
            return $this->successResponse(null, 'Data Hutang Belanja berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function deleteHutangBaru(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_htb_hutang_baru,id'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_htb_hutang_baru')
                ->where('id', $request->id)
                // ->update([
                //     'deleted_at' => now(),
                //     'deleted_by' => auth()->id()
                // ]);
                ->delete();

            DB::commit();
            return $this->successResponse(null, 'Data Hutang Belanja berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }
}
