<?php

namespace App\Http\Controllers\API\Accountancy;

use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Accountancy\PADB\Atribusi;
use App\Models\Accountancy\PADB\BarjasKeAset;
use App\Models\Accountancy\PADB\ModalKeBeban;
use App\Models\Accountancy\PADB\PenyesuaianAset;
use Illuminate\Support\Facades\Validator;
use App\Models\Accountancy\PADB\PenyesuaianBebanBarjas;
use App\Models\Instance;
use App\Models\Ref\KodeRekening;
use Carbon\Carbon;

class DataImportController extends Controller
{
    use JsonReturner;

    function uploadExcelAll(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors()->first());
        }
        $params = json_decode($request->params, true);
        if (!isset($params['instance'])) {
            return $this->validationResponse('Perangkat Daerah harus dipilih.');
        }
        if (!$params['instance']) {
            return $this->validationResponse('Perangkat Daerah harus dipilih.');
        }

        if ($params['category'] == 'padb') {
            if ($params['type'] == 'beban_barjas') {
                $file = $request->file('file');
                $fileName = 'padb-beban-barjas-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'TOTAL')
                    ->where('A', '!=', 'Total')
                    ->where('B', '!=', null)
                    ->where('C', '!=', null)
                    ->values();

                return $this->PADBBebanBarjasImport($allData, $params);
            }
            if ($params['type'] == 'modal_beban') {
                $file = $request->file('file');
                $fileName = 'padb-modal-beban-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'TOTAL')
                    ->where('A', '!=', 'Total')
                    ->where('B', '!=', null)
                    ->where('C', '!=', null)
                    ->values();

                return $this->PADBModalBebanImport($allData, $params);
            }
            if ($params['type'] == 'barjas_aset') {
                $file = $request->file('file');
                $fileName = 'padb-barjas-aset-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'TOTAL')
                    ->where('A', '!=', 'Total')
                    ->where('B', '!=', null)
                    ->where('C', '!=', null)
                    ->values();

                return $this->PADBBarjasAsetImport($allData, $params);
            }
            if ($params['type'] == 'penyesuaian_aset') {
                $file = $request->file('file');
                $fileName = 'padb-penyesuaian-aset-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'TOTAL')
                    ->where('A', '!=', 'Total')
                    ->where('B', '!=', null)
                    ->where('C', '!=', null)
                    ->values();

                return $this->PADBPenyesuaianAsetImport($allData, $params);
            }
            if ($params['type'] == 'atribusi') {
                $file = $request->file('file');
                $fileName = 'padb-atribusi-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->PADBAtribusiImport($allData, $params);
            }
            if ($params['type'] == 'penilaian_aset') {
                $file = $request->file('file');
                $fileName = 'padb-penilaian-aset-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->PADBPenilaianAsetImport($allData, $params);
            }
            if ($params['type'] == 'penghapusan_aset') {
                $file = $request->file('file');
                $fileName = 'padb-penghapusan-aset-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->PADBPenghapusanAsetImport($allData, $params);
            }
            if ($params['type'] == 'penjualan_aset') {
                $file = $request->file('file');
                $fileName = 'padb-penjualan-aset-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->PADBPenjualanAsetImport($allData, $params);
            }
            if ($params['type'] == 'mutasi_aset') {
                $file = $request->file('file');
                $fileName = 'padb-mutasi-aset-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah Lama')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->PADBMutasiAsetImport($allData, $params);
            }
            if ($params['type'] == 'pekerjaan_kontrak') {
                $file = $request->file('file');
                $fileName = 'padb-pekerjaan-kontrak-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->PADBPekerjaanKontrakImport($allData, $params);
            }
            if ($params['type'] == 'hibah_masuk') {
                $file = $request->file('file');
                $fileName = 'padb-hibah-masuk-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->PADBHibahMasukImport($allData, $params);
            }
            if ($params['type'] == 'hibah_keluar') {
                $file = $request->file('file');
                $fileName = 'padb-hibah-keluar-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->PADBHibahKeluarImport($allData, $params);
            }
        }

        if ($params['category'] == 'belanja_bayar_dimuka') {
            if ($params['type'] == 'belanja_bayar_dimuka') {
                $file = $request->file('file');
                $fileName = 'belanja-bayar-dimuka-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->BelanjaBayarDimukaImport($allData, $params);
            }
        }

        if ($params['category'] == 'persediaan') {
            if ($params['type'] == 'barang_habis_pakai') {
                $file = $request->file('file');
                $fileName = 'barang-habis-pakai-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->BarangHabisPakaiImport($allData, $params);
            }
        }

        if ($params['category'] == 'hutang_belanja') {
            if ($params['type'] == 'pembayaran_utang') {
                $file = $request->file('file');
                $fileName = 'pembayaran-utang-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->PembayaranUtangImport($allData, $params);
            }

            if ($params['type'] == 'utang_baru') {
                $file = $request->file('file');
                $fileName = 'utang-baru-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->UtangBaruImport($allData, $params);
            }
        }

        if ($params['category'] == 'pendapatan_lo') {
            if ($params['type'] == 'piutang') {
                $file = $request->file('file');
                $fileName = 'piutang-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    ->where('O', '!=', null)
                    ->where('O', '!=', '')
                    ->where('B', '!=', null)
                    ->where('B', '!=', '')
                    ->values();

                return $this->PiutangImport($allData, $params);
            }
            if ($params['type'] == 'penyisihan') {
                $file = $request->file('file');
                $fileName = 'penyisihan-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    ->where('O', '!=', null)
                    ->where('O', '!=', '')
                    ->where('B', '!=', null)
                    ->where('B', '!=', '')
                    ->values();

                return $this->PenyisihanImport($allData, $params);
            }
            if ($params['type'] == 'beban') {
                $file = $request->file('file');
                $fileName = 'beban-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    ->where('B', '!=', null)
                    ->where('B', '!=', '')
                    ->values();

                return $this->PendapatanLOBebanImport($allData, $params);
            }
            if ($params['type'] == 'pdd') {
                $file = $request->file('file');
                $fileName = 'pdd-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    ->where('B', '!=', null)
                    ->where('B', '!=', '')
                    ->values();

                return $this->PDDImport($allData, $params);
            }
        }

        if ($params['category'] == 'pengembalian-belanja') {
            if ($params['type'] == 'pengembalian-belanja') {
                $file = $request->file('file');
                $fileName = 'pengembalian-belanja-' . $params['instance'] . '-' . $params['year'] . '-' . $params['periode'] . '.' . $file->getClientOriginalExtension();
                $file->move('uploads/accountancy', $fileName);

                $path = public_path('uploads/accountancy/' . $fileName);
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($path);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
                $allData = collect($allData);
                $allData = $allData->where('A', '!=', null)
                    ->where('A', '!=', 'Perangkat Daerah')
                    ->where('A', '!=', 'Total')
                    ->where('A', '!=', 'TOTAL')
                    // ->where('B', '!=', null)
                    // ->where('C', '!=', null)
                    ->values();

                return $this->PengembalianBelanjaImport($allData, $params);
            }
        }
    }

    private function changeStringMoneyToFloatDouble($value)
    {
        // check last 2 characters are ,0
        if (substr($value, -2) == ',0') {
            // if ,xx replace with .xx
            $value = str_replace('Rp', '', $value); // Remove 'Rp'
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
            return floatval($value);
        } else {
            // Rp4,240,426.77 example
            $value = str_replace('Rp', '', $value); // Remove 'Rp'
            $value = str_replace(',', '', $value); // Remove commas
            return floatval($value);
        }
    }

    private function changeStringToDate($value)
    {
        if (!$value) {
            return null;
        }

        // if format is already Y-m-d
        if (Carbon::hasFormat($value, 'Y-m-d')) {
            return $value;
        }

        $bulan = [
            'Januari' => '01',
            'Februari' => '02',
            'Maret' => '03',
            'April' => '04',
            'Mei' => '05',
            'Juni' => '06',
            'Juli' => '07',
            'Agustus' => '08',
            'September' => '09',
            'Oktober' => '10',
            'November' => '11',
            'Desember' => '12',
        ];

        [$hari, $namaBulan, $tahun] = explode(' ', $value);
        $bulanAngka = $bulan[$namaBulan];
        $hasil = "$tahun-$bulanAngka-$hari";
        return $hasil;
    }

    private function PADBBebanBarjasImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }

                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                if (!$kodeRekening) {
                    continue;
                }
                $data = PenyesuaianBebanBarjas::updateOrCreate(
                    [
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,
                        'kode_rekening_id' => $kodeRekening->id,
                        'nama_barang_pekerjaan' => $input['D'],
                        'nomor_kontrak' => $input['E'],
                        'nomor_sp2d' => $input['F'],

                        'plus_beban_pegawai' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'plus_beban_persediaan' => $this->changeStringMoneyToFloatDouble($input['H']),
                        'plus_beban_jasa' => $this->changeStringMoneyToFloatDouble($input['I']),
                        'plus_beban_pemeliharaan' => $this->changeStringMoneyToFloatDouble($input['J']),
                        'plus_beban_perjalanan_dinas' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'plus_beban_hibah' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'plus_beban_lain_lain' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'plus_jumlah_penyesuaian' => $this->changeStringMoneyToFloatDouble($input['N']),

                        'min_beban_pegawai' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'min_beban_persediaan' => $this->changeStringMoneyToFloatDouble($input['P']),
                        'min_beban_jasa' => $this->changeStringMoneyToFloatDouble($input['Q']),
                        'min_beban_pemeliharaan' => $this->changeStringMoneyToFloatDouble($input['R']),
                        'min_beban_perjalanan_dinas' => $this->changeStringMoneyToFloatDouble($input['S']),
                        'min_beban_hibah' => $this->changeStringMoneyToFloatDouble($input['U']),
                        'min_beban_lain_lain' => $this->changeStringMoneyToFloatDouble($input['T']),
                        'min_jumlah_penyesuaian' => $this->changeStringMoneyToFloatDouble($input['V']),
                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
            DB::commit();
            return $this->successResponse('Data PADB Penyesuaian Beban Barjas berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PADBModalBebanImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }

                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                if (!$kodeRekening) {
                    continue;
                }
                $data = ModalKeBeban::updateOrCreate(
                    [
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'kode_rekening_id' => $kodeRekening->id,
                        'created_by' => $user->id,
                        'nama_barang_pekerjaan' => $input['D'],
                        'nomor_kontrak' => $input['E'],
                        'nomor_sp2d' => $input['F'],
                        'plus_beban_pegawai' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'plus_beban_persediaan' => $this->changeStringMoneyToFloatDouble($input['H']),
                        'plus_beban_jasa' => $this->changeStringMoneyToFloatDouble($input['I']),
                        'plus_beban_pemeliharaan' => $this->changeStringMoneyToFloatDouble($input['J']),
                        'plus_beban_perjalanan_dinas' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'plus_beban_hibah' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'plus_beban_lain_lain' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'plus_jumlah_penyesuaian' => $this->changeStringMoneyToFloatDouble($input['N']),

                        'min_aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'min_aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['P']),
                        'min_aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['Q']),
                        'min_aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['R']),
                        'min_aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['S']),
                        'min_konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['T']),
                        'min_aset_lain_lain' => $this->changeStringMoneyToFloatDouble($input['U']),
                        'min_jumlah_penyesuaian' => $this->changeStringMoneyToFloatDouble($input['V']),
                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
            DB::commit();
            return $this->successResponse('Data PADB Modal Ke Beban berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PADBBarjasAsetImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }

                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                if (!$kodeRekening) {
                    continue;
                }
                $data = BarjasKeAset::updateOrCreate(
                    [
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'kode_rekening_id' => $kodeRekening->id,
                        'created_by' => $user->id,
                        'nama_barang_pekerjaan' => $input['D'],
                        'nomor_kontrak' => $input['E'],
                        'nomor_sp2d' => $input['F'],
                        'plus_aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'plus_aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['H']),
                        'plus_aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['I']),
                        'plus_aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['J']),
                        'plus_aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'plus_konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'plus_aset_lain_lain' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'plus_jumlah_penyesuaian' => $this->changeStringMoneyToFloatDouble($input['N']),

                        'min_beban_pegawai' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'min_beban_persediaan' => $this->changeStringMoneyToFloatDouble($input['P']),
                        'min_beban_jasa' => $this->changeStringMoneyToFloatDouble($input['Q']),
                        'min_beban_pemeliharaan' => $this->changeStringMoneyToFloatDouble($input['R']),
                        'min_beban_perjalanan_dinas' => $this->changeStringMoneyToFloatDouble($input['S']),
                        'min_beban_hibah' => $this->changeStringMoneyToFloatDouble($input['U']),
                        'min_beban_lain_lain' => $this->changeStringMoneyToFloatDouble($input['T']),
                        'min_jumlah_penyesuaian' => $this->changeStringMoneyToFloatDouble($input['V']),
                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
            DB::commit();
            return $this->successResponse('Data PADB Barjas Ke Aset berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PADBPenyesuaianAsetImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }

                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                if (!$kodeRekening) {
                    continue;
                }
                $data = PenyesuaianAset::updateOrCreate(
                    [
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'kode_rekening_id' => $kodeRekening->id,
                        'created_by' => $user->id,
                        'nama_barang_pekerjaan' => $input['D'],
                        'nomor_kontrak' => $input['E'],
                        'nomor_sp2d' => $input['F'],
                        'plus_aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'plus_aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['H']),
                        'plus_aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['I']),
                        'plus_aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['J']),
                        'plus_aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'plus_konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'plus_aset_lain_lain' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'plus_jumlah_penyesuaian' => $this->changeStringMoneyToFloatDouble($input['N']),

                        'min_aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'min_aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['P']),
                        'min_aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['Q']),
                        'min_aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['R']),
                        'min_aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['S']),
                        'min_konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['T']),
                        'min_aset_lain_lain' => $this->changeStringMoneyToFloatDouble($input['U']),
                        'min_jumlah_penyesuaian' => $this->changeStringMoneyToFloatDouble($input['V']),
                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
            DB::commit();
            return $this->successResponse('Data PADB Barjas Ke Aset berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PADBAtribusiImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }

                if ($input['C']) {
                    $belBarjasKR = KodeRekening::where('fullcode', $input['C'])->first();
                    if (!$belBarjasKR) {
                        continue;
                    }
                }

                if ($input['H']) {
                    $berModalKR = KodeRekening::where('fullcode', $input['H'])->first();
                    if (!$berModalKR) {
                        continue;
                    }
                }

                $data = Atribusi::updateOrCreate(
                    [
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'uraian_pekerjaan' => $input['B'],
                        'bel_barjas_kode_rekening_id' => $belBarjasKR->id ?? null,
                        'bel_barjas_nama_rekening_rincian_paket' => $belBarjasKR->name ?? null,
                        'bel_barjas_belanja' => $this->changeStringMoneyToFloatDouble($input['E']),
                        'bel_barjas_hutang' => $this->changeStringMoneyToFloatDouble($input['F']),
                        'bel_barjas_jumlah' => $this->changeStringMoneyToFloatDouble($input['G']),

                        'bel_modal_kode_rekening_id' => $berModalKR->id ?? null,
                        'bel_modal_nama_rekening_rincian_paket' => $berModalKR->name ?? null,
                        'bel_modal_belanja' => $this->changeStringMoneyToFloatDouble($input['J']),
                        'bel_modal_hutang' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'bel_modal_jumlah' => $this->changeStringMoneyToFloatDouble($input['L']),

                        'ket_no_kontrak_pegawai_barang_jasa' => $input['M'],
                        'ket_no_sp2d_pegawai_barang_jasa' => $input['N'],

                        'atri_aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'atri_aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['P']),
                        'atri_aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['Q']),
                        'atri_aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['R']),
                        'atri_aset_tetap_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['S']),
                        'atri_konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['T']),
                        'atri_aset_lain_lain' => $this->changeStringMoneyToFloatDouble($input['U']),

                        'atri_ket_no_kontrak_sp2d' => null,
                    ],
                    [
                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            DB::commit();
            return $this->successResponse('Data PADB Atribusi berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PADBPenilaianAsetImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }

                $data = DB::table('acc_padb_penilaian_aset')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'kelompok_barang_aset' => $input['B'],
                        'nama_barang' => $input['C'],
                        'tahun_perolehan' => $input['D'],
                        'metode_perolehan' => $input['E'],
                        'nilai_awal_aset' => $this->changeStringMoneyToFloatDouble($input['F']),
                        'hasil_penilaian' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'nomor_berita_acara' => $input['H'],
                        'tanggal_berita_acara' => $this->changeStringToDate($input['I']),
                        'keterangan' => $input['J'],

                        'persediaan' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['N']),
                        'aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['P']),
                        'konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['Q']),
                        'aset_lainnya' => $this->changeStringMoneyToFloatDouble($input['R']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data PADB Penilaian Aset berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PADBPenghapusanAsetImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }

                $data = DB::table('acc_padb_penghapusan_aset')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'kelompok_barang_aset' => $input['B'],
                        'nama_barang' => $input['C'],
                        'tahun_perolehan' => $input['D'],
                        'nilai_perolehan' => $this->changeStringMoneyToFloatDouble($input['E']),
                        'akumulasi_penyusutan' => $this->changeStringMoneyToFloatDouble($input['F']),

                        'nomor_berita_acara' => $input['G'],
                        'tanggal_berita_acara' => $this->changeStringToDate($input['H']),

                        'persediaan' => $this->changeStringMoneyToFloatDouble($input['I']),
                        'aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['J']),
                        'aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['N']),
                        'konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'aset_lainnya' => $this->changeStringMoneyToFloatDouble($input['P']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data PADB Penghapusan Aset berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage() . ' Line: ' . $e->getLine() . ' File: ' . $e->getFile());
        }
    }

    private function PADBPenjualanAsetImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }

                $data = DB::table('acc_padb_penjualan_aset')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'kelompok_barang_aset' => $input['B'],
                        'nama_barang' => $input['C'],
                        'tahun_perolehan' => $input['D'],
                        'harga_perolehan' => $this->changeStringMoneyToFloatDouble($input['E']),
                        'akumulasi_penyusutan' => $this->changeStringMoneyToFloatDouble($input['F']),
                        'harga_jual' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'surplus' => $this->changeStringMoneyToFloatDouble($input['H']),

                        'nomor_berita_acara' => $input['I'],
                        'tanggal_berita_acara' => $this->changeStringToDate($input['J']),

                        'persediaan' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['N']),
                        'aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['P']),
                        'konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['Q']),
                        'aset_lainnya' => $this->changeStringMoneyToFloatDouble($input['R']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data PADB Penghapusan Aset berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PADBMutasiAsetImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $toInstance = Instance::where('name', $input['B'])->first();
                // if (!$toInstance) {
                //     continue;
                // }


                $data = DB::table('acc_padb_tambahan_mutasi_aset')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'from_instance_id' => $instance->id,
                        'to_instance_id' => $toInstance->id ?? null,

                        'kelompok_aset' => $input['C'],
                        'nama_barang' => $input['D'],
                        'tahun_perolehan' => (int)$input['E'],
                        'nilai_perolehan' => $this->changeStringMoneyToFloatDouble($input['F']),
                        'akumulasi_penyusutan' => $this->changeStringMoneyToFloatDouble($input['G']),

                        'bast_number' => $input['H'],
                        'bast_date' => $this->changeStringToDate($input['I']),

                        'plus_aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['J']),
                        'plus_aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'plus_aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'plus_aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'plus_aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['N']),
                        'plus_kdp' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'plus_aset_lainnya' => $this->changeStringMoneyToFloatDouble($input['P']),

                        // 'min_aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['L']),
                        // 'min_aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['M']),
                        // 'min_aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['N']),
                        // 'min_aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['O']),
                        // 'min_aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['P']),
                        // 'min_kdp' => $this->changeStringMoneyToFloatDouble($input['Q']),
                        // 'min_aset_lainnya' => $this->changeStringMoneyToFloatDouble($input['R']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data PADB Mutasi Aset berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PADBPekerjaanKontrakImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                if (!$kodeRekening) {
                    continue;
                }

                $data = DB::table('acc_padb_tambahan_daftar_pekerjaan')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'kode_rekening_id' => $kodeRekening->id ?? null,
                        'kode_rekening_name' => $kodeRekening->name ?? null,

                        'nama_kegiatan_paket' => $input['D'],
                        'pelaksana_pekerjaan' => $input['E'],
                        'no_kontrak' => $input['F'],
                        'periode_kontrak' => $input['G'],
                        'tanggal_kontrak' => $this->changeStringToDate($input['H']) ?? null,
                        'nilai_belanja_kontrak' => $this->changeStringMoneyToFloatDouble($input['I']),

                        'payment_1_sp2d' => $input['J'],
                        'payment_1_tanggal' => $this->changeStringToDate($input['K']),
                        'payment_1_jumlah' => $this->changeStringMoneyToFloatDouble($input['L']),

                        'payment_2_sp2d' => $input['M'],
                        'payment_2_tanggal' => $this->changeStringToDate($input['N']),
                        'payment_2_jumlah' => $this->changeStringMoneyToFloatDouble($input['O']),

                        'payment_3_sp2d' => $input['P'],
                        'payment_3_tanggal' => $this->changeStringToDate($input['Q']),
                        'payment_3_jumlah' => $this->changeStringMoneyToFloatDouble($input['R']),

                        'payment_4_sp2d' => $input['S'],
                        'payment_4_tanggal' => $this->changeStringToDate($input['T']),
                        'payment_4_jumlah' => $this->changeStringMoneyToFloatDouble($input['U']),

                        'jumlah_pembayaran_sd_desember' => $this->changeStringMoneyToFloatDouble($input['V']),
                        'kewajiban_tidak_terbayar_sd_desember' => $this->changeStringMoneyToFloatDouble($input['W']),
                        // 'tanggal_berita_acara' => '',
                        // 'tanggal_surat_pengakuan_hutang' => '',

                        'keterangan' => '',

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data PADB Pekerjaan Kontrak berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PADBHibahMasukImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['D'])->first();
                if (!$kodeRekening) {
                    continue;
                }

                $data = DB::table('acc_padb_tambahan_hibah_masuk')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'penerima_hibah' => $input['B'],
                        'pemberi_hibah' => $input['C'],

                        'kode_rekening_id' => $kodeRekening->id ?? null,

                        'nama_barang' => $input['F'],
                        'nilai' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'nomor_berita_acara' => $input['H'],
                        'tanggal_berita_acara' => $this->changeStringToDate($input['I']) ?? null,

                        'persediaan' => $this->changeStringMoneyToFloatDouble($input['J']),
                        'aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['N']),
                        'aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['P']),
                        'aset_lainnya' => $this->changeStringMoneyToFloatDouble($input['Q']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data PADB Hibah Masuk berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PADBHibahKeluarImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['D'])->first();
                if (!$kodeRekening) {
                    continue;
                }

                $data = DB::table('acc_padb_tambahan_hibah_keluar')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'penerima_hibah' => $input['B'],
                        'pemberi_hibah' => $input['C'],

                        'kode_rekening_id' => $kodeRekening->id ?? null,

                        'nama_barang' => $input['F'],
                        'nilai' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'nomor_berita_acara' => $input['H'],
                        'tanggal_berita_acara' => $this->changeStringToDate($input['I']) ?? null,

                        'persediaan' => $this->changeStringMoneyToFloatDouble($input['J']),
                        'aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['N']),
                        'aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['P']),
                        'aset_lainnya' => $this->changeStringMoneyToFloatDouble($input['Q']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data PADB Hibah Masuk berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function BelanjaBayarDimukaImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                if (!$kodeRekening) {
                    continue;
                }

                $data = DB::table('acc_belanja_bayar_dimuka')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'kode_rekening_id' => $kodeRekening->id ?? null,

                        'uraian' => $input['D'],
                        'nomor_perjanjian' => $input['E'],
                        'tanggal_perjanjian' => $this->changeStringToDate($input['F']) ?? null,
                        'rekanan' => $input['G'],
                        'jangka_waktu' => $input['H'],
                        'kontrak_date_start' => $this->changeStringToDate($input['I']) ?? null,
                        'kontrak_date_end' => $this->changeStringToDate($input['J']) ?? null,
                        'kontrak_value' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'sudah_jatuh_tempo' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'belum_jatuh_tempo' => $this->changeStringMoneyToFloatDouble($input['M']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data Belanja Bayar Dimuka berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function BarangHabisPakaiImport($datas, $params)
    {
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['D'])->first();
                if (!$kodeRekening) {
                    continue;
                }

                $data = DB::table('acc_persediaan_barang_habis_pakai')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'nama_persediaan' => $input['B'],
                        'saldo_awal' => $this->changeStringMoneyToFloatDouble($input['C']),

                        'kode_rekening_id' => $kodeRekening->id ?? null,

                        'realisasi_lra' => $this->changeStringMoneyToFloatDouble($input['F']),
                        'hutang_belanja' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'perolehan_hibah' => $this->changeStringMoneyToFloatDouble($input['H']),
                        'saldo_akhir' => $this->changeStringMoneyToFloatDouble($input['I']),
                        'beban_persediaan' => $this->changeStringMoneyToFloatDouble($input['J']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data Persediaan Barang Habis Pakai berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    private function PembayaranUtangImport($datas, $params)
    {
        // return $datas;
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                // if (!$kodeRekening) {
                //     continue;
                // }

                // $jangkaPendekKeys = ['aset_tetap_tanah', 'aset_tetap_peralatan_mesin', 'aset_tetap_gedung_bangunan', 'aset_tetap_jalan_jaringan_irigasi', 'aset_tetap_lainnya', 'konstruksi_dalam_pekerjaan', 'aset_lain_lain'];
                $jangkaPendekKeys = [
                    'X',
                    'Y',
                    'Z',
                    'AA',
                    'AB',
                    'AC',
                    'AD'
                ];
                $jangkaPendek = 0;
                foreach ($jangkaPendekKeys as $key) {
                    if ($key) {
                        $jangkaPendek += $this->changeStringMoneyToFloatDouble($input[$key]);
                    }
                }

                $data = DB::table('acc_htb_pembayaran_hutang')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'kode_rekening_id' => $kodeRekening->id ?? null,
                        'nama_kegiatan' => $input['D'],
                        'pelaksana_pekerjaan' => $input['E'],
                        'nomor_kontrak' => $input['F'],
                        'tahun_kontrak' => (int)$input['G'],
                        'kewajiban_tidak_terbayar' => 0,
                        'kewajiban_tidak_terbayar_last_year' => $this->changeStringMoneyToFloatDouble($input['H']),

                        'p1_nomor_sp2d' => $input['I'],
                        'p1_tanggal' => $this->changeStringToDate($input['J']),
                        'p1_jumlah' => $this->changeStringMoneyToFloatDouble($input['K']),

                        'p2_nomor_sp2d' => $input['L'],
                        'p2_tanggal' => $this->changeStringToDate($input['M']),
                        'p2_jumlah' => $this->changeStringMoneyToFloatDouble($input['N']),

                        'jumlah_pembayaran_hutang' => $this->changeStringMoneyToFloatDouble($input['O']),
                        'sisa_hutang' => $this->changeStringMoneyToFloatDouble($input['P']),

                        'pegawai' => $this->changeStringMoneyToFloatDouble($input['Q']),
                        'persediaan' => $this->changeStringMoneyToFloatDouble($input['R']),
                        'perjadin' => $this->changeStringMoneyToFloatDouble($input['S']),
                        'jasa' => $this->changeStringMoneyToFloatDouble($input['T']),
                        'pemeliharaan' => $this->changeStringMoneyToFloatDouble($input['U']),
                        'uang_jasa_diserahkan' => $this->changeStringMoneyToFloatDouble($input['V']),
                        'hibah' => $this->changeStringMoneyToFloatDouble($input['W']),

                        'aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['X']),
                        'aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['Y']),
                        'aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['Z']),
                        'aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['AA']),
                        'aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['AB']),
                        'konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['AC']),
                        'aset_lain_lain' => $this->changeStringMoneyToFloatDouble($input['AD']),
                        'beban' => $this->changeStringMoneyToFloatDouble($input['AE']),
                        // 'jangka_pendek' => 0,
                        'jangka_pendek' => $jangkaPendek ?? 0,

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data Pembayaran Utang berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e);
        }
    }

    private function UtangBaruImport($datas, $params)
    {
        // return $datas;
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                // if (!$kodeRekening) {
                //     continue;
                // }

                // $jangkaPendekKeys = ['aset_tetap_tanah', 'aset_tetap_peralatan_mesin', 'aset_tetap_gedung_bangunan', 'aset_tetap_jalan_jaringan_irigasi', 'aset_tetap_lainnya', 'konstruksi_dalam_pekerjaan', 'aset_lain_lain'];
                $jangkaPendekKeys = [
                    'AD',
                    'AE',
                    'AF',
                    'AG',
                    'AH',
                    'AI',
                    'AJ'
                ];
                $jangkaPendek = 0;
                foreach ($jangkaPendekKeys as $key) {
                    if ($key) {
                        $jangkaPendek += $this->changeStringMoneyToFloatDouble($input[$key]);
                    }
                }

                $data = DB::table('acc_htb_hutang_baru')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'kode_rekening_id' => $kodeRekening->id ?? null,
                        'nama_kegiatan' => $input['D'],
                        'pelaksana_pekerjaan' => $input['E'],
                        'nomor_kontrak' => $input['F'],
                        'tahun_kontrak' => (int)$input['G'],
                        'nilai_kontrak' => $this->changeStringMoneyToFloatDouble($input['H']),

                        'p1_nomor_sp2d' => $input['I'],
                        'p1_tanggal' => $this->changeStringToDate($input['J']),
                        'p1_jumlah' => $this->changeStringMoneyToFloatDouble($input['K']),

                        'p2_nomor_sp2d' => $input['L'],
                        'p2_tanggal' => $this->changeStringToDate($input['M']),
                        'p2_jumlah' => $this->changeStringMoneyToFloatDouble($input['N']),

                        'p3_nomor_sp2d' => $input['O'],
                        'p3_tanggal' => $this->changeStringToDate($input['P']),
                        'p3_jumlah' => $this->changeStringMoneyToFloatDouble($input['Q']),

                        'p4_nomor_sp2d' => $input['R'],
                        'p4_tanggal' => $this->changeStringToDate($input['S']),
                        'p4_jumlah' => $this->changeStringMoneyToFloatDouble($input['T']),

                        'jumlah_pembayaran_hutang' => $this->changeStringMoneyToFloatDouble($input['U']),
                        'hutang_baru' => $this->changeStringMoneyToFloatDouble($input['V']),

                        'pegawai' => $this->changeStringMoneyToFloatDouble($input['W']),
                        'persediaan' => $this->changeStringMoneyToFloatDouble($input['X']),
                        'perjadin' => $this->changeStringMoneyToFloatDouble($input['Y']),
                        'jasa' => $this->changeStringMoneyToFloatDouble($input['Z']),
                        'pemeliharaan' => $this->changeStringMoneyToFloatDouble($input['AA']),
                        'uang_jasa_diserahkan' => $this->changeStringMoneyToFloatDouble($input['AB']),
                        'hibah' => $this->changeStringMoneyToFloatDouble($input['AC']),

                        'aset_tetap_tanah' => $this->changeStringMoneyToFloatDouble($input['AD']),
                        'aset_tetap_peralatan_mesin' => $this->changeStringMoneyToFloatDouble($input['AE']),
                        'aset_tetap_gedung_bangunan' => $this->changeStringMoneyToFloatDouble($input['AF']),
                        'aset_tetap_jalan_jaringan_irigasi' => $this->changeStringMoneyToFloatDouble($input['AG']),
                        'aset_tetap_lainnya' => $this->changeStringMoneyToFloatDouble($input['AH']),
                        'konstruksi_dalam_pekerjaan' => $this->changeStringMoneyToFloatDouble($input['AI']),
                        'aset_lain_lain' => $this->changeStringMoneyToFloatDouble($input['AJ']),
                        'beban' => $this->changeStringMoneyToFloatDouble($input['AK']),
                        // 'jangka_pendek' => 0,
                        'jangka_pendek' => $jangkaPendek ?? 0,

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data Utang Baru berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e);
        }
    }

    private function PiutangImport($datas, $params)
    {
        // return $datas;
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                if (!$kodeRekening) {
                    continue;
                }

                $data = DB::table('acc_plo_piutang')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'type' => $input['O'],
                        'kode_rekening_id' => $kodeRekening->id ?? null,
                        'saldo_awal' => $this->changeStringMoneyToFloatDouble($input['D']),
                        'koreksi_saldo_awal' => $this->changeStringMoneyToFloatDouble($input['E']),
                        'mutasi_debet' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'mutasi_kredit' => $this->changeStringMoneyToFloatDouble($input['H']),
                        'saldo_akhir' => $this->changeStringMoneyToFloatDouble($input['I']),
                        'umur_piutang_1' => $this->changeStringMoneyToFloatDouble($input['J']),
                        'umur_piutang_2' => $this->changeStringMoneyToFloatDouble($input['K']),
                        'umur_piutang_3' => $this->changeStringMoneyToFloatDouble($input['L']),
                        'umur_piutang_4' => $this->changeStringMoneyToFloatDouble($input['M']),
                        'piutang_bruto' => $this->changeStringMoneyToFloatDouble($input['N']),
                        'penghapusan_piutang' => $this->changeStringMoneyToFloatDouble($input['F']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data Piutang berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e);
        }
    }

    private function PenyisihanImport($datas, $params)
    {
        // return $datas;
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                if (!$kodeRekening) {
                    continue;
                }

                $data = DB::table('acc_plo_penyisihan')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'type' => $input['J'],
                        'kode_rekening_id' => $kodeRekening->id ?? null,

                        'piutang_bruto' => $this->changeStringMoneyToFloatDouble($input['D']),
                        'penyisihan_piutang_1' => $this->changeStringMoneyToFloatDouble($input['E']),
                        'penyisihan_piutang_2' => $this->changeStringMoneyToFloatDouble($input['F']),
                        'penyisihan_piutang_3' => $this->changeStringMoneyToFloatDouble($input['G']),
                        'penyisihan_piutang_4' => $this->changeStringMoneyToFloatDouble($input['H']),
                        'jumlah' => $this->changeStringMoneyToFloatDouble($input['I']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data Penyisihan berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e);
        }
    }

    private function PendapatanLOBebanImport($datas, $params)
    {
        // return $datas;
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                if (!$kodeRekening) {
                    continue;
                }

                $data = DB::table('acc_plo_beban')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'type' => $input['H'],
                        'kode_rekening_id' => $kodeRekening->id ?? null,

                        'jumlah_penyisihan' => $this->changeStringMoneyToFloatDouble($input['D']),
                        'jumlah_penyisihan_last_year' => $this->changeStringMoneyToFloatDouble($input['E']),
                        'koreksi_penyisihan' => $this->changeStringMoneyToFloatDouble($input['F']),
                        'beban_penyisihan' => $this->changeStringMoneyToFloatDouble($input['G']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data Beban berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e);
        }
    }

    private function PDDImport($datas, $params)
    {
        // return $datas;
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                $kodeRekening = KodeRekening::where('fullcode', $input['B'])->first();
                if (!$kodeRekening) {
                    continue;
                }

                $data = DB::table('acc_plo_pdd')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        'type' => $input['H'],
                        'kode_rekening_id' => $kodeRekening->id ?? null,

                        'pendapatan_diterima_dimuka_awal' => $this->changeStringMoneyToFloatDouble($input['D']),
                        'mutasi_berkurang' => $this->changeStringMoneyToFloatDouble($input['E']),
                        'mutasi_bertambah' => $this->changeStringMoneyToFloatDouble($input['F']),
                        'pendapatan_diterima_dimuka_akhir' => $this->changeStringMoneyToFloatDouble($input['G']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data PDD berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e);
        }
    }

    private function PengembalianBelanjaImport($datas, $params)
    {
        // return $datas;
        $user = auth()->user();
        DB::beginTransaction();
        try {
            foreach ($datas as $input) {
                $instance = Instance::find($params['instance']);
                if (!$instance) {
                    continue;
                }
                // $kodeRekening = KodeRekening::where('fullcode', $input['C'])->first();
                // if (!$kodeRekening) {
                //     continue;
                // }

                if(!$input['C'] && !$input['H']){
                    continue;
                }

                $data = DB::table('acc_pengembalian_belanja')
                    ->insert([
                        'periode_id' => $params['periode'],
                        'year' => $params['year'],
                        'instance_id' => $instance->id,
                        'created_by' => $user->id,

                        // 'tanggal_setor' => $this->changeStringToDate($input['C']),
                        'tanggal_setor' => $input['C'],
                        'kode_rekening_id' => $kodeRekening->id ?? null,
                        'uraian' => $input['F'],
                        'jenis_spm' => $input['G'],
                        'jumlah' => $this->changeStringMoneyToFloatDouble($input['H']),

                        'updated_by' => $user->id,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            return $this->successResponse('Data Pengembalian Belanja berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan saat mengimpor data: ' . $e);
        }
    }
}
