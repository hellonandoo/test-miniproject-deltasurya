<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Marketing - RS Delta Surya</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { box-shadow: 0 4px 12px rgba(0,0,0,0.05); background-color: #ffffff; }
        .navbar-brand { font-weight: 700; color: #6f42c1 !important; }
        
        .stat-card {
            background: linear-gradient(135deg, #6f42c1 0%, #8540f5 100%);
            color: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 6px 15px rgba(111, 66, 193, 0.2);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card .icon-wrapper {
            background-color: rgba(255, 255, 255, 0.2);
            height: 60px;
            width: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        .stat-card h3 { font-size: 2.2rem; font-weight: 700; margin: 0; }
        
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .card-header { 
            background-color: #ffffff; 
            border-bottom: 1px solid #f1f3f5; 
            font-weight: 700; 
            padding: 1.25rem 1.5rem; 
            border-radius: 12px 12px 0 0 !important;
            color: #495057;
        }
        .card-header i { color: #6f42c1; margin-right: 0.5rem; }
        
        .table { margin-bottom: 0; }
        .table th { border-bottom: 2px solid #edf2f9; color: #8392a5; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 1rem 1.5rem; }
        .table td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #edf2f9; color: #495057; }
        .table tr:last-child td { border-bottom: none; }
        
        .badge-rank {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .rank-1 { background-color: #ffd700; color: #856404; }
        .rank-2 { background-color: #e0e0e0; color: #383d41; }
        .rank-3 { background-color: #cd7f32; color: #fff; }
        .rank-other { background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; }
        
        .revenue-amount { font-weight: 600; color: #198754; }
        .visit-count { font-weight: 600; color: #0d6efd; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">
                <i class="fa-solid fa-chart-pie me-2"></i> Dashboard Marketing
            </a>
            <div class="d-flex text-muted align-items-center">
                <i class="fa-solid fa-circle-user me-2 fs-4"></i>
                <span class="fw-semibold me-3">Halo, {{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fa-solid fa-right-from-bracket"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        
        <!-- Total Revenue Stat Card -->
        <div class="row">
            <div class="col-12">
                <div class="stat-card">
                    <div>
                        <p class="mb-1 text-uppercase fw-semibold" style="letter-spacing: 1px;">Total Pendapatan (Paid)</p>
                        <h3>Rp {{ isset($total_revenue) ? number_format($total_revenue, 0, ',', '.') : '0' }}</h3>
                    </div>
                    <div class="icon-wrapper">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Card Kiri: Top Visited -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fa-solid fa-users me-2"></i> Top 5 Asuransi (Kunjungan Terbanyak)
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="80">Peringkat</th>
                                        <th>Nama Asuransi</th>
                                        <th class="text-end">Total Kunjungan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($top_visited_insurances) && count($top_visited_insurances) > 0)
                                        @foreach($top_visited_insurances as $index => $item)
                                            <tr>
                                                <td>
                                                    @if($index == 0)
                                                        <span class="badge-rank rank-1">1</span>
                                                    @elseif($index == 1)
                                                        <span class="badge-rank rank-2">2</span>
                                                    @elseif($index == 2)
                                                        <span class="badge-rank rank-3">3</span>
                                                    @else
                                                        <span class="badge-rank rank-other">{{ $index + 1 }}</span>
                                                    @endif
                                                </td>
                                                <td class="fw-semibold">{{ $item['insurance_name'] ?? 'Unknown' }}</td>
                                                <td class="text-end visit-count">
                                                    {{ number_format($item['total_transactions'] ?? 0, 0, ',', '.') }} x
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Belum ada data kunjungan.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Kanan: Top Paid -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fa-solid fa-money-bill-trend-up me-2"></i> Top 5 Asuransi (Pembayaran Terbesar)
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="80">Peringkat</th>
                                        <th>Nama Asuransi</th>
                                        <th class="text-end">Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($top_paid_insurances) && count($top_paid_insurances) > 0)
                                        @foreach($top_paid_insurances as $index => $item)
                                            <tr>
                                                <td>
                                                    @if($index == 0)
                                                        <span class="badge-rank rank-1"><i class="fa-solid fa-trophy"></i></span>
                                                    @elseif($index == 1)
                                                        <span class="badge-rank rank-2">2</span>
                                                    @elseif($index == 2)
                                                        <span class="badge-rank rank-3">3</span>
                                                    @else
                                                        <span class="badge-rank rank-other">{{ $index + 1 }}</span>
                                                    @endif
                                                </td>
                                                <td class="fw-semibold">{{ $item['insurance_name'] ?? 'Unknown' }}</td>
                                                <td class="text-end revenue-amount">
                                                    Rp {{ number_format($item['total_amount'] ?? 0, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Belum ada data pembayaran.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
