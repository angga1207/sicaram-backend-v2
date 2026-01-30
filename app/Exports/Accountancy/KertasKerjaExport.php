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

class KertasKerjaExport implements FromCollection, WithHeadings, WithColumnFormatting, WithStyles, WithColumnWidths
{
    public $datas, $params;
    function __construct($datas, $params)
    {
        $this->datas = $datas;
        $this->params = $params;
    }

    public function headings(): array
    {
        if ($this->params['category'] == 'kibs') {
            return [
                'Perangkat Daerah',
                'Saldo Awal',

                'Realisasi Belanja',
                'Utang Kegiatan',
                'Atribusi',
                'Reklasifikasi dari Barang Habis Pakai',
                'Reklasifikasi dari Pemeliharaan',
                'Reklasifikasi dari Jasa',
                'Reklasifikasi dari KIB A',
                'Reklasifikasi dari KIB B',
                'Reklasifikasi dari KIB C',
                'Reklasifikasi dari KIB D',
                'Reklasifikasi dari KIB E',
                'Reklasifikasi dari KDP',
                'Reklasifikasi dari Aset Lain-lain',
                'Hibah Masuk',
                'Penilaian',
                'Mutasi Antar OPD',
                'Total Penambahan',

                'Pembayaran Utang',
                'Reklasifikasi ke Beban Persediaan',
                'Reklasifikasi ke Beban Jasa',
                'Reklasifikasi ke Beban Pemeliharaan',
                'Reklasifikasi ke Beban Hibah/Bansos',
                'Reklasifikasi ke KIB A',
                'Reklasifikasi ke KIB B',
                'Reklasifikasi ke KIB C',
                'Reklasifikasi ke KIB D',
                'Reklasifikasi ke KIB E',
                'Reklasifikasi ke KDP',
                'Reklasifikasi ke Aset Lain-lain',
                'Penghapusan/Penjualan',
                'Mutasi Antar OPD',
                'TPTGR',
                'Total Pengurangan',

                'Saldo Akhir',
            ];
        } else if ($this->params['category'] == 'belanja_bayar_dimuka') {
            return [
                'Perangkat Daerah',
                'Kode Rekening',
                'Nama Rekening',
                'Uraian',
                'Nomor Perjanjian',
                'Tanggal Perjanjian',
                'Rekanan',
                'Jangka Waktu (Bulan)',
                'Tanggal Mulai',
                'Tanggal Berakhir',
                'Nilai (Rp)',
                'Sudah Jatuh Tempo / Digunakan (Rp)',
                'Belum Jatuh Tempo / Digunakan (Rp)',
            ];
        } else if ($this->params['category'] == 'persediaan') {
            if ($this->params['type'] == 'rekap') {
                return [
                    'Uraian',
                    'Saldo Awal',
                    'Realisasi LRA',
                    'Utang Belanja',
                    'Perolehan dari Hibah',
                    'Saldo Akhir',
                    'Beban',
                ];
            } else if ($this->params['type'] == 'barang_habis_pakai') {
                return [
                    'Perangkat Daerah',
                    'Nama Persediaan',
                    'Saldo Awal',
                    'Kode Rekening',
                    'Nama Rekening',
                    'Realisasi LRA',
                    'Utang Belanja',
                    'Perolehan dari Hibah',
                    'Saldo Akhir',
                    'Beban Persediaan',
                ];
            } else if ($this->params['type'] == 'untuk_dijual') {
                return [
                    'Perangkat Daerah',
                    'Nama Persediaan',
                    'Saldo Awal',
                    'Kode Rekening',
                    'Nama Rekening',
                    'Realisasi LRA',
                    'Utang Belanja',
                    'Perolehan dari Hibah',
                    'Saldo Akhir',
                    'Beban Hibah',
                ];
            }
        } else if ($this->params['category'] == 'hutang_belanja') {
            if ($this->params['type'] == 'pembayaran_utang') {
                return [
                    'Perangkat Daerah',
                    'Kode Rekening',
                    'Nama Rekening',
                    'Nama Kegiatan / Uraian Paket Pekerjaan',
                    'Pelaksana Pekerjaan',
                    'Nomor Kontrak',
                    'Tahun Kontrak',
                    'Kewajiban Tidak Terbayar',
                    'Nomor SP2D Pembayaran 1',
                    'Tanggal Pembayaran 1',
                    'Jumlah Pembayaran 1',
                    'Nomor SP2D Pembayaran 2',
                    'Tanggal Pembayaran 2',
                    'Jumlah Pembayaran 2',
                    'Jumlah Pembayaran Utang',
                    'Sisa Utang',
                    'Pembayaran Utang Pegawai',
                    'Pembayaran Utang Persediaan',
                    'Pembayaran Utang Perjadin',
                    'Pembayaran Utang Jasa',
                    'Pembayaran Utang Pemeliharaan',
                    'Pembayaran Utang Uang/Jasa Diserahkan',
                    'Pembayaran Utang Hibah',
                    'Pembayaran Utang Aset Tetap Tanah',
                    'Pembayaran Utang Aset Tetap Peralatan dan Mesin',
                    'Pembayaran Utang Aset Tetap Gedung dan Bangunan',
                    'Pembayaran Utang Aset Tetap Jalan, Irigasi dan Jaringan',
                    'Pembayaran Utang Aset Tetap Aset Tetap Lainnya',
                    'Pembayaran Utang Konstruksi Dalam Pengerjaan',
                    'Pembayaran Utang Aset Lain-lain',
                    'Total Penyesuaian'
                ];
            } else if ($this->params['type'] == 'utang_baru') {
                return [
                    'Perangkat Daerah',
                    'Kode Rekening',
                    'Nama Rekening',
                    'Nama Kegiatan / Uraian Paket Pekerjaan',
                    'Pelaksana Pekerjaan',
                    'Nomor Kontrak',
                    'Tahun Kontrak',
                    'Nilai Belanja / Kontrak',
                    'Nomor SP2D Pembayaran 1',
                    'Tanggal Pembayaran 1',
                    'Jumlah Pembayaran 1',
                    'Nomor SP2D Pembayaran 2',
                    'Tanggal Pembayaran 2',
                    'Jumlah Pembayaran 2',
                    'Nomor SP2D Pembayaran 3',
                    'Tanggal Pembayaran 3',
                    'Jumlah Pembayaran 3',
                    'Nomor SP2D Pembayaran 4',
                    'Tanggal Pembayaran 4',
                    'Jumlah Pembayaran 4',
                    'Jumlah Pembayaran',
                    'Utang Baru',
                    'Utang Pegawai',
                    'Utang Persediaan',
                    'Utang Perjadin',
                    'Utang Jasa',
                    'Utang Pemeliharaan',
                    'Utang Uang/Jasa Diserahkan',
                    'Utang Hibah',
                    'Utang Aset Tetap Tanah',
                    'Utang Aset Tetap Peralatan dan Mesin',
                    'Utang Aset Tetap Gedung dan Bangunan',
                    'Utang Aset Tetap Jalan, Irigasi dan Jaringan',
                    'Utang Aset Tetap Aset Tetap Lainnya',
                    'Utang Konstruksi Dalam Pengerjaan',
                    'Utang Aset Lain-lain',
                    'Total Utang Baru'
                ];
            } else if ($this->params['type'] == 'rekap_utang_belanja') {
                return [
                    'Perangkat Daerah',
                    'Kode Rekening',
                    'Nama Rekening',
                    'Nama Kegiatan / Uraian Paket Pekerjaan',
                    'Pelaksana Pekerjaan',
                    'Nomor Kontrak',
                    'Tahun Kontrak',
                    'Nilai Belanja / Kontrak',
                    'Kewajiban Tidak Terbayar Tahun Lalu',
                    'Nomor SP2D Pembayaran 1',
                    'Tanggal Pembayaran 1',
                    'Jumlah Pembayaran 1',
                    'Nomor SP2D Pembayaran 2',
                    'Tanggal Pembayaran 2',
                    'Jumlah Pembayaran 2',
                    'Nomor SP2D Pembayaran 3',
                    'Tanggal Pembayaran 3',
                    'Jumlah Pembayaran 3',
                    'Jumlah Pembayaran Utang',
                    'Kewajiban Tidak Terbayar',
                    'Utang Baru',
                    'Utang Pegawai',
                    'Utang Persediaan',
                    'Utang Perjadin',
                    'Utang Jasa',
                    'Utang Pemeliharaan',
                    'Utang Uang/Jasa Diserahkan',
                    'Utang Hibah',
                    'Utang Aset Tetap Tanah',
                    'Utang Aset Tetap Peralatan dan Mesin',
                    'Utang Aset Tetap Gedung dan Bangunan',
                    'Utang Aset Tetap Jalan, Irigasi dan Jaringan',
                    'Utang Aset Tetap Aset Tetap Lainnya',
                    'Utang Konstruksi Dalam Pengerjaan',
                    'Utang Aset Lain-lain',
                    'Total Utang',
                    'Jenis'
                ];
            }
        } else if ($this->params['category'] == 'beban_lo') {
            if (in_array($this->params['type'], ['pegawai', 'persediaan', 'perjadin', 'jasa', 'pemeliharaan', 'uang_jasa_diserahkan', 'hibah', 'subsidi'])) {
                return [
                    'Perangkat Daerah',
                    'Kode Rekening',
                    'Nama Rekening',
                    'Realisasi Belanja',
                    'Saldo Awal',
                    'Belanja Dibayar Dimuka Akhir',
                    'Hutang',
                    'Hibah Masuk',
                    'Reklas Tambah dari Rekening Lain/BOS',
                    'Reklas Tambah dari Modal',
                    'Jukor Tambah',
                    'Jumlah Tambah',
                    'Saldo Akhir',
                    'Beban Tahun Lalu Dibayar Tahun Ini',
                    'Belanja Dibayar Dimuka Awal',
                    'Pembayaran Utang',
                    'Reklas Kurang ke Rekening Lain',
                    'Reklas Kurang ke Aset',
                    'Atribusi / Kapitalisasi Belanja Modal',
                    'Jukor Kurang',
                    'Jumlah Kurang',
                    'Beban LO',
                ];
            }
        } else if ($this->params['category'] == 'pendapatan_lo') {
            if (in_array($this->params['type'], ['rekap_pendapatan_lo'])) {
                return [
                    'Jenis Piutang',
                    'Saldo Awal',
                    'Saldo Akhir',
                    'Piutang Bruto',
                    'Penyisihan Piutang',
                    'Beban Penyisihan',
                ];
            } else if (in_array($this->params['type'], ['piutang'])) {
                return [
                    'Perangkat Daerah',
                    'Kode Rekening',
                    'Jenis Piutang',
                    'Saldo Awal',
                    'Koreksi Saldo Awal',
                    'Penghapusan Piutang',
                    'Mutasi Debet',
                    'Mutasi Kredit',
                    'Saldo Akhir',
                    'Lancar < 1 Tahun',
                    'Kurang Lancar 1-3 Tahun',
                    'Diragukan 3-5 Tahun',
                    'Macet > 5 Tahun',
                    'Piutang Bruto',
                    'Jenis',
                ];
            } else if (in_array($this->params['type'], ['penyisihan'])) {
                return [
                    'Perangkat Daerah',
                    'Kode Rekening',
                    'Jenis Piutang',
                    'Piutang Bruto',
                    'Lancar < 1 Tahun',
                    'Kurang Lancar 1-3 Tahun',
                    'Diragukan 3-5 Tahun',
                    'Macet > 5 Tahun',
                    'Jumlah Penyisihan',
                    'Jenis',
                ];
            } else if (in_array($this->params['type'], ['beban'])) {
                return [
                    'Perangkat Daerah',
                    'Kode Rekening',
                    'Jenis Piutang',
                    'Jumlah Penyisihan',
                    'Jumlah Penyisihan Tahun Lalu',
                    'Koreksi Penyisihan',
                    'Beban Penyisihan',
                    'Jenis',
                ];
            } else if (in_array($this->params['type'], ['pdd'])) {
                return [
                    'Perangkat Daerah',
                    'Kode Rekening',
                    'Uraian',
                    'Pendapatan Diterima Dimuka Awal',
                    'Mutasi Tambah',
                    'Mutasi Kurang',
                    'Pendapatan Diterima Dimuka Akhir',
                    'Jenis',
                ];
            } else if (in_array($this->params['type'], ['lota'])) {
                return [
                    'Perangkat Daerah',
                    'Uraian',
                    'Anggaran Perubahan',
                    'Laporan Realisasi Anggaran',
                    '% LRA',
                    'Piutang Awal',
                    'Piutang Akhir',
                    'PDD Awal Tahun (Kredit)',
                    'PDD Akhir Tahun (Debet)',
                    'Laporan Operasional',
                    '% LO',
                    'Penambahan / Pengurangan LO',
                    'Reklas & Koreksi LO',
                    'Perbedaan LO & LRA',
                ];
            }
        } else if ($this->params['category'] == 'pengembalian-belanja') {
            if ($this->params['type'] == 'pengembalian-belanja') {
                return [
                    'No',
                    'Perangkat Daerah',
                    'Tanggal Setor',
                    'Kode Rekening',
                    'Nama Rekening',
                    'Uraian',
                    'Jenis SPM',
                    'Jumlah (Rp)',
                ];
            }
        }

        return [];
    }

    public function columnFormats(): array
    {
        if ($this->params['category'] == 'kibs') {
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
        } else if ($this->params['category'] == 'belanja_bayar_dimuka') {
            return [
                'A' => NumberFormat::FORMAT_TEXT,
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'G' => NumberFormat::FORMAT_TEXT,
                'H' => NumberFormat::FORMAT_NUMBER,
                'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'J' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'K' => NumberFormat::FORMAT_CURRENCY_ID,
                'L' => NumberFormat::FORMAT_CURRENCY_ID,
                'M' => NumberFormat::FORMAT_CURRENCY_ID,
            ];
        } else if ($this->params['category'] == 'persediaan') {
            if ($this->params['type'] == 'rekap') {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_CURRENCY_ID,
                    'C' => NumberFormat::FORMAT_CURRENCY_ID,
                    'D' => NumberFormat::FORMAT_CURRENCY_ID,
                    'E' => NumberFormat::FORMAT_CURRENCY_ID,
                    'F' => NumberFormat::FORMAT_CURRENCY_ID,
                    'G' => NumberFormat::FORMAT_CURRENCY_ID,
                ];
            } else if ($this->params['type'] == 'barang_habis_pakai') {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_TEXT,
                    'C' => NumberFormat::FORMAT_CURRENCY_ID,
                    'D' => NumberFormat::FORMAT_TEXT,
                    'E' => NumberFormat::FORMAT_TEXT,
                    'F' => NumberFormat::FORMAT_CURRENCY_ID,
                    'G' => NumberFormat::FORMAT_CURRENCY_ID,
                    'H' => NumberFormat::FORMAT_CURRENCY_ID,
                    'I' => NumberFormat::FORMAT_CURRENCY_ID,
                    'J' => NumberFormat::FORMAT_CURRENCY_ID,
                ];
            } else if ($this->params['type'] == 'untuk_dijual') {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_TEXT,
                    'C' => NumberFormat::FORMAT_CURRENCY_ID,
                    'D' => NumberFormat::FORMAT_TEXT,
                    'E' => NumberFormat::FORMAT_TEXT,
                    'F' => NumberFormat::FORMAT_CURRENCY_ID,
                    'G' => NumberFormat::FORMAT_CURRENCY_ID,
                    'H' => NumberFormat::FORMAT_CURRENCY_ID,
                    'I' => NumberFormat::FORMAT_CURRENCY_ID,
                    'J' => NumberFormat::FORMAT_CURRENCY_ID,
                ];
            }
        } else if ($this->params['category'] == 'hutang_belanja') {
            if ($this->params['type'] == 'pembayaran_utang') {
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
                    'J' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                    'K' => NumberFormat::FORMAT_CURRENCY_ID,
                    'L' => NumberFormat::FORMAT_TEXT,
                    'M' => NumberFormat::FORMAT_DATE_DDMMYYYY,
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
            } else if ($this->params['type'] == 'utang_baru') {
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
                    'J' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                    'K' => NumberFormat::FORMAT_CURRENCY_ID,
                    'L' => NumberFormat::FORMAT_TEXT,
                    'M' => NumberFormat::FORMAT_DATE_DDMMYYYY,
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
                    'AK' => NumberFormat::FORMAT_CURRENCY_ID,
                ];
            } else if ($this->params['type'] == 'rekap_utang_belanja') {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_TEXT,
                    'C' => NumberFormat::FORMAT_TEXT,
                    'D' => NumberFormat::FORMAT_TEXT,
                    'E' => NumberFormat::FORMAT_TEXT,
                    'F' => NumberFormat::FORMAT_TEXT,
                    'G' => NumberFormat::FORMAT_TEXT,
                    'H' => NumberFormat::FORMAT_CURRENCY_ID,
                    'I' => NumberFormat::FORMAT_CURRENCY_ID,
                    'J' => NumberFormat::FORMAT_TEXT,
                    'K' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                    'L' => NumberFormat::FORMAT_CURRENCY_ID,
                    'M' => NumberFormat::FORMAT_TEXT,
                    'N' => NumberFormat::FORMAT_DATE_DDMMYYYY,
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
                    'AK' => NumberFormat::FORMAT_CURRENCY_ID,
                ];
            }
        } else if ($this->params['category'] == 'beban_lo') {
            if (in_array($this->params['type'], ['pegawai', 'persediaan', 'perjadin', 'jasa', 'pemeliharaan', 'uang_jasa_diserahkan', 'hibah', 'subsidi'])) {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_TEXT,
                    'C' => NumberFormat::FORMAT_TEXT,
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
                ];
            }
        } else if ($this->params['category'] == 'pendapatan_lo') {
            if (in_array($this->params['type'], ['rekap_pendapatan_lo'])) {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_CURRENCY_ID,
                    'C' => NumberFormat::FORMAT_CURRENCY_ID,
                    'D' => NumberFormat::FORMAT_CURRENCY_ID,
                    'E' => NumberFormat::FORMAT_CURRENCY_ID,
                    'F' => NumberFormat::FORMAT_CURRENCY_ID,
                ];
            } else if (in_array($this->params['type'], ['piutang'])) {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_TEXT,
                    'C' => NumberFormat::FORMAT_TEXT,
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
                    'O' => NumberFormat::FORMAT_CURRENCY_ID,
                    'P' => NumberFormat::FORMAT_TEXT,
                ];
            } else if (in_array($this->params['type'], ['penyisihan'])) {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_TEXT,
                    'C' => NumberFormat::FORMAT_TEXT,
                    'D' => NumberFormat::FORMAT_CURRENCY_ID,
                    'E' => NumberFormat::FORMAT_CURRENCY_ID,
                    'F' => NumberFormat::FORMAT_CURRENCY_ID,
                    'G' => NumberFormat::FORMAT_CURRENCY_ID,
                    'H' => NumberFormat::FORMAT_CURRENCY_ID,
                    'I' => NumberFormat::FORMAT_CURRENCY_ID,
                    'J' => NumberFormat::FORMAT_TEXT,
                ];
            } else if (in_array($this->params['type'], ['beban'])) {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_TEXT,
                    'C' => NumberFormat::FORMAT_TEXT,
                    'D' => NumberFormat::FORMAT_CURRENCY_ID,
                    'E' => NumberFormat::FORMAT_CURRENCY_ID,
                    'F' => NumberFormat::FORMAT_CURRENCY_ID,
                    'H' => NumberFormat::FORMAT_CURRENCY_ID,
                    'I' => NumberFormat::FORMAT_TEXT,
                ];
            } else if (in_array($this->params['type'], ['pdd'])) {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_TEXT,
                    'C' => NumberFormat::FORMAT_TEXT,
                    'D' => NumberFormat::FORMAT_CURRENCY_ID,
                    'E' => NumberFormat::FORMAT_CURRENCY_ID,
                    'F' => NumberFormat::FORMAT_CURRENCY_ID,
                    'G' => NumberFormat::FORMAT_CURRENCY_ID,
                    'H' => NumberFormat::FORMAT_TEXT,
                ];
            } else if (in_array($this->params['type'], ['lota'])) {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_TEXT,
                    'C' => NumberFormat::FORMAT_CURRENCY_ID,
                    'D' => NumberFormat::FORMAT_CURRENCY_ID,
                    'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
                    'F' => NumberFormat::FORMAT_CURRENCY_ID,
                    'G' => NumberFormat::FORMAT_CURRENCY_ID,
                    'H' => NumberFormat::FORMAT_CURRENCY_ID,
                    'I' => NumberFormat::FORMAT_CURRENCY_ID,
                    'J' => NumberFormat::FORMAT_CURRENCY_ID,
                    'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
                    'L' => NumberFormat::FORMAT_CURRENCY_ID,
                    'M' => NumberFormat::FORMAT_CURRENCY_ID,
                    'N' => NumberFormat::FORMAT_CURRENCY_ID,
                ];
            }
        } else if ($this->params['category'] == 'pengembalian-belanja') {
            if ($this->params['type'] == 'pengembalian-belanja') {
                return [
                    'A' => NumberFormat::FORMAT_TEXT,
                    'B' => NumberFormat::FORMAT_TEXT,
                    'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                    'D' => NumberFormat::FORMAT_TEXT,
                    'E' => NumberFormat::FORMAT_TEXT,
                    'F' => NumberFormat::FORMAT_TEXT,
                    'G' => NumberFormat::FORMAT_TEXT,
                    'H' => NumberFormat::FORMAT_CURRENCY_ID,
                ];
            }
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
        if ($this->params['category'] == 'kibs') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance_name'],
                    $data['saldo_awal'],
                    $data['plus_realisasi_belanja'],
                    $data['plus_hutang_kegiatan'],
                    $data['plus_atribusi'],
                    $data['plus_reklasifikasi_barang_habis_pakai'],
                    $data['plus_reklasifikasi_pemeliharaan'],
                    $data['plus_reklasifikasi_jasa'],
                    $data['plus_reklasifikasi_kib_a'],
                    $data['plus_reklasifikasi_kib_b'],
                    $data['plus_reklasifikasi_kib_c'],
                    $data['plus_reklasifikasi_kib_d'],
                    $data['plus_reklasifikasi_kib_e'],
                    $data['plus_reklasifikasi_kdp'],
                    $data['plus_reklasifikasi_aset_lain_lain'],
                    $data['plus_hibah_masuk'],
                    $data['plus_penilaian'],
                    $data['plus_mutasi_antar_opd'],
                    $data['plus_total'],
                    $data['min_pembayaran_utang'],
                    $data['min_reklasifikasi_beban_persediaan'],
                    $data['min_reklasifikasi_beban_jasa'],
                    $data['min_reklasifikasi_beban_pemeliharaan'],
                    $data['min_reklasifikasi_beban_hibah'],
                    $data['min_reklasifikasi_beban_kib_a'],
                    $data['min_reklasifikasi_beban_kib_b'],
                    $data['min_reklasifikasi_beban_kib_c'],
                    $data['min_reklasifikasi_beban_kib_d'],
                    $data['min_reklasifikasi_beban_kib_e'],
                    $data['min_reklasifikasi_beban_kdp'],
                    $data['min_reklasifikasi_beban_aset_lain_lain'],
                    $data['min_penghapusan'],
                    $data['min_mutasi_antar_opd'],
                    $data['min_tptgr'],
                    $data['min_total'],
                    $data['saldo_akhir'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'Total',
                $collectDatas->sum('saldo_awal'),
                $collectDatas->sum('plus_realisasi_belanja'),
                $collectDatas->sum('plus_hutang_kegiatan'),
                $collectDatas->sum('plus_atribusi'),
                $collectDatas->sum('plus_reklasifikasi_barang_habis_pakai'),
                $collectDatas->sum('plus_reklasifikasi_pemeliharaan'),
                $collectDatas->sum('plus_reklasifikasi_jasa'),
                $collectDatas->sum('plus_reklasifikasi_kib_a'),
                $collectDatas->sum('plus_reklasifikasi_kib_b'),
                $collectDatas->sum('plus_reklasifikasi_kib_c'),
                $collectDatas->sum('plus_reklasifikasi_kib_d'),
                $collectDatas->sum('plus_reklasifikasi_kib_e'),
                $collectDatas->sum('plus_reklasifikasi_kdp'),
                $collectDatas->sum('plus_reklasifikasi_aset_lain_lain'),
                $collectDatas->sum('plus_hibah_masuk'),
                $collectDatas->sum('plus_penilaian'),
                $collectDatas->sum('plus_mutasi_antar_opd'),
                $collectDatas->sum('plus_total'),
                $collectDatas->sum('min_pembayaran_utang'),
                $collectDatas->sum('min_reklasifikasi_beban_persediaan'),
                $collectDatas->sum('min_reklasifikasi_beban_jasa'),
                $collectDatas->sum('min_reklasifikasi_beban_pemeliharaan'),
                $collectDatas->sum('min_reklasifikasi_beban_hibah'),
                $collectDatas->sum('min_reklasifikasi_beban_kib_a'),
                $collectDatas->sum('min_reklasifikasi_beban_kib_b'),
                $collectDatas->sum('min_reklasifikasi_beban_kib_c'),
                $collectDatas->sum('min_reklasifikasi_beban_kib_d'),
                $collectDatas->sum('min_reklasifikasi_beban_kib_e'),
                $collectDatas->sum('min_reklasifikasi_beban_kdp'),
                $collectDatas->sum('min_reklasifikasi_beban_aset_lain_lain'),
                $collectDatas->sum('min_penghapusan'),
                $collectDatas->sum('min_mutasi_antar_opd'),
                $collectDatas->sum('min_tptgr'),
                $collectDatas->sum('min_total'),
                $collectDatas->sum('saldo_akhir'),
            ];
        } else if ($this->params['category'] == 'belanja_bayar_dimuka') {
            $collectDatas = collect($this->datas);
            foreach ($collectDatas as $data) {
                $datas[] = [
                    $data['instance']['name'] ?? null,
                    $data['kode_rekening']['fullcode'] ?? null,
                    $data['kode_rekening']['name'] ?? null,
                    $data['uraian'],
                    $data['nomor_perjanjian'],
                    Carbon::parse($data['tanggal_perjanjian'])->isoFormat('D MMMM Y'),
                    $data['rekanan'],
                    $data['jangka_waktu'],
                    Carbon::parse($data['kontrak_date_start'])->isoFormat('D MMMM Y'),
                    Carbon::parse($data['kontrak_date_end'])->isoFormat('D MMMM Y'),
                    $data['kontrak_value'],
                    $data['sudah_jatuh_tempo'],
                    $data['belum_jatuh_tempo'],
                ];
            }
            $datas = collect($datas);
            // add total row
            $datas[] = [
                'Total',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $collectDatas->sum('kontrak_value'),
                $collectDatas->sum('sudah_jatuh_tempo'),
                $collectDatas->sum('belum_jatuh_tempo'),
            ];
        } else if ($this->params['category'] == 'persediaan') {
            $collectDatas = collect($this->datas);
            if ($this->params['type'] == 'rekap') {
                foreach ($collectDatas as $data) {
                    $datas[] = [
                        $data['uraian'],
                        $data['saldo_awal'],
                        $data['realisasi_lra'],
                        $data['hutang_belanja'],
                        $data['perolehan_hibah'],
                        $data['saldo_akhir'],
                        $data['beban'],
                    ];
                }
                $datas = collect($datas);
                // add total row
                $datas[] = [
                    'Total',
                    $collectDatas->sum('saldo_awal'),
                    $collectDatas->sum('realisasi_lra'),
                    $collectDatas->sum('hutang_belanja'),
                    $collectDatas->sum('perolehan_hibah'),
                    $collectDatas->sum('saldo_akhir'),
                    $collectDatas->sum('beban'),
                ];
            } else if ($this->params['type'] == 'barang_habis_pakai') {
                foreach ($collectDatas as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['nama_persediaan'],
                        $data['saldo_awal'],
                        $data['kode_rekening_fullcode'] ?? null,
                        $data['kode_rekening_name'] ?? null,
                        $data['realisasi_lra'],
                        $data['hutang_belanja'],
                        $data['perolehan_hibah'],
                        $data['saldo_akhir'],
                        $data['beban_persediaan'],
                    ];
                }
                $datas = collect($datas);
                // add total row
                $datas[] = [
                    'Total',
                    '',
                    $collectDatas->sum('saldo_awal'),
                    '',
                    '',
                    $collectDatas->sum('realisasi_lra'),
                    $collectDatas->sum('hutang_belanja'),
                    $collectDatas->sum('perolehan_hibah'),
                    $collectDatas->sum('saldo_akhir'),
                    $collectDatas->sum('beban_persediaan'),
                ];
            } else if ($this->params['type'] == 'untuk_dijual') {
                foreach ($collectDatas as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['nama_persediaan'],
                        $data['saldo_awal'],
                        $data['kode_rekening_fullcode'] ?? null,
                        $data['kode_rekening_name'] ?? null,
                        $data['realisasi_lra'],
                        $data['hutang_belanja'],
                        $data['perolehan_hibah'],
                        $data['saldo_akhir'],
                        $data['beban_hibah'],
                    ];
                }
                $datas = collect($datas);
                // add total row
                $datas[] = [
                    'Total',
                    '',
                    $collectDatas->sum('saldo_awal'),
                    '',
                    '',
                    $collectDatas->sum('realisasi_lra'),
                    $collectDatas->sum('hutang_belanja'),
                    $collectDatas->sum('perolehan_hibah'),
                    $collectDatas->sum('saldo_akhir'),
                    $collectDatas->sum('beban_hibah'),
                ];
            }
        } else if ($this->params['category'] == 'hutang_belanja') {
            $collectDatas = collect($this->datas);
            if ($this->params['type'] == 'pembayaran_utang') {
                foreach ($collectDatas as $data) {
                    $datas[] = [
                        $data['instance_name'],
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['nama_kegiatan'],
                        $data['pelaksana_pekerjaan'],
                        $data['nomor_kontrak'],
                        $data['tahun_kontrak'],
                        $data['kewajiban_tidak_terbayar_last_year'],
                        $data['p1_nomor_sp2d'],
                        $data['p1_tanggal'],
                        $data['p1_jumlah'],
                        $data['p2_nomor_sp2d'],
                        $data['p2_tanggal'],
                        $data['p2_jumlah'],
                        $data['jumlah_pembayaran_hutang'],
                        $data['sisa_hutang'],
                        $data['pegawai'],
                        $data['persediaan'],
                        $data['perjadin'],
                        $data['jasa'],
                        $data['pemeliharaan'],
                        $data['uang_jasa_diserahkan'],
                        $data['hibah'],
                        $data['aset_tetap_tanah'],
                        $data['aset_tetap_peralatan_mesin'],
                        $data['aset_tetap_gedung_bangunan'],
                        $data['aset_tetap_jalan_jaringan_irigasi'],
                        $data['aset_tetap_lainnya'],
                        $data['konstruksi_dalam_pekerjaan'],
                        $data['aset_lain_lain'],
                        $data['total_hutang'],
                    ];
                }
                $datas = collect($datas);
                // add total row
                $datas[] = [
                    'Total',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $collectDatas->sum('kewajiban_tidak_terbayar_last_year'),
                    '',
                    '',
                    $collectDatas->sum('p1_jumlah'),
                    '',
                    '',
                    $collectDatas->sum('p2_jumlah'),
                    $collectDatas->sum('jumlah_pembayaran_hutang'),
                    $collectDatas->sum('sisa_hutang'),
                    $collectDatas->sum('pegawai'),
                    $collectDatas->sum('persediaan'),
                    $collectDatas->sum('perjadin'),
                    $collectDatas->sum('jasa'),
                    $collectDatas->sum('pemeliharaan'),
                    $collectDatas->sum('uang_jasa_diserahkan'),
                    $collectDatas->sum('hibah'),
                    $collectDatas->sum('aset_tetap_tanah'),
                    $collectDatas->sum('aset_tetap_peralatan_mesin'),
                    $collectDatas->sum('aset_tetap_gedung_bangunan'),
                    $collectDatas->sum('aset_tetap_jalan_jaringan_irigasi'),
                    $collectDatas->sum('aset_tetap_lainnya'),
                    $collectDatas->sum('konstruksi_dalam_pekerjaan'),
                    $collectDatas->sum('aset_lain_lain'),
                    $collectDatas->sum('total_hutang'),
                ];
            } else if ($this->params['type'] == 'utang_baru') {
                foreach ($collectDatas as $data) {
                    $datas[] = [
                        $data['instance_name'],
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['nama_kegiatan'],
                        $data['pelaksana_pekerjaan'],
                        $data['nomor_kontrak'],
                        $data['tahun_kontrak'],
                        $data['nilai_kontrak'],
                        $data['p1_nomor_sp2d'],
                        $data['p1_tanggal'],
                        $data['p1_jumlah'],
                        $data['p2_nomor_sp2d'],
                        $data['p2_tanggal'],
                        $data['p2_jumlah'],
                        $data['p3_nomor_sp2d'],
                        $data['p3_tanggal'],
                        $data['p3_jumlah'],
                        $data['p4_nomor_sp2d'],
                        $data['p4_tanggal'],
                        $data['p4_jumlah'],
                        $data['jumlah_pembayaran_hutang'],
                        $data['hutang_baru'],
                        $data['pegawai'],
                        $data['persediaan'],
                        $data['perjadin'],
                        $data['jasa'],
                        $data['pemeliharaan'],
                        $data['uang_jasa_diserahkan'],
                        $data['hibah'],
                        $data['aset_tetap_tanah'],
                        $data['aset_tetap_peralatan_mesin'],
                        $data['aset_tetap_gedung_bangunan'],
                        $data['aset_tetap_jalan_jaringan_irigasi'],
                        $data['aset_tetap_lainnya'],
                        $data['konstruksi_dalam_pekerjaan'],
                        $data['aset_lain_lain'],
                        $data['total_hutang'],
                    ];
                }
                $datas = collect($datas);
                // add total row
                $datas[] = [
                    'Total',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $collectDatas->sum('nilai_kontrak'),
                    '',
                    '',
                    $collectDatas->sum('p1_jumlah'),
                    '',
                    '',
                    $collectDatas->sum('p2_jumlah'),
                    '',
                    '',
                    $collectDatas->sum('p3_jumlah'),
                    '',
                    '',
                    $collectDatas->sum('p4_jumlah'),
                    $collectDatas->sum('jumlah_pembayaran_hutang'),
                    $collectDatas->sum('hutang_baru'),
                    $collectDatas->sum('pegawai'),
                    $collectDatas->sum('persediaan'),
                    $collectDatas->sum('perjadin'),
                    $collectDatas->sum('jasa'),
                    $collectDatas->sum('pemeliharaan'),
                    $collectDatas->sum('uang_jasa_diserahkan'),
                    $collectDatas->sum('hibah'),
                    $collectDatas->sum('aset_tetap_tanah'),
                    $collectDatas->sum('aset_tetap_peralatan_mesin'),
                    $collectDatas->sum('aset_tetap_gedung_bangunan'),
                    $collectDatas->sum('aset_tetap_jalan_jaringan_irigasi'),
                    $collectDatas->sum('aset_tetap_lainnya'),
                    $collectDatas->sum('konstruksi_dalam_pekerjaan'),
                    $collectDatas->sum('aset_lain_lain'),
                    $collectDatas->sum('total_hutang'),
                ];
            } else if ($this->params['type'] == 'rekap_utang_belanja') {
                foreach ($collectDatas as $data) {
                    $datas[] = [
                        $data['instance_name'],
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['nama_kegiatan'],
                        $data['pelaksana_pekerjaan'],
                        $data['nomor_kontrak'],
                        $data['tahun_kontrak'],
                        $data['nilai_kontrak'],
                        $data['kewajiban_tidak_terbayar_last_year'],
                        $data['p1_nomor_sp2d'],
                        $data['p1_tanggal'],
                        $data['p1_jumlah'],
                        $data['p2_nomor_sp2d'],
                        $data['p2_tanggal'],
                        $data['p2_jumlah'],
                        $data['p3_nomor_sp2d'],
                        $data['p3_tanggal'],
                        $data['p3_jumlah'],
                        // $data['p4_nomor_sp2d'],
                        // $data['p4_tanggal'],
                        // $data['p4_jumlah'],
                        $data['jumlah_pembayaran_hutang'],
                        $data['kewajiban_tidak_terbayar'],
                        $data['hutang_baru'],
                        $data['pegawai'],
                        $data['persediaan'],
                        $data['perjadin'],
                        $data['jasa'],
                        $data['pemeliharaan'],
                        $data['uang_jasa_diserahkan'],
                        $data['hibah'],
                        $data['aset_tetap_tanah'],
                        $data['aset_tetap_peralatan_mesin'],
                        $data['aset_tetap_gedung_bangunan'],
                        $data['aset_tetap_jalan_jaringan_irigasi'],
                        $data['aset_tetap_lainnya'],
                        $data['konstruksi_dalam_pekerjaan'],
                        $data['aset_lain_lain'],
                        $data['total_hutang'],
                        str()->headline($data['type']),
                    ];
                }
                $datas = collect($datas);
                // add total row
                $datas[] = [
                    'Total',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $collectDatas->sum('nilai_kontrak'),
                    $collectDatas->sum('kewajiban_tidak_terbayar_last_year'),
                    '',
                    '',
                    $collectDatas->sum('p1_jumlah'),
                    '',
                    '',
                    $collectDatas->sum('p2_jumlah'),
                    '',
                    '',
                    $collectDatas->sum('p3_jumlah'),
                    // '',
                    // '',
                    // $collectDatas->sum('p4_jumlah'),
                    $collectDatas->sum('jumlah_pembayaran_hutang'),
                    $collectDatas->sum('kewajiban_tidak_terbayar'),
                    $collectDatas->sum('hutang_baru'),
                    $collectDatas->sum('pegawai'),
                    $collectDatas->sum('persediaan'),
                    $collectDatas->sum('perjadin'),
                    $collectDatas->sum('jasa'),
                    $collectDatas->sum('pemeliharaan'),
                    $collectDatas->sum('uang_jasa_diserahkan'),
                    $collectDatas->sum('hibah'),
                    $collectDatas->sum('aset_tetap_tanah'),
                    $collectDatas->sum('aset_tetap_peralatan_mesin'),
                    $collectDatas->sum('aset_tetap_gedung_bangunan'),
                    $collectDatas->sum('aset_tetap_jalan_jaringan_irigasi'),
                    $collectDatas->sum('aset_tetap_lainnya'),
                    $collectDatas->sum('konstruksi_dalam_pekerjaan'),
                    $collectDatas->sum('aset_lain_lain'),
                    $collectDatas->sum('total_hutang'),
                    '',
                ];
            }
        } else if ($this->params['category'] == 'beban_lo') {
            $collectDatas = collect($this->datas);
            if (in_array($this->params['type'], ['pegawai', 'persediaan', 'perjadin', 'jasa', 'pemeliharaan', 'uang_jasa_diserahkan', 'hibah', 'subsidi'])) {
                foreach ($collectDatas as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['realisasi_belanja'],
                        $data['saldo_awal'],
                        $data['belanja_dibayar_dimuka_akhir'],
                        $data['hutang'],
                        $data['hibah'],
                        $data['reklas_tambah_dari_rekening'],
                        $data['reklas_tambah_dari_modal'],
                        $data['plus_jukor'],
                        $data['plus_total'],
                        $data['saldo_akhir'],
                        $data['beban_tahun_lalu'],
                        $data['belanja_dibayar_dimuka_awal'],
                        $data['pembayaran_hutang'],
                        $data['reklas_kurang_ke_rekening'],
                        $data['reklas_kurang_ke_aset'],
                        $data['atribusi'],
                        $data['min_jukor'],
                        $data['min_total'],
                        $data['beban_lo'],
                    ];
                }
                $datas = collect($datas);
                // add total row
                $datas[] = [
                    'Total',
                    '',
                    '',
                    $collectDatas->sum('realisasi_belanja'),
                    $collectDatas->sum('saldo_awal'),
                    $collectDatas->sum('belanja_dibayar_dimuka_akhir'),
                    $collectDatas->sum('hutang'),
                    $collectDatas->sum('hibah'),
                    $collectDatas->sum('reklas_tambah_dari_rekening'),
                    $collectDatas->sum('reklas_tambah_dari_modal'),
                    $collectDatas->sum('plus_jukor'),
                    $collectDatas->sum('plus_total'),
                    $collectDatas->sum('saldo_akhir'),
                    $collectDatas->sum('beban_tahun_lalu'),
                    $collectDatas->sum('belanja_dibayar_dimuka_awal'),
                    $collectDatas->sum('pembayaran_hutang'),
                    $collectDatas->sum('reklas_kurang_ke_rekening'),
                    $collectDatas->sum('reklas_kurang_ke_aset'),
                    $collectDatas->sum('atribusi'),
                    $collectDatas->sum('min_jukor'),
                    $collectDatas->sum('min_total'),
                    $collectDatas->sum('beban_lo'),
                ];
            }
        } else if ($this->params['category'] == 'pendapatan_lo') {
            if (in_array($this->params['type'], ['rekap_pendapatan_lo'])) {
                $collectDatas = collect($this->datas);
                foreach ($collectDatas as $data) {
                    $datas[] = [
                        $data['uraian'],
                        $data['saldo_awal'],
                        $data['saldo_akhir'],
                        $data['piutang_bruto'],
                        $data['penyisihan_piutang'],
                        $data['beban_penyisihan'],
                    ];
                }
                $datas[] = [
                    'Total',
                    '=sum(B2:B' . ($collectDatas->count() + 1) . ')',
                    '=sum(C2:C' . ($collectDatas->count() + 1) . ')',
                    '=sum(D2:D' . ($collectDatas->count() + 1) . ')',
                    '=sum(E2:E' . ($collectDatas->count() + 1) . ')',
                    '=sum(F2:F' . ($collectDatas->count() + 1) . ')',
                ];
                $datas = collect($datas);
            } else if (in_array($this->params['type'], ['piutang'])) {
                $collectDatas = collect($this->datas);
                // Pendapatan Pajak Daerah
                foreach ($collectDatas->where('type', 'pendapatan_pajak_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['saldo_awal'],
                        $data['koreksi_saldo_awal'],
                        $data['penghapusan_piutang'],
                        $data['mutasi_debet'],
                        $data['mutasi_kredit'],
                        $data['saldo_akhir'],
                        $data['umur_piutang_1'],
                        $data['umur_piutang_2'],
                        $data['umur_piutang_3'],
                        $data['umur_piutang_4'],
                        $data['piutang_bruto'],
                        $data['type'],
                    ];
                }
                $count1 = $collectDatas->where('type', 'pendapatan_pajak_daerah')->count();
                $datas[] = [
                    'Pendapatan Pajak Daerah',
                    '',
                    '',
                    '=sum(D2:D' . ($count1 + 1) . ')',
                    '=sum(E2:E' . ($count1 + 1) . ')',
                    '=sum(F2:F' . ($count1 + 1) . ')',
                    '=sum(G2:G' . ($count1 + 1) . ')',
                    '=sum(H2:H' . ($count1 + 1) . ')',
                    '=sum(I2:I' . ($count1 + 1) . ')',
                    '=sum(J2:J' . ($count1 + 1) . ')',
                    '=sum(K2:K' . ($count1 + 1) . ')',
                    '=sum(L2:L' . ($count1 + 1) . ')',
                    '=sum(M2:M' . ($count1 + 1) . ')',
                    '=sum(N2:N' . ($count1 + 1) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Pendapatan Pajak Daerah
                foreach ($collectDatas->where('type', 'hasil_retribusi_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['saldo_awal'],
                        $data['koreksi_saldo_awal'],
                        $data['penghapusan_piutang'],
                        $data['mutasi_debet'],
                        $data['mutasi_kredit'],
                        $data['saldo_akhir'],
                        $data['umur_piutang_1'],
                        $data['umur_piutang_2'],
                        $data['umur_piutang_3'],
                        $data['umur_piutang_4'],
                        $data['piutang_bruto'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2;
                $count2 = $collectDatas->where('type', 'hasil_retribusi_daerah')->count() + 1;
                $datas[] = [
                    'Pendapatan Retribusi Daerah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count2) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count2) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count2) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count2) . ')',
                    '=sum(H' . $lastCount + 1 . ':H' . ($lastCount + $count2) . ')',
                    '=sum(I' . $lastCount + 1 . ':I' . ($lastCount + $count2) . ')',
                    '=sum(J' . $lastCount + 1 . ':J' . ($lastCount + $count2) . ')',
                    '=sum(K' . $lastCount + 1 . ':K' . ($lastCount + $count2) . ')',
                    '=sum(L' . $lastCount + 1 . ':L' . ($lastCount + $count2) . ')',
                    '=sum(M' . $lastCount + 1 . ':M' . ($lastCount + $count2) . ')',
                    '=sum(N' . $lastCount + 1 . ':N' . ($lastCount + $count2) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan
                foreach ($collectDatas->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['saldo_awal'],
                        $data['koreksi_saldo_awal'],
                        $data['penghapusan_piutang'],
                        $data['mutasi_debet'],
                        $data['mutasi_kredit'],
                        $data['saldo_akhir'],
                        $data['umur_piutang_1'],
                        $data['umur_piutang_2'],
                        $data['umur_piutang_3'],
                        $data['umur_piutang_4'],
                        $data['piutang_bruto'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1;
                $count3 = $collectDatas->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan')->count() + 1;
                $datas[] = [
                    'Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count3) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count3) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count3) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count3) . ')',
                    '=sum(H' . $lastCount + 1 . ':H' . ($lastCount + $count3) . ')',
                    '=sum(I' . $lastCount + 1 . ':I' . ($lastCount + $count3) . ')',
                    '=sum(J' . $lastCount + 1 . ':J' . ($lastCount + $count3) . ')',
                    '=sum(K' . $lastCount + 1 . ':K' . ($lastCount + $count3) . ')',
                    '=sum(L' . $lastCount + 1 . ':L' . ($lastCount + $count3) . ')',
                    '=sum(M' . $lastCount + 1 . ':M' . ($lastCount + $count3) . ')',
                    '=sum(N' . $lastCount + 1 . ':N' . ($lastCount + $count3) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Lain-lain PAD Yang Sah
                foreach ($collectDatas->where('type', 'lain_lain_pad_yang_sah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['saldo_awal'],
                        $data['koreksi_saldo_awal'],
                        $data['penghapusan_piutang'],
                        $data['mutasi_debet'],
                        $data['mutasi_kredit'],
                        $data['saldo_akhir'],
                        $data['umur_piutang_1'],
                        $data['umur_piutang_2'],
                        $data['umur_piutang_3'],
                        $data['umur_piutang_4'],
                        $data['piutang_bruto'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1;
                $count4 = $collectDatas->where('type', 'lain_lain_pad_yang_sah')->count() + 1;
                $datas[] = [
                    'Lain-lain PAD Yang Sah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count4) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count4) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count4) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count4) . ')',
                    '=sum(H' . $lastCount + 1 . ':H' . ($lastCount + $count4) . ')',
                    '=sum(I' . $lastCount + 1 . ':I' . ($lastCount + $count4) . ')',
                    '=sum(J' . $lastCount + 1 . ':J' . ($lastCount + $count4) . ')',
                    '=sum(K' . $lastCount + 1 . ':K' . ($lastCount + $count4) . ')',
                    '=sum(L' . $lastCount + 1 . ':L' . ($lastCount + $count4) . ')',
                    '=sum(M' . $lastCount + 1 . ':M' . ($lastCount + $count4) . ')',
                    '=sum(N' . $lastCount + 1 . ':N' . ($lastCount + $count4) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Transfer Pemerintah Pusat
                foreach ($collectDatas->where('type', 'transfer_pemerintah_pusat') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['saldo_awal'],
                        $data['koreksi_saldo_awal'],
                        $data['penghapusan_piutang'],
                        $data['mutasi_debet'],
                        $data['mutasi_kredit'],
                        $data['saldo_akhir'],
                        $data['umur_piutang_1'],
                        $data['umur_piutang_2'],
                        $data['umur_piutang_3'],
                        $data['umur_piutang_4'],
                        $data['piutang_bruto'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1 + $count4 + 1;
                $count5 = $collectDatas->where('type', 'transfer_pemerintah_pusat')->count() + 1;
                $datas[] = [
                    'Transfer Pemerintah Pusat',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count5) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count5) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count5) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count5) . ')',
                    '=sum(H' . $lastCount + 1 . ':H' . ($lastCount + $count5) . ')',
                    '=sum(I' . $lastCount + 1 . ':I' . ($lastCount + $count5) . ')',
                    '=sum(J' . $lastCount + 1 . ':J' . ($lastCount + $count5) . ')',
                    '=sum(K' . $lastCount + 1 . ':K' . ($lastCount + $count5) . ')',
                    '=sum(L' . $lastCount + 1 . ':L' . ($lastCount + $count5) . ')',
                    '=sum(M' . $lastCount + 1 . ':M' . ($lastCount + $count5) . ')',
                    '=sum(N' . $lastCount + 1 . ':N' . ($lastCount + $count5) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Transfer Antar Daerah
                foreach ($collectDatas->where('type', 'transfer_antar_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['saldo_awal'],
                        $data['koreksi_saldo_awal'],
                        $data['penghapusan_piutang'],
                        $data['mutasi_debet'],
                        $data['mutasi_kredit'],
                        $data['saldo_akhir'],
                        $data['umur_piutang_1'],
                        $data['umur_piutang_2'],
                        $data['umur_piutang_3'],
                        $data['umur_piutang_4'],
                        $data['piutang_bruto'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1 + $count4 + 1 + $count5 + 1;
                $count6 = $collectDatas->where('type', 'transfer_antar_daerah')->count() + 1;
                $datas[] = [
                    'Transfer Antar Daerah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count6) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count6) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count6) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count6) . ')',
                    '=sum(H' . $lastCount + 1 . ':H' . ($lastCount + $count6) . ')',
                    '=sum(I' . $lastCount + 1 . ':I' . ($lastCount + $count6) . ')',
                    '=sum(J' . $lastCount + 1 . ':J' . ($lastCount + $count6) . ')',
                    '=sum(K' . $lastCount + 1 . ':K' . ($lastCount + $count6) . ')',
                    '=sum(L' . $lastCount + 1 . ':L' . ($lastCount + $count6) . ')',
                    '=sum(M' . $lastCount + 1 . ':M' . ($lastCount + $count6) . ')',
                    '=sum(N' . $lastCount + 1 . ':N' . ($lastCount + $count6) . ')',
                    '',
                ];

                $datas = collect($datas);
            } else if (in_array($this->params['type'], ['penyisihan'])) {
                $collectDatas = collect($this->datas);
                // Pendapatan Pajak Daerah
                foreach ($collectDatas->where('type', 'pendapatan_pajak_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['piutang_bruto'],
                        $data['penyisihan_piutang_1'],
                        $data['penyisihan_piutang_2'],
                        $data['penyisihan_piutang_3'],
                        $data['penyisihan_piutang_4'],
                        $data['jumlah'],
                        $data['type'],
                    ];
                }
                $count1 = $collectDatas->where('type', 'pendapatan_pajak_daerah')->count();
                $datas[] = [
                    'Pendapatan Pajak Daerah',
                    '',
                    '',
                    '=sum(D2:D' . ($count1 + 1) . ')',
                    '=sum(E2:E' . ($count1 + 1) . ')',
                    '=sum(F2:F' . ($count1 + 1) . ')',
                    '=sum(G2:G' . ($count1 + 1) . ')',
                    '=sum(H2:H' . ($count1 + 1) . ')',
                    '=sum(I2:I' . ($count1 + 1) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Pendapatan Pajak Daerah
                foreach ($collectDatas->where('type', 'hasil_retribusi_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['piutang_bruto'],
                        $data['penyisihan_piutang_1'],
                        $data['penyisihan_piutang_2'],
                        $data['penyisihan_piutang_3'],
                        $data['penyisihan_piutang_4'],
                        $data['jumlah'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2;
                $count2 = $collectDatas->where('type', 'hasil_retribusi_daerah')->count() + 1;
                $datas[] = [
                    'Pendapatan Retribusi Daerah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count2) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count2) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count2) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count2) . ')',
                    '=sum(H' . $lastCount + 1 . ':H' . ($lastCount + $count2) . ')',
                    '=sum(I' . $lastCount + 1 . ':I' . ($lastCount + $count2) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan
                foreach ($collectDatas->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['piutang_bruto'],
                        $data['penyisihan_piutang_1'],
                        $data['penyisihan_piutang_2'],
                        $data['penyisihan_piutang_3'],
                        $data['penyisihan_piutang_4'],
                        $data['jumlah'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1;
                $count3 = $collectDatas->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan')->count() + 1;
                $datas[] = [
                    'Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count3) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count3) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count3) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count3) . ')',
                    '=sum(H' . $lastCount + 1 . ':H' . ($lastCount + $count3) . ')',
                    '=sum(I' . $lastCount + 1 . ':I' . ($lastCount + $count3) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Lain-lain PAD Yang Sah
                foreach ($collectDatas->where('type', 'lain_lain_pad_yang_sah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['piutang_bruto'],
                        $data['penyisihan_piutang_1'],
                        $data['penyisihan_piutang_2'],
                        $data['penyisihan_piutang_3'],
                        $data['penyisihan_piutang_4'],
                        $data['jumlah'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1;
                $count4 = $collectDatas->where('type', 'lain_lain_pad_yang_sah')->count() + 1;
                $datas[] = [
                    'Lain-lain PAD Yang Sah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count4) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count4) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count4) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count4) . ')',
                    '=sum(H' . $lastCount + 1 . ':H' . ($lastCount + $count4) . ')',
                    '=sum(I' . $lastCount + 1 . ':I' . ($lastCount + $count4) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Transfer Pemerintah Pusat
                foreach ($collectDatas->where('type', 'transfer_pemerintah_pusat') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['piutang_bruto'],
                        $data['penyisihan_piutang_1'],
                        $data['penyisihan_piutang_2'],
                        $data['penyisihan_piutang_3'],
                        $data['penyisihan_piutang_4'],
                        $data['jumlah'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1 + $count4 + 1;
                $count5 = $collectDatas->where('type', 'transfer_pemerintah_pusat')->count() + 1;
                $datas[] = [
                    'Transfer Pemerintah Pusat',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count5) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count5) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count5) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count5) . ')',
                    '=sum(H' . $lastCount + 1 . ':H' . ($lastCount + $count5) . ')',
                    '=sum(I' . $lastCount + 1 . ':I' . ($lastCount + $count5) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Transfer Antar Daerah
                foreach ($collectDatas->where('type', 'transfer_antar_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['piutang_bruto'],
                        $data['penyisihan_piutang_1'],
                        $data['penyisihan_piutang_2'],
                        $data['penyisihan_piutang_3'],
                        $data['penyisihan_piutang_4'],
                        $data['jumlah'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1 + $count4 + 1 + $count5 + 1;
                $count6 = $collectDatas->where('type', 'transfer_antar_daerah')->count() + 1;
                $datas[] = [
                    'Transfer Antar Daerah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count6) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count6) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count6) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count6) . ')',
                    '=sum(H' . $lastCount + 1 . ':H' . ($lastCount + $count6) . ')',
                    '=sum(I' . $lastCount + 1 . ':I' . ($lastCount + $count6) . ')',
                    '',
                ];

                $datas = collect($datas);
            } else if (in_array($this->params['type'], ['beban'])) {
                $collectDatas = collect($this->datas);
                // Pendapatan Pajak Daerah
                foreach ($collectDatas->where('type', 'pendapatan_pajak_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['jumlah_penyisihan'],
                        $data['jumlah_penyisihan_last_year'],
                        $data['koreksi_penyisihan'],
                        $data['beban_penyisihan'],
                        $data['type'],
                    ];
                }
                $count1 = $collectDatas->where('type', 'pendapatan_pajak_daerah')->count();
                $datas[] = [
                    'Pendapatan Pajak Daerah',
                    '',
                    '',
                    '=sum(D2:D' . ($count1 + 1) . ')',
                    '=sum(E2:E' . ($count1 + 1) . ')',
                    '=sum(F2:F' . ($count1 + 1) . ')',
                    '=sum(G2:G' . ($count1 + 1) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Pendapatan Pajak Daerah
                foreach ($collectDatas->where('type', 'hasil_retribusi_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['jumlah_penyisihan'],
                        $data['jumlah_penyisihan_last_year'],
                        $data['koreksi_penyisihan'],
                        $data['beban_penyisihan'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2;
                $count2 = $collectDatas->where('type', 'hasil_retribusi_daerah')->count() + 1;
                $datas[] = [
                    'Pendapatan Retribusi Daerah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count2) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count2) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count2) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count2) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan
                foreach ($collectDatas->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['jumlah_penyisihan'],
                        $data['jumlah_penyisihan_last_year'],
                        $data['koreksi_penyisihan'],
                        $data['beban_penyisihan'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1;
                $count3 = $collectDatas->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan')->count() + 1;
                $datas[] = [
                    'Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count3) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count3) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count3) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count3) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Lain-lain PAD Yang Sah
                foreach ($collectDatas->where('type', 'lain_lain_pad_yang_sah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['jumlah_penyisihan'],
                        $data['jumlah_penyisihan_last_year'],
                        $data['koreksi_penyisihan'],
                        $data['beban_penyisihan'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1;
                $count4 = $collectDatas->where('type', 'lain_lain_pad_yang_sah')->count() + 1;
                $datas[] = [
                    'Lain-lain PAD Yang Sah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count4) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count4) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count4) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count4) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Transfer Pemerintah Pusat
                foreach ($collectDatas->where('type', 'transfer_pemerintah_pusat') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['jumlah_penyisihan'],
                        $data['jumlah_penyisihan_last_year'],
                        $data['koreksi_penyisihan'],
                        $data['beban_penyisihan'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1 + $count4 + 1;
                $count5 = $collectDatas->where('type', 'transfer_pemerintah_pusat')->count() + 1;
                $datas[] = [
                    'Transfer Pemerintah Pusat',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count5) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count5) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count5) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count5) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Transfer Antar Daerah
                foreach ($collectDatas->where('type', 'transfer_antar_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['jumlah_penyisihan'],
                        $data['jumlah_penyisihan_last_year'],
                        $data['koreksi_penyisihan'],
                        $data['beban_penyisihan'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1 + $count4 + 1 + $count5 + 1;
                $count6 = $collectDatas->where('type', 'transfer_antar_daerah')->count() + 1;
                $datas[] = [
                    'Transfer Antar Daerah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count6) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count6) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count6) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count6) . ')',
                    '',
                ];

                $datas = collect($datas);
            } else if (in_array($this->params['type'], ['pdd'])) {
                $collectDatas = collect($this->datas);
                // Pendapatan Pajak Daerah
                foreach ($collectDatas->where('type', 'pendapatan_pajak_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['pendapatan_diterima_dimuka_awal'],
                        $data['mutasi_berkurang'],
                        $data['mutasi_bertambah'],
                        $data['pendapatan_diterima_dimuka_akhir'],
                        $data['type'],
                    ];
                }
                $count1 = $collectDatas->where('type', 'pendapatan_pajak_daerah')->count();
                $datas[] = [
                    'Pendapatan Pajak Daerah',
                    '',
                    '',
                    '=sum(D2:D' . ($count1 + 1) . ')',
                    '=sum(E2:E' . ($count1 + 1) . ')',
                    '=sum(F2:F' . ($count1 + 1) . ')',
                    '=sum(G2:G' . ($count1 + 1) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Pendapatan Pajak Daerah
                foreach ($collectDatas->where('type', 'hasil_retribusi_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['pendapatan_diterima_dimuka_awal'],
                        $data['mutasi_berkurang'],
                        $data['mutasi_bertambah'],
                        $data['pendapatan_diterima_dimuka_akhir'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2;
                $count2 = $collectDatas->where('type', 'hasil_retribusi_daerah')->count() + 1;
                $datas[] = [
                    'Pendapatan Retribusi Daerah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count2) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count2) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count2) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count2) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan
                foreach ($collectDatas->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['pendapatan_diterima_dimuka_awal'],
                        $data['mutasi_berkurang'],
                        $data['mutasi_bertambah'],
                        $data['pendapatan_diterima_dimuka_akhir'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1;
                $count3 = $collectDatas->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan')->count() + 1;
                $datas[] = [
                    'Hasil Pengelolaan Kekayaan Daerah Yang Dipisahkan',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count3) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count3) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count3) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count3) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Lain-lain PAD Yang Sah
                foreach ($collectDatas->where('type', 'lain_lain_pad_yang_sah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['pendapatan_diterima_dimuka_awal'],
                        $data['mutasi_berkurang'],
                        $data['mutasi_bertambah'],
                        $data['pendapatan_diterima_dimuka_akhir'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1;
                $count4 = $collectDatas->where('type', 'lain_lain_pad_yang_sah')->count() + 1;
                $datas[] = [
                    'Lain-lain PAD Yang Sah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count4) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count4) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count4) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count4) . ')',
                    ''
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Transfer Pemerintah Pusat
                foreach ($collectDatas->where('type', 'transfer_pemerintah_pusat') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['pendapatan_diterima_dimuka_awal'],
                        $data['mutasi_berkurang'],
                        $data['mutasi_bertambah'],
                        $data['pendapatan_diterima_dimuka_akhir'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1 + $count4 + 1;
                $count5 = $collectDatas->where('type', 'transfer_pemerintah_pusat')->count() + 1;
                $datas[] = [
                    'Transfer Pemerintah Pusat',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count5) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count5) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count5) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count5) . ')',
                    '',
                ];
                $datas[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

                // Transfer Antar Daerah
                foreach ($collectDatas->where('type', 'transfer_antar_daerah') as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'],
                        $data['kode_rekening_name'],
                        $data['pendapatan_diterima_dimuka_awal'],
                        $data['mutasi_berkurang'],
                        $data['mutasi_bertambah'],
                        $data['pendapatan_diterima_dimuka_akhir'],
                        $data['type'],
                    ];
                }
                $lastCount = $count1 + 2 + $count2 + 1 + $count3 + 1 + $count4 + 1 + $count5 + 1;
                $count6 = $collectDatas->where('type', 'transfer_antar_daerah')->count() + 1;
                $datas[] = [
                    'Transfer Antar Daerah',
                    '',
                    '',
                    '=sum(D' . $lastCount + 1 . ':D' . ($lastCount + $count6) . ')',
                    '=sum(E' . $lastCount + 1 . ':E' . ($lastCount + $count6) . ')',
                    '=sum(F' . $lastCount + 1 . ':F' . ($lastCount + $count6) . ')',
                    '=sum(G' . $lastCount + 1 . ':G' . ($lastCount + $count6) . ')',
                    '',
                ];

                $datas = collect($datas);
            } else if (in_array($this->params['type'], ['lota'])) {
                $collectDatas = collect($this->datas);
                foreach ($collectDatas as $data) {
                    $datas[] = [
                        $data['instance_name'] ?? null,
                        $data['kode_rekening_fullcode'] . ' - ' . $data['kode_rekening_name'],
                        $data['anggaran_perubahan'],
                        $data['lra'],
                        number_format($data['lra_percent'], 2, '.', ','),
                        $data['piutang_awal'],
                        $data['piutang_akhir'],
                        $data['pdd_awal'],
                        $data['pdd_akhir'],
                        $data['laporan_operasional'],
                        number_format($data['laporan_operasional_percent'], 2, '.', ','),
                        $data['penambahan_pengurangan_lo'],
                        $data['reklas_koreksi_lo'],
                        $data['perbedaan_lo_lra'],
                    ];
                }

                $datas[] = [
                    'Total',
                    '',
                    '=sum(C2:C' . (count($collectDatas)) . ')',
                    '=sum(D2:D' . (count($collectDatas)) . ')',
                    '=average(E2:E' . (count($collectDatas)) . ')',
                    '=sum(F2:F' . (count($collectDatas)) . ')',
                    '=sum(G2:G' . (count($collectDatas)) . ')',
                    '=sum(H2:H' . (count($collectDatas)) . ')',
                    '=sum(I2:I' . (count($collectDatas)) . ')',
                    '=sum(J2:J' . (count($collectDatas)) . ')',
                    '=average(K2:K' . (count($collectDatas)) . ')',
                    '=sum(L2:L' . (count($collectDatas)) . ')',
                    '=sum(M2:M' . (count($collectDatas)) . ')',
                    '=sum(N2:N' . (count($collectDatas)) . ')',
                ];

                $datas = collect($datas);
            }
        } else if ($this->params['category'] == 'pengembalian-belanja') {
            if ($this->params['type'] == 'pengembalian-belanja') {
                $collectDatas = collect($this->datas);
                foreach ($collectDatas as $key => $data) {
                    $datas[] = [
                        $key + 1,
                        $data['instance_name'] ?? null,
                        $data['tanggal_setor'] ?? null,
                        $data['kode_rekening_fullcode'] ?? null,
                        $data['kode_rekening_name'] ?? null,
                        $data['uraian'] ?? null,
                        $data['jenis_spm'] ?? null,
                        $data['jumlah'] ?? 0,
                    ];
                }
                $datas = collect($datas);
                // add total row
                $datas->push([
                    'Total',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '=sum(G2:G' . ($collectDatas->count() + 1) . ')',
                ]);
            }
        }
        return $datas;
    }
}
