<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Label — {{ $variant->sku }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; background: #f3f4f6; }

.label {
    width: 58mm;
    height: 17mm;
    background: white;
    padding: 1mm 2mm;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    overflow: hidden;
    border: 1px dashed #ccc;
    margin: 4mm auto;
    page-break-after: always;
}

.header {
    display: flex;
    justify-content: space-between;
    width: 100%;
    font-size: 6.5pt;
    font-weight: bold;
    line-height: 1;
    margin-bottom: 0.5mm;
    color: #000;
}

.name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 60%;
}

.price {
    max-width: 40%;
    text-align: right;
}

.barcode-container {
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    overflow: hidden;
}

.barcode-container svg {
    height: 100%;
    max-width: 100%;
}

/* Jarak 70px untuk layar monitor agar tidak tertutup header navigasi */
.content-wrapper {
    padding-top: 70px;
    padding-bottom: 20px;
}

@media print {
    body, html { background: white; margin: 0; padding: 0; }
    .no-print { display: none !important; }
    
    /* MENGHILANGKAN JARAK 70PX SAAT DIPRINT AGAR LABEL TIDAK TERPOTONG */
    .content-wrapper { padding-top: 0; padding-bottom: 0; }
    
    .label { 
        border: none; 
        margin: 0; 
        width: 58mm; 
        height: 17mm; 
        padding: 1mm;
    }
    @page { margin: 0; size: 58mm 17mm; } 
}
</style>
</head>
<body>

{{-- Print controls --}}
<div class="no-print" style="position:fixed;top:0;left:0;right:0;background:#1e1b4b;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;z-index:100;">
    <div style="color:white;font-family:sans-serif;font-size:14px;font-weight:bold;">
        Label 17x58mm — {{ $variant->sku }}
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <label style="color:#c7d2fe;font-family:sans-serif;font-size:12px;">Jumlah:</label>
        <input type="number" id="copiesInput" value="{{ $copies }}" min="1" max="100"
            style="width:60px;padding:4px 8px;border-radius:6px;border:1px solid #4f46e5;font-size:13px;">
        <button onclick="updateCopies()"
            style="background:#4f46e5;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:13px;font-family:sans-serif;">
            Update
        </button>
        <button onclick="window.print()"
            style="background:#16a34a;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:13px;font-family:sans-serif;">
            Cetak Label
        </button>
        <!-- FIX TOMBOL KEMBALI: Mengarah pasti ke halaman tabel label -->
        <a href="{{ route('labels.picker') }}"
            style="background:#374151;color:white;text-decoration:none;padding:8px 16px;border-radius:6px;font-size:13px;font-family:sans-serif;">
            Kembali
        </a>
    </div>
</div>

<div class="content-wrapper">
    @for($i = 0; $i < $copies; $i++)
    <div class="label">
        <div class="header">
            <div class="name">{{ $variant->product->name }} ({{ $variant->size->name }})</div>
            <div class="price">Rp{{ number_format($variant->sellPrice(), 0, ',', '.') }}</div>
        </div>
        <div class="barcode-container">
            <svg class="barcode"></svg>
        </div>
    </div>
    @endfor
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js"></script>
<script>
document.querySelectorAll('.barcode').forEach(function(el) {
    JsBarcode(el, '{{ $variant->sku }}', {
        format: 'CODE128',
        width: 1.4,          
        height: 25,          
        displayValue: true,  
        fontSize: 10,
        textMargin: 1,
        margin: 2
    });
});

function updateCopies() {
    var copies = document.getElementById('copiesInput').value;
    window.location.href = '{{ route('labels.single', $variant) }}?copies=' + copies;
}
</script>
</body>
</html>