@extends('layouts.app')
@section('title', 'Sesi Kasir')
@section('page-title', 'Sesi Kasir')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- ==========================================
         JIKA ADA SESI KASIR YANG SEDANG AKTIF
         ========================================== --}}
    @if($active)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-black text-gray-800 mb-1">Sesi Kasir Aktif</h2>
            <p class="text-sm text-gray-500 mb-6">Anda sedang beroperasi di <span class="font-semibold text-indigo-600">{{ $store->name }}</span></p>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                    <p class="text-[10px] text-indigo-500 font-bold uppercase tracking-wider mb-1">Waktu Dibuka</p>
                    <p class="font-bold text-indigo-900">{{ \Carbon\Carbon::parse($active->opened_at)->format('d M Y, H:i') }}</p>
                </div>
                <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                    <p class="text-[10px] text-indigo-500 font-bold uppercase tracking-wider mb-1">Modal Awal di Laci</p>
                    <p class="font-bold text-indigo-900">Rp {{ number_format($active->opening_amount, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('pos.index') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center font-bold py-3.5 rounded-xl shadow-md transition-colors flex items-center justify-center">
                    Lanjut Transaksi Kasir
                </a>
                <button type="button" onclick="document.getElementById('closeSessionForm').classList.toggle('hidden')" class="bg-red-50 hover:bg-red-100 border border-red-100 text-red-600 font-bold px-6 py-3.5 rounded-xl transition-colors">
                    Tutup Sesi
                </button>
            </div>

            <!-- Form Tutup Sesi (Tersembunyi secara default, akan muncul jika tombol Tutup Sesi diklik) -->
            <div id="closeSessionForm" class="hidden mt-6 pt-6 border-t border-gray-100">
                <form method="POST" action="{{ route('pos.session.close') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Total Uang Fisik di Laci Saat Ini (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="closing_amount" required min="0" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-red-500 outline-none text-lg font-bold" placeholder="Hitung uang tunai...">
                        <p class="text-[11px] text-gray-500 mt-1.5">Masukkan jumlah seluruh uang tunai fisik yang ada di laci kasir saat ini (termasuk modal awal).</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Catatan Penutupan</label>
                        <textarea name="notes" rows="2" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-red-500 outline-none" placeholder="Catatan opsional..."></textarea>
                    </div>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 rounded-xl shadow-md transition-colors mt-2">
                        Konfirmasi Penutupan Sesi
                    </button>
                </form>
            </div>
        </div>
    
    {{-- ==========================================
         JIKA TIDAK ADA SESI (HARUS BUKA SESI DULU)
         ========================================== --}}
    @else
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-black text-gray-800 mb-1">Buka Sesi Kasir</h2>
            <p class="text-sm text-gray-500 mb-6">Toko: <span class="font-semibold text-indigo-600">{{ $store->name }}</span></p>

            <form method="POST" action="{{ route('pos.session.open') }}" class="space-y-4" 
      x-data="sessionApp()" @submit="saveSettings()">
    @csrf
    
    <div>
        <label class="block text-sm font-bold text-gray-700 mb-1">Modal Awal (Tunai) <span class="text-red-500">*</span></label>
        <input type="number" name="opening_amount" value="0" required min="0" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none text-lg font-bold">
    </div>

    <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl">
        <label class="block text-sm font-bold text-indigo-900 mb-2">Metode Cetak Struk (Perangkat Saat Ini)</label>
        <select x-model="device" class="w-full border border-indigo-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none font-semibold text-gray-700 bg-white">
            <option value="ios_bluefy">1. iOS / iPad (Browser Bluefy)</option>
            <option value="pc_usb">2. Laptop / PC (Kabel USB)</option>
            <option value="pc_bluetooth">3. Laptop / PC (Bluetooth Murni)</option>
            <option value="android_flutter">4. Android (Aplikasi SevenKey POS)</option>
            <option value="android_bluetooth">5. Android (Browser Chrome Bluetooth)</option>
        </select>

        <div class="mt-3 p-3 bg-white rounded-lg text-xs text-gray-600 border border-indigo-50 shadow-sm leading-relaxed min-h-[80px]">
            <p x-show="device === 'ios_bluefy'"><strong>⚠️ Tutorial iOS:</strong> Anda WAJIB membuka web ini melalui aplikasi Bluefy dari App Store. Safari tidak mendukung fitur ini. Pastikan Bluetooth iPad menyala.</p>
            <p x-show="device === 'pc_usb'"><strong>💡 Tutorial PC USB:</strong> Pastikan driver printer generic (POS-80) sudah diinstal. Saat mencetak, browser akan memunculkan pop-up print bawaan sistem, pastikan nama printer yang dipilih sudah benar.</p>
            <p x-show="device === 'pc_bluetooth'"><strong>⚡ Tutorial PC Bluetooth:</strong> Saat transaksi pertama, Chrome akan meminta izin pairing (Pop-up kecil di atas). Jika Anda tidak me-refresh halaman (F5), transaksi berikutnya akan tercetak otomatis tanpa pop-up!</p>
            <p x-show="device === 'android_flutter'"><strong>📱 Tutorial Aplikasi:</strong> Anda WAJIB membuka halaman ini dari dalam aplikasi "SevenKey POS" buatan Anda. Koneksi Bluetooth ditangani otomatis oleh aplikasi secara senyap.</p>
            <p x-show="device === 'android_bluetooth'"><strong>⚠️ Tutorial Android Chrome:</strong> Pastikan URL web menggunakan HTTPS. Anda harus memilih nama printer di pop-up Chrome pada saat mencetak.</p>
            
            <button type="button" @click="testPrint()" class="mt-3 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                🖨️ Test Print Perangkat Ini
            </button>
        </div>
    </div>

    <div>
        <label class="block text-sm font-bold text-gray-700 mb-1">Catatan</label>
        <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
    </div>

    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-md mt-2">
        Mulai Sesi Kasir Baru
    </button>
</form>

<script>
function sessionApp() {
    return {
        device: localStorage.getItem('pos_print_method') || 'pc_usb',
        
        saveSettings() {
            localStorage.setItem('pos_print_method', this.device);
        },

        async testPrint() {
            if (this.device === 'pc_usb') {
                // Test Print USB Menggunakan Iframe Tersembunyi
                let printFrame = document.createElement('iframe');
                printFrame.style.display = 'none';
                document.body.appendChild(printFrame);
                
                let testHtml = "<div style='font-family:monospace; text-align:center; padding: 10px; width: 72mm;'><h3>TEST PRINT USB</h3><p>Koneksi Berhasil!</p><p>SevenKey ERP</p></div><div style='height:2cm;'></div>";
                printFrame.contentDocument.write('<html><head><style>@page { margin: 0; } body { margin: 0; }</style></head><body>' + testHtml + '</body></html>');
                printFrame.contentDocument.close();
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
                
                setTimeout(() => document.body.removeChild(printFrame), 2000);
            } 
            else if (this.device === 'android_flutter') {
                // Dummy Data agar aplikasi Flutter tidak Error "Null" saat testing
                const dummyData = {
                    store_name: "TEST FLUTTER",
                    store_address: "Koneksi Printer Berhasil!",
                    receipt_no: "TEST-000",
                    date: "Hari ini",
                    cashier: "Sistem",
                    items: [{ name: "Kertas Test", qty: 1, price: "0", total: "0" }],
                    subtotal: "0", grand_total: "0", paid: "0"
                };
                if (window.PrintChannel) window.PrintChannel.postMessage(JSON.stringify(dummyData));
                else alert("Buka lewat aplikasi Flutter!");
            } 
            else {
                // Test Print Bluetooth Murni
                try {
                    const text = "\nTEST OK - " + this.device.toUpperCase() + "\n\n";
                    const btDevice = await navigator.bluetooth.requestDevice({
                        acceptAllDevices: true,
                        optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                    });
                    const server = await btDevice.gatt.connect();
                    const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                    const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                    
                    const encoder = new TextEncoder();
                    const payload = encoder.encode(text);

                    for (let i = 0; i < payload.length; i += 40) {
                        await characteristic.writeValue(payload.slice(i, i + 40));
                    }
                    btDevice.gatt.disconnect();
                } catch (e) { alert("Gagal Bluetooth: " + e.message); }
            }
        }
    }
}
</script>
        </div>
    @endif

    {{-- ==========================================
         RIWAYAT SESI SEBELUMNYA
         ========================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Riwayat Sesi Terakhir</h2>
        </div>
        
        <div class="divide-y divide-gray-100">
            @forelse($history as $h)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    
                    {{-- Judul dan Selisih --}}
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ \Carbon\Carbon::parse($h->opened_at)->format('d/m/Y H:i') }} &rarr; 
                                {{ $h->closed_at ? \Carbon\Carbon::parse($h->closed_at)->format('H:i') : 'Belum Ditutup' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">oleh <span class="font-semibold">{{ $h->user->name }}</span></p>
                        </div>
                        <div class="text-right">
                            @php
                                $selisih = $h->closing_amount - $h->expected_amount;
                            @endphp
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border {{ $selisih == 0 ? 'bg-green-50 border-green-200 text-green-700' : ($selisih > 0 ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-red-50 border-red-200 text-red-600') }}">
                                <span class="text-xs font-bold uppercase tracking-wider">Selisih:</span>
                                <span class="text-sm font-black">Rp {{ number_format($selisih, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Breakdown Angka Lengkap (Di sinilah uang Anda terlihat!) --}}
                    <div class="grid grid-cols-3 gap-3 bg-gray-100 p-3.5 rounded-xl border border-gray-200">
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Modal Awal</p>
                            <p class="text-sm font-black text-gray-800">Rp {{ number_format($h->opening_amount, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Harapan Sistem</p>
                            <p class="text-sm font-black text-blue-600">Rp {{ number_format($h->expected_amount, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Uang Aktual di Laci</p>
                            <p class="text-sm font-black text-indigo-600">Rp {{ number_format($h->closing_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    
                </div>
            @empty
                <div class="p-10 text-center flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-gray-400 font-medium">Belum ada riwayat sesi kasir di toko ini.</p>
                </div>
            @endforelse
        </div>
        
        @if($history->hasPages())
            <div class="mt-4">
                {{ $history->links() }}
            </div>
        @endif
    </div>

</div>
@endsection