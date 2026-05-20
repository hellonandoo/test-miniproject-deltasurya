<?php

namespace App\Http\Controllers;

use App\Services\DeltaSuryaApiService;
use App\Services\Discounts\DiscountFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class CashierTransactionController extends Controller
{
    protected DeltaSuryaApiService $apiService;

    public function __construct(DeltaSuryaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Show cashier index view.
     */
    public function index()
    {
        $insurancesRaw = $this->apiService->getInsurances();
        $insurances = $insurancesRaw['data'] ?? $insurancesRaw;

        $proceduresRaw = $this->apiService->getProcedures();
        $procedures = $proceduresRaw['data'] ?? $proceduresRaw;

        return view('cashier.index', compact('insurances', 'procedures'));
    }

    /**
     * Create a new transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'insurance_id' => 'nullable|string|max:255',
        ]);

        $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

        $transactionId = DB::table('transactions')->insertGetId([
            'invoice_number' => $invoiceNumber,
            'patient_name'   => $validated['patient_name'],
            'insurance_id'   => $validated['insurance_id'] ?? null,
            'subtotal'       => 0,
            'total_discount' => 0,
            'grand_total'    => 0,
            'status'         => 'pending',
            'cashier_id'     => auth()->id(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return response()->json([
            'message'        => 'Transaksi berhasil dibuat',
            'transaction_id' => $transactionId,
            'invoice_number' => $invoiceNumber
        ], 201);
    }

    /**
     * Add a procedure to the transaction and apply discounts if a valid voucher exists.
     */
    public function addProcedure(Request $request, $transactionId)
    {
        $validated = $request->validate([
            'procedure_id'   => 'required|string',
            'procedure_name' => 'required|string',
        ]);

        $transaction = DB::table('transactions')->where('id', $transactionId)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        if ($transaction->status === 'paid') {
            throw new Exception("Transaksi sudah dibayar, tidak bisa ditambah");
        }

        // Fetch price from API
        $priceData = $this->apiService->getProcedurePrice($validated['procedure_id']);
        
        // Assuming the API returns the price under a 'price' or 'data.price' key. 
        // Fallback to 0 if we cannot resolve the price structure.
        $price = (float) ($priceData['price'] ?? ($priceData['data']['price'] ?? 0));

        // Check for active voucher based on insurance_id
        $discountAmount = 0;

        if ($transaction->insurance_id) {
            $voucher = DB::table('discount_vouchers')
                ->where('insurance_id', $transaction->insurance_id)
                ->whereDate('valid_from', '<=', now())
                ->whereDate('valid_until', '>=', now())
                ->first();

            if ($voucher) {
                $discountStrategy = DiscountFactory::make($voucher->discount_type);
                $discountAmount = $discountStrategy->calculate($price, $voucher);
            }
        }

        $netPrice = $price - $discountAmount;

        DB::table('transaction_details')->insert([
            'transaction_id'  => $transaction->id,
            'procedure_id'    => $validated['procedure_id'],
            'procedure_name'  => $validated['procedure_name'],
            'price'           => $price,
            'discount_amount' => $discountAmount,
            'net_price'       => $netPrice,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->recalculateTotals($transaction->id);

        return response()->json(['message' => 'Tindakan berhasil ditambahkan']);
    }

    /**
     * Remove a procedure from the transaction.
     */
    public function removeProcedure($transactionId, $detailId)
    {
        $transaction = DB::table('transactions')->where('id', $transactionId)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        if ($transaction->status === 'paid') {
            throw new Exception("Transaksi sudah dibayar, tidak bisa diubah");
        }

        $deleted = DB::table('transaction_details')
            ->where('id', $detailId)
            ->where('transaction_id', $transactionId)
            ->delete();

        if ($deleted) {
            $this->recalculateTotals($transactionId);
            return response()->json(['message' => 'Tindakan berhasil dihapus']);
        }

        return response()->json(['message' => 'Detail tindakan tidak ditemukan'], 404);
    }

    /**
     * Pay the transaction.
     */
    public function pay($transactionId)
    {
        $transaction = DB::table('transactions')->where('id', $transactionId)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        if ($transaction->status === 'paid') {
            return response()->json(['message' => 'Transaksi ini sudah berstatus paid'], 400);
        }

        DB::table('transactions')
            ->where('id', $transactionId)
            ->update([
                'status'     => 'paid',
                'updated_at' => now()
            ]);

        return response()->json(['message' => 'Transaksi berhasil dibayar']);
    }

    /**
     * Print receipt PDF for a paid transaction.
     */
    public function printReceipt($transactionId)
    {
        $transaction = DB::table('transactions')->where('id', $transactionId)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        if ($transaction->status !== 'paid') {
            throw new Exception("Transaksi belum dibayar");
        }

        $transaction->details = DB::table('transaction_details')->where('transaction_id', $transactionId)->get();

        $pdf = Pdf::loadView('receipt', ['transaction' => $transaction]);
        $filename = 'Invoice-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Recalculate transaction totals based on its details.
     */
    private function recalculateTotals($transactionId): void
    {
        $totals = DB::table('transaction_details')
            ->where('transaction_id', $transactionId)
            ->selectRaw('SUM(price) as subtotal')
            ->selectRaw('SUM(discount_amount) as total_discount')
            ->selectRaw('SUM(net_price) as grand_total')
            ->first();

        DB::table('transactions')
            ->where('id', $transactionId)
            ->update([
                'subtotal'       => $totals->subtotal ?? 0,
                'total_discount' => $totals->total_discount ?? 0,
                'grand_total'    => $totals->grand_total ?? 0,
                'updated_at'     => now(),
            ]);
    }
}
