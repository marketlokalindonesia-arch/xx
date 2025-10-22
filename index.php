    <?php
    // Get current date in Indonesian format
    function getCurrentDate() {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $day = $days[date('w')];
        $date = date('j');
        $month = $months[date('n') - 1];
        $year = date('Y');
        
        return "$day, $date $month $year";
    }
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <title>Setoran Harian</title>
        <link rel="shortcut icon" href="https://scontent.fbth7-1.fna.fbcdn.net/v/t39.30808-6/477440850_589520410728667_7020102743555682684_n.jpg?_nc_cat=105&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=jrdr1qgu2AYQ7kNvwEJiMMJ&_nc_oc=AdmP9tWSG2fIs97XwFRLZV_U6lLldjcflxr0YgkuLtgl0QyuV_0MGj5ztVrgllUhqAQ&_nc_zt=23&_nc_ht=scontent.fbth7-1.fna&_nc_gid=ZX8bPCLbQjYlucWrE7pSmg&oh=00_AfXN1n6ExVIkUYDVViRWBejXBXGgR9W3s9CdH_N8DT-Oyw&oe=68B619AA">
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            .modal {
                display: none;
            }
            .modal.show {
                display: flex;
            }
        </style>
    </head>
    <body class="min-h-screen bg-gray-50 py-4">
        <div class="max-w-2xl mx-auto px-3">
            <div class="bg-white rounded-lg shadow-lg p-4 space-y-6">
                <!-- Title with Date -->
                <div class="border-b pb-3">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        Setoran Harian 📋
                    </h2>
                    <p class="text-sm text-gray-600"><?= getCurrentDate() ?></p>
                </div>

                <!-- Nama Karyawan -->
            <fieldset class="border-t border-gray-200 pt-6 space-y-4">
        <legend class="text-xl font-semibold text-gray-800">👤 Data Karyawan & Toko</legend>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="store_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Toko/Lokasi</label>
                <select id="store_id" required class="w-full p-2.5 border border-gray-300 rounded-lg">
                    <option value="">-- Pilih Toko --</option>
                </select>
            </div>
            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Karyawan</label>
            <select id="employee_id"required disabled class="w-full p-2.5 border border-gray-300 rounded-lg" >
                    <option value="" disabled selected>Pilih Store Dulu</option>
                </select>
            </div>
            
        </div>

                <!-- Jam Kerja -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">🕐 Jam Kerja</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Jam Masuk</label>
                            <input
                                type="time"
                                id="jam_masuk"
                                class="w-full p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Jam Keluar</label>
                            <input
                                type="time"
                                id="jam_keluar"
                                class="w-full p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                    <div id="jam_kerja_display" class="mt-2 p-2 bg-blue-50 rounded-lg hidden">
                        <span class="text-sm text-blue-700 font-medium">
                            Jam Kerja: <span id="jam_kerja_text"></span>
                        </span>
                    </div>
                </div>

                <!-- Data Meter -->
                <div>
                    <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        ⛽ Data Meter
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Awal</label>
                            <input
                                type="number"
                                id="nomor_awal"
                                class="w-full p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Akhir</label>
                            <input
                                type="number"
                                id="nomor_akhir"
                                class="w-full p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Total Liter</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="total_liter_display"
        document.getElementById('total_liter_display').value = totalLiter.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' L';
        document.getElementById('total_liter_badge').textContent = totalLiter.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' L';
                                    readonly
                                    class="w-full p-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700"
                                    value="0,00 L"
                                />
                                <div id="total_liter_badge" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-900 text-white px-1.5 py-0.5 rounded text-xs font-medium">
                                    0,00 L
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Setoran -->
                <div>
                    <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        💰 Setoran
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Cash</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                <input
                                    type="text"
                                    id="cash_display"
                                    readonly
                                    class="w-full p-2.5 pl-8 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700"
                                    value="0"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">QRIS</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                <input
                                    type="number"
                                    id="qris"
                                    class="w-full p-2.5 pl-8 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Total</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                <input
                                    type="text"
                                    id="total_display"
                                    readonly
                                    class="w-full p-2.5 pl-8 text-sm border border-green-300 rounded-lg bg-green-50 text-green-700 font-semibold"
                                    value="0"
                                />
                                <div id="total_badge" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-green-600 text-white px-1.5 py-0.5 rounded-full text-xs font-medium">
                                    0
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengeluaran -->
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                            💸 Pengeluaran (PU)
                        </h3>
                        <button
                            onclick="showModal('pengeluaranModal')"
                            class="flex items-center gap-1 px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                        >
                            ➕ Tambah Item
                        </button>
                    </div>

                    <div id="pengeluaran_list"></div>

                    <div class="bg-red-100 p-2.5 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Total Pengeluaran:</span>
                            <span id="total_pengeluaran" class="text-red-600 font-bold text-base">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Pemasukan -->
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                            💵 Pemasukan (PU)
                        </h3>
                        <button
                            onclick="showModal('pemasukanModal')"
                            class="flex items-center gap-1 px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        >
                            ➕ Tambah Item
                        </button>
                    </div>

                    <div id="pemasukan_list"></div>

                    <div class="bg-green-100 p-2.5 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Total Pemasukan:</span>
                            <span id="total_pemasukan" class="text-green-600 font-bold text-base">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Total Keseluruhan -->
                <div class="bg-gray-900 text-white p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold">💼 Total Keseluruhan:</h3>
                        <div class="text-right">
                            <div id="total_keseluruhan" class="text-xl font-bold">Rp 0</div>
                            <div id="total_breakdown" class="text-xs opacity-75">
                                Setoran: 0 + Pemasukan: 0 - Pengeluaran: 0
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <button 
                    onclick="copyToClipboard(); setTimeout(saveToDatabase, 500);"
                       id="btnn" class="flex-1 py-2.5 px-4 text-sm rounded-lg font-semibold transition-colors flex items-center justify-center gap-2 bg-blue-600 text-white hover:bg-blue-700"
                    >
                        📋 Copy ke Clipboard
                    </button>
                
                </div>
            </div>
        </div>

        <!-- Pengeluaran Modal -->
        <div id="pengeluaranModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Pengeluaran</h3>
                <form id="pengeluaranForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <input
                                type="text"
                                id="pengeluaran_desc"
                                class="w-full p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Masukkan keterangan pengeluaran..."
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                <input
                                    type="number"
                                    id="pengeluaran_amount"
                                    class="w-full p-2.5 pl-8 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0"
                                    required
                                />
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium"
                        >
                            Tambah
                        </button>
                        <button
                            type="button"
                            onclick="hideModal('pengeluaranModal')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors font-medium"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Pemasukan Modal -->
        <div id="pemasukanModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Pemasukan</h3>
                <form id="pemasukanForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <input
                                type="text"
                                id="pemasukan_desc"
                                class="w-full p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Masukkan keterangan pemasukan..."
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                <input
                                    type="number"
                                    id="pemasukan_amount"
                                    class="w-full p-2.5 pl-8 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0"
                                    required
                                />
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                        >
                            Tambah
                        </button>
                        <button
                            type="button"
                            onclick="hideModal('pemasukanModal')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors font-medium"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        // Fungsi bantu: parsing angka termasuk desimal dengan koma/titik
        function parseInputNumber(value) {
            if (!value) return 0;
            value = value.toString().replace(',', '.'); // ubah koma jadi titik
            return parseFloat(value) || 0;
        }

            // Global data storage
            let appData = {
                pengeluaran: [],
                pemasukan: [],
                calculations: {
                    totalLiter: 0,
                    cash: 0,
                    total: 0,
                    totalPengeluaran: 0,
                    totalPemasukan: 0,
                    totalKeseluruhan: 0
                }
            };

            // Format number with comma as decimal separator and dot as thousand separator
            function formatNumber(number, decimals = 0) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }).format(number);
            }

            // Format rupiah
            function formatRupiah(amount) {
                return formatNumber(amount, 0);
            }

            // Calculate all values
            function calculateAll() {
                const nomorAwal = parseInputNumber(document.getElementById('nomor_awal').value);
                const nomorAkhir = parseInputNumber(document.getElementById('nomor_akhir').value);
                const qris = parseInt(document.getElementById('qris').value) || 0;

                // Calculate total liter
                const totalLiter = Math.max(0, nomorAkhir - nomorAwal);
                appData.calculations.totalLiter = totalLiter;

                // Calculate total setoran
                const total = totalLiter * 11500;
                appData.calculations.total = total;

                // Calculate cash
                const cash = Math.max(0, total - qris);
                appData.calculations.cash = cash;

                // Calculate totals
                appData.calculations.totalPengeluaran = appData.pengeluaran.reduce((sum, item) => sum + item.amount, 0);
                appData.calculations.totalPemasukan = appData.pemasukan.reduce((sum, item) => sum + item.amount, 0);
                appData.calculations.totalKeseluruhan = cash + appData.calculations.totalPemasukan - appData.calculations.totalPengeluaran;

                updateDisplay();
            }

        // Fungsi validasi terpusat (Pastikan fungsi ini sudah ada di <script> Anda)
    function isDataValid() {
    // Memastikan elemen ada dan memiliki nilai
    const storeID = document.getElementById('store_id')?.value;
    const employeeID = document.getElementById('employee_id')?.value;
    const jamMasuk = document.getElementById('jam_masuk')?.value;
    const jamKeluar = document.getElementById('jam_keluar')?.value;
    
    // Total Liter harus lebih dari 0
    const totalLiter = appData.calculations.totalLiter;
    
    // Cek semua field wajib dan total liter > 0
    if (!storeID || !employeeID || !jamMasuk || !jamKeluar || totalLiter <= 0) {
        return false;
    }

    return true;
}

    // Update display
    function updateDisplay() {
        const calc = appData.calculations;
        const isValid = isDataValid(); // Dapatkan status validasi

        // Update meter display
        document.getElementById('total_liter_display').value = formatNumber(calc.totalLiter, 2) + ' L';
        document.getElementById('total_liter_badge').textContent = formatNumber(calc.totalLiter, 2) + ' L';

        // Update setoran display
        document.getElementById('cash_display').value = formatRupiah(calc.cash);
        document.getElementById('total_display').value = formatRupiah(calc.total);
        document.getElementById('total_badge').textContent = formatRupiah(calc.total);

        // Update totals
        document.getElementById('total_pengeluaran').textContent = 'Rp ' + formatRupiah(calc.totalPengeluaran);
        document.getElementById('total_pemasukan').textContent = 'Rp ' + formatRupiah(calc.totalPemasukan);
        document.getElementById('total_keseluruhan').textContent = 'Rp ' + formatRupiah(calc.totalKeseluruhan);
        document.getElementById('total_breakdown').textContent = 
            `Setoran: ${formatRupiah(calc.cash)} + Pemasukan: ${formatRupiah(calc.totalPemasukan)} - Pengeluaran: ${formatRupiah(calc.totalPengeluaran)}`;

        // Update jam kerja display
        updateJamKerjaDisplay();

       const btnCopy = document.getElementById('btnn');
    const btnSave = document.getElementById('btnn'); 

    if (btnCopy) {
        btnCopy.disabled = !isValid; // Tombol nonaktif jika TIDAK valid
        btnCopy.classList.toggle('bg-gray-400', !isValid);
        btnCopy.classList.toggle('hover:bg-blue-700', isValid);
    }
    
    if (btnSave) {
        btnSave.disabled = !isValid; // Tombol nonaktif jika TIDAK valid
        btnSave.classList.toggle('bg-gray-400', !isValid);
        btnSave.classList.toggle('hover:bg-green-700', isValid);
    }
        // ------------------------------------------------------------
    }

            // Update jam kerja display
            function updateJamKerjaDisplay() {
                const jamMasuk = document.getElementById('jam_masuk').value;
                const jamKeluar = document.getElementById('jam_keluar').value;
                const display = document.getElementById('jam_kerja_display');
                const text = document.getElementById('jam_kerja_text');

                if (jamMasuk && jamKeluar) {
                    text.textContent = `(${jamMasuk} - ${jamKeluar})`;
                    display.classList.remove('hidden');
                } else {
                    display.classList.add('hidden');
                }
            }

            // Render pengeluaran list
            function renderPengeluaranList() {
                const container = document.getElementById('pengeluaran_list');
                container.innerHTML = '';

                appData.pengeluaran.forEach((item, index) => {
                    const div = document.createElement('div');
                    div.className = 'flex justify-between items-center p-2.5 bg-red-50 rounded-lg mb-2';
                    div.innerHTML = `
                        <span class="text-sm text-gray-700">${item.description}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-red-600 font-semibold">Rp ${formatRupiah(item.amount)}</span>
                            <button onclick="removePengeluaran(${index})" class="text-red-600 hover:text-red-800 text-sm">✕</button>
                        </div>
                    `;
                    container.appendChild(div);
                });
            }

            // Render pemasukan list
            function renderPemasukanList() {
                const container = document.getElementById('pemasukan_list');
                container.innerHTML = '';

                appData.pemasukan.forEach((item, index) => {
                    const div = document.createElement('div');
                    div.className = 'flex justify-between items-center p-2.5 bg-green-50 rounded-lg mb-2';
                    div.innerHTML = `
                        <span class="text-sm text-gray-700">${item.description}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-green-600 font-semibold">Rp ${formatRupiah(item.amount)}</span>
                            <button onclick="removePemasukan(${index})" class="text-red-600 hover:text-red-800 text-sm">✕</button>
                        </div>
                    `;
                    container.appendChild(div);
                });
            }

            // Add pengeluaran
            function addPengeluaran(description, amount) {
                appData.pengeluaran.push({ description, amount });
                renderPengeluaranList();
                calculateAll();
            }

            // Remove pengeluaran
            function removePengeluaran(index) {
                appData.pengeluaran.splice(index, 1);
                renderPengeluaranList();
                calculateAll();
            }

            // Add pemasukan
            function addPemasukan(description, amount) {
                appData.pemasukan.push({ description, amount });
                renderPemasukanList();
                calculateAll();
            }

            // Remove pemasukan
            function removePemasukan(index) {
                appData.pemasukan.splice(index, 1);
                renderPemasukanList();
                calculateAll();
            }

            // Modal functions
            function showModal(modalId) {
                document.getElementById(modalId).classList.add('show');
            }

            function hideModal(modalId) {
                document.getElementById(modalId).classList.remove('show');
                // Clear form fields
                if (modalId === 'pengeluaranModal') {
                    document.getElementById('pengeluaran_desc').value = '';
                    document.getElementById('pengeluaran_amount').value = '';
                } else if (modalId === 'pemasukanModal') {
                    document.getElementById('pemasukan_desc').value = '';
                    document.getElementById('pemasukan_amount').value = '';
                }
            }

        // Generate report text
    function generateReport() {
        // 1. Ambil Nama Karyawan dari elemen 'employee_id' yang aktif
        const employeeSelect = document.getElementById('employee_id');
        // Cek apakah elemen ada, dan apakah ada opsi yang dipilih, lalu ambil Teks-nya (Nama)
        const namaKaryawan = employeeSelect 
            ? employeeSelect.options[employeeSelect.selectedIndex]?.textContent || ''
            : '';
            
        // 2. Ambil data Jam Kerja
        const jamMasuk = document.getElementById('jam_masuk').value || '';
        const jamKeluar = document.getElementById('jam_keluar').value || '';
        
        // 3. Ambil data Meter & QRIS (Menggunakan optional chaining '?' untuk keamanan)
        const nomorAwal = parseFloat(document.getElementById('nomor_awal')?.value.replace(',', '.')) || 0;
        const nomorAkhir = parseFloat(document.getElementById('nomor_akhir')?.value.replace(',', '.')) || 0;
        const qris = parseInt(document.getElementById('qris')?.value) || 0;

        // 4. Hitung dan Format Data
        const calc = appData.calculations;
        const jamKerja = (jamMasuk && jamKeluar) ? `(${jamMasuk} - ${jamKeluar})` : '';
        
        let report = "*Setoran Harian* 📋\n";
        report += "<?= getCurrentDate() ?>\n";
        report += `🤦‍♀️ Nama: ${namaKaryawan}\n`;
        report += `🕐 Jam: ${jamKerja}\n\n`;
        
        report += "⛽ Data Meter\n";
        report += `• Nomor Awal : ${nomorAwal.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}\n`;
        report += `• Nomor Akhir: ${nomorAkhir.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}\n`;
        report += `• Total Liter: ${calc.totalLiter.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} L\n\n`;
        
        report += "💰 Setoran\n";
        report += `• Cash  : Rp ${formatRupiah(calc.cash)}\n`;
        report += `• QRIS  : Rp ${formatRupiah(qris)}\n`;
        report += `• Total : Rp ${formatRupiah(calc.total)}\n\n`;
        
        if (appData.pengeluaran.length > 0) {
            report += "💸 Pengeluaran (PU)\n";
            appData.pengeluaran.forEach(item => {
                report += `• ${item.description}: Rp ${formatRupiah(item.amount)}\n`;
            });
            report += `Total Pengeluaran: Rp ${formatRupiah(calc.totalPengeluaran)}\n\n`;
        }
        
        if (appData.pemasukan.length > 0) {
            report += "💵 Pemasukan (PU)\n";
            appData.pemasukan.forEach(item => {
                report += `• ${item.description}: Rp ${formatRupiah(item.amount)}\n`;
            });
            report += `Total Pemasukan: Rp ${formatRupiah(calc.totalPemasukan)}\n\n`;
        }
        
        report += `💼 Total Keseluruhan: Rp ${formatRupiah(calc.totalKeseluruhan)}`;
        
        return report;
    }
            // Copy to clipboard
            function copyToClipboard() {
                const reportText = generateReport();
                
                navigator.clipboard.writeText(reportText).then(() => {
                }).catch(() => {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = reportText;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                });
            }

        // Save to database
    async function saveToDatabase() {
        // 1. Panggil Validasi Terpusat (Wajib & Konsisten dengan tombol Copy/Save)
        if (!isDataValid()) {
            // Alert ini berfungsi sebagai lapisan keamanan jika tombol disabled dibypass.
            alert('Mohon lengkapi semua data sebelum Salin!');
            return;
        }

        // 2. Deklarasi Variabel (Menggunakan Optional Chaining '?' untuk keamanan)
        const storeID = document.getElementById('store_id')?.value; 
        const employeeID = document.getElementById('employee_id')?.value;
        
        const jamMasuk = document.getElementById('jam_masuk')?.value;
        const jamKeluar = document.getElementById('jam_keluar')?.value;
        
        // Ambil nilai dari input, gunakan parseInputNumber (asumsi fungsi ini sudah Anda buat)
        const nomorAwal = parseInputNumber(document.getElementById('nomor_awal')?.value);
        const nomorAkhir = parseInputNumber(document.getElementById('nomor_akhir')?.value);
        
        const qrisInput = document.getElementById('qris')?.value;
        const qris = parseInt(qrisInput) || 0; // Pastikan QRIS adalah integer

        // VALIDASI MANUAL LAMA TELAH DIHAPUS DI SINI.
        
        // 3. Objek Data untuk Dikirim ke API
        const data = {
            store_id: storeID, 
            employee_id: employeeID,
            jam_masuk: jamMasuk,
            jam_keluar: jamKeluar,
            nomor_awal: nomorAwal,
            nomor_akhir: nomorAkhir,
            total_liter: appData.calculations.totalLiter,
            qris: qris,
            cash: appData.calculations.cash,
            total_setoran: appData.calculations.totalSetoran, // Menggunakan totalSetoran (Cash + QRIS)
            total_pengeluaran: appData.calculations.totalPengeluaran,
            total_pemasukan: appData.calculations.totalPemasukan,
            total_keseluruhan: appData.calculations.totalKeseluruhan,
            pengeluaran: appData.pengeluaran,
            pemasukan: appData.pemasukan
        };

        // 4. Proses Kirim Data (Fetch API)
        try {
            const response = await fetch('api.php?action=save_setoran', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                alert('Data berhasil disalin ke clipboard!');
                // Opsional: Muat ulang halaman atau reset form setelah sukses
                // window.location.reload(); 
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error: Gagal terhubung ke server. ' + error.message);
        }
    }
    // Memuat data Store
    async function fetchAndPopulateStores() {
        try {
            const response = await fetch('api.php?action=get_stores');
            const result = await response.json();
            const storeSelect = document.getElementById('store_id');
            
            if (result.success && result.data.length > 0) {
                storeSelect.innerHTML = '<option value="" disabled selected>Pilih Store</option>';
                result.data.forEach(store => {
                    const option = document.createElement('option');
                    option.value = store.id; 
                    option.textContent = store.store_name;
                    storeSelect.appendChild(option);
                });
                storeSelect.disabled = false;
            }
        } catch (error) {
            console.error('Error fetching stores:', error);
        }
    }

    // Memuat data Employee berdasarkan Store yang dipilih
    async function fetchAndPopulateEmployees(store_id) {
        const employeeSelect = document.getElementById('employee_id');
        employeeSelect.innerHTML = '<option value="" disabled selected>Memuat Karyawan...</option>';
        employeeSelect.disabled = true;

        try {
            const response = await fetch(`api.php?action=get_employees&store_id=${store_id}`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                employeeSelect.innerHTML = '<option value="" disabled selected>Pilih Karyawan</option>';
                result.data.forEach(employee => {
                    const option = document.createElement('option');
                    option.value = employee.id; // Kirim ID Karyawan
                    option.textContent = employee.employee_name;
                    employeeSelect.appendChild(option);
                });
                employeeSelect.disabled = false;
            } else {
                employeeSelect.innerHTML = '<option value="" disabled selected>Tidak ada Karyawan aktif</option>';
            }
        } catch (error) {
            console.error('Error fetching employees:', error);
        }
    }

    // Event Listener untuk Store
    document.getElementById('store_id').addEventListener('change', function() {
        const selectedStoreId = this.value;
        if (selectedStoreId) {
            fetchAndPopulateEmployees(selectedStoreId);
        } else {
            document.getElementById('employee_id').innerHTML = '<option value="" disabled selected>Pilih Store Dulu</option>';
            document.getElementById('employee_id').disabled = true;
        }
    });

    // Panggil fungsi Store saat halaman dimuat
    document.addEventListener('DOMContentLoaded', fetchAndPopulateStores);

   // Fungsi untuk memuat data Store (sudah dikoreksi agar menggunakan ID 'store_id')
async function fetchAndPopulateStores() {
    const storeSelect = document.getElementById('store_id'); // Menggunakan ID yang benar
    if (!storeSelect) return; 

    storeSelect.innerHTML = '<option value="" disabled selected>Memuat Store...</option>';
    storeSelect.disabled = true;

    try {
        // Asumsi API endpoint sudah disesuaikan menjadi get_stores
        const response = await fetch(`api.php?action=get_stores`); 
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            storeSelect.innerHTML = '<option value="" disabled selected>Pilih Store</option>';
            result.data.forEach(store => {
                const option = document.createElement('option');
                option.value = store.id; 
                option.textContent = store.store_name; 
                storeSelect.appendChild(option);
            });
            storeSelect.disabled = false;
        } else {
            storeSelect.innerHTML = '<option value="" disabled selected>Tidak ada Store</option>';
        }
    } catch (error) {
        console.error('Error fetching stores:', error);
        storeSelect.innerHTML = '<option value="" disabled selected>Gagal memuat Store</option>';
    }
    // PENTING: Panggil calculateAll untuk inisialisasi status tombol
    calculateAll();
}

// Blok DOMContentLoaded tunggal yang sudah bersih dan benar
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initial Data Load: Memuat Stores
    fetchAndPopulateStores();

    // 2. Listener untuk Dropdown Store (Mengganti 'outlet_id' menjadi 'store_id')
    document.getElementById('store_id')?.addEventListener('change', function() {
        const selectedStoreId = this.value;
        if (selectedStoreId) {
            // Ganti 'fetchAndPopulateKaryawan' menjadi 'fetchAndPopulateEmployees'
            fetchAndPopulateEmployees(selectedStoreId); 
        } else {
            // Ganti 'nama_karyawan' menjadi 'employee_id'
            const employeeSelect = document.getElementById('employee_id');
            if (employeeSelect) {
                employeeSelect.innerHTML = '<option value="" disabled selected>Pilih Store Dulu</option>';
                employeeSelect.disabled = true;
            }
        }
        // PENTING: Hitung ulang untuk menjalankan updateDisplay() dan kontrol tombol
        calculateAll();
    });

    // 3. Input event listeners for live calculation (sudah benar)
    ['nomor_awal', 'nomor_akhir', 'qris'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', calculateAll);
    });

    // 4. Jam kerja event listeners
    ['jam_masuk', 'jam_keluar'].forEach(id => {
        // Memastikan updateJamKerjaDisplay dan calculateAll dipanggil
        document.getElementById(id)?.addEventListener('change', updateJamKerjaDisplay);
        document.getElementById(id)?.addEventListener('change', calculateAll); 
    });

    // 5. Form submissions (Tambahkan calculateAll setelah penambahan/pengurangan)
    document.getElementById('pengeluaranForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const desc = document.getElementById('pengeluaran_desc').value.trim();
        const amount = parseInt(document.getElementById('pengeluaran_amount').value) || 0;
        
        if (desc && amount > 0) {
            addPengeluaran(desc, amount);
            hideModal('pengeluaranModal');
            calculateAll(); // PENTING: Update total dan tombol
        }
    });

    document.getElementById('pemasukanForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const desc = document.getElementById('pemasukan_desc').value.trim();
        const amount = parseInt(document.getElementById('pemasukan_amount').value) || 0;
        
        if (desc && amount > 0) {
            addPemasukan(desc, amount);
            hideModal('pemasukanModal');
            calculateAll(); // PENTING: Update total dan tombol
        }
    });

    // 6. Close modal when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('show');
        }
    });

    // 7. Initial calculation (Paling akhir)
    calculateAll(); 
});
        </script>
    </body>
    </html>