<?php

namespace App\Http\Controllers\API\Local;

use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use App\Models\Ref\KodeRekening;
use App\Models\Data\TargetKinerja;
use App\Http\Controllers\Controller;
use App\Models\Ref\SubKegiatan;
use Illuminate\Support\Facades\Validator;

class SPPDAppsController extends Controller
{
    use JsonReturner;

    function getRekeningPerjadin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'year' => 'required|numeric|digits:4',
            'month' => 'required|numeric|between:1,12',
            'instance_id' => 'required|numeric|exists:instances,id',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors()->first());
        }

        $returnData = [];
        $parentFullcode = '5.1.02.04.01';
        $parent = KodeRekening::where('fullcode', $parentFullcode)->first();
        if (!$parent) {
            return $this->errorResponse("Parent rekening with fullcode {$parentFullcode} not found.");
        }
        $arrRekening = KodeRekening::where('parent_id', $parent->id)
            ->get();

        $arrSubKegiatan = TargetKinerja::where('instance_id', $request->instance_id)
            ->where('year', $request->year)
            ->where('month', '<=', $request->month)
            ->pluck('sub_kegiatan_id')
            ->unique()
            ->toArray();
        $arrSubKegiatan = SubKegiatan::whereIn('id', $arrSubKegiatan)
            ->get();

        foreach ($arrSubKegiatan as $key => $subKegiatan) {
            $returnData[$key] = [
                'id' => $subKegiatan->id,
                'fullcode' => $subKegiatan->fullcode,
                'name' => $subKegiatan->name,
                'pagu_induk' => 0,
                'kode_rekening' => [],
            ];
        }

        $rekening = [];
        foreach ($arrSubKegiatan as $key => $subKegiatan) {
            $anggaran = TargetKinerja::selectRaw('kode_rekening_id, SUM(pagu_sipd) as total_pagu_sipd')
                ->where('year', $request->year)
                ->where('month', '<=', $request->month)
                ->where('instance_id', $request->instance_id)
                ->where('sub_kegiatan_id', $subKegiatan->id)
                ->whereIn('kode_rekening_id', $arrRekening->pluck('id'))
                ->groupBy('kode_rekening_id')
                ->get()
                ->keyBy('kode_rekening_id');
            // if (!$anggaran->isEmpty()) {
            //     return $this->successResponse($anggaran);
            // }
            $totalPaguInduk = 0;
            foreach ($arrRekening as $rekening) {
                $totalPaguSipd = isset($anggaran[$rekening->id]) ? $anggaran[$rekening->id]->total_pagu_sipd : 0;
                $returnData[$key]['kode_rekening'][] = [
                    'kode_rekening_id' => $rekening->id,
                    'fullcode' => $rekening->fullcode,
                    'name' => $rekening->name,
                    'pagu_induk' => $totalPaguSipd,
                ];
                $totalPaguInduk += $totalPaguSipd;
            }
            $returnData[$key]['pagu_induk'] = $totalPaguInduk;
        }

        return $this->successResponse($returnData);

        $anggaran = TargetKinerja::selectRaw('kode_rekening_id, SUM(pagu_sipd) as total_pagu_sipd')
            ->where('year', $request->year)
            ->where('month', '<=', $request->month)
            ->where('instance_id', $request->instance_id)
            ->whereIn('kode_rekening_id', $arrRekening->pluck('id'))
            ->groupBy('kode_rekening_id')
            ->get()
            ->keyBy('kode_rekening_id');
        //     ->map(function ($item) {
        //         return (object)[
        //             'item' => $item,
        //             'kode_rekening_id' => $item->kode_rekening_id,
        //             'kode_rekening_name' => $item->KodeRekening ? $item->KodeRekening->name : null,
        //             'total_pagu_sipd' => floatval($item->total_pagu_sipd),
        //         ];
        //     });
        // return $this->successResponse($anggaran);

        foreach ($arrRekening as $rekening) {
            $totalPaguSipd = isset($anggaran[$rekening->id]) ? $anggaran[$rekening->id]->total_pagu_sipd : 0;
            $returnData[] = [
                'kode_rekening_id' => $rekening->id,
                'fullcode' => $rekening->fullcode,
                'name' => $rekening->name,
                'pagu_induk' => $totalPaguSipd,
            ];
        }

        return $this->successResponse($returnData);
    }
}
