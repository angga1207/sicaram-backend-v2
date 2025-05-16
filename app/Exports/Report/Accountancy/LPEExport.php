<?php

namespace App\Exports\Report\Accountancy;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LPEExport implements FromCollection, WithHeadings, WithColumnFormatting, WithStyles, WithColumnWidths
{
    public $datas;
    public $year;
    function __construct($datas, $year)
    {
        $this->datas = $datas;
        $this->year = $year;
    }

    public function headings(): array
    {
        $thisYear = $this->year;
        $lastYear = $this->year - 1;
        return [
            'Uraian',
            (string)$thisYear . ' (Rp)',
            (string)$lastYear . ' (Rp)',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_CURRENCY_ID,
            'C' => NumberFormat::FORMAT_CURRENCY_ID,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1   => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],

            ],
            'A' => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => 'left',
                    'vertical' => 'center',
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'C' => 30,
            'D' => 30,
        ];
    }

    public function collection()
    {
        $datas = [];
        $collectDatas = collect($this->datas);
        foreach ($collectDatas as $data) {
            $datas[] = [
                $data['uraian'],
                $data['saldo_akhir'],
                $data['saldo_awal'],
            ];
        }
        $datas = collect($datas);
        return $datas;
    }
}
