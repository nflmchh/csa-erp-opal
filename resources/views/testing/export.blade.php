<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Halaman testing export laporan penjualan — kompatibel Bluefy iOS">
    <title>Testing Export — SevenKey ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary:      #4f46e5;
            --primary-dark: #3730a3;
            --primary-light:#eef2ff;
            --green:        #16a34a;
            --green-light:  #dcfce7;
            --amber:        #d97706;
            --amber-light:  #fef3c7;
            --red:          #dc2626;
            --red-light:    #fee2e2;
            --gray-900:     #111827;
            --gray-700:     #374151;
            --gray-500:     #6b7280;
            --gray-300:     #d1d5db;
            --gray-100:     #f3f4f6;
            --gray-50:      #f9fafb;
            --white:        #ffffff;
            --radius:       14px;
            --shadow:       0 4px 24px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
            --shadow-lg:    0 12px 40px rgba(79,70,229,.15), 0 2px 8px rgba(0,0,0,.06);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f0fdf4 50%, #faf5ff 100%);
            min-height: 100vh;
            color: var(--gray-900);
        }

        /* ─── TOP BAR ─────────────────────────────────────── */
        .topbar {
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(79,70,229,.1);
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar-logo {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; color: white; font-size: 14px;
            letter-spacing: -1px; flex-shrink: 0;
        }
        .topbar-title { font-size: 15px; font-weight: 700; color: var(--gray-900); }
        .topbar-sub   { font-size: 11px; color: var(--gray-500); }
        .topbar-badge {
            margin-left: auto;
            background: var(--amber-light);
            color: var(--amber);
            font-size: 10px; font-weight: 700;
            padding: 4px 10px; border-radius: 20px;
            letter-spacing: .5px; text-transform: uppercase;
        }

        /* ─── MAIN CONTENT ─────────────────────────────────── */
        .container { max-width: 720px; margin: 0 auto; padding: 32px 20px 60px; }

        /* ─── ALERT BANNER ─────────────────────────────────── */
        .alert {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 16px; border-radius: 12px;
            margin-bottom: 24px; font-size: 13px;
        }
        .alert-warning { background: var(--amber-light); border: 1px solid #fde68a; color: #92400e; }
        .alert-info    { background: var(--primary-light); border: 1px solid #c7d2fe; color: var(--primary-dark); }
        .alert-success { background: var(--green-light); border: 1px solid #bbf7d0; color: #14532d; }
        .alert-icon    { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
        .alert strong  { display: block; font-weight: 700; margin-bottom: 3px; }

        /* ─── SECTION ──────────────────────────────────────── */
        .section-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: var(--gray-500);
            margin-bottom: 10px; margin-top: 28px;
        }

        /* ─── CARD ─────────────────────────────────────────── */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid rgba(0,0,0,.07);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            padding: 18px 20px 14px;
            border-bottom: 1px solid var(--gray-100);
            display: flex; align-items: center; gap: 10px;
        }
        .card-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; flex-shrink: 0;
        }
        .card-icon.green  { background: var(--green-light); }
        .card-icon.indigo { background: var(--primary-light); }
        .card-icon.gray   { background: var(--gray-100); }
        .card-title       { font-size: 14px; font-weight: 700; color: var(--gray-900); }
        .card-desc        { font-size: 12px; color: var(--gray-500); margin-top: 1px; }
        .card-body        { padding: 18px 20px; }

        /* ─── FILTER FORM ─────────────────────────────────── */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-label { font-size: 11px; font-weight: 600; color: var(--gray-700); text-transform: uppercase; letter-spacing: .4px; }
        .form-control {
            width: 100%;
            border: 1.5px solid var(--gray-300);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: var(--gray-900);
            background: var(--white);
            transition: border-color .2s, box-shadow .2s;
            -webkit-appearance: none;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
        .form-actions { display: flex; gap: 8px; margin-top: 14px; flex-wrap: wrap; }

        /* ─── BUTTONS ─────────────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 11px 18px; border-radius: 10px;
            font-size: 13px; font-weight: 600; text-decoration: none;
            cursor: pointer; border: none; transition: all .18s;
            -webkit-tap-highlight-color: transparent;
            white-space: nowrap;
        }
        .btn:active { transform: scale(.97); }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); box-shadow: var(--shadow-lg); }
        .btn-green   { background: var(--green); color: white; }
        .btn-green:hover { background: #15803d; box-shadow: 0 6px 20px rgba(22,163,74,.3); }
        .btn-gray    { background: var(--gray-100); color: var(--gray-700); }
        .btn-gray:hover { background: var(--gray-300); }
        .btn-outline {
            background: white; color: var(--primary);
            border: 1.5px solid var(--primary);
        }
        .btn-outline:hover { background: var(--primary-light); }
        .btn-sm { padding: 8px 14px; font-size: 12px; }
        .btn-full { width: 100%; justify-content: center; }

        /* ─── EXPORT BUTTONS GRID ─────────────────────────── */
        .export-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        @media (max-width: 480px) { .export-grid { grid-template-columns: 1fr; } }

        .export-btn {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 16px;
            border-radius: 12px; border: 1.5px solid;
            text-decoration: none; cursor: pointer;
            transition: all .2s; background: white;
            -webkit-tap-highlight-color: transparent;
        }
        .export-btn:active { transform: scale(.97); }

        .export-btn.csv {
            border-color: #bbf7d0; color: var(--green);
        }
        .export-btn.csv:hover { background: var(--green-light); border-color: var(--green); }

        .export-btn.xlsx {
            border-color: #c7d2fe; color: var(--primary);
        }
        .export-btn.xlsx:hover { background: var(--primary-light); border-color: var(--primary); }

        .export-btn.pdf {
            border-color: #fecaca; color: var(--red);
        }
        .export-btn.pdf:hover { background: var(--red-light); border-color: var(--red); }

        .export-btn-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .export-btn.csv  .export-btn-icon { background: var(--green-light); }
        .export-btn.xlsx .export-btn-icon { background: var(--primary-light); }
        .export-btn.pdf  .export-btn-icon { background: var(--red-light); }

        .export-btn-label { font-size: 13px; font-weight: 700; }
        .export-btn-sub   { font-size: 11px; opacity: .7; margin-top: 1px; }

        /* ─── COMPATIBILITY TABLE ──────────────────────────── */
        .compat-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 8px; }
        .compat-table th {
            text-align: left; padding: 8px 10px;
            background: var(--gray-50); border-bottom: 1px solid var(--gray-100);
            font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: var(--gray-500);
        }
        .compat-table td { padding: 8px 10px; border-bottom: 1px solid var(--gray-100); color: var(--gray-700); }
        .compat-table tr:last-child td { border-bottom: none; }
        .badge-ok  { background: var(--green-light); color: var(--green); font-weight: 700; padding: 2px 8px; border-radius: 6px; font-size: 10px; }
        .badge-warn{ background: var(--amber-light); color: var(--amber); font-weight: 700; padding: 2px 8px; border-radius: 6px; font-size: 10px; }
        .badge-bad { background: var(--red-light);   color: var(--red);   font-weight: 700; padding: 2px 8px; border-radius: 6px; font-size: 10px; }

        /* ─── LOG ──────────────────────────────────────────── */
        #log-box {
            background: #0f172a; border-radius: 10px;
            padding: 14px 16px; font-family: monospace; font-size: 11px;
            color: #94a3b8; max-height: 180px; overflow-y: auto;
            border: 1px solid #1e293b; margin-top: 12px;
            line-height: 1.7;
        }
        #log-box .ok   { color: #4ade80; }
        #log-box .warn { color: #fbbf24; }
        #log-box .err  { color: #f87171; }
        #log-box .info { color: #60a5fa; }

        /* ─── FOOTER ───────────────────────────────────────── */
        .footer {
            text-align: center; font-size: 11px; color: var(--gray-500);
            margin-top: 40px; padding-top: 20px;
            border-top: 1px solid var(--gray-100);
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
    <div class="topbar-logo">SK</div>
    <div>
        <div class="topbar-title">SevenKey ERP</div>
        <div class="topbar-sub">Testing Export — Kompatibilitas Bluefy</div>
    </div>
    <div class="topbar-badge">⚠ Testing Mode</div>
</div>

<div class="container">

    <!-- ALERT: Peringatan testing mode -->
    <div class="alert alert-warning" role="alert">
        <span class="alert-icon">⚠️</span>
        <div>
            <strong>Halaman Testing Tanpa Login</strong>
            Halaman ini dibuat khusus untuk menguji kompatibilitas download file di Bluefy (iOS).
            Setelah testing selesai, route ini sebaiknya dinonaktifkan atau dilindungi dengan token.
        </div>
    </div>

    <!-- ALERT: Info Bluefy -->
    <div class="alert alert-info" role="alert">
        <span class="alert-icon">ℹ️</span>
        <div>
            <strong>Tentang Masalah Bluefy & Excel</strong>
            Bluefy (browser iOS berbasis WebKit) sering gagal mendownload file <code>.xlsx</code> karena
            tidak mengenali MIME type <code>application/vnd.openxmlformats-officedocument.spreadsheetml.sheet</code>
            dengan benar. <strong>Solusi terbaik: gunakan CSV</strong> — format yang selalu dikenali oleh semua browser,
            termasuk Bluefy, Safari, dan Chrome iOS.
        </div>
    </div>

    <!-- SECTION: FILTER -->
    <p class="section-title">📅 Filter Data</p>
    <div class="card">
        <div class="card-body">
            <form id="filter-form" method="GET" action="{{ route('testing.export') }}">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="store_id">Toko</label>
                        <select name="store_id" id="store_id" class="form-control">
                            <option value="">Semua Toko</option>
                            @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="date_from">Dari Tanggal</label>
                        <input type="date" name="date_from" id="date_from"
                               value="{{ $dateFrom }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="date_to">Sampai Tanggal</label>
                        <input type="date" name="date_to" id="date_to"
                               value="{{ $dateTo }}" class="form-control">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Terapkan Filter
                    </button>
                    <a href="{{ route('testing.export') }}" class="btn btn-gray">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- SECTION: EXPORT PENJUALAN -->
    <p class="section-title">📊 Export Laporan Penjualan</p>
    <div class="card">
        <div class="card-header">
            <div class="card-icon indigo">📋</div>
            <div>
                <div class="card-title">Laporan Penjualan</div>
                <div class="card-desc">Data transaksi penjualan sesuai filter periode & toko</div>
            </div>
        </div>
        <div class="card-body">
            <!-- Rekomendasi: CSV -->
            <div class="alert alert-success" style="margin-bottom: 14px;">
                <span class="alert-icon">✅</span>
                <div>
                    <strong>Direkomendasikan untuk Bluefy: CSV</strong>
                    CSV selalu bisa didownload di semua browser iOS. File bisa dibuka di Numbers, Google Sheets, atau Excel.
                </div>
            </div>

            <div class="export-grid" id="export-buttons">
                <!-- CSV — paling kompatibel -->
                <a id="btn-csv"
                   href="{{ route('testing.sales.csv', ['store_id' => $storeId, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                   download="laporan-penjualan.csv"
                   class="export-btn csv"
                   onclick="logDownload('CSV', this.href)">
                    <div class="export-btn-icon">📄</div>
                    <div>
                        <div class="export-btn-label">Download CSV</div>
                        <div class="export-btn-sub">✅ Kompatibel Bluefy</div>
                    </div>
                </a>

                <!-- Excel — mungkin bermasalah di Bluefy -->
                <a id="btn-xlsx"
                   href="{{ route('testing.sales.excel', ['store_id' => $storeId, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                   download="laporan-penjualan.xlsx"
                   class="export-btn xlsx"
                   onclick="logDownload('Excel (.xlsx)', this.href)">
                    <div class="export-btn-icon">📊</div>
                    <div>
                        <div class="export-btn-label">Download Excel</div>
                        <div class="export-btn-sub">⚠ Uji kompatibilitas</div>
                    </div>
                </a>
            </div>

            <!-- Log testing -->
            <div id="log-box" aria-label="Log aktivitas download">
                <span class="info">[ Testing Export Logger ]</span><br>
                <span class="info">► Klik tombol download di atas untuk mulai testing...</span><br>
                <span class="info">► User-Agent: <span id="ua-display"></span></span>
            </div>
        </div>
    </div>

    <!-- SECTION: TABEL KOMPATIBILITAS -->
    <p class="section-title">🔍 Tabel Kompatibilitas Format</p>
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <table class="compat-table">
                <thead>
                    <tr>
                        <th>Format</th>
                        <th>Bluefy (iOS)</th>
                        <th>Safari iOS</th>
                        <th>Chrome Android</th>
                        <th>Desktop Browser</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>CSV</strong> <span style="font-size:10px;color:#6b7280">.csv</span></td>
                        <td><span class="badge-ok">✅ OK</span></td>
                        <td><span class="badge-ok">✅ OK</span></td>
                        <td><span class="badge-ok">✅ OK</span></td>
                        <td><span class="badge-ok">✅ OK</span></td>
                    </tr>
                    <tr>
                        <td><strong>Excel</strong> <span style="font-size:10px;color:#6b7280">.xlsx</span></td>
                        <td><span class="badge-warn">⚠ Uji dulu</span></td>
                        <td><span class="badge-warn">⚠ Uji dulu</span></td>
                        <td><span class="badge-ok">✅ OK</span></td>
                        <td><span class="badge-ok">✅ OK</span></td>
                    </tr>
                    <tr>
                        <td><strong>PDF</strong> <span style="font-size:10px;color:#6b7280">.pdf</span></td>
                        <td><span class="badge-ok">✅ OK</span></td>
                        <td><span class="badge-ok">✅ OK</span></td>
                        <td><span class="badge-ok">✅ OK</span></td>
                        <td><span class="badge-ok">✅ OK</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION: INFO TEKNIS -->
    <p class="section-title">⚙️ Info Teknis Perbaikan</p>
    <div class="card">
        <div class="card-body">
            <p style="font-size:13px; color: var(--gray-700); line-height:1.7; margin-bottom: 12px;">
                Perbaikan yang sudah diterapkan di <code>ExportController</code> agar kompatibel dengan Bluefy:
            </p>
            <ul style="font-size:12px; color: var(--gray-700); line-height:2; padding-left: 18px;">
                <li>Header <code>Content-Type</code> yang eksplisit untuk setiap format file</li>
                <li>Header <code>Content-Disposition: attachment</code> dengan nama file yang di-encode UTF-8</li>
                <li><code>Cache-Control: no-store</code> &amp; <code>Pragma: no-cache</code> untuk mencegah caching di WebKit</li>
                <li>BOM (Byte Order Mark) UTF-8 pada CSV agar Numbers/Excel membaca encoding dengan benar</li>
                <li>Kolom CSV diperlengkap: diskon, status bayar, warna, ukuran</li>
            </ul>
        </div>
    </div>

    <div class="footer">
        SevenKey ERP — Testing Export Page &nbsp;·&nbsp;
        {{ now()->format('d F Y, H:i') }} WIB &nbsp;·&nbsp;
        <span style="color: var(--amber);">⚠ Halaman ini tidak memerlukan login</span>
    </div>
</div>

<script>
    // Tampilkan User-Agent di log
    document.getElementById('ua-display').textContent = navigator.userAgent;

    function addLog(msg, type) {
        var box = document.getElementById('log-box');
        var line = document.createElement('span');
        line.className = type || 'ok';
        var time = new Date().toLocaleTimeString('id-ID');
        line.textContent = '[' + time + '] ' + msg;
        box.appendChild(document.createElement('br'));
        box.appendChild(line);
        box.scrollTop = box.scrollHeight;
    }

    function logDownload(format, url) {
        addLog('Memulai download ' + format + '...', 'info');
        addLog('URL: ' + url.split('?')[0] + '?...', 'info');

        // Deteksi Bluefy / WebKit iOS
        var ua = navigator.userAgent;
        var isBluefy = ua.includes('Bluefy');
        var isWebKitIOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;

        if (isBluefy) {
            addLog('Browser terdeteksi: Bluefy — menggunakan CSV sangat dianjurkan!', 'warn');
        } else if (isWebKitIOS) {
            addLog('Browser terdeteksi: WebKit iOS (Safari/dll) — CSV lebih kompatibel', 'warn');
        } else {
            addLog('Browser terdeteksi: Non-iOS — semua format harusnya bekerja', 'ok');
        }
    }

    // Auto-update link filter ketika form berubah
    function updateExportLinks() {
        var storeId  = document.getElementById('store_id').value;
        var dateFrom = document.getElementById('date_from').value;
        var dateTo   = document.getElementById('date_to').value;

        var baseParams = '';
        if (storeId)  baseParams += '&store_id='  + encodeURIComponent(storeId);
        if (dateFrom) baseParams += '&date_from=' + encodeURIComponent(dateFrom);
        if (dateTo)   baseParams += '&date_to='   + encodeURIComponent(dateTo);

        var csvBase  = '{{ route("testing.sales.csv") }}';
        var xlsxBase = '{{ route("testing.sales.excel") }}';

        document.getElementById('btn-csv').href  = csvBase  + '?' + baseParams.substring(1);
        document.getElementById('btn-xlsx').href = xlsxBase + '?' + baseParams.substring(1);
    }

    document.getElementById('store_id').addEventListener('change', updateExportLinks);
    document.getElementById('date_from').addEventListener('change', updateExportLinks);
    document.getElementById('date_to').addEventListener('change', updateExportLinks);
</script>
</body>
</html>
