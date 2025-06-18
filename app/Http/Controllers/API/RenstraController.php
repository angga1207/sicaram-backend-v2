<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Caram\Renstra;
use App\Models\Caram\RenstraKegiatan;
use App\Models\Caram\RenstraSubKegiatan;
use App\Models\Caram\RPJMD;
use App\Models\Caram\RPJMDIndikator;
use App\Models\Ref\IndikatorKegiatan;
use App\Models\Ref\IndikatorSubKegiatan;
use App\Models\Ref\Kegiatan;
use App\Models\Ref\Periode;
use App\Models\Ref\Program;
use App\Models\Ref\Satuan;
use App\Models\Ref\SubKegiatan;
use App\Models\User;
use App\Notifications\GlobalNotification;
use App\Traits\JsonReturner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class RenstraController extends Controller
{
    use JsonReturner;

    function listPrograms(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|numeric|exists:ref_periode,id',
            'instance' => 'required|numeric|exists:instances,id',
            // 'program' => 'required|numeric|exists:ref_program,id',
            // 'renstra' => 'required|numeric|exists:data_renstra,id',
        ], [], [
            'periode' => 'Periode',
            'instance' => 'Perangkat Daerah',
            // 'program' => 'Program',
            // 'renstra' => 'Renstra',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $return = [];

        $instance = DB::table('instances')
            ->where('id', $request->instance)
            ->first();
        if (!$instance) {
            return $this->errorResponse('Perangkat Daerah tidak ditemukan');
        }

        $programs = DB::table('ref_program')
            ->where('instance_id', $request->instance)
            ->where('periode_id', $request->periode)
            ->where('status', 'active')
            ->orderBy('fullcode', 'asc')
            ->whereNull('deleted_at')
            ->get();
        if ($programs->isEmpty()) {
            return $this->errorResponse('Program tidak ditemukan');
        }

        $return['instance'] = $instance;
        $return['programs'] = $programs;

        return $this->successResponse($return, 'List Program');
    }

    function listCaramRenstra(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|numeric|exists:ref_periode,id',
            'instance' => 'required|numeric|exists:instances,id',
            'program' => 'required|numeric|exists:ref_program,id',
            // 'renstra' => 'required|numeric|exists:data_renstra,id',
        ], [], [
            'periode' => 'Periode',
            'instance' => 'Perangkat Daerah',
            'program' => 'Program',
            // 'renstra' => 'Renstra',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        $user = auth()->user();
        $now = now();
        try {
            $datas = [];
            $renstra = Renstra::where('periode_id', $request->periode)
                ->where('instance_id', $request->instance)
                ->where('program_id', $request->program)
                ->first();
            $rpjmd = RPJMD::where('periode_id', $request->periode)
                ->where('instance_id', $request->instance)
                ->where('program_id', $request->program)
                ->latest('id') // latest id karena Ada Duplikat dengan Program ID yang sama
                ->first();
            if (!$renstra) {
                $renstra = new Renstra();
                $renstra->periode_id = $request->periode;
                $renstra->instance_id = $request->instance;
                $renstra->program_id = $request->program;
                $renstra->total_anggaran = 0;
                $renstra->total_kinerja = 0;
                $renstra->percent_anggaran = 0;
                $renstra->percent_kinerja = 0;
                $renstra->status = 'draft';
                $renstra->status_leader = 'draft';
                $renstra->created_by = $user->id ?? null;
            }
            $renstra->rpjmd_id = $rpjmd->id;
            $renstra->save();

            $periode = Periode::where('id', $request->periode)->first();
            $range = [];
            if ($periode) {
                $start = Carbon::parse($periode->start_date);
                $end = Carbon::parse($periode->end_date);
                for ($i = $start->year; $i <= $end->year; $i++) {
                    $range[] = $i;
                    $anggaran[$i] = null;
                }
            }

            foreach ($range as $year) {
                $program = Program::find($request->program);
                $indicators = [];
                $rpjmdIndicators = RPJMDIndikator::where('rpjmd_id', $rpjmd->id)
                    ->where('year', $year)
                    ->get();
                foreach ($rpjmdIndicators as $ind) {
                    $indicators[] = [
                        'id' => $ind->id,
                        'name' => $ind->name,
                        'value' => $ind->value,
                        'satuan_id' => $ind->satuan_id,
                        'satuan_name' => $ind->Satuan->name ?? null,
                    ];
                }
                $RenstraKegiatan = RenstraKegiatan::where('renstra_id', $renstra->id)
                    ->where('program_id', $program->id)
                    ->where('year', $year)
                    ->where('status', 'active')
                    ->get();
                $anggaranModal = $RenstraKegiatan->sum('anggaran_modal');
                $anggaranOperasi  = $RenstraKegiatan->sum('anggaran_operasi');
                $anggaranTransfer = $RenstraKegiatan->sum('anggaran_transfer');
                $anggaranTidakTerduga  = $RenstraKegiatan->sum('anggaran_tidak_terduga');
                $totalAnggaran = $RenstraKegiatan->sum('total_anggaran');

                $renstra->total_anggaran = $totalAnggaran;
                $renstra->percent_anggaran = 100;

                $averagePercentKinerja = $RenstraKegiatan->avg('percent_kinerja') ?? 0;
                $renstra->percent_kinerja = $averagePercentKinerja;
                $renstra->save();

                $datas[$year][] = [
                    'id' => $program->id,
                    'type' => 'program',
                    'rpjmd_id' => $renstra->rpjmd_id,
                    'rpjmd_data' => $renstra->RPJMD,
                    'indicators' => $indicators ?? null,

                    'anggaran_modal' => $anggaranModal,
                    'anggaran_operasi' => $anggaranOperasi,
                    'anggaran_transfer' => $anggaranTransfer,
                    'anggaran_tidak_terduga' => $anggaranTidakTerduga,

                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'program_fullcode' => $program->fullcode,
                    'total_anggaran' => $totalAnggaran,
                    'total_kinerja' => $renstra->total_kinerja,
                    'percent_anggaran' => $renstra->percent_anggaran,
                    'percent_kinerja' => $renstra->percent_kinerja,
                    'status' => $renstra->status,
                    'created_by' => $renstra->CreatedBy->fullname ?? '-',
                    'updated_by' => $renstra->UpdatedBy->fullname ?? '-',
                ];

                if ($user->role_id == 9 && $user->instance_type == 'staff') {
                    $kegs = $user->MyPermissions()->pluck('kegiatan_id');
                    $kegs = collect($kegs)->unique()->values();
                    $kegiatans = Kegiatan::where('program_id', $program->id)
                        ->whereIn('id', $kegs)
                        ->where('status', 'active')
                        ->get();
                } else {
                    $kegiatans = Kegiatan::where('program_id', $program->id)
                        ->where('status', 'active')
                        ->get();
                }
                foreach ($kegiatans as $kegiatan) {
                    $renstraKegiatan = RenstraKegiatan::where('renstra_id', $renstra->id)
                        ->where('program_id', $program->id)
                        ->where('kegiatan_id', $kegiatan->id)
                        ->where('year', $year)
                        ->where('status', 'active')
                        ->first();

                    if (!$renstraKegiatan) {
                        $renstraKegiatan = new RenstraKegiatan();
                        $renstraKegiatan->renstra_id = $renstra->id;
                        $renstraKegiatan->program_id = $program->id;
                        $renstraKegiatan->kegiatan_id = $kegiatan->id;
                        $renstraKegiatan->year = $year;
                        $renstraKegiatan->anggaran_json = null;
                        $renstraKegiatan->kinerja_json = null;
                        $renstraKegiatan->satuan_json = null;
                        $renstraKegiatan->anggaran_modal = 0;
                        $renstraKegiatan->anggaran_operasi = 0;
                        $renstraKegiatan->anggaran_transfer = 0;
                        $renstraKegiatan->anggaran_tidak_terduga = 0;
                        $renstraKegiatan->total_anggaran = 0;
                        $renstraKegiatan->total_kinerja = 0;
                        $renstraKegiatan->percent_anggaran = 0;
                        $renstraKegiatan->percent_kinerja = 0;
                        $renstraKegiatan->status = 'active';
                        $renstraKegiatan->created_by = $user->id ?? null;
                        $renstraKegiatan->save();
                    }

                    // delete if duplicated data
                    RenstraKegiatan::where('renstra_id', $renstra->id)
                        ->where('program_id', $program->id)
                        ->where('kegiatan_id', $kegiatan->id)
                        ->where('year', $year)
                        ->where('status', 'active')
                        ->where('id', '!=', $renstraKegiatan->id)
                        ->delete();

                    $indicators = [];
                    $indikatorCons = DB::table('con_indikator_kinerja_kegiatan')
                        ->where('instance_id', $request->instance)
                        ->where('program_id', $program->id)
                        ->where('kegiatan_id', $kegiatan->id)
                        ->first();
                    if ($indikatorCons) {
                        $indikators = IndikatorKegiatan::where('pivot_id', $indikatorCons->id)
                            ->get();
                        foreach ($indikators as $key => $indi) {
                            if ($renstraKegiatan->satuan_json) {
                                $satuanId = json_decode($renstraKegiatan->satuan_json, true)[$key] ?? null;
                                $satuanName = Satuan::where('id', $satuanId)->first()->name ?? null;
                            }
                            $indicators[] = [
                                'id' => $indi->id,
                                'name' => $indi->name,
                                'value' => json_decode($renstraKegiatan->kinerja_json, true)[$key] ?? null,
                                'satuan_id' => $satuanId ?? null,
                                'satuan_name' => $satuanName ?? null,
                            ];
                        }
                    }

                    $anggaranModal = 0;
                    $anggaranOperasi = 0;
                    $anggaranTransfer = 0;
                    $anggaranTidakTerduga = 0;
                    $totalAnggaran = RenstraSubKegiatan::where('renstra_id', $renstra->id)
                        ->where('parent_id', $renstraKegiatan->id)
                        ->where('program_id', $program->id)
                        ->where('kegiatan_id', $kegiatan->id)
                        ->where('year', $year)
                        ->where('status', 'active')
                        ->get()->sum('total_anggaran');

                    $renstraKegiatan->anggaran_modal = $anggaranModal;
                    $renstraKegiatan->anggaran_operasi = $anggaranOperasi;
                    $renstraKegiatan->anggaran_transfer = $anggaranTransfer;
                    $renstraKegiatan->anggaran_tidak_terduga = $anggaranTidakTerduga;
                    $renstraKegiatan->total_anggaran = $totalAnggaran;
                    $renstraKegiatan->save();


                    $datas[$year][] = [
                        'id' => $kegiatan->id,
                        'type' => 'kegiatan',
                        'program_id' => $renstraKegiatan->program_id,
                        'program_name' => $program->name,
                        'program_fullcode' => $program->fullcode,
                        'kegiatan_id' => $kegiatan->id,
                        'kegiatan_name' => $kegiatan->name,
                        'kegiatan_fullcode' => $kegiatan->fullcode,
                        'indicators' => $indicators,
                        'anggaran_json' => $renstraKegiatan->anggaran_json,
                        'kinerja_json' => $renstraKegiatan->kinerja_json,
                        'satuan_json' => $renstraKegiatan->satuan_json,

                        'anggaran_modal' => $renstraKegiatan->anggaran_modal,
                        'anggaran_operasi' => $renstraKegiatan->anggaran_operasi,
                        'anggaran_transfer' => $renstraKegiatan->anggaran_transfer,
                        'anggaran_tidak_terduga' => $renstraKegiatan->anggaran_tidak_terduga,

                        'total_anggaran' => $renstraKegiatan->total_anggaran,

                        'total_kinerja' => $renstraKegiatan->total_kinerja,
                        'percent_anggaran' => $renstraKegiatan->percent_anggaran,
                        'percent_kinerja' => $renstraKegiatan->percent_kinerja,
                        'year' => $renstraKegiatan->year,
                        'status' => $renstraKegiatan->status,
                        'created_by' => $renstraKegiatan->created_by,
                        'updated_by' => $renstraKegiatan->updated_by,
                        'created_by' => $renstraKegiatan->CreatedBy->fullname ?? '-',
                        'updated_by' => $renstraKegiatan->UpdatedBy->fullname ?? '-',
                    ];

                    if ($user->role_id == 9 && $user->instance_type == 'staff') {
                        $subKegs = $user->MyPermissions()->pluck('sub_kegiatan_id');
                        $subKegs = collect($subKegs)->unique()->values();

                        $subKegiatans = SubKegiatan::where('program_id', $program->id)
                            ->where('kegiatan_id', $kegiatan->id)
                            ->whereIn('id', $subKegs)
                            ->where('status', 'active')
                            ->get();
                    } else {
                        $subKegiatans = SubKegiatan::where('program_id', $program->id)
                            ->where('kegiatan_id', $kegiatan->id)
                            ->where('status', 'active')
                            ->get();
                    }
                    foreach ($subKegiatans as $subKegiatan) {
                        $renstraSubKegiatan = RenstraSubKegiatan::where('renstra_id', $renstra->id)
                            ->where('program_id', $program->id)
                            ->where('kegiatan_id', $kegiatan->id)
                            ->where('sub_kegiatan_id', $subKegiatan->id)
                            // ->where('parent_id', $renstraKegiatan->id)
                            ->where('year', $year)
                            ->where('status', 'active')
                            ->first();

                        if (!$renstraSubKegiatan) {
                            $renstraSubKegiatan = new RenstraSubKegiatan();
                            $renstraSubKegiatan->renstra_id = $renstra->id;
                            $renstraSubKegiatan->parent_id = $renstraKegiatan->id;
                            $renstraSubKegiatan->program_id = $program->id;
                            $renstraSubKegiatan->kegiatan_id = $kegiatan->id;
                            $renstraSubKegiatan->sub_kegiatan_id = $subKegiatan->id;
                            $renstraSubKegiatan->year = $year;
                            $renstraSubKegiatan->anggaran_json = null;
                            $renstraSubKegiatan->kinerja_json = null;
                            $renstraSubKegiatan->satuan_json = null;
                            $renstraSubKegiatan->anggaran_modal = 0;
                            $renstraSubKegiatan->anggaran_operasi = 0;
                            $renstraSubKegiatan->anggaran_transfer = 0;
                            $renstraSubKegiatan->anggaran_tidak_terduga = 0;
                            $renstraSubKegiatan->total_anggaran = 0;
                            $renstraSubKegiatan->total_kinerja = 0;
                            $renstraSubKegiatan->percent_anggaran = 0;
                            $renstraSubKegiatan->percent_kinerja = 0;
                            $renstraSubKegiatan->status = 'active';
                            $renstraSubKegiatan->created_by = $user->id ?? null;
                            $renstraSubKegiatan->save();
                        }
                        $renstraSubKegiatan->parent_id = $renstraKegiatan->id;
                        $renstraSubKegiatan->save();

                        // delete if duplicated data
                        RenstraSubKegiatan::where('renstra_id', $renstra->id)
                            ->where('program_id', $program->id)
                            ->where('kegiatan_id', $kegiatan->id)
                            ->where('sub_kegiatan_id', $subKegiatan->id)
                            ->where('year', $year)
                            ->where('status', 'active')
                            ->where('id', '!=', $renstraSubKegiatan->id)
                            ->delete();

                        $indicators = [];
                        $indikatorCons = DB::table('con_indikator_kinerja_sub_kegiatan')
                            ->where('instance_id', $request->instance)
                            ->where('program_id', $program->id)
                            ->where('kegiatan_id', $kegiatan->id)
                            ->where('sub_kegiatan_id', $subKegiatan->id)
                            ->first();
                        if ($indikatorCons) {
                            $indikators = IndikatorSubKegiatan::where('pivot_id', $indikatorCons->id)
                                ->get();
                            foreach ($indikators as $key => $indi) {

                                $arrSatuanIds = $renstraSubKegiatan->satuan_json ?? null;
                                if ($arrSatuanIds) {
                                    $satuanId = json_decode($renstraSubKegiatan->satuan_json, true)[$key] ?? null;
                                    $satuanName = Satuan::where('id', $satuanId)->first()->name ?? null;
                                }

                                $arrKinerjaValues = $renstraSubKegiatan->kinerja_json ?? null;
                                if ($arrKinerjaValues) {
                                    $value = json_decode($renstraSubKegiatan->kinerja_json, true)[$key] ?? null;
                                }
                                $indicators[] = [
                                    'id' => $indi->id,
                                    'name' => $indi->name,
                                    'value' => $value ?? null,
                                    'satuan_id' => $satuanId ?? null,
                                    'satuan_name' => $satuanName ?? null,
                                ];
                            }
                        }
                        $datas[$year][] = [
                            'id' => $subKegiatan->id,
                            'type' => 'sub-kegiatan',
                            'program_id' => $program->id,
                            'program_name' => $program->name ?? null,
                            'program_fullcode' => $program->fullcode,
                            'kegiatan_id' => $kegiatan->id,
                            'kegiatan_name' => $kegiatan->name ?? null,
                            'kegiatan_fullcode' => $kegiatan->fullcode,
                            'sub_kegiatan_id' => $subKegiatan->id,
                            'sub_kegiatan_name' => $subKegiatan->name ?? null,
                            'sub_kegiatan_fullcode' => $subKegiatan->fullcode,
                            'indicators' => $indicators,
                            'anggaran_modal' => $renstraSubKegiatan->anggaran_modal ?? null,
                            'anggaran_operasi' => $renstraSubKegiatan->anggaran_operasi ?? null,
                            'anggaran_transfer' => $renstraSubKegiatan->anggaran_transfer ?? null,
                            'anggaran_tidak_terduga' => $renstraSubKegiatan->anggaran_tidak_terduga ?? null,
                            'total_anggaran' => $renstraSubKegiatan->total_anggaran ?? null,
                            'total_kinerja' => $renstraSubKegiatan->total_kinerja ?? null,
                            'percent_anggaran' => $renstraSubKegiatan->percent_anggaran,
                            'percent_kinerja' => $renstraSubKegiatan->percent_kinerja,
                            'year' => $renstraSubKegiatan->year ?? null,
                            'status' => $renstraSubKegiatan->status ?? null,
                            'created_by' => $renstraSubKegiatan->created_by ?? null,
                            'updated_by' => $renstraSubKegiatan->updated_by ?? null,
                            'created_by' => $renstraSubKegiatan->CreatedBy->fullname ?? '-',
                            'updated_by' => $renstraSubKegiatan->UpdatedBy->fullname ?? '-',
                        ];
                    }
                }
            }
            $renstra = [
                'id' => $renstra->id,
                'rpjmd_id' => $renstra->rpjmd_id,
                'rpjmd_data' => $renstra->RPJMD,
                'program_id' => $renstra->program_id,
                'program_name' => $renstra->Program->name ?? null,
                'program_fullcode' => $renstra->Program->fullcode ?? null,
                'total_anggaran' => $renstra->total_anggaran,
                'total_kinerja' => $renstra->total_kinerja,
                'percent_anggaran' => $renstra->percent_anggaran,
                'percent_kinerja' => $renstra->percent_kinerja,
                'status' => $renstra->status,
                'status_leader' => $renstra->status_leader,
                'notes_verificator' => $renstra->notes_verificator,
                'created_by' => $renstra->created_by,
                'CreatedBy' => $renstra->CreatedBy->fullname ?? null,
                'updated_by' => $renstra->updated_by,
                'UpdatedBy' => $renstra->UpdatedBy->fullname ?? null,
                'created_at' => $renstra->created_at,
                'updated_at' => $renstra->updated_at,
            ];
            DB::commit();
            return $this->successResponse([
                'renstra' => $renstra,
                'datas' => $datas,
            ], 'List Renstra');
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->errorResponse($th->getMessage() . ' - ' . $th->getFile() . ' - ' . $th->getLine());
        }
    }

    function detailCaramRenstra($id, Request $request)
    {
        if ($request->type == 'kegiatan') {
            $validate = Validator::make($request->all(), [
                'periode' => 'required|numeric|exists:ref_periode,id',
                'instance' => 'required|numeric|exists:instances,id',
                'program' => 'required|numeric|exists:ref_program,id',
                'year' => 'required|numeric',
            ], [], [
                'periode' => 'Periode',
                'instance' => 'Perangkat Daerah',
                'program' => 'Program',
                'year' => 'Tahun',
            ]);

            if ($validate->fails()) {
                return $this->validationResponse($validate->errors());
            }

            DB::beginTransaction();
            try {
                $datas = [];
                $kegiatan = Kegiatan::find($id);
                if (!$kegiatan) {
                    return $this->errorResponse('Kegiatan tidak ditemukan');
                }

                $indicators = [];
                $anggaran = [];
                $conIndikator = DB::table('con_indikator_kinerja_kegiatan')
                    ->where('instance_id', $request->instance)
                    ->where('program_id', $request->program)
                    ->where('kegiatan_id', $kegiatan->id)
                    ->first();

                $arrIndikator = IndikatorKegiatan::where('pivot_id', $conIndikator->id)
                    ->get();
                $renstraDetail = RenstraKegiatan::where('program_id', $request->program)
                    ->where('kegiatan_id', $kegiatan->id)
                    ->where('year', $request->year)
                    ->where('status', 'active')
                    ->first();
                foreach ($arrIndikator as $key => $indikator) {

                    if ($renstraDetail->kinerja_json) {
                        $value = json_decode($renstraDetail->kinerja_json, true)[$key] ?? null;
                    }
                    if ($renstraDetail->satuan_json) {
                        $satuanId = json_decode($renstraDetail->satuan_json, true)[$key] ?? null;
                        $satuanName = Satuan::where('id', $satuanId)->first()->name ?? null;
                    }
                    $indicators[] = [
                        'id_indikator' => $indikator->id,
                        'name' => $indikator->name,
                        'value' => $value ?? null,
                        'satuan_id' => $satuanId ?? null,
                        'satuan_name' => $satuanName ?? null,
                    ];
                }

                $anggaran = [
                    'total_anggaran' => $renstraDetail->total_anggaran,
                    'anggaran_modal' => $renstraDetail->anggaran_modal,
                    'anggaran_operasi' => $renstraDetail->anggaran_operasi,
                    'anggaran_transfer' => $renstraDetail->anggaran_transfer,
                    'anggaran_tidak_terduga' => $renstraDetail->anggaran_tidak_terduga,
                    'percent_anggaran' => $renstraDetail->percent_anggaran,
                    'percent_kinerja' => $renstraDetail->percent_kinerja,
                ];

                $datas = [
                    'id' => $kegiatan->id,
                    'id_renstra_detail' => $renstraDetail->id,
                    'type' => 'kegiatan',
                    'program_id' => $renstraDetail->program_id,
                    'program_name' => $kegiatan->Program->name ?? null,
                    'program_fullcode' => $kegiatan->Program->fullcode ?? null,
                    'kegiatan_id' => $kegiatan->id,
                    'kegiatan_name' => $kegiatan->name ?? null,
                    'kegiatan_fullcode' => $kegiatan->fullcode,
                    'year' => $renstraDetail->year,
                    'indicators' => $indicators,
                    'anggaran' => $anggaran,
                    'total_anggaran' => $renstraDetail->total_anggaran,
                    'percent_anggaran' => $renstraDetail->percent_anggaran,
                    'percent_kinerja' => $renstraDetail->percent_kinerja,
                ];


                // DB::commit();
                return $this->successResponse($datas, 'Detail Kegiatan');
            } catch (\Throwable $th) {
                DB::rollback();
                return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine() . ' - ' . $th->getFile());
            }
        }

        if ($request->type == 'subkegiatan') {
            $validate = Validator::make($request->all(), [
                'periode' => 'required|numeric|exists:ref_periode,id',
                'instance' => 'required|numeric|exists:instances,id',
                'program' => 'required|numeric|exists:ref_program,id',
                'year' => 'required|numeric',
            ], [], [
                'periode' => 'Periode',
                'instance' => 'Perangkat Daerah',
                'program' => 'Program',
                'year' => 'Tahun',
            ]);

            if ($validate->fails()) {
                return $this->validationResponse($validate->errors());
            }

            DB::beginTransaction();
            try {
                $datas = [];
                $subKegiatan = SubKegiatan::find($id);
                if (!$subKegiatan) {
                    return $this->errorResponse('Sub Kegiatan tidak ditemukan');
                }

                $indicators = [];
                $anggaran = [];
                $conIndikator = DB::table('con_indikator_kinerja_sub_kegiatan')
                    ->where('instance_id', $request->instance)
                    ->where('program_id', $request->program)
                    ->where('kegiatan_id', $subKegiatan->kegiatan_id)
                    ->where('sub_kegiatan_id', $subKegiatan->id)
                    ->first();
                $arrIndikator = [];
                if ($conIndikator) {
                    $arrIndikator = IndikatorSubKegiatan::where('pivot_id', $conIndikator->id)
                        ->get();
                }
                $renstraDetail = RenstraSubKegiatan::where('program_id', $request->program)
                    ->where('kegiatan_id', $subKegiatan->kegiatan_id)
                    ->where('sub_kegiatan_id', $subKegiatan->id)
                    ->where('year', $request->year)
                    ->where('status', 'active')
                    ->first();
                foreach ($arrIndikator as $key => $indikator) {
                    if ($renstraDetail->kinerja_json) {
                        $value = json_decode($renstraDetail->kinerja_json, true)[$key] ?? null;
                    }
                    if ($renstraDetail->satuan_json) {
                        $satuanId = json_decode($renstraDetail->satuan_json, true)[$key] ?? null;
                        $satuanName = Satuan::where('id', $satuanId)->first()->name ?? null;
                    }
                    $indicators[] = [
                        'id_indikator' => $indikator->id,
                        'name' => $indikator->name,
                        'value' => $value ?? null,
                        'satuan_id' => $satuanId ?? null,
                        'satuan_name' => $satuanName ?? null,
                    ];
                }

                $anggaran = [
                    'total_anggaran' => $renstraDetail->total_anggaran,
                    'anggaran_modal' => $renstraDetail->anggaran_modal,
                    'anggaran_operasi' => $renstraDetail->anggaran_operasi,
                    'anggaran_transfer' => $renstraDetail->anggaran_transfer,
                    'anggaran_tidak_terduga' => $renstraDetail->anggaran_tidak_terduga,
                    'percent_anggaran' => $renstraDetail->percent_anggaran,
                    'percent_kinerja' => $renstraDetail->percent_kinerja,
                ];

                $datas = [
                    'id' => $subKegiatan->id,
                    'id_renstra_detail' => $renstraDetail->id,
                    'type' => 'sub-kegiatan',
                    'program_id' => $renstraDetail->program_id,
                    'program_name' => $subKegiatan->Program->name ?? null,
                    'program_fullcode' => $subKegiatan->Program->fullcode ?? null,
                    'kegiatan_id' => $renstraDetail->kegiatan_id,
                    'kegiatan_name' => $subKegiatan->Kegiatan->name ?? null,
                    'kegiatan_fullcode' => $subKegiatan->Kegiatan->fullcode,
                    'sub_kegiatan_id' => $subKegiatan->id,
                    'sub_kegiatan_name' => $subKegiatan->name ?? null,
                    'sub_kegiatan_fullcode' => $subKegiatan->fullcode,
                    'year' => $renstraDetail->year,
                    'indicators' => $indicators,
                    'anggaran' => $anggaran,
                    'total_anggaran' => $renstraDetail->total_anggaran,
                    'percent_anggaran' => $renstraDetail->percent_anggaran,
                    'percent_kinerja' => $renstraDetail->percent_kinerja,
                ];

                // DB::commit();
                return $this->successResponse($datas, 'Detail Sub Kegiatan');
            } catch (\Throwable $th) {
                DB::rollback();
                return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine() . ' - ' . $th->getFile());
            }
        }

        return $this->errorResponse('Tipe tidak ditemukan');
    }

    function saveCaramRenstra($id, Request $request)
    {
        $user = auth()->user();
        $now = now();
        if ($request->type == 'kegiatan') {
            $validate = Validator::make($request->all(), [
                'periode' => 'required|numeric|exists:ref_periode,id',
                'instance' => 'required|numeric|exists:instances,id',
                'program' => 'required|numeric|exists:ref_program,id',
                'year' => 'required|numeric',
            ], [], [
                'periode' => 'Periode',
                'instance' => 'Perangkat Daerah',
                'program' => 'Program',
                'year' => 'Tahun',
            ]);
            if ($validate->fails()) {
                return $this->validationResponse($validate->errors());
            }
            DB::beginTransaction();
            try {
                $data = RenstraKegiatan::find($request->data['id_renstra_detail']);
                $renstra = Renstra::find($data->renstra_id);
                if ($user->role_id !== 1) {
                    if ($renstra->status == 'verified') {
                        return $this->errorResponse('Renstra sudah diverifikasi');
                    }
                }

                $data->anggaran_modal = $request->data['anggaran']['anggaran_modal'] ?? 0;
                $data->anggaran_operasi = $request->data['anggaran']['anggaran_operasi'] ?? 0;
                $data->anggaran_transfer = $request->data['anggaran']['anggaran_transfer'] ?? 0;
                $data->anggaran_tidak_terduga = $request->data['anggaran']['anggaran_tidak_terduga'] ?? 0;
                $data->total_anggaran = $request->data['total_anggaran'] ?? 0;

                $kinerjaArray = [];
                $satuanArray = [];
                $indicators = $request->data['indicators'];
                foreach ($indicators as $indi) {
                    $kinerjaArray[] = $indi['value'] ?? null;
                    $satuanArray[] = $indi['satuan_id'] ?? null;
                }
                $data->kinerja_json = json_encode($kinerjaArray, true);
                $data->satuan_json = json_encode($satuanArray, true);

                $percentAnggaran = 0;
                if ($request->data['percent_anggaran'] > 100) {
                    $percentAnggaran = 100;
                } elseif ($request->data['percent_anggaran'] < 0) {
                    $percentAnggaran = 0;
                } else {
                    $percentAnggaran = $request->data['percent_anggaran'];
                }
                $data->percent_anggaran = $percentAnggaran;

                $percentKinerja = 0;
                if ($request->data['percent_kinerja'] > 100) {
                    $percentKinerja = 100;
                } elseif ($request->data['percent_kinerja'] < 0) {
                    $percentKinerja = 0;
                } else {
                    $percentKinerja = $request->data['percent_kinerja'];
                }
                $data->percent_kinerja = $percentKinerja;
                $data->save();

                $renstra = Renstra::find($data->renstra_id);
                $renstra->updated_by = $user->id ?? null;
                $renstra->updated_at = $now;
                $renstra->save();

                DB::commit();
                return $this->successResponse($data, 'Data Renstra Berhasil disimpan');
            } catch (\Throwable $th) {
                DB::rollback();
                return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine());
            }
        }

        if ($request->type == 'subkegiatan') {
            $validate = Validator::make($request->all(), [
                'periode' => 'required|numeric|exists:ref_periode,id',
                'instance' => 'required|numeric|exists:instances,id',
                'program' => 'required|numeric|exists:ref_program,id',
                'year' => 'required|numeric',
            ], [], [
                'periode' => 'Periode',
                'instance' => 'Perangkat Daerah',
                'program' => 'Program',
                'year' => 'Tahun',
            ]);
            if ($validate->fails()) {
                return $this->validationResponse($validate->errors());
            }
            DB::beginTransaction();
            try {
                $data = RenstraSubKegiatan::find($request->data['id_renstra_detail']);
                $renstra = Renstra::find($data->renstra_id);
                if ($user->role_id !== 1) {
                    if ($renstra->status == 'verified') {
                        return $this->errorResponse('Renstra sudah diverifikasi');
                    }
                }

                $data->anggaran_modal = $request->data['anggaran']['anggaran_modal'] ?? 0;
                $data->anggaran_operasi = $request->data['anggaran']['anggaran_operasi'] ?? 0;
                $data->anggaran_transfer = $request->data['anggaran']['anggaran_transfer'] ?? 0;
                $data->anggaran_tidak_terduga = $request->data['anggaran']['anggaran_tidak_terduga'] ?? 0;
                $data->total_anggaran = $request->data['total_anggaran'] ?? 0;

                $kinerjaArray = [];
                $satuanArray = [];
                $indicators = $request->data['indicators'];
                foreach ($indicators as $indi) {
                    $kinerjaArray[] = $indi['value'] ?? null;
                    $satuanArray[] = $indi['satuan_id'] ?? null;
                }
                $data->kinerja_json = json_encode($kinerjaArray, true);
                $data->satuan_json = json_encode($satuanArray, true);
                $percentAnggaran = 0;
                if ($request->data['percent_anggaran'] > 100) {
                    $percentAnggaran = 100;
                } elseif ($request->data['percent_anggaran'] < 0) {
                    $percentAnggaran = 0;
                } else {
                    $percentAnggaran = $request->data['percent_anggaran'];
                }
                $data->percent_anggaran = $percentAnggaran;

                $percentKinerja = 0;
                if ($request->data['percent_kinerja'] > 100) {
                    $percentKinerja = 100;
                } elseif ($request->data['percent_kinerja'] < 0) {
                    $percentKinerja = 0;
                } else {
                    $percentKinerja = $request->data['percent_kinerja'];
                }
                $data->percent_kinerja = $percentKinerja;
                $data->save();

                $renstraKegiatan = RenstraKegiatan::find($data->parent_id);
                $renstraKegiatan->total_anggaran = $renstraKegiatan->dataSubKegiatan->sum('total_anggaran');
                $renstraKegiatan->save();

                $renstra->updated_by = $user->id ?? null;
                $renstra->updated_at = $now;
                $renstra->save();

                DB::commit();
                return $this->successResponse($data, 'Data Renstra Berhasil disimpan');
            } catch (\Throwable $th) {
                DB::rollback();
                return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine());
            }
        }
    }

    function listCaramRenstraNotes($id, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|numeric|exists:ref_periode,id',
            'instance' => 'required|numeric|exists:instances,id',
            'program' => 'required|numeric|exists:ref_program,id',
            // 'renstra' => 'required|numeric|exists:data_renstra,id',
        ], [], [
            'periode' => 'Periode',
            'instance' => 'Perangkat Daerah',
            'program' => 'Program',
            // 'renstra' => 'Renstra',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $datas = [];
        $notes = DB::table('notes_renstra')
            ->where('renstra_id', $id)
            ->latest('created_at')
            ->limit(10)
            ->get();
        foreach ($notes->reverse() as $note) {
            $user = User::find($note->user_id);
            $datas[] = [
                'id' => $note->id,
                'user_id' => $note->user_id,
                'user_name' => $user->fullname ?? null,
                'user_photo' => asset($user->photo) ?? null,
                'message' => $note->message,
                'status' => $note->status,
                'type' => $note->type,
                'created_at' => $note->created_at,
                'updated_at' => $note->updated_at,
            ];
        }

        return $this->successResponse($datas, 'List Renstra');
    }

    function postCaramRenstraNotes($id, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|numeric|exists:ref_periode,id',
            'instance' => 'required|numeric|exists:instances,id',
            'program' => 'required|numeric|exists:ref_program,id',
            // 'renstra' => 'required|numeric|exists:data_renstra,id',
            'message' => 'required|string',
            'status' => 'required|string',
        ], [], [
            'periode' => 'Periode',
            'instance' => 'Perangkat Daerah',
            'program' => 'Program',
            // 'renstra' => 'Renstra',
            'message' => 'Pesan',
            'status' => 'Status',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $user = auth()->user();
        $now = now();
        DB::beginTransaction();
        try {
            $renstra = Renstra::find($id);
            if (!$renstra) {
                return $this->errorResponse('Renstra tidak ditemukan');
            }
            if ($user->role_id == 9) {
                $type = 'request';
                $renstra->status = $request->status;
                $renstra->save();

                // send notification
                $users = User::where('role_id', 6)->get();
                Notification::send($users, new GlobalNotification(
                    'sent',
                    $renstra->id,
                    $user->id,
                    $users->pluck('id')->toArray(),
                    '/renstra/' . $renstra->instance_id . '&program=' . $renstra->program_id,
                    'Permintaan Verifikasi Renstra',
                    'Permintaan Verifikasi Renstra dari ' . $user->fullname,
                    [
                        'type' => 'renstra',
                        'renstra_id' => $renstra->id,
                        'instance_id' => $renstra->instance_id,
                        'program_id' => $renstra->program_id,
                        'uri' => '/renstra/' . $renstra->instance_id . '&program=' . $renstra->program_id,
                    ]
                ));
            } else {
                $type = 'return';
                $renstra->status = $request->status;
                $renstra->notes_verificator = $request->message;
                $renstra->save();

                // send notification
                $users = User::where('role_id', 9)
                    ->where('instance_id', $renstra->instance_id)
                    ->get();
                Notification::send($users, new GlobalNotification(
                    'sent',
                    $renstra->id,
                    $user->id,
                    $users->pluck('id')->toArray(),
                    '/renstra/' . $renstra->instance_id . '&program=' . $renstra->program_id,
                    'Verifikasi Renstra',
                    $user->fullname . ' telah memberikan verifikasi Renstra',
                    [
                        'type' => 'renstra',
                        'renstra_id' => $renstra->id,
                        'instance_id' => $renstra->instance_id,
                        'program_id' => $renstra->program_id,
                        'uri' => '/renstra/' . $renstra->instance_id . '&program=' . $renstra->program_id,
                    ]
                ));
            }

            $note = DB::table('notes_renstra')
                ->insert([
                    'renstra_id' => $id,
                    'user_id' => $user->id,
                    'message' => $request->message,
                    'status' => $request->status,
                    'type' => $type ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::commit();
            return $this->successResponse($note, 'Verifikasi Renstra Berhasil dikirim');
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine());
        }
    }
}
