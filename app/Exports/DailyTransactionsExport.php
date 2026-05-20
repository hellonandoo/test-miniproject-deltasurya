<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailyTransactionsExport implements FromCollection, WithHeadings
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        $data = [];
        
        foreach ($this->transactions as $transaction) {
            $details = collect($transaction->details);
            $procedures = $details->pluck('procedure_name')->join(', ');
            
            $data[] = [
                'Invoice Number' => $transaction->invoice_number,
                'Patient Name'   => $transaction->patient_name,
                'Insurance ID'   => $transaction->insurance_id ?? 'Pribadi',
                'Procedures'     => $procedures,
                'Subtotal'       => $transaction->subtotal,
                'Total Discount' => $transaction->total_discount,
                'Grand Total'    => $transaction->grand_total,
                'Paid At'        => $transaction->updated_at,
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Invoice Number', 
            'Patient Name', 
            'Insurance ID', 
            'Procedures',
            'Subtotal (Rp)', 
            'Total Discount (Rp)', 
            'Grand Total (Rp)', 
            'Paid At'
        ];
    }
}
