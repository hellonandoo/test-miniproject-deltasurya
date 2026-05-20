<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DailyTransactionsExport;

class SendDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:send-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send daily paid transactions Excel report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday();

        $transactions = DB::table('transactions')
            ->where('status', 'paid')
            ->whereDate('created_at', $yesterday)
            ->get();

        if ($transactions->isEmpty()) {
            $this->info("Tidak ada transaksi berstatus 'paid' untuk tanggal {$yesterday->format('Y-m-d')}.");
            return;
        }

        // Ambil details untuk menghidari N+1 query karena query builder digunakan
        $transactionIds = $transactions->pluck('id');
        $details = DB::table('transaction_details')
            ->whereIn('transaction_id', $transactionIds)
            ->get();

        // Hubungkan details ke transaksi
        $transactions->transform(function ($transaction) use ($details) {
            $transaction->details = $details->where('transaction_id', $transaction->id)->values();
            return $transaction;
        });

        // 1. Generate dan simpan Excel ke local storage
        $fileName = 'Laporan_Transaksi_' . $yesterday->format('Ymd') . '.xlsx';
        $filePath = 'reports/' . $fileName;

        Excel::store(new DailyTransactionsExport($transactions), $filePath, 'local');

        // Mengambil absolute path file dari disk local
        $fullPath = Storage::disk('local')->path($filePath);

        // 2. Kirim email dengan attachment
        $subject = 'Laporan Transaksi Harian - ' . $yesterday->format('d M Y');
        
        Mail::raw("Berikut adalah lampiran laporan transaksi harian untuk tanggal {$yesterday->format('d M Y')}.", function ($message) use ($fullPath, $fileName, $subject) {
            $message->to('interview.deltasurya@yopmail.com')
                    ->subject($subject)
                    ->attach($fullPath, [
                        'as' => $fileName,
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
        });

        $this->info('Laporan harian berhasil di-generate dan dikirim ke email tujuan.');
    }
}
