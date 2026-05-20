<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $transaction->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RS DELTA SURYA</h2>
        <p>Jl. Pahlawan No.9, Sidoarjo<br>Invoice Pembayaran</p>
    </div>
    
    <table class="info-table">
        <tr>
            <td width="150" class="bold">No. Invoice</td>
            <td width="10">:</td>
            <td>{{ $transaction->invoice_number }}</td>
        </tr>
        <tr>
            <td class="bold">Nama Pasien</td>
            <td>:</td>
            <td>{{ $transaction->patient_name }}</td>
        </tr>
        <tr>
            <td class="bold">Tgl. Cetak</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::now()->format('d M Y H:i') }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Tindakan</th>
                <th class="text-right">Harga (Rp)</th>
                <th class="text-right">Diskon (Rp)</th>
                <th class="text-right">Net (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->details as $detail)
            <tr>
                <td>{{ $detail->procedure_name }}</td>
                <td class="text-right">{{ number_format($detail->price, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($detail->discount_amount, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($detail->net_price, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Subtotal</th>
                <th class="text-right">{{ number_format($transaction->subtotal, 2, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Total Diskon</th>
                <th class="text-right">{{ number_format($transaction->total_discount, 2, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right bold">Grand Total</th>
                <th class="text-right bold">{{ number_format($transaction->grand_total, 2, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="text-center" style="margin-top: 50px;">
        <p>Kasir</p>
        <br><br><br>
        <p>(_____________________)</p>
    </div>
</body>
</html>
