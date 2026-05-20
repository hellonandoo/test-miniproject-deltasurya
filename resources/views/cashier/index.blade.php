<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir - RS Delta Surya</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
        .navbar-brand { font-weight: 700; color: #0d6efd !important; }
        .card { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card-header { background-color: #fff; border-bottom: 1px solid #edf2f9; font-weight: 600; padding: 1rem 1.25rem; border-radius: 10px 10px 0 0 !important;}
        .summary-box { background-color: #f1f5f9; border-radius: 8px; padding: 1.25rem; }
        .grand-total { font-size: 1.5rem; font-weight: 700; color: #198754; }
        .table th { background-color: #f8f9fa; color: #495057; font-weight: 600; }
        .btn-action { width: 100%; padding: 0.75rem; font-weight: 600; margin-bottom: 10px; border-radius: 8px;}
    </style>
</head>
<body>

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">
                <i class="fa-solid fa-hospital-user me-2"></i> Sistem Kasir RS Delta Surya
            </a>
            <div class="d-flex text-muted align-items-center">
                <i class="fa-solid fa-circle-user me-2 fs-4"></i>
                <span class="me-3">Halo, {{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fa-solid fa-right-from-bracket"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            
            <!-- KOLOM KIRI: Form Input -->
            <div class="col-lg-4">
                
                <!-- 1. Form Pasien & Asuransi -->
                <div class="card mb-4">
                    <div class="card-header text-primary">
                        <i class="fa-solid fa-file-invoice me-1"></i> Data Transaksi
                    </div>
                    <div class="card-body">
                        <!-- Simulated form for creating transaction -->
                        <form id="formTransaction" onsubmit="createTransaction(event)">
                            <div class="mb-3">
                                <label for="patient_name" class="form-label text-muted">Nama Pasien <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="patient_name" placeholder="Masukkan nama pasien" required autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="insurance_id" class="form-label text-muted">Asuransi (Opsional)</label>
                                <select class="form-select" id="insurance_id">
                                    <option value="">-- Pribadi / Non-Asuransi --</option>
                                    {{-- Data dilempar dari controller, misal: --}}
                                    @if(isset($insurances))
                                        @foreach($insurances as $ins)
                                            <option value="{{ $ins['id'] }}">{{ $ins['name'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="btnCreateTx">
                                <i class="fa-solid fa-plus me-1"></i> Buat Transaksi Baru
                            </button>
                        </form>

                        <div id="txActiveInfo" class="alert alert-success mt-3 d-none">
                            <strong>Invoice:</strong> <span id="lblInvoice"></span><br>
                            Status: <span class="badge bg-warning text-dark">Pending</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Form Tambah Tindakan -->
                <div class="card">
                    <div class="card-header text-primary">
                        <i class="fa-solid fa-stethoscope me-1"></i> Tambah Tindakan Medis
                    </div>
                    <div class="card-body">
                        <form id="formProcedure" onsubmit="addProcedure(event)">
                            <div class="mb-3">
                                <label for="procedure_select" class="form-label text-muted">Pilih Tindakan <span class="text-danger">*</span></label>
                                <select class="form-select" id="procedure_select" required disabled>
                                    <option value="">-- Pilih Tindakan Medis --</option>
                                    {{-- Data dilempar dari controller --}}
                                    @if(isset($procedures))
                                        @foreach($procedures as $proc)
                                            <option value="{{ $proc['id'] }}" data-name="{{ $proc['name'] }}">
                                                {{ $proc['name'] }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <button type="submit" class="btn btn-outline-primary w-100" id="btnAddProc" disabled>
                                <i class="fa-solid fa-cart-plus me-1"></i> Tambah ke Keranjang
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: Tabel Keranjang & Ringkasan -->
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header text-primary d-flex justify-content-between align-items-center">
                        <div><i class="fa-solid fa-cart-shopping me-1"></i> Rincian Tindakan</div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                            <i class="fa-solid fa-rotate-right"></i> Reset / Segarkan
                        </button>
                    </div>
                    <div class="card-body d-flex flex-column">
                        
                        <!-- TABEL KERANJANG -->
                        <div class="table-responsive flex-grow-1">
                            <table class="table table-hover align-middle" id="cartTable">
                                <thead>
                                    <tr>
                                        <th>Nama Tindakan</th>
                                        <th class="text-end">Harga Asli</th>
                                        <th class="text-end text-danger">Diskon</th>
                                        <th class="text-end text-success">Netto</th>
                                        <th class="text-center" width="80">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="emptyRow">
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <em>Belum ada tindakan yang ditambahkan.</em>
                                        </td>
                                    </tr>
                                    <!-- Rows added via JS dynamically for UX demonstration -->
                                </tbody>
                            </table>
                        </div>

                        <hr class="text-muted">

                        <!-- RINGKASAN & TOMBOL -->
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <!-- Tombol Aksi -->
                                <button class="btn btn-success btn-action" id="btnPay" onclick="payTransaction()" disabled>
                                    <i class="fa-solid fa-money-bill-wave me-2"></i> Proses Pembayaran
                                </button>
                                <a href="#" class="btn btn-danger btn-action disabled" id="btnPrint" target="_blank">
                                    <i class="fa-solid fa-file-pdf me-2"></i> Cetak Bukti Pembayaran (PDF)
                                </a>
                            </div>
                            <div class="col-md-6">
                                <!-- Summary Box -->
                                <div class="summary-box">
                                    <div class="d-flex justify-content-between mb-2 text-muted">
                                        <span>Subtotal</span>
                                        <span id="lblSubtotal">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 text-danger">
                                        <span>Total Diskon</span>
                                        <span id="lblDiscount">- Rp 0</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fw-bold">Grand Total</span>
                                        <span class="grand-total" id="lblGrandTotal">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- UX MOCK & AJAX LOGIC -->
    <script>
        // State management
        let currentTransactionId = null;
        
        // Formatter Rupiah
        const formatRp = (num) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
        };

        // 1. BUAT TRANSAKSI (AJAX)
        async function createTransaction(e) {
            e.preventDefault();
            
            // Collect data
            const patientName = document.getElementById('patient_name').value;
            const insuranceId = document.getElementById('insurance_id').value;

            try {
                // Di sistem riil: POST ke route CashierTransactionController@store
                const response = await fetch('/api/transactions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        patient_name: patientName,
                        insurance_id: insuranceId !== "" ? insuranceId : null
                    })
                });

                // --- MOCK RESPONSE JIKA ROUTE BELUM ADA ---
                // const data = await response.json(); 
                const mockData = { transaction_id: 1, invoice_number: 'INV-' + Date.now() };

                // Kunci form pembuatan transaksi
                document.getElementById('patient_name').disabled = true;
                document.getElementById('insurance_id').disabled = true;
                document.getElementById('btnCreateTx').classList.add('d-none');
                
                // Tampilkan invoice
                document.getElementById('txActiveInfo').classList.remove('d-none');
                document.getElementById('lblInvoice').innerText = mockData.invoice_number;

                // Buka form tindakan
                document.getElementById('procedure_select').disabled = false;
                document.getElementById('btnAddProc').disabled = false;

                // Simpan state
                currentTransactionId = mockData.transaction_id;

                alert('Transaksi berhasil dibuat. Silakan tambahkan tindakan medis.');

            } catch (error) {
                alert('Gagal membuat transaksi.');
            }
        }

        // 2. TAMBAH TINDAKAN
        async function addProcedure(e) {
            e.preventDefault();
            if (!currentTransactionId) return;

            const select = document.getElementById('procedure_select');
            const procId = select.value;
            const procName = select.options[select.selectedIndex].getAttribute('data-name') || select.options[select.selectedIndex].text;

            try {
                // Di sistem riil: POST ke route CashierTransactionController@addProcedure
                const response = await fetch(`/api/transactions/${currentTransactionId}/procedures`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        procedure_id: procId,
                        procedure_name: procName
                    })
                }); 
                
                // MOCK UP UX Penambahan Keranjang (karena endpoint tidak me-return detail)
                // Di environment asli, kita re-fetch detil transaksi lalu re-render array cart

                
                const mockPrice = Math.floor(Math.random() * 500000) + 100000;
                const mockDiscount = mockPrice * 0.1;
                const mockNet = mockPrice - mockDiscount;

                const tbody = document.querySelector('#cartTable tbody');
                document.getElementById('emptyRow').classList.add('d-none');

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${procName}</td>
                    <td class="text-end">${formatRp(mockPrice)}</td>
                    <td class="text-end text-danger">- ${formatRp(mockDiscount)}</td>
                    <td class="text-end text-success fw-bold">${formatRp(mockNet)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); calculateMock()">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                `;
                // Add attributes for calculation
                tr.setAttribute('data-price', mockPrice);
                tr.setAttribute('data-disc', mockDiscount);
                tr.setAttribute('data-net', mockNet);

                tbody.appendChild(tr);

                // Hitung ui total
                calculateMock();

                // Unlock Pay button
                document.getElementById('btnPay').disabled = false;

                // Reset select
                select.value = "";

            } catch (error) {
                alert('Gagal menambahkan tindakan.');
            }
        }

        // Hitung rekalkulasi murni UX Mock (Untuk mempercantik Demo)
        function calculateMock() {
            let sub = 0; let disc = 0; let net = 0;
            const rows = document.querySelectorAll('#cartTable tbody tr[data-price]');
            
            rows.forEach(tr => {
                sub += parseFloat(tr.getAttribute('data-price'));
                disc += parseFloat(tr.getAttribute('data-disc'));
                net += parseFloat(tr.getAttribute('data-net'));
            });

            document.getElementById('lblSubtotal').innerText = formatRp(sub);
            document.getElementById('lblDiscount').innerText = '- ' + formatRp(disc);
            document.getElementById('lblGrandTotal').innerText = formatRp(net);

            if(rows.length === 0) {
                document.getElementById('emptyRow').classList.remove('d-none');
                document.getElementById('btnPay').disabled = true;
            }
        }

        // 3. PROSES PEMBAYARAN
        async function payTransaction() {
            if (!confirm('Proses pembayaran sekarang? Data transaksi akan dikunci.')) return;

            try {
                // Di riil sistem: POST ke CashierTransactionController@pay
                const response = await fetch(`/api/transactions/${currentTransactionId}/pay`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }); 

                alert('Transaksi berhasil dibayar!');

                // Kunci tombol add procedure, hapus tr
                document.getElementById('procedure_select').disabled = true;
                document.getElementById('btnAddProc').disabled = true;
                document.getElementById('btnPay').disabled = true;
                
                // Matikan hapus
                document.querySelectorAll('#cartTable .btn-outline-danger').forEach(btn => btn.disabled = true);

                // Nyalakan tombol PDF
                const btnPrint = document.getElementById('btnPrint');
                btnPrint.classList.remove('disabled');
                btnPrint.href = `/transactions/${currentTransactionId}/print`; // Sesuaikan route asli system backend

            } catch (error) {
                alert('Gagal memproses pembayaran.');
            }
        }
    </script>
</body>
</html>
