<?php

namespace App\Exports\Accountancy;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KertasKerjaExportPadb implements FromCollection, WithHeadings, WithColumnFormatting, WithStyles, WithColumnWidths
{
    public $datas, $params;
    function __construct($datas, $params)
    {
        $this->datas = $datas;
        $this->params = $params;
    }

    public function headings(): array
    {
        if ($this->params['type'] == 'beban_barjas') {
            return [
                'Perangkat Daerah',
                'Kode Rekening',
                'Kode Rekening',
                'Nama Barang / Pekerjaan',
                'Nomor Kontrak',
                'Nomor SP2D',
                'Beban Pegawai', // Kuning Start
                'Beban Persediaan',
                'Beban Jasa',
                'Beban Pemeliharaan',
                'Beban Perjalanan Dinas',
                'Beban Uang/ Jasa Diberikan',
                'Beban Hibah',
                'Jumlah Penyesuaian',
                'Beban Pegawai', // Ijo Start
                'Beban Persediaan',
                'Beban Jasa',
                'Beban Pemeliharaan',
                'Beban Perjalanan Dinas',
                'Beban Uang/ Jasa Diberikan',
                'Beban Hibah',
                'Jumlah Penyesuaian',
            ];
        } else if ($this->params['type'] == 'modal_beban') {
            return [
                'Perangkat Daerah',
                'Kode Rekening',
                'Kode Rekening',
                'Nama Barang / Pekerjaan',
                'Nomor Kontrak',
                'Nomor SP2D',
                'Beban Pegawai', // Kuning Start
                'Beban Persediaan',
                'Beban Jasa',
                'Beban Pemeliharaan',
                'Beban Perjalanan Dinas',
                'Beban Uang/ Jasa Diberikan',
                'Beban Hibah',
                'Jumlah Penyesuaian',
                'Aset Tetap Tanah', // Ijo Start
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lain-lain',
                'Jumlah Penyesuaian',
            ];
        } else if ($this->params['type'] == 'barjas_aset') {
            return [
                'Perangkat Daerah',
                'Kode Rekening',
                'Kode Rekening',
                'Nama Barang / Pekerjaan',
                'Nomor Kontrak',
                'Nomor SP2D',
                'Aset Tetap Tanah', // Kuning Start
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lain-lain',
                'Jumlah Penyesuaian',
                'Beban Pegawai', // Ijo Start
                'Beban Persediaan',
                'Beban Jasa',
                'Beban Pemeliharaan',
                'Beban Perjalanan Dinas',
                'Beban Uang/ Jasa Diberikan',
                'Beban Hibah',
                'Jumlah Penyesuaian',
            ];
        } else if ($this->params['type'] == 'penyesuaian_aset') {
            return [
                'Perangkat Daerah',
                'Kode Rekening',
                'Kode Rekening',
                'Nama Barang / Pekerjaan',
                'Nomor Kontrak',
                'Nomor SP2D',
                'Aset Tetap Tanah', // Kuning Start
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lain-lain',
                'Aset Tetap Tanah', // Ijo Start
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lain-lain',
                'Jumlah Penyesuaian',
            ];
        } else if ($this->params['type'] == 'atribusi') {
            return [
                'Perangkat Daerah',
                'Uraian Pekerjaan',
                // 'Kode Rekening',
                // 'Nama Rekening',
                // 'Belanja Pegawai', // Kuning Start
                // 'Hutang Pegawai',
                // 'Jumlah',
                'Kode Rekening',
                'Nama Rekening',
                'Belanja Barang Jasa',
                'Hutang Barang Jasa',
                'Jumlah',
                'Kode Rekening',
                'Nama Rekening',
                'Belanja Modal',
                'Hutang Modal',
                'Jumlah',
                'Keterangan No Kontrak Pegawai / Barang Jasa',
                'Keterangan No SP2D Pegawai / Barang Jasa',
                'Aset Tetap Tanah', // Kuning Start
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lain-lain',
                // 'Keterangan No Kontrak SP2D',
            ];
        } else if ($this->params['type'] == 'mutasi_aset') {
            return [
                'Perangkat Daerah Lama',
                'Perangkat Daerah Baru',
                'Kelompok Aset',
                'Nama dan Rincian Barang',
                'Tahun Perolehan',
                'Nilai Perolehan',
                'Akumulasi Penyusutan',
                'Nomor Berita Acara',
                'Tanggal Berita Acara',
                'Aset Tetap Tanah', // Kuning Start
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lain-lain',
            ];
        } else if ($this->params['type'] == 'pekerjaan_kontrak') {
            return [
                'Perangkat Daerah',
                'Kode Rekening',
                'Nama Rekening',
                'Nama Kegiatan - Paket Pekerjaan',
                'Pelaksana Pekerjaan',
                'No Kontrak',
                'Periode Kontrak',
                'Nilai Belanja / Nilai Kontrak',
                'SP2D Pembayaran 1',
                'Tanggal Pembayaran 1',
                'Jumlah Pembayaran 1',
                'SP2D Pembayaran 2',
                'Tanggal Pembayaran 2',
                'Jumlah Pembayaran 2',
                'SP2D Pembayaran 3',
                'Tanggal Pembayaran 3',
                'Jumlah Pembayaran 3',
                'SP2D Pembayaran 4',
                'Tanggal Pembayaran 4',
                'Jumlah Pembayaran 4',
                'Jumlah Pembayaran s/d 31 Desember',
                'Kewajiban Tidak Terbayar s/d 31 Desember',
                'Tanggal Berita Acara',
                'Tanggal Surat Pengakuan Utang',
            ];
        } else if ($this->params['type'] == 'hibah_masuk') {
            return [
                'Perangkat Daerah',
                'Penerima Hibah',
                'Pemberi Hibah',
                'Kode Rekening',
                'Nama Rekening',
                'Nama Barang',
                'Nilai',
                'Nomor Berita Acara',
                'Tanggal Berita Acara',
                'Persediaan',
                'Aset Tetap Tanah',
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lainnya',
            ];
        } else if ($this->params['type'] == 'hibah_keluar') {
            return [
                'Perangkat Daerah',
                'Penerima Hibah',
                'Pemberi Hibah',
                'Kode Rekening',
                'Nama Rekening',
                'Nama Barang',
                'Nilai',
                'Nomor Berita Acara',
                'Tanggal Berita Acara',
                'Persediaan',
                'Aset Tetap Tanah',
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lainnya',
            ];
        } else if ($this->params['type'] == 'penilaian_aset') {
            return [
                'Perangkat Daerah',
                'Kelompok Barang / Aset',
                'Nama Barang',
                'Tahun Perolehan',
                'Metode Perolehan',
                'Nilai Awal Aset',
                'Hasil Penilaian',
                'Nomor Berita Acara',
                'Tanggal Berita Acara',
                'Keterangan',
                'Persediaan',
                'Aset Tetap Tanah',
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lainnya',
                'Jumlah Penyesuaian',
            ];
        } else if ($this->params['type'] == 'penghapusan_aset') {
            return [
                'Perangkat Daerah',
                'Kelompok Barang / Aset',
                'Nama Barang',
                'Tahun Perolehan',
                'Nilai Perolehan',
                'Akumulasi Penyusutan',
                'Nomor Berita Acara',
                'Tanggal Berita Acara',
                'Keterangan',
                'Persediaan',
                'Aset Tetap Tanah',
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lainnya',
                'Jumlah Penyesuaian',
            ];
        } else if ($this->params['type'] == 'penjualan_aset') {
            return [
                'Perangkat Daerah',
                'Kelompok Barang / Aset',
                'Nama Barang',
                'Tahun Perolehan',
                'Harga Perolehan',
                'Akumulasi Penyusutan',
                'Harga Jual',
                'Surplus',
                'Nomor Berita Acara',
                'Tanggal Berita Acara',
                'Keterangan',
                'Persediaan',
                'Aset Tetap Tanah',
                'Aset Tetap Peralatan dan Mesin',
                'Aset Tetap Gedung dan Bangunan',
                'Aset Tetap Jalan Jaringan dan Irigasi',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Aset Lainnya',
                'Jumlah Penyesuaian',
            ];
        }
        return [];
    }

    public function columnFormats(): array
    {
        if (in_array($this->params['type'], ['mutasi_aset'])) {
            return [
                'A' => NumberFormat::FORMAT_TEXT,
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_CURRENCY_ID,
                'G' => NumberFormat::FORMAT_CURRENCY_ID,
                'H' => NumberFormat::FORMAT_TEXT,
                'I' => NumberFormat::FORMAT_TEXT,
                'J' => NumberFormat::FORMAT_CURRENCY_ID,
                'K' => NumberFormat::FORMAT_CURRENCY_ID,
                'L' => NumberFormat::FORMAT_CURRENCY_ID,
                'M' => NumberFormat::FORMAT_CURRENCY_ID,
                'N' => NumberFormat::FORMAT_CURRENCY_ID,
                'O' => NumberFormat::FORMAT_CURRENCY_ID,
                'P' => NumberFormat::FORMAT_CURRENCY_ID,
                'Q' => NumberFormat::FORMAT_CURRENCY_ID,
                'R' => NumberFormat::FORMAT_CURRENCY_ID,
                'S' => NumberFormat::FORMAT_CURRENCY_ID,
                'T' => NumberFormat::FORMAT_CURRENCY_ID,
            ];
        } else if (in_array($this->params['type'], ['pekerjaan_kontrak'])) {
            return [
                'A' => NumberFormat::FORMAT_TEXT,
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_TEXT,
                'G' => NumberFormat::FORMAT_TEXT,
                'H' => NumberFormat::FORMAT_CURRENCY_ID,
                'I' => NumberFormat::FORMAT_TEXT,
                'J' => NumberFormat::FORMAT_TEXT,
                'K' => NumberFormat::FORMAT_CURRENCY_ID,
                'L' => NumberFormat::FORMAT_TEXT,
                'M' => NumberFormat::FORMAT_TEXT,
                'N' => NumberFormat::FORMAT_CURRENCY_ID,
                'O' => NumberFormat::FORMAT_TEXT,
                'P' => NumberFormat::FORMAT_TEXT,
                'Q' => NumberFormat::FORMAT_CURRENCY_ID,
                'R' => NumberFormat::FORMAT_TEXT,
                'S' => NumberFormat::FORMAT_TEXT,
                'T' => NumberFormat::FORMAT_CURRENCY_ID,
                'U' => NumberFormat::FORMAT_CURRENCY_ID,
                'V' => NumberFormat::FORMAT_CURRENCY_ID,
                'W' => NumberFormat::FORMAT_TEXT,
                'X' => NumberFormat::FORMAT_TEXT,
            ];
        } else if (in_array($this->params['type'], ['hibah_masuk', 'hibah_keluar'])) {
            return [
                'A' => NumberFormat::FORMAT_TEXT,
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_TEXT,
                'G' => NumberFormat::FORMAT_CURRENCY_ID,
                'H' => NumberFormat::FORMAT_TEXT,
                'I' => NumberFormat::FORMAT_TEXT,
                'J' => NumberFormat::FORMAT_CURRENCY_ID,
                'K' => NumberFormat::FORMAT_CURRENCY_ID,
                'L' => NumberFormat::FORMAT_CURRENCY_ID,
                'M' => NumberFormat::FORMAT_CURRENCY_ID,
                'N' => NumberFormat::FORMAT_CURRENCY_ID,
                'O' => NumberFormat::FORMAT_CURRENCY_ID,
                'P' => NumberFormat::FORMAT_CURRENCY_ID,
                'Q' => NumberFormat::FORMAT_CURRENCY_ID,
            ];
        } else if (in_array($this->params['type'], ['penilaian_aset'])) {
            return [
                'A' => NumberFormat::FORMAT_TEXT,
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_CURRENCY_ID,
                'G' => NumberFormat::FORMAT_CURRENCY_ID,
                'H' => NumberFormat::FORMAT_TEXT,
                'I' => NumberFormat::FORMAT_TEXT,
                'J' => NumberFormat::FORMAT_TEXT,
                'K' => NumberFormat::FORMAT_CURRENCY_ID,
                'L' => NumberFormat::FORMAT_CURRENCY_ID,
                'M' => NumberFormat::FORMAT_CURRENCY_ID,
                'N' => NumberFormat::FORMAT_CURRENCY_ID,
                'O' => NumberFormat::FORMAT_CURRENCY_ID,
                'P' => NumberFormat::FORMAT_CURRENCY_ID,
                'Q' => NumberFormat::FORMAT_CURRENCY_ID,
                'R' => NumberFormat::FORMAT_CURRENCY_ID,
                'S' => NumberFormat::FORMAT_CURRENCY_ID,
            ];
        } else if (in_array($this->params['type'], ['penghapusan_aset'])) {
            return [
                'A' => NumberFormat::FORMAT_TEXT,
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_CURRENCY_ID,
                'F' => NumberFormat::FORMAT_CURRENCY_ID,
                'G' => NumberFormat::FORMAT_TEXT,
                'H' => NumberFormat::FORMAT_TEXT,
                'I' => NumberFormat::FORMAT_TEXT,
                'J' => NumberFormat::FORMAT_CURRENCY_ID,
                'K' => NumberFormat::FORMAT_CURRENCY_ID,
                'L' => NumberFormat::FORMAT_CURRENCY_ID,
                'M' => NumberFormat::FORMAT_CURRENCY_ID,
                'N' => NumberFormat::FORMAT_CURRENCY_ID,
                'O' => NumberFormat::FORMAT_CURRENCY_ID,
                'P' => NumberFormat::FORMAT_CURRENCY_ID,
                'Q' => NumberFormat::FORMAT_CURRENCY_ID,
                'R' => NumberFormat::FORMAT_CURRENCY_ID,
                'S' => NumberFormat::FORMAT_CURRENCY_ID,
            ];
        } else if (in_array($this->params['type'], ['penjualan_aset'])) {
            return [
                'A' => NumberFormat::FORMAT_TEXT,
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_CURRENCY_ID,
                'F' => NumberFormat::FORMAT_CURRENCY_ID,
                'G' => NumberFormat::FORMAT_CURRENCY_ID,
                'H' => NumberFormat::FORMAT_CURRENCY_ID,
                'I' => NumberFormat::FORMAT_TEXT,
                'J' => NumberFormat::FORMAT_TEXT,
                'K' => NumberFormat::FORMAT_TEXT,
                'L' => NumberFormat::FORMAT_CURRENCY_ID,
                'M' => NumberFormat::FORMAT_CURRENCY_ID,
                'N' => NumberFormat::FORMAT_CURRENCY_ID,
                'O' => NumberFormat::FORMAT_CURRENCY_ID,
                'P' => NumberFormat::FORMAT_CURRENCY_ID,
                'Q' => NumberFormat::FORMAT_CURRENCY_ID,
                'R' => NumberFormat::FORMAT_CURRENCY_ID,
                'S' => NumberFormat::FORMAT_CURRENCY_ID,
                'T' => NumberFormat::FORMAT_CURRENCY_ID,
            ];
        } else {
            return [
                'A' => NumberFormat::FORMAT_TEXT,
                'B' => NumberFormat::FORMAT_CURRENCY_ID,
                'C' => NumberFormat::FORMAT_CURRENCY_ID,
                'D' => NumberFormat::FORMAT_CURRENCY_ID,
                'E' => NumberFormat::FORMAT_CURRENCY_ID,
                'F' => NumberFormat::FORMAT_CURRENCY_ID,
                'G' => NumberFormat::FORMAT_CURRENCY_ID,
                'H' => NumberFormat::FORMAT_CURRENCY_ID,
                'I' => NumberFormat::FORMAT_CURRENCY_ID,
                'J' => NumberFormat::FORMAT_CURRENCY_ID,
                'K' => NumberFormat::FORMAT_CURRENCY_ID,
                'L' => NumberFormat::FORMAT_CURRENCY_ID,
                'M' => NumberFormat::FORMAT_CURRENCY_ID,
                'N' => NumberFormat::FORMAT_CURRENCY_ID,
                'O' => NumberFormat::FORMAT_CURRENCY_ID,
                'P' => NumberFormat::FORMAT_CURRENCY_ID,
                'Q' => NumberFormat::FORMAT_CURRENCY_ID,
                'R' => NumberFormat::FORMAT_CURRENCY_ID,
                'S' => NumberFormat::FORMAT_CURRENCY_ID,
                'T' => NumberFormat::FORMAT_CURRENCY_ID,
                'U' => NumberFormat::FORMAT_CURRENCY_ID,
                'V' => NumberFormat::FORMAT_CURRENCY_ID,
                'W' => NumberFormat::FORMAT_CURRENCY_ID,
                'X' => NumberFormat::FORMAT_CURRENCY_ID,
                'Y' => NumberFormat::FORMAT_CURRENCY_ID,
                'Z' => NumberFormat::FORMAT_CURRENCY_ID,
                'AA' => NumberFormat::FORMAT_CURRENCY_ID,
                'AB' => NumberFormat::FORMAT_CURRENCY_ID,
                'AC' => NumberFormat::FORMAT_CURRENCY_ID,
                'AD' => NumberFormat::FORMAT_CURRENCY_ID,
                'AE' => NumberFormat::FORMAT_CURRENCY_ID,
                'AF' => NumberFormat::FORMAT_CURRENCY_ID,
                'AG' => NumberFormat::FORMAT_CURRENCY_ID,
                'AH' => NumberFormat::FORMAT_CURRENCY_ID,
                'AI' => NumberFormat::FORMAT_CURRENCY_ID,
                'AJ' => NumberFormat::FORMAT_CURRENCY_ID,
            ];
        }

        return [];
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
            // 'A' => 20,
            // 'B' => 40,
            // 'C' => 30,
            // 'D' => 30,
        ];
    }

    public function collection()
    {
        $datas = [];

        if ($this->params['type'] == 'beban_barjas') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance']['name'] ?? null,
                    $data['kode_rekening']['fullcode'] ?? null,
                    $data['kode_rekening']['name'] ?? null,
                    $data['nama_barang_pekerjaan'],
                    $data['nomor_kontrak'],
                    $data['nomor_sp2d'],
                    $data['plus_beban_pegawai'],
                    $data['plus_beban_persediaan'],
                    $data['plus_beban_jasa'],
                    $data['plus_beban_pemeliharaan'],
                    $data['plus_beban_perjalanan_dinas'],
                    $data['plus_beban_lain_lain'],
                    $data['plus_beban_hibah'],
                    $data['plus_jumlah_penyesuaian'],
                    $data['min_beban_pegawai'],
                    $data['min_beban_persediaan'],
                    $data['min_beban_jasa'],
                    $data['min_beban_pemeliharaan'],
                    $data['min_beban_perjalanan_dinas'],
                    $data['min_beban_lain_lain'],
                    $data['min_beban_hibah'],
                    $data['min_jumlah_penyesuaian'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                $collectDatas->sum('plus_beban_pegawai') ?? '0',
                $collectDatas->sum('plus_beban_persediaan') ?? '0',
                $collectDatas->sum('plus_beban_jasa') ?? '0',
                $collectDatas->sum('plus_beban_pemeliharaan') ?? '0',
                $collectDatas->sum('plus_beban_perjalanan_dinas') ?? '0',
                $collectDatas->sum('plus_beban_lain_lain') ?? '0',
                $collectDatas->sum('plus_beban_hibah') ?? '0',
                $collectDatas->sum('plus_jumlah_penyesuaian') ?? '0',
                $collectDatas->sum('min_beban_pegawai') ?? '0',
                $collectDatas->sum('min_beban_persediaan') ?? '0',
                $collectDatas->sum('min_beban_jasa') ?? '0',
                $collectDatas->sum('min_beban_pemeliharaan') ?? '0',
                $collectDatas->sum('min_beban_perjalanan_dinas') ?? '0',
                $collectDatas->sum('min_beban_lain_lain') ?? '0',
                $collectDatas->sum('min_beban_hibah') ?? '0',
                $collectDatas->sum('min_jumlah_penyesuaian') ?? '0',
            ];
        } else if ($this->params['type'] == 'modal_beban') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance_name'] ?? null,
                    $data['kode_rekening_fullcode'] ?? null,
                    $data['kode_rekening_name'] ?? null,
                    $data['nama_barang_pekerjaan'],
                    $data['nomor_kontrak'],
                    $data['nomor_sp2d'],
                    $data['plus_beban_pegawai'],
                    $data['plus_beban_persediaan'],
                    $data['plus_beban_jasa'],
                    $data['plus_beban_pemeliharaan'],
                    $data['plus_beban_perjalanan_dinas'],
                    $data['plus_beban_lain_lain'],
                    $data['plus_beban_hibah'],
                    $data['plus_jumlah_penyesuaian'],
                    $data['min_aset_tetap_tanah'],
                    $data['min_aset_tetap_peralatan_mesin'],
                    $data['min_aset_tetap_gedung_bangunan'],
                    $data['min_aset_tetap_jalan_jaringan_irigasi'],
                    $data['min_aset_tetap_lainnya'],
                    $data['min_konstruksi_dalam_pekerjaan'],
                    $data['min_aset_lain_lain'],
                    $data['min_jumlah_penyesuaian'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                $collectDatas->sum('plus_beban_pegawai'),
                $collectDatas->sum('plus_beban_persediaan'),
                $collectDatas->sum('plus_beban_jasa'),
                $collectDatas->sum('plus_beban_pemeliharaan'),
                $collectDatas->sum('plus_beban_perjalanan_dinas'),
                $collectDatas->sum('plus_beban_lain_lain'),
                $collectDatas->sum('plus_beban_hibah'),
                $collectDatas->sum('plus_jumlah_penyesuaian'),
                $collectDatas->sum('min_aset_tetap_tanah'),
                $collectDatas->sum('min_aset_tetap_peralatan_mesin'),
                $collectDatas->sum('min_aset_tetap_gedung_bangunan'),
                $collectDatas->sum('min_aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('min_aset_tetap_lainnya'),
                $collectDatas->sum('min_konstruksi_dalam_pekerjaan'),
                $collectDatas->sum('min_aset_lain_lain'),
                $collectDatas->sum('min_jumlah_penyesuaian'),
            ];
        } else if ($this->params['type'] == 'barjas_aset') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance']['name'] ?? null,
                    $data['kode_rekening']['fullcode'] ?? null,
                    $data['kode_rekening']['name'] ?? null,
                    $data['nama_barang_pekerjaan'],
                    $data['nomor_kontrak'],
                    $data['nomor_sp2d'],
                    $data['plus_aset_tetap_tanah'],
                    $data['plus_aset_tetap_peralatan_mesin'],
                    $data['plus_aset_tetap_gedung_bangunan'],
                    $data['plus_aset_tetap_jalan_jaringan_irigasi'],
                    $data['plus_aset_tetap_lainnya'],
                    $data['plus_konstruksi_dalam_pekerjaan'],
                    $data['plus_aset_lain_lain'],
                    $data['plus_jumlah_penyesuaian'],
                    $data['min_beban_pegawai'],
                    $data['min_beban_persediaan'],
                    $data['min_beban_jasa'],
                    $data['min_beban_pemeliharaan'],
                    $data['min_beban_perjalanan_dinas'],
                    $data['min_beban_lain_lain'],
                    $data['min_beban_hibah'],
                    $data['min_jumlah_penyesuaian'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                $collectDatas->sum('plus_aset_tetap_tanah'),
                $collectDatas->sum('plus_aset_tetap_peralatan_mesin'),
                $collectDatas->sum('plus_aset_tetap_gedung_bangunan'),
                $collectDatas->sum('plus_aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('plus_aset_tetap_lainnya'),
                $collectDatas->sum('plus_konstruksi_dalam_pekerjaan'),
                $collectDatas->sum('plus_aset_lain_lain'),
                $collectDatas->sum('plus_jumlah_penyesuaian'),
                $collectDatas->sum('min_beban_pegawai'),
                $collectDatas->sum('min_beban_persediaan'),
                $collectDatas->sum('min_beban_jasa'),
                $collectDatas->sum('min_beban_pemeliharaan'),
                $collectDatas->sum('min_beban_perjalanan_dinas'),
                $collectDatas->sum('min_beban_lain_lain'),
                $collectDatas->sum('min_beban_hibah'),
                $collectDatas->sum('min_jumlah_penyesuaian'),
            ];
        } else if ($this->params['type'] == 'penyesuaian_aset') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance']['name'] ?? null,
                    $data['kode_rekening']['fullcode'] ?? null,
                    $data['kode_rekening']['name'] ?? null,
                    $data['nama_barang_pekerjaan'],
                    $data['nomor_kontrak'],
                    $data['nomor_sp2d'],
                    $data['plus_aset_tetap_tanah'],
                    $data['plus_aset_tetap_peralatan_mesin'],
                    $data['plus_aset_tetap_gedung_bangunan'],
                    $data['plus_aset_tetap_jalan_jaringan_irigasi'],
                    $data['plus_aset_tetap_lainnya'],
                    $data['plus_konstruksi_dalam_pekerjaan'],
                    $data['plus_aset_lain_lain'],
                    $data['plus_jumlah_penyesuaian'],
                    $data['min_aset_tetap_tanah'],
                    $data['min_aset_tetap_peralatan_mesin'],
                    $data['min_aset_tetap_gedung_bangunan'],
                    $data['min_aset_tetap_jalan_jaringan_irigasi'],
                    $data['min_aset_tetap_lainnya'],
                    $data['min_konstruksi_dalam_pekerjaan'],
                    $data['min_aset_lain_lain'],
                    $data['min_jumlah_penyesuaian'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                $collectDatas->sum('plus_aset_tetap_tanah'),
                $collectDatas->sum('plus_aset_tetap_peralatan_mesin'),
                $collectDatas->sum('plus_aset_tetap_gedung_bangunan'),
                $collectDatas->sum('plus_aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('plus_aset_tetap_lainnya'),
                $collectDatas->sum('plus_konstruksi_dalam_pekerjaan'),
                $collectDatas->sum('plus_aset_lain_lain'),
                $collectDatas->sum('plus_jumlah_penyesuaian'),
                $collectDatas->sum('min_aset_tetap_tanah'),
                $collectDatas->sum('min_aset_tetap_peralatan_mesin'),
                $collectDatas->sum('min_aset_tetap_gedung_bangunan'),
                $collectDatas->sum('min_aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('min_aset_tetap_lainnya'),
                $collectDatas->sum('min_konstruksi_dalam_pekerjaan'),
                $collectDatas->sum('min_aset_lain_lain'),
                $collectDatas->sum('min_jumlah_penyesuaian'),
            ];
        } else if ($this->params['type'] == 'atribusi') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance']['name'] ?? null,
                    $data['uraian_pekerjaan'] ?? null,
                    // $data['kode_rekening_pegawai']['fullcode'] ?? null,
                    // $data['kode_rekening_pegawai']['name'] ?? null,
                    // $data['bel_peg_belanja_last_year'],
                    // $data['bel_peg_hutang_last_year'],
                    // $data['bel_peg_jumlah'],
                    $data['kode_rekening_barjas']['fullcode'] ?? null,
                    $data['kode_rekening_barjas']['name'] ?? null,
                    $data['bel_barjas_belanja'],
                    $data['bel_barjas_hutang'],
                    $data['bel_barjas_jumlah'],
                    $data['kode_rekening_modal']['fullcode'] ?? null,
                    $data['kode_rekening_modal']['name'] ?? null,
                    $data['bel_modal_belanja'],
                    $data['bel_modal_hutang'],
                    $data['bel_modal_jumlah'],
                    $data['ket_no_kontrak_pegawai_barang_jasa'],
                    $data['ket_no_sp2d_pegawai_barang_jasa'],
                    $data['atri_aset_tetap_tanah'],
                    $data['atri_aset_tetap_peralatan_mesin'],
                    $data['atri_aset_tetap_gedung_bangunan'],
                    $data['atri_aset_tetap_jalan_jaringan_irigasi'],
                    $data['atri_aset_tetap_tetap_lainnya'],
                    $data['atri_konstruksi_dalam_pekerjaan'],
                    $data['atri_aset_lain_lain'],
                    // $data['atri_ket_no_kontrak_sp2d'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                $collectDatas->sum('bel_barjas_belanja'),
                $collectDatas->sum('bel_barjas_hutang'),
                $collectDatas->sum('bel_barjas_jumlah'),
                '',
                '',
                $collectDatas->sum('bel_modal_belanja'),
                $collectDatas->sum('bel_modal_hutang'),
                $collectDatas->sum('bel_modal_jumlah'),
                '',
                '',
                $collectDatas->sum('atri_aset_tetap_tanah'),
                $collectDatas->sum('atri_aset_tetap_peralatan_mesin'),
                $collectDatas->sum('atri_aset_tetap_gedung_bangunan'),
                $collectDatas->sum('atri_aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('atri_aset_tetap_tetap_lainnya'),
                $collectDatas->sum('atri_konstruksi_dalam_pekerjaan'),
                $collectDatas->sum('atri_aset_lain_lain'),
                // '',
            ];
        } else if ($this->params['type'] == 'mutasi_aset') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['from_instance_name'],
                    $data['to_instance_name'],
                    $data['kelompok_aset'],
                    $data['nama_barang'],
                    $data['tahun_perolehan'],
                    $data['nilai_perolehan'],
                    $data['akumulasi_penyusutan'],
                    $data['bast_number'],
                    Carbon::parse($data['bast_date'])->isoFormat('D MMMM Y'),
                    $data['plus_aset_tetap_tanah'],
                    $data['plus_aset_tetap_peralatan_mesin'],
                    $data['plus_aset_tetap_gedung_bangunan'],
                    $data['plus_aset_tetap_jalan_jaringan_irigasi'],
                    $data['plus_aset_tetap_lainnya'],
                    $data['plus_kdp'],
                    $data['plus_aset_lainnya'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                '',
                $collectDatas->sum('nilai_perolehan'),
                $collectDatas->sum('akumulasi_penyusutan'),
                '',
                '',
                $collectDatas->sum('plus_aset_tetap_tanah'),
                $collectDatas->sum('plus_aset_tetap_peralatan_mesin'),
                $collectDatas->sum('plus_aset_tetap_gedung_bangunan'),
                $collectDatas->sum('plus_aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('plus_aset_tetap_lainnya'),
                $collectDatas->sum('plus_kdp'),
                $collectDatas->sum('plus_aset_lainnya'),
            ];
        } else if ($this->params['type'] == 'pekerjaan_kontrak') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance_name'],
                    $data['kode_rekening_fullcode'],
                    $data['kode_rekening_uraian'],
                    $data['nama_kegiatan_paket'],
                    $data['pelaksana_pekerjaan'],
                    $data['no_kontrak'],
                    $data['periode_kontrak'],
                    $data['nilai_belanja_kontrak'],
                    $data['payment_1_sp2d'],
                    Carbon::parse($data['payment_1_tanggal'])->isoFormat('D MMMM Y'),
                    $data['payment_1_jumlah'],
                    $data['payment_2_sp2d'],
                    Carbon::parse($data['payment_2_tanggal'])->isoFormat('D MMMM Y'),
                    $data['payment_2_jumlah'],
                    $data['payment_3_sp2d'],
                    Carbon::parse($data['payment_3_tanggal'])->isoFormat('D MMMM Y'),
                    $data['payment_3_jumlah'],
                    $data['payment_4_sp2d'],
                    Carbon::parse($data['payment_4_tanggal'])->isoFormat('D MMMM Y'),
                    $data['payment_4_jumlah'],
                    $data['jumlah_pembayaran_sd_desember'],
                    $data['kewajiban_tidak_terbayar_sd_desember'],
                    $data['tanggal_berita_acara'],
                    $data['tanggal_surat_pengakuan_hutang'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                '',
                $collectDatas->sum('nilai_belanja_kontrak'),
                '',
                '',
                $collectDatas->sum('payment_1_jumlah'),
                '',
                '',
                $collectDatas->sum('payment_2_jumlah'),
                '',
                '',
                $collectDatas->sum('payment_3_jumlah'),
                '',
                '',
                $collectDatas->sum('payment_4_jumlah'),
                $collectDatas->sum('jumlah_pembayaran_sd_desember'),
                $collectDatas->sum('kewajiban_tidak_terbayar_sd_desember'),
                '',
                '',
            ];
        } else if ($this->params['type'] == 'hibah_masuk') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance_name'],
                    $data['penerima_hibah'],
                    $data['pemberi_hibah'],
                    $data['kode_rekening_fullcode'],
                    $data['kode_rekening_name'],
                    $data['nama_barang'],
                    $data['nilai'],
                    $data['nomor_berita_acara'],
                    Carbon::parse($data['tanggal_berita_acara'])->isoFormat('D MMMM Y'),
                    $data['persediaan'],
                    $data['aset_tetap_tanah'],
                    $data['aset_tetap_peralatan_mesin'],
                    $data['aset_tetap_gedung_bangunan'],
                    $data['aset_tetap_jalan_jaringan_irigasi'],
                    $data['aset_tetap_lainnya'],
                    $data['konstruksi_dalam_pekerjaan'],
                    $data['aset_lainnya'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                $collectDatas->sum('nilai'),
                '',
                '',
                '',
                $collectDatas->sum('persediaan'),
                $collectDatas->sum('aset_tetap_tanah'),
                $collectDatas->sum('aset_tetap_peralatan_mesin'),
                $collectDatas->sum('aset_tetap_gedung_bangunan'),
                $collectDatas->sum('aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('aset_tetap_lainnya'),
                $collectDatas->sum('konstruksi_dalam_pekerjaan'),
                $collectDatas->sum('aset_lainnya'),
            ];
        } else if ($this->params['type'] == 'hibah_keluar') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance_name'],
                    $data['penerima_hibah'],
                    $data['pemberi_hibah'],
                    $data['kode_rekening_fullcode'],
                    $data['kode_rekening_name'],
                    $data['nama_barang'],
                    $data['nilai'],
                    $data['nomor_berita_acara'],
                    Carbon::parse($data['tanggal_berita_acara'])->isoFormat('D MMMM Y'),
                    $data['persediaan'],
                    $data['aset_tetap_tanah'],
                    $data['aset_tetap_peralatan_mesin'],
                    $data['aset_tetap_gedung_bangunan'],
                    $data['aset_tetap_jalan_jaringan_irigasi'],
                    $data['aset_tetap_lainnya'],
                    $data['konstruksi_dalam_pekerjaan'],
                    $data['aset_lainnya'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                $collectDatas->sum('nilai'),
                '',
                '',
                '',
                $collectDatas->sum('persediaan'),
                $collectDatas->sum('aset_tetap_tanah'),
                $collectDatas->sum('aset_tetap_peralatan_mesin'),
                $collectDatas->sum('aset_tetap_gedung_bangunan'),
                $collectDatas->sum('aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('aset_tetap_lainnya'),
                $collectDatas->sum('konstruksi_dalam_pekerjaan'),
                $collectDatas->sum('aset_lainnya'),
            ];
        } else if ($this->params['type'] == 'penilaian_aset') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance_name'],
                    $data['kelompok_barang_aset'],
                    $data['nama_barang'],
                    $data['tahun_perolehan'],
                    $data['metode_perolehan'],
                    $data['nilai_awal_aset'],
                    $data['hasil_penilaian'],
                    $data['nomor_berita_acara'],
                    Carbon::parse($data['tanggal_berita_acara'])->isoFormat('D MMMM Y'),
                    $data['keterangan'],
                    $data['persediaan'],
                    $data['aset_tetap_tanah'],
                    $data['aset_tetap_peralatan_mesin'],
                    $data['aset_tetap_gedung_bangunan'],
                    $data['aset_tetap_jalan_jaringan_irigasi'],
                    $data['aset_tetap_lainnya'],
                    $data['konstruksi_dalam_pekerjaan'],
                    $data['aset_lainnya'],
                    $data['jumlah_penyesuaian'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                '',
                $collectDatas->sum('nilai_awal_aset'),
                $collectDatas->sum('hasil_penilaian'),
                '',
                '',
                '',
                $collectDatas->sum('persediaan'),
                $collectDatas->sum('aset_tetap_tanah'),
                $collectDatas->sum('aset_tetap_peralatan_mesin'),
                $collectDatas->sum('aset_tetap_gedung_bangunan'),
                $collectDatas->sum('aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('aset_tetap_lainnya'),
                $collectDatas->sum('konstruksi_dalam_pekerjaan'),
                $collectDatas->sum('aset_lainnya'),
                $collectDatas->sum('jumlah_penyesuaian'),
            ];
        } else if ($this->params['type'] == 'penghapusan_aset') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance_name'],
                    $data['kelompok_barang_aset'],
                    $data['nama_barang'],
                    $data['tahun_perolehan'],
                    $data['nilai_perolehan'],
                    $data['akumulasi_penyusutan'],
                    $data['nomor_berita_acara'],
                    Carbon::parse($data['tanggal_berita_acara'])->isoFormat('D MMMM Y'),
                    $data['keterangan'],
                    $data['persediaan'],
                    $data['aset_tetap_tanah'],
                    $data['aset_tetap_peralatan_mesin'],
                    $data['aset_tetap_gedung_bangunan'],
                    $data['aset_tetap_jalan_jaringan_irigasi'],
                    $data['aset_tetap_lainnya'],
                    $data['konstruksi_dalam_pekerjaan'],
                    $data['aset_lainnya'],
                    $data['jumlah_penyesuaian'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                $collectDatas->sum('nilai_perolehan'),
                $collectDatas->sum('akumulasi_penyusutan'),
                '',
                '',
                '',
                $collectDatas->sum('persediaan'),
                $collectDatas->sum('aset_tetap_tanah'),
                $collectDatas->sum('aset_tetap_peralatan_mesin'),
                $collectDatas->sum('aset_tetap_gedung_bangunan'),
                $collectDatas->sum('aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('aset_tetap_lainnya'),
                $collectDatas->sum('konstruksi_dalam_pekerjaan'),
                $collectDatas->sum('aset_lainnya'),
                $collectDatas->sum('jumlah_penyesuaian'),
            ];
        } else if ($this->params['type'] == 'penjualan_aset') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance_name'],
                    $data['kelompok_barang_aset'],
                    $data['nama_barang'],
                    $data['tahun_perolehan'],
                    $data['harga_perolehan'],
                    $data['akumulasi_penyusutan'],
                    $data['harga_jual'],
                    $data['surplus'],
                    $data['nomor_berita_acara'],
                    Carbon::parse($data['tanggal_berita_acara'])->isoFormat('D MMMM Y'),
                    $data['keterangan'],
                    $data['persediaan'],
                    $data['aset_tetap_tanah'],
                    $data['aset_tetap_peralatan_mesin'],
                    $data['aset_tetap_gedung_bangunan'],
                    $data['aset_tetap_jalan_jaringan_irigasi'],
                    $data['aset_tetap_lainnya'],
                    $data['konstruksi_dalam_pekerjaan'],
                    $data['aset_lainnya'],
                    $data['jumlah_penyesuaian'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'TOTAL',
                '',
                '',
                '',
                $collectDatas->sum('harga_perolehan'),
                $collectDatas->sum('akumulasi_penyusutan'),
                $collectDatas->sum('harga_jual'),
                $collectDatas->sum('surplus'),
                '',
                '',
                '',
                $collectDatas->sum('persediaan'),
                $collectDatas->sum('aset_tetap_tanah'),
                $collectDatas->sum('aset_tetap_peralatan_mesin'),
                $collectDatas->sum('aset_tetap_gedung_bangunan'),
                $collectDatas->sum('aset_tetap_jalan_jaringan_irigasi'),
                $collectDatas->sum('aset_tetap_lainnya'),
                $collectDatas->sum('konstruksi_dalam_pekerjaan'),
                $collectDatas->sum('aset_lainnya'),
                $collectDatas->sum('jumlah_penyesuaian'),
            ];
        }
        return $datas;
    }
}
