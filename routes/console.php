<?php

use App\Jobs\AccountancyReportLO;
use App\Jobs\AccountancyReportNeraca;
use App\Jobs\ProcessRecap;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

// Schedule commands
$schedule = app(\Illuminate\Console\Scheduling\Schedule::class);

$schedule->call(function () {
    $isRunning = Queue::size('process_recap');
    if ($isRunning == 0) {
        dispatch(new ProcessRecap(1))->onQueue('process_recap');
    }
    // })->everyFiveMinutes();
})->everyMinute();

// $schedule->call(function () {
//     $arrInstance = DB::table('instances')
//         ->get();

//     $chunks = $arrInstance->chunk(5);
//     foreach ($chunks as $key => $chunk) {
//         $ids = $chunk->pluck('id')->toArray();
//         $isRunning = Queue::size('accountancy_report_neraca_' . $key);
//         if ($isRunning == 0) {
//             dispatch(new AccountancyReportNeraca(1, 2024, $ids))->onQueue('accountancy_report_neraca_' . $key);
//         }
//     }
// // })->everyThirtyMinutes();
// })->hourlyAt(45);

// $schedule->call(function () {
//     $arrInstance = DB::table('instances')
//         ->get();

//     $chunks = $arrInstance->chunk(3);
//     foreach ($chunks as $key => $chunk) {
//         $ids = $chunk->pluck('id')->toArray();
//         $isRunningLo = Queue::size('accountancy_report_lo_' . $key);
//         if ($isRunningLo == 0) {
//             dispatch(new AccountancyReportLO(1, 2024, $ids))->onQueue('accountancy_report_lo_' . $key);
//         }
//     }
// // })->hourly();
// })->hourlyAt(15);
