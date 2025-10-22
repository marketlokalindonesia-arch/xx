<?php
session_start();
// Cek sesi login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil data filter dari URL atau set default ke bulan/tahun saat ini
$current_month = date('m');
$current_year = date('Y');

$months_data = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Menghasilkan tahun dari 3 tahun ke belakang sampai tahun depan
$years_data = range($current_year - 2, $current_year + 1);

$selected_month = $_GET['month'] ?? $current_month;
$selected_year = $_GET['year'] ?? $current_year;
$selected_employee_id = $_GET['employee_id'] ?? '';
$selected_store_id = $_GET['store_id'] ?? ''; 

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin | Setoran Harian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Gaya dasar untuk modal */
        .modal {
            background-color: rgba(0, 0, 0, 0.5);
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s;
        }

        .modal.show {
            visibility: visible;
            opacity: 1;
        }

        /* Peningkatan UI untuk Tab */
        .tab-button {
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease;
        }

        .active-tab {
            border-bottom-color: #4f46e5;
            color: #4f46e5;
            font-weight: 600;
        }

        /* Untuk tampilan penuh yang lebih bersih */
        .content-wrapper {
            max-width: 1400px;
            /* Batas lebar baru, lebih lebar dari 7xl (1280px) */
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">

        <header class="bg-white shadow-lg sticky top-0 z-10 border-b border-gray-200">
            <div class="content-wrapper px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <h1 class="text-3xl font-extrabold text-indigo-700">Admin Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700 text-sm">Masuk sebagai: <span class="font-semibold">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span></span>
                    <button id="logoutBtn"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-full text-sm font-medium transition duration-200">
                        <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        Logout
                    </button>
                </div>
            </div>

            <nav class="bg-gray-50 border-t border-gray-200">
                <div class="content-wrapper px-4 sm:px-6 lg:px-8">
                    <div class="flex space-x-6">
                        <button class="tab-button py-3 px-1 text-sm text-gray-700 hover:text-indigo-600 active-tab"
                            data-tab="dashboard">Dashboard Wallet</button>
                        <button class="tab-button py-3 px-1 text-sm text-gray-700 hover:text-indigo-600"
                            data-tab="setoran">History Setoran</button>
                        <button class="tab-button py-3 px-1 text-sm text-gray-700 hover:text-indigo-600"
                            data-tab="cashflow">Manajemen Kas</button>
                        <button class="tab-button py-3 px-1 text-sm text-gray-700 hover:text-indigo-600"
                            data-tab="stores">Manajemen Store</button>
                        <button class="tab-button py-3 px-1 text-sm text-gray-700 hover:text-indigo-600"
                            data-tab="employees">Manajemen Karyawan</button>
                    </div>
                </div>
            </nav>
        </header>

        <main class="w-full px-4 sm:px-6 lg:px-8 py-8 flex-grow">
            <div class="content-wrapper">

                <div id="tab-dashboard" class="tab-content">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Dashboard Wallet & Cashflow</h2>

                    <form id="filterFormDashboard"
                        class="bg-white p-5 rounded-xl shadow-md mb-8 flex flex-wrap gap-4 items-end">
                        <div class="w-full sm:w-auto">
                            <label for="filter_month_dashboard"
                                class="block text-xs font-medium text-gray-500 mb-1">Bulan</label>
                            <select id="filter_month_dashboard" name="month"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm">
                                <?php foreach ($months_data as $key => $value) : ?>
                                <option value="<?php echo $key; ?>" <?php echo ($selected_month==$key) ? 'selected' : ''
                                    ; ?>>
                                    <?php echo $value; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <label for="filter_year_dashboard"
                                class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                            <select id="filter_year_dashboard" name="year"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm">
                                <?php foreach ($years_data as $year) : ?>
                                <option value="<?php echo $year; ?>" <?php echo ($selected_year==$year) ? 'selected'
                                    : '' ; ?>>
                                    <?php echo $year; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4 md:mb-0 md:mr-3">
                            <label for="filter_store_dashboard" class="block text-sm font-medium text-gray-700">Filter
                                Store</label>
                            <select id="filter_store_dashboard" name="store_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">Semua Store</option>
                            </select>
                        </div>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg shadow-md text-sm transition duration-200">
                            Terapkan Filter
                        </button>
                        <button type="button" id="resetFilterBtnDashboard"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2.5 rounded-lg shadow-md text-sm transition duration-200">
                            Reset
                        </button>
                        <div class="ml-auto flex gap-2">
                            <button type="button" onclick="exportDashboard('pdf')"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg shadow-md text-sm transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Export PDF
                            </button>
                            <button type="button" onclick="exportDashboard('excel')"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg shadow-md text-sm transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Export Excel
                            </button>
                        </div>
                    </form>

                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-6 rounded-xl shadow-xl mb-8">
                        <h3 class="text-xl font-bold mb-4">Wallet Utama (All Stores)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <p class="text-sm opacity-90">Total Pemasukan</p>
                                <p id="wallet_all_income" class="text-2xl font-bold mt-1">Rp 0</p>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <p class="text-sm opacity-90">Total Pengeluaran</p>
                                <p id="wallet_all_expense" class="text-2xl font-bold mt-1">Rp 0</p>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <p class="text-sm opacity-90">Saldo Bersih</p>
                                <p id="wallet_all_balance" class="text-2xl font-bold mt-1">Rp 0</p>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <p class="text-sm opacity-90">Total Liter Terjual</p>
                                <p id="wallet_all_liter" class="text-2xl font-bold mt-1">0 L</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-xl shadow-md">
                            <h3 class="text-lg font-bold mb-4 text-gray-800">Breakdown Pengeluaran</h3>
                            <div id="expense_breakdown" class="space-y-2"></div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-md">
                            <h3 class="text-lg font-bold mb-4 text-gray-800">Breakdown Pemasukan</h3>
                            <div id="income_breakdown" class="space-y-2"></div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-md">
                        <h3 class="text-lg font-bold mb-4 text-gray-800">Wallet Per Store</h3>
                        <div id="wallet_per_store" class="space-y-4"></div>
                    </div>
                </div>


                <div id="tab-setoran" class="tab-content">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Riwayat Setoran Harian</h2>

                    <!-- FILTER FORM -->
                    <form id="filterFormSetoran"
                        class="bg-white p-5 rounded-xl shadow-md mb-8 flex flex-wrap gap-4 items-end">
                        <div class="w-full sm:w-auto flex-grow">
                            <label for="filter_store_id_setoran"
                                class="block text-xs font-medium text-gray-500 mb-1">Store</label>
                            <select id="filter_store_id_setoran" name="store_id"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm">
                                <option value="">Semua Store</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-auto flex-grow">
                            <label for="filter_employee_id_setoran"
                                class="block text-xs font-medium text-gray-500 mb-1">Karyawan</label>
                            <select id="filter_employee_id_setoran" name="employee_id"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm">
                                <option value="">Semua Karyawan</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <label for="filter_month_setoran"
                                class="block text-xs font-medium text-gray-500 mb-1">Bulan</label>
                            <select id="filter_month_setoran" name="month"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm">
                                <?php foreach ($months_data as $key => $value) : ?>
                                <option value="<?php echo $key; ?>" <?php echo ($selected_month==$key) ? 'selected' : ''
                                    ; ?>>
                                    <?php echo $value; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <label for="filter_year_setoran"
                                class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                            <select id="filter_year_setoran" name="year"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm">
                                <?php foreach ($years_data as $year) : ?>
                                <option value="<?php echo $year; ?>" <?php echo ($selected_year==$year) ? 'selected'
                                    : '' ; ?>>
                                    <?php echo $year; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg shadow-md text-sm transition duration-200">
                            Terapkan Filter
                        </button>
                        <button type="button" id="resetFilterBtnSetoran"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2.5 rounded-lg shadow-md text-sm transition duration-200">
                            Reset
                        </button>
                    </form>

                    <!-- SUMMARY -->
                    <div id="summaryCardSetoran" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                        <div class="bg-white p-5 rounded-xl shadow-md border-l-4 border-indigo-500">
                            <p class="text-sm font-medium text-gray-500">Total Liter</p>
                            <p id="sum_liter" class="text-2xl font-bold text-indigo-700 mt-1">...</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl shadow-md border-l-4 border-green-500">
                            <p class="text-sm font-medium text-gray-500">Total Cash</p>
                            <p id="sum_cash" class="text-2xl font-bold text-green-700 mt-1">...</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl shadow-md border-l-4 border-blue-500">
                            <p class="text-sm font-medium text-gray-500">Total QRIS</p>
                            <p id="sum_qris" class="text-2xl font-bold text-blue-700 mt-1">...</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl shadow-md border-l-4 border-red-500">
                            <p class="text-sm font-medium text-gray-500">Total Pengeluaran</p>
                            <p id="sum_pengeluaran" class="text-2xl font-bold text-red-700 mt-1">...</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl shadow-md border-l-4 border-orange-500">
                            <p class="text-sm font-medium text-gray-500">Total Keseluruhan</p>
                            <p id="sum_total_keseluruhan" class="text-2xl font-bold text-orange-700 mt-1">...</p>
                        </div>
                    </div>

                    <!-- TABEL SETORAN LENGKAP -->
                    <div class="bg-white p-6 rounded-xl shadow-md overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">Tanggal</th>
                                    <th class="px-4 py-2 text-left font-semibold">Karyawan</th>
                                    <th class="px-4 py-2 text-left font-semibold">Store</th>
                                    <th class="px-4 py-2 text-center font-semibold">Jam Masuk</th>
                                    <th class="px-4 py-2 text-center font-semibold">Jam Keluar</th>
                                    <th class="px-4 py-2 text-right font-semibold">No Awal</th>
                                    <th class="px-4 py-2 text-right font-semibold">No Akhir</th>
                                    <th class="px-4 py-2 text-right font-semibold">Total Liter</th>
                                    <th class="px-4 py-2 text-right font-semibold">QRIS</th>
                                    <th class="px-4 py-2 text-right font-semibold">Cash</th>
                                    <th class="px-4 py-2 text-right font-semibold">Total Setoran</th>
                                    <th class="px-4 py-2 text-right font-semibold">Total Pengeluaran</th>
                                    <th class="px-4 py-2 text-right font-semibold">Total Pemasukan</th>
                                    <th class="px-4 py-2 text-right font-semibold">Total Keseluruhan</th>
                                    <th class="px-4 py-2 text-center font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="setoranTableBody" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="15" class="text-center py-6 text-gray-500">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>


                    <div id="detailModal"
                        class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
                        <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-white">Detail Setoran</h3>
                                            <p class="text-blue-100 text-sm">Informasi lengkap setoran harian</p>
                                        </div>
                                    </div>
                                    <button onclick="hideModal('detailModal')"
                                        class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition-all duration-200">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Loading -->
                            <div id="detailLoading" class="p-8 text-center">
                                <div
                                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4">
                                </div>
                                <p class="text-gray-600 font-medium">Memuat rincian setoran...</p>
                            </div>

                            <!-- Content -->
                            <div id="detailContent" class="hidden">
                                <div class="p-6 space-y-6">
                                    <!-- Informasi Utama -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Data Karyawan & Toko -->
                                        <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                                            <h4 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                    </path>
                                                </svg>
                                                Data Karyawan & Toko
                                            </h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">ID Setoran:</span>
                                                    <span id="detail_id" class="font-semibold text-gray-800"></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Tanggal:</span>
                                                    <span id="detail_tanggal"
                                                        class="font-semibold text-gray-800"></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Karyawan:</span>
                                                    <span id="detail_employee"
                                                        class="font-semibold text-gray-800"></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Store:</span>
                                                    <span id="detail_store" class="font-semibold text-gray-800"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Jam Kerja -->
                                        <div class="bg-purple-50 rounded-xl p-4 border border-purple-100">
                                            <h4 class="font-semibold text-purple-800 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Jam Kerja
                                            </h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Jam Masuk:</span>
                                                    <span id="detail_jam_masuk"
                                                        class="font-semibold text-gray-800"></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Jam Keluar:</span>
                                                    <span id="detail_jam_keluar"
                                                        class="font-semibold text-gray-800"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Data Meter & Setoran -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Data Meter -->
                                        <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                                            <h4 class="font-semibold text-green-800 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                    </path>
                                                </svg>
                                                Data Meter
                                            </h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">No. Awal:</span>
                                                    <span id="detail_nomor_awal"
                                                        class="font-semibold text-gray-800"></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">No. Akhir:</span>
                                                    <span id="detail_nomor_akhir"
                                                        class="font-semibold text-gray-800"></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Total Liter:</span>
                                                    <span id="detail_total_liter"
                                                        class="font-semibold text-green-700"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Setoran -->
                                        <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100">
                                            <h4 class="font-semibold text-yellow-800 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                                    </path>
                                                </svg>
                                                Setoran
                                            </h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Cash:</span>
                                                    <span id="detail_cash" class="font-semibold text-gray-800"></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">QRIS:</span>
                                                    <span id="detail_qris" class="font-semibold text-gray-800"></span>
                                                </div>
                                                <div class="flex justify-between border-t pt-2">
                                                    <span class="text-gray-600 font-medium">Total Setoran:</span>
                                                    <span id="detail_total_setoran"
                                                        class="font-bold text-yellow-700"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pengeluaran & Pemasukan -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Pengeluaran -->
                                        <div class="bg-red-50 rounded-xl p-4 border border-red-100">
                                            <h4 class="font-semibold text-red-800 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                                </svg>
                                                Pengeluaran (PU)
                                            </h4>
                                            <div class="space-y-2 text-sm mb-3">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Total Pengeluaran:</span>
                                                    <span id="detail_total_pengeluaran"
                                                        class="font-bold text-red-700"></span>
                                                </div>
                                            </div>
                                            <ul id="detail_pengeluaran_list" class="space-y-2 max-h-32 overflow-y-auto">
                                            </ul>
                                        </div>

                                        <!-- Pemasukan -->
                                        <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                                            <h4 class="font-semibold text-emerald-800 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                                </svg>
                                                Pemasukan (PU)
                                            </h4>
                                            <div class="space-y-2 text-sm mb-3">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Total Pemasukan:</span>
                                                    <span id="detail_total_pemasukan"
                                                        class="font-bold text-emerald-700"></span>
                                                </div>
                                            </div>
                                            <ul id="detail_pemasukan_list" class="space-y-2 max-h-32 overflow-y-auto">
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Total Keseluruhan -->
                                    <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-xl p-6 text-white">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="bg-white bg-opacity-20 p-3 rounded-xl">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-lg">Total Keseluruhan</h4>
                                                    <p class="text-gray-300 text-sm">Setoran + Pemasukan - Pengeluaran
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div id="detail_total_keseluruhan"
                                                    class="text-2xl font-bold text-white">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-cashflow" class="tab-content">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Manajemen Kas: Pemasukan & Pengeluaran</h2>

                    <!-- Filter Form -->
                    <form id="filterFormCashflow"
                        class="bg-white p-5 rounded-xl shadow-md mb-8 flex flex-wrap gap-4 items-end">
                        <div class="w-full sm:w-auto">
                            <label for="filter_month_cashflow"
                                class="block text-xs font-medium text-gray-500 mb-1">Bulan</label>
                            <select id="filter_month_cashflow" name="month"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm">
                                <?php foreach ($months_data as $key => $value) : ?>
                                <option value="<?php echo $key; ?>" <?php echo ($selected_month==$key) ? 'selected' : ''
                                    ; ?>>
                                    <?php echo $value; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <label for="filter_year_cashflow"
                                class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                            <select id="filter_year_cashflow" name="year"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm">
                                <?php foreach ($years_data as $year) : ?>
                                <option value="<?php echo $year; ?>" <?php echo ($selected_year==$year) ? 'selected'
                                    : '' ; ?>>
                                    <?php echo $year; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4 md:mb-0 md:mr-3">
                            <label for="filter_store_cashflow" class="block text-sm font-medium text-gray-700">Filter
                                Store</label>
                            <select id="filter_store_cashflow" name="store_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">Semua Store</option>
                            </select>
                        </div>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg shadow-md text-sm transition duration-200">
                            Terapkan Filter
                        </button>
                    </form>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 mb-6">
                        <button onclick="showCashFlowModal()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-md text-sm transition duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Tambah Transaksi Kas
                        </button>
                        <button onclick="exportCashflow('excel')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md text-sm transition duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Export Excel
                        </button>
                    </div>

                    <!-- Data Table -->
                    <div class="bg-white p-6 rounded-xl shadow-md">
                        <h3 class="text-lg font-bold mb-4 text-gray-800">Daftar Transaksi Kas</h3>

                        <!-- Summary Cards -->
                        <div class="grid grid-cols-3 gap-4 mb-4 text-center">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <p class="text-sm text-green-700">Total Pemasukan</p>
                                <p id="cashflow_total_income" class="text-xl font-bold text-green-700">Rp 0</p>
                            </div>
                            <div class="p-3 bg-red-100 rounded-lg">
                                <p class="text-sm text-red-700">Total Pengeluaran</p>
                                <p id="cashflow_total_expense" class="text-xl font-bold text-red-700">Rp 0</p>
                            </div>
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <p class="text-sm text-indigo-700">Saldo Akhir</p>
                                <p id="cashflow_balance" class="text-xl font-bold text-indigo-700">Rp 0</p>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-left w-[100px]">
                                            Tgl</th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-left w-[160px]">
                                            Store</th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">
                                            Deskripsi</th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center w-[120px]">
                                            Jenis</th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right w-[140px]">
                                            Nominal</th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center w-[100px]">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cashflowTableBody" class="bg-white divide-y divide-gray-200 text-sm">
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-gray-400">Silakan terapkan filter
                                            untuk memuat data.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 💰 Modal Cashflow -->
                    <div id="modalCashflow"
                        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                        <div class="relative top-10 mx-auto p-4 w-full max-w-md">
                            <div class="bg-white rounded-lg shadow-xl">
                                <!-- Header -->
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900" id="modalCashflowTitle">
                                        Tambah Transaksi Kas
                                    </h3>
                                </div>

                                <!-- Form Content -->
                                <div class="px-6 py-4">
                                    <form id="cashflowForm" method="POST" action="api.php">
                                        <input type="hidden" id="cashflow_id" name="id">

                                        <!-- 🗓️ Tanggal -->
                                        <div class="mb-4">
                                            <label for="cashflow_date"
                                                class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                            <input type="date" id="cashflow_date" name="tanggal" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>

                                        <!-- 🏪 Store -->
                                        <div  id="storeSelectContainer" class="mb-4">
                                            <label for="cashflow_store_id"
                                                class="block text-sm font-medium text-gray-700 mb-1">Store</label>
                                            <select id="cashflow_store_id" name="store_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">Pilih Store</option>
                                                <?php
                                require_once 'config.php';
                                $stores = $pdo->query('SELECT id, store_name FROM stores')->fetchAll();
                                foreach ($stores as $s) {
                                    echo "<option value='{$s['id']}'>" . htmlspecialchars($s['store_name']) . "</option>";
                                }
                                ?>
                                            </select>
                                        </div>

                                        <!-- 💸 Jenis Transaksi -->
                                        <div class="mb-4">
                                            <label for="cashflow_type"
                                                class="block text-sm font-medium text-gray-700 mb-1">Jenis
                                                Transaksi</label>
                                            <select id="cashflow_type" name="type" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="pemasukan">Pemasukan</option>
                                                <option value="pengeluaran">Pengeluaran</option>
                                            </select>
                                        </div>

                                        <!-- 🧾 Kategori -->
                                        <div class="mb-4">
                                            <label for="cashflow_category"
                                                class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                            <select id="cashflow_category" name="category" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">-- Pilih Kategori --</option>
                                                <option value="bbm">BBM</option>
                                                <option value="operasional">Operasional</option>
                                                <option value="penjualan">Penjualan</option>
                                                <option value="gaji">Gaji</option>
                                                <option value="lainnya">Lainnya</option>
                                            </select>
                                        </div>

                                        <!-- 📝 Deskripsi -->
                                        <div class="mb-4">
                                            <label for="cashflow_description"
                                                class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                            <input type="text" id="cashflow_description" name="description" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Masukkan deskripsi transaksi">
                                        </div>

                                        <!-- 💰 Nominal -->
                                        <div class="mb-4">
                                            <label for="cashflow_amount"
                                                class="block text-sm font-medium text-gray-700 mb-1">Nominal
                                                (Rp)</label>
                                            <input type="number" id="cashflow_amount" name="amount" required min="1"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="0">
                                        </div>

                                        <!-- 🛢️ SIMULATOR BBM -->
                                        <div id="bbmSimulator" class="hidden border-t border-gray-200 pt-4 mt-4">
                                            <h4
                                                class="text-md font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                                    </path>
                                                </svg>
                                                Simulasi Pembelian BBM
                                            </h4>

                                            <div class="space-y-3">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga
                                                        per Drigen (Rp)</label>
                                                    <input type="number" id="bbm_harga"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                        value="340000">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pajak
                                                        per Drigen (Rp)</label>
                                                    <input type="number" id="bbm_pajak"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                        value="12000">
                                                </div>

                                                <div>
                                                    <p class="text-sm font-medium text-gray-700 mb-2">Pilih Store:</p>
                                                    <div id="storeCheckboxContainer"
                                                        class="grid grid-cols-1 gap-2 max-h-32 overflow-y-auto p-2 border border-gray-200 rounded-md">
                                                        <?php foreach ($stores as $store): ?>
                                                        <label
                                                            class="flex items-center gap-2 p-1 hover:bg-gray-50 rounded">
                                                            <input type="checkbox" name="store_bbm[]"
                                                                value="<?= $store['id'] ?>"
                                                                class="store-check h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                            <span class="text-sm text-gray-700">
                                                                <?= htmlspecialchars($store['store_name']) ?>
                                                            </span>
                                                        </label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                                <div id="storeInputs" class="hidden space-y-2"></div>

                                                <div class="pt-2">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Total
                                                        Akhir (Rp)</label>
                                                    <input type="number" id="bbm_total" name="bbm_total"
                                                        class="w-full px-3 py-2 border border-gray-300 bg-gray-50 rounded-md font-semibold"
                                                        readonly>
                                                    <small id="bbm_info"
                                                        class="text-xs text-gray-500 mt-1 block"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 🔘 Tombol Action -->
                                        <div class="flex justify-end gap-3 pt-4 mt-6 border-t border-gray-200">
                                            <button type="button" onclick="hideModal('modalCashflow')"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                                Batal
                                            </button>
                                            <button type="submit"
                                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                                id="cashflowSubmitBtn">
                                                Simpan Transaksi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-stores" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Manajemen Store/SPBU</h2>
                        <button onclick="showStoreModal()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-md text-sm transition duration-200 flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Tambah Store
                        </button>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-md overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        ID</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Nama Store</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Alamat</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="storesTableBody" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-gray-500">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="tab-employees" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Manajemen Karyawan</h2>
                        <button onclick="showEmployeeModal()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-md text-sm transition duration-200 flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Tambah Karyawan
                        </button>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-md overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        ID</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Nama</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Kode</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Store</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="employeesTableBody" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-gray-500">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>

        <footer class="bg-white border-t border-gray-200 mt-8">
            <div class="content-wrapper px-4 sm:px-6 lg:px-8 py-3 text-center text-sm text-gray-500">
                &copy;
                <?php echo date('Y'); ?> Setoran Harian. All rights reserved.
            </div>
        </footer>
    </div>
    <div id="detailModal" class="modal fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
            class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 transform transition-all duration-300 scale-95 modal-content">
            <h3 class="text-xl font-bold mb-4 border-b pb-2 text-indigo-700">Rincian Setoran <span
                    id="detail_id">#...</span></h3>
            <div id="detailLoading" class="text-center text-gray-500 py-4">Memuat...</div>
            <div id="detailContent" class="space-y-4 text-sm hidden">
                <div class="grid grid-cols-2 gap-x-4">
                    <p class="p-2 bg-indigo-50 rounded"><strong>Store:</strong> <span id="detail_store"
                            class="font-medium"></span></p>
                    <p class="p-2 bg-indigo-50 rounded"><strong>Karyawan:</strong> <span id="detail_employee"
                            class="font-medium"></span></p>
                    <p class="p-2 bg-indigo-50 rounded"><strong>Tanggal:</strong> <span id="detail_tanggal"
                            class="font-medium"></span></p>
                    <p class="p-2 bg-indigo-50 rounded"><strong>Jam Kerja:</strong> <span id="detail_jam_masuk"
                            class="font-medium"></span> - <span id="detail_jam_keluar" class="font-medium"></span></p>
                </div>

                <div class="bg-yellow-50 p-3 rounded-lg border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-700">Meter Awal/Akhir: <span id="detail_nomor_awal"
                            class="font-medium"></span> - <span id="detail_nomor_akhir" class="font-medium"></span></p>
                    <p class="font-bold text-lg text-yellow-800 mt-1">Total Liter: <span id="detail_total_liter"></span>
                        L</p>
                </div>

                <div class="border border-indigo-200 p-3 rounded-lg bg-indigo-50">
                    <p class="font-bold text-lg mb-2 text-indigo-700">Total Setoran Kasir: <span
                            id="detail_total_setoran"></span></p>
                    <div class="flex justify-between text-sm">
                        <p class="text-green-600">Cash: <span id="detail_cash" class="font-medium"></span></p>
                        <p class="text-blue-600">QRIS: <span id="detail_qris" class="font-medium"></span></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 border rounded-lg bg-red-50 border-red-200">
                        <h4 class="font-semibold text-red-700 border-b border-red-300 pb-1 mb-2">Pengeluaran (<span
                                id="detail_total_pengeluaran"></span>)</h4>
                        <ul id="detail_pengeluaran_list" class="space-y-1 text-xs"></ul>
                    </div>
                    <div class="p-3 border rounded-lg bg-green-50 border-green-200">
                        <h4 class="font-semibold text-green-700 border-b border-green-300 pb-1 mb-2">Pemasukan (<span
                                id="detail_total_pemasukan"></span>)</h4>
                        <ul id="detail_pemasukan_list" class="space-y-1 text-xs"></ul>
                    </div>
                </div>

                <p class="font-bold text-xl pt-3 border-t-2 border-dashed mt-4 text-center">TOTAL BERSIH: <span
                        id="detail_total_keseluruhan" class="text-green-800"></span></p>
            </div>
            <div class="flex justify-end mt-6">
                <button onclick="hideModal('detailModal')"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">Tutup</button>
            </div>
        </div>
    </div>

    <div id="storeModal" class="modal fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
            class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 transform transition-all duration-300 scale-95 modal-content">
            <h3 id="storeModalTitle" class="text-xl font-bold mb-4 border-b pb-2 text-indigo-700">Tambah Store Baru</h3>
            <form id="storeForm">
                <input type="hidden" id="store_id_field" name="id">
                <div class="mb-4">
                    <label for="store_name_field" class="block text-sm font-medium text-gray-700">Nama Store</label>
                    <input type="text" id="store_name_field" name="store_name"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="store_address_field" class="block text-sm font-medium text-gray-700">Alamat
                        (Opsional)</label>
                    <textarea id="store_address_field" name="address" rows="2"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <p id="storeMessage" class="text-center text-red-500 mb-4 hidden"></p>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideModal('storeModal')"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition duration-200">Batal</button>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="employeeModal" class="modal fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
            class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 transform transition-all duration-300 scale-95 modal-content">
            <h3 id="employeeModalTitle" class="text-xl font-bold mb-4 border-b pb-2 text-indigo-700">Tambah Karyawan
                Baru</h3>
            <form id="employeeForm">
                <input type="hidden" id="employee_id_field" name="id">
                <div class="mb-4">
                    <label for="employee_name_field" class="block text-sm font-medium text-gray-700">Nama
                        Karyawan</label>
                    <input type="text" id="employee_name_field" name="employee_name"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="employee_code_field" class="block text-sm font-medium text-gray-700">Kode Karyawan
                        (Opsional)</label>
                    <input type="text" id="employee_code_field" name="employee_code"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label for="employee_store_id_field" class="block text-sm font-medium text-gray-700">Store /
                        SPBU</label>
                    <select id="employee_store_id_field" name="store_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                    </select>
                </div>
                <div id="employeeStatusDiv" class="mb-4 hidden">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="employee_is_active_field" name="is_active"
                            class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 font-medium">Karyawan Aktif</span>
                    </label>
                </div>
                <p id="employeeMessage" class="text-center text-red-500 mb-4 hidden"></p>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideModal('employeeModal')"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition duration-200">Batal</button>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="cashFlowModal" class="modal fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
            class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 transform transition-all duration-300 scale-95 modal-content">
            <h3 id="cashFlowModalTitle" class="text-xl font-bold mb-4 border-b pb-2 text-indigo-700">Catat Transaksi Kas
                Manajemen</h3>
            <form id="cashFlowForm">
                <input type="hidden" id="cf_id_field" name="id">
                <div class="mb-4">
                    <label for="cf_tanggal_field" class="block text-sm font-medium text-gray-700">Tanggal
                        Transaksi</label>
                    <input type="date" id="cf_tanggal_field" name="tanggal"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="mb-4">
                    <label for="cf_store_id_field" class="block text-sm font-medium text-gray-700">Store Terkait</label>
                    <select id="cf_store_id_field" name="store_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                        <option value="" disabled selected>Pilih Store (Wajib)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="cf_type_field" class="block text-sm font-medium text-gray-700">Tipe Transaksi</label>
                    <select id="cf_type_field" name="type"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                        <option value="" disabled selected>Pilih Tipe</option>
                        <option value="Pemasukan">Pemasukan</option>
                        <option value="Pengeluaran">Pengeluaran</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="cf_description_field"
                        class="block text-sm font-medium text-gray-700">Deskripsi/Keterangan</label>
                    <input type="text" id="cf_description_field" name="description"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="cf_amount_field" class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                    <input type="number" id="cf_amount_field" name="amount"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"
                        required min="1000">
                </div>
                <div class="mb-4">
                    <label for="cf_notes_field" class="block text-sm font-medium text-gray-700">Catatan
                        (Opsional)</label>
                    <textarea id="cf_notes_field" name="notes" rows="2"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-2 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <p id="cashFlowMessage" class="text-center text-red-500 mb-4 hidden"></p>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideModal('cashFlowModal')"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition duration-200">Batal</button>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        // Variabel global untuk data store
        let allStores = [];
        let allEmployees = [];


        // =========================================================
        // --- Utility Functions (Final) ---
        // =========================================================

        // Format angka jadi Rupiah, misal 15000 -> Rp 15.000
        function formatRupiah(number) {
            const num = Number(number);
            if (isNaN(num)) return 'Rp 0';
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(num);
        }

        // Tampilkan modal (hapus class hidden, tambah z-index dan layout center)
        function showModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;

            modal.classList.remove('hidden');
            modal.classList.add('flex', 'items-center', 'justify-center');
            modal.style.zIndex = '9999';
        }

        // Sembunyikan modal (tambah class hidden kembali)
        function hideModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;

            modal.classList.add('hidden');
            modal.classList.remove('flex', 'items-center', 'justify-center');
            modal.style.zIndex = '';
        }


        // --- Navigasi Tab ---
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function () {
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active-tab'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));

                this.classList.add('active-tab');
                const tabId = this.dataset.tab;
                document.getElementById(`tab-${tabId}`).classList.remove('hidden');

                // Muat data spesifik saat tab dibuka
                if (tabId === 'dashboard') fetchDashboardData();
                if (tabId === 'setoran') fetchSetoranData();
                if (tabId === 'cashflow') fetchCashFlowData();
                if (tabId === 'stores') fetchStores();
                if (tabId === 'employees') fetchEmployees();
            });
        });

        // --- Autentikasi ---
        document.getElementById('logoutBtn').addEventListener('click', async function () {
            if (confirm('Anda yakin ingin logout?')) {
                await fetch('api.php?action=logout');
                window.location.href = 'login.php';
            }
        });

        // --- Populate Store/Employee Filter ---
        async function fetchAllStoresAndEmployeesForFilter() {
            // Ambil Store untuk filter dan modal
            const storeResponse = await fetch('api.php?action=get_stores');
            const storeResult = await storeResponse.json();
            if (storeResult.success) {
                allStores = storeResult.data;

                // Populate Filter Setoran
                const filterStoreSelectSetoran = document.getElementById('filter_store_id_setoran');
                const selectedStoreIdSetoran = '<?php echo $selected_store_id; ?>';
                filterStoreSelectSetoran.innerHTML = '<option value="">Semua Store</option>';

                // Populate Filter Cash Flow
                const filterStoreSelectCashFlow = document.getElementById('filter_store_id_cashflow');
                filterStoreSelectCashFlow.innerHTML = '<option value="">Semua Store</option>';

                // Populate Modal Cash Flow
                const modalStoreSelectCashFlow = document.getElementById('cf_store_id_field');
                modalStoreSelectCashFlow.innerHTML = '<option value="" disabled selected>Pilih Store (Wajib)</option>';

                allStores.forEach(store => {
                    // Setoran Filter
                    const optionSetoran = new Option(store.store_name, store.id);
                    if (store.id == selectedStoreIdSetoran) optionSetoran.selected = true;
                    filterStoreSelectSetoran.appendChild(optionSetoran);

                    // Cash Flow Filter
                    const optionCashFlowFilter = new Option(store.store_name, store.id);
                    filterStoreSelectCashFlow.appendChild(optionCashFlowFilter);

                    // Cash Flow Modal
                    const optionCashFlowModal = new Option(store.store_name, store.id);
                    modalStoreSelectCashFlow.appendChild(optionCashFlowModal);
                });
            }

            // Ambil Employee untuk filter 
            const employeeResponse = await fetch('api.php?action=get_employees');
            const employeeResult = await employeeResponse.json();
            if (employeeResult.success) {
                allEmployees = employeeResult.data;
                const filterEmployeeSelect = document.getElementById('filter_employee_id_setoran');
                const selectedEmployeeId = '<?php echo $selected_employee_id; ?>';

                filterEmployeeSelect.innerHTML = '<option value="">Semua Karyawan</option>';

                allEmployees.forEach(employee => {
                    const option = new Option(`${employee.employee_name} (${employee.store_name})`, employee.id);
                    if (employee.id == selectedEmployeeId) option.selected = true;
                    filterEmployeeSelect.appendChild(option);
                });
            }
        }


        // =========================================================
        // A. LOGIKA UTAMA SETORAN HISTORY
        // =========================================================

        async function fetchSetoranData() {
            const tableBody = document.getElementById('setoranTableBody');
            tableBody.innerHTML = '<tr><td colspan="15" class="text-center py-6 text-gray-500">Memuat data...</td></tr>';

            const urlParams = new URLSearchParams(window.location.search);
            const month = urlParams.get('month') || '<?php echo $current_month; ?>';
            const year = urlParams.get('year') || '<?php echo $current_year; ?>';
            const employeeId = urlParams.get('employee_id') || '';
            const storeId = urlParams.get('store_id') || '';

            let apiUrl = `api.php?action=get_history&month=${month}&year=${year}`;
            if (employeeId) apiUrl += `&employee_id=${employeeId}`;
            if (storeId) apiUrl += `&store_id=${storeId}`;

            try {
                const response = await fetch(apiUrl);
                const result = await response.json();

                if (result.success && result.data.setoran.length > 0) {
                    tableBody.innerHTML = '';
                    result.data.setoran.forEach(setoran => {
                        const row = tableBody.insertRow();
                        row.className = 'hover:bg-gray-50 transition duration-150 ease-in-out';

                        const total_liter_formatted = parseFloat(setoran.total_liter).toFixed(2);

                        row.innerHTML = `
                    <td class="px-4 py-2 whitespace-nowrap text-gray-800">${setoran.tanggal}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-gray-800">${setoran.employee_name || 'N/A'}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-gray-800 font-medium">${setoran.store_name || 'N/A'}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-center text-gray-800">${setoran.jam_masuk.substring(0, 5)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-center text-gray-800">${setoran.jam_keluar.substring(0, 5)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-gray-800">${parseFloat(setoran.nomor_awal).toFixed(2)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-gray-800">${parseFloat(setoran.nomor_akhir).toFixed(2)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-indigo-700 font-medium">${total_liter_formatted} L</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-blue-600">${formatRupiah(setoran.qris)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-green-600">${formatRupiah(setoran.cash)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right font-medium">${formatRupiah(setoran.total_setoran)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-red-600">${formatRupiah(setoran.total_pengeluaran)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-green-600">${formatRupiah(setoran.total_pemasukan)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right font-semibold text-green-700">${formatRupiah(setoran.total_keseluruhan)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-center">
                        <div class="flex justify-center space-x-1">
                            <button onclick="showDetail(${setoran.id})" class="text-blue-600 hover:text-blue-900 text-xs font-semibold bg-blue-100 px-2 py-1 rounded" title="Lihat Detail">
                                👁️
                            </button>
                            <button onclick="deleteSetoran(${setoran.id})" class="text-red-600 hover:text-red-900 text-xs font-semibold bg-red-100 px-2 py-1 rounded" title="Hapus">
                                🗑️
                            </button>
                        </div>
                    </td>
                `;
                    });
                    updateSetoranSummary(result.summary);
                } else {
                    tableBody.innerHTML = '<tr><td colspan="15" class="text-center py-6 text-gray-500">Tidak ada data setoran yang ditemukan.</td></tr>';
                    updateSetoranSummary(result.summary || {});
                }
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="15" class="text-center py-6 text-red-500">Gagal memuat data: ${error.message}</td></tr>`;
            }
        }

        function updateSetoranSummary(summary) {
            document.getElementById('sum_liter').textContent = summary.total_liter ? `${parseFloat(summary.total_liter).toFixed(2)} L` : '0 L';
            document.getElementById('sum_cash').textContent = summary.total_cash ? formatRupiah(summary.total_cash) : formatRupiah(0);
            document.getElementById('sum_qris').textContent = summary.total_qris ? formatRupiah(summary.total_qris) : formatRupiah(0);
            document.getElementById('sum_pengeluaran').textContent = summary.total_pengeluaran ? formatRupiah(summary.total_pengeluaran) : formatRupiah(0);
            document.getElementById('sum_total_keseluruhan').textContent = summary.total_keseluruhan ? formatRupiah(summary.total_keseluruhan) : formatRupiah(0);
        }

        async function deleteSetoran(id) {
            if (!confirm('Anda yakin ingin menghapus setoran ini?')) return;

            try {
                const response = await fetch('api.php?action=delete_setoran', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const result = await response.json();
                alert(result.message);
                if (result.success) fetchSetoranData(); // Refresh data
            } catch (error) {
                alert('Gagal terhubung ke server.');
            }
        }

        // --- FUNGSI LIHAT DETAIL SETORAN ---
        async function showDetail(setoranId) {
            const modal = document.getElementById('detailModal');
            const loading = document.getElementById('detailLoading');
            const content = document.getElementById('detailContent');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            content.classList.add('hidden');
            loading.classList.remove('hidden');
            loading.textContent = 'Memuat rincian setoran...';

            try {
                const response = await fetch(`api.php?action=get_setoran_detail&id=${setoranId}`);
                const result = await response.json();

                if (result.success && result.data.setoran.length === 1) {
                    const detail = result.data.setoran[0];
                    const pemasukan = result.data.pemasukan;
                    const pengeluaran = result.data.pengeluaran;

                    // Isi data utama
                    document.getElementById('detail_id').textContent = detail.id;
                    document.getElementById('detail_tanggal').textContent = detail.tanggal;
                    document.getElementById('detail_employee').textContent = detail.employee_name || 'N/A';
                    document.getElementById('detail_store').textContent = detail.store_name || 'N/A';
                    document.getElementById('detail_jam_masuk').textContent = detail.jam_masuk.substring(0, 5);
                    document.getElementById('detail_jam_keluar').textContent = detail.jam_keluar.substring(0, 5);
                    document.getElementById('detail_nomor_awal').textContent = parseFloat(detail.nomor_awal).toFixed(2);
                    document.getElementById('detail_nomor_akhir').textContent = parseFloat(detail.nomor_akhir).toFixed(2);
                    document.getElementById('detail_total_liter').textContent = parseFloat(detail.total_liter).toFixed(2) + ' L';
                    document.getElementById('detail_cash').textContent = formatRupiah(detail.cash);
                    document.getElementById('detail_qris').textContent = formatRupiah(detail.qris);
                    document.getElementById('detail_total_setoran').textContent = formatRupiah(detail.total_setoran);
                    document.getElementById('detail_total_pengeluaran').textContent = formatRupiah(detail.total_pengeluaran);
                    document.getElementById('detail_total_pemasukan').textContent = formatRupiah(detail.total_pemasukan);
                    document.getElementById('detail_total_keseluruhan').textContent = formatRupiah(detail.total_keseluruhan);

                    // Isi rincian pengeluaran
                    const pengeluaranList = document.getElementById('detail_pengeluaran_list');
                    pengeluaranList.innerHTML = '';
                    if (pengeluaran.length > 0) {
                        pengeluaran.forEach(item => {
                            pengeluaranList.innerHTML += `<li>${item.description}: ${formatRupiah(item.amount)}</li>`;
                        });
                    } else {
                        pengeluaranList.innerHTML = '<li class="text-gray-500 italic">Tidak ada pengeluaran tambahan</li>';
                    }

                    // Isi rincian pemasukan
                    const pemasukanList = document.getElementById('detail_pemasukan_list');
                    pemasukanList.innerHTML = '';
                    if (pemasukan.length > 0) {
                        pemasukan.forEach(item => {
                            pemasukanList.innerHTML += `<li>${item.description}: ${formatRupiah(item.amount)}</li>`;
                        });
                    } else {
                        pemasukanList.innerHTML = '<li class="text-gray-500 italic">Tidak ada pemasukan tambahan</li>';
                    }

                    loading.classList.add('hidden');
                    content.classList.remove('hidden');
                } else {
                    alert(result.message || 'Gagal memuat detail setoran.');
                    hideModal('detailModal');
                }
            } catch (error) {
                loading.textContent = 'Gagal memuat detail: ' + error.message;
            }
        }

        // =========================================================
        // C. EVENT LISTENERS UNTUK FILTER SETORAN
        // =========================================================

        document.getElementById('filterFormSetoran').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }
            window.location.search = params.toString();
        });

        document.getElementById('resetFilterBtnSetoran').addEventListener('click', function () {
            window.location.search = `month=<?php echo $current_month; ?>&year=<?php echo $current_year; ?>`;
        });


        // =========================================================
        // B. LOGIKA MANAGEMENT CASH FLOW (NEW)
        // =========================================================

        // Pastikan variabel global ini terdefinisi di bagian atas file admin.php
        let allCashFlowTransactions = [];

        // =========================================================
        // FUNGSI UTAMA UNTUK MEMUAT DATA CASH FLOW
        // =========================================================
        async function fetchCashFlowData() {
            const tableBody = document.getElementById('cashflowTableBody');
            if (!tableBody) {
                console.warn("Element cashflowTableBody tidak ditemukan. Membatalkan fetch data.");
                return;
            }

            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-gray-500">Memuat data...</td></tr>';

            const month = document.getElementById('filter_month_cashflow')?.value || '<?php echo $current_month; ?>';
            const year = document.getElementById('filter_year_cashflow')?.value || '<?php echo $current_year; ?>';
            const storeId = document.getElementById('filter_store_cashflow')?.value || '';

            let apiUrl = `api.php?action=get_management_cash_flow&month=${month}&year=${year}`;
            if (storeId) apiUrl += `&store_id=${storeId}`;

            try {
                const response = await fetch(apiUrl);
                const result = await response.json();
                allCashFlowTransactions = result.data || [];

                if (result.success && allCashFlowTransactions.length > 0) {
                    tableBody.innerHTML = '';
                    allCashFlowTransactions.forEach(cf => {
                        const isPengeluaran = cf.type === 'Pengeluaran';
                        const amountClass = isPengeluaran ? 'text-red-600' : 'text-green-600';
                        const typeClass = cf.type === 'Setoran'
                            ? 'bg-yellow-100 text-yellow-800'
                            : (isPengeluaran ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800');
                        const formattedAmount = isPengeluaran ? `-${formatRupiah(cf.amount)}` : `+${formatRupiah(cf.amount)}`;

                        const row = tableBody.insertRow();
                        row.className = 'hover:bg-gray-50 transition duration-150 ease-in-out';
                        row.innerHTML = `
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">${cf.tanggal}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">${cf.store_name || 'N/A'}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 max-w-xs truncate">${cf.description}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ${typeClass}">${cf.type}</span></td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold ${amountClass}">${formattedAmount}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                        <button onclick="editCashFlow(${cf.id})" class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold mr-2 bg-indigo-100 px-2 py-1 rounded">Edit</button>
                        <button onclick="deleteCashFlow(${cf.id})" class="text-red-600 hover:text-red-900 text-xs font-semibold bg-red-100 px-2 py-1 rounded">Hapus</button>
                    </td>
                `;
                    });
                    updateCashFlowSummary(result.summary);
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-gray-500">Tidak ada data transaksi kas manajemen.</td></tr>';
                    updateCashFlowSummary(result.summary || {});
                }
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-red-500">Gagal memuat data: ${error.message}</td></tr>`;
                console.error("Fetch Cash Flow Error:", error);
            }
        }

        // =========================================================
        // FUNGSI UNTUK MEMPERBARUI RINGKASAN TOTAL (FINAL REVISI)
        // =========================================================
        function updateCashFlowSummary(summary) {
            const pemasukan = Number(summary.total_pemasukan_manajemen) || 0;
            const pengeluaran = Number(summary.total_pengeluaran_manajemen) || 0;
            const setoran = Number(summary.total_setoran) || 0;
            const saldoBersih = Number(summary.saldo_bersih) || 0;

            const pemasukanEl = document.getElementById('cashflow_total_income');
            const pengeluaranEl = document.getElementById('cashflow_total_expense');
            const setoranEl = document.getElementById('cashflow_total_setoran');
            const saldoEl = document.getElementById('cashflow_balance');

            if (pemasukanEl) pemasukanEl.textContent = formatRupiah(pemasukan);
            if (pengeluaranEl) pengeluaranEl.textContent = formatRupiah(pengeluaran);
            if (setoranEl) setoranEl.textContent = formatRupiah(setoran);
            if (saldoEl) {
                saldoEl.textContent = formatRupiah(saldoBersih);
                saldoEl.classList.remove('text-red-700', 'text-indigo-700');
                saldoEl.classList.add(saldoBersih < 0 ? 'text-red-700' : 'text-indigo-700');
            }

            console.log("Ringkasan Cashflow:", { pemasukan, pengeluaran, setoran, saldoBersih });
        }

        // =========================================================
        // EVENT LISTENER FILTER
        // =========================================================
        const filterFormCashFlow = document.getElementById('filterFormCashflow');
        if (filterFormCashFlow) {
            filterFormCashFlow.addEventListener('submit', function (e) {
                e.preventDefault();
                fetchCashFlowData();
            });
        }

        const resetFilterBtnCashFlow = document.getElementById('resetFilterBtnCashFlow');
        if (resetFilterBtnCashFlow) {
            resetFilterBtnCashFlow.addEventListener('click', function () {
                if (document.getElementById('filter_month_cashflow')) document.getElementById('filter_month_cashflow').value = '<?php echo $current_month; ?>';
                if (document.getElementById('filter_year_cashflow')) document.getElementById('filter_year_cashflow').value = '<?php echo $current_year; ?>';
                if (document.getElementById('filter_store_cashflow')) document.getElementById('filter_store_cashflow').value = '';
                fetchCashFlowData();
            });
        }

        // =========================================================
        // MODAL TAMBAH / EDIT CASH FLOW
        // =========================================================
        function showCashFlowModal(cf = null) {
            const modal = document.getElementById('modalCashflow');
            if (!modal) return;

            const messageEl = document.getElementById('cashFlowMessage');
            if (messageEl) {
                messageEl.classList.add('hidden');
                messageEl.classList.remove('text-red-600', 'text-green-600');
            }

            const form = document.getElementById('cashFlowForm');
            if (form) form.reset();

            const modalStoreSelect = document.getElementById('cf_store_id_field');
            if (modalStoreSelect && allStores && allStores.length > 0) {
                const defaultStoreId = cf ? cf.store_id : (allStores[0] ? allStores[0].id : '');
                modalStoreSelect.value = defaultStoreId;
            }

            if (cf) {
                if (document.getElementById('cashFlowModalTitle')) document.getElementById('cashFlowModalTitle').textContent = 'Edit Transaksi Kas: #' + cf.id;
                if (document.getElementById('cf_id_field')) document.getElementById('cf_id_field').value = cf.id;
                if (document.getElementById('cf_tanggal_field')) document.getElementById('cf_tanggal_field').value = cf.tanggal;
                if (document.getElementById('cf_type_field')) document.getElementById('cf_type_field').value = cf.type;
                if (document.getElementById('cf_store_id_field')) document.getElementById('cf_store_id_field').value = cf.store_id;
                if (document.getElementById('cf_description_field')) document.getElementById('cf_description_field').value = cf.description;
                if (document.getElementById('cf_amount_field')) document.getElementById('cf_amount_field').value = cf.amount;
                if (document.getElementById('cf_notes_field')) document.getElementById('cf_notes_field').value = cf.notes || '';
            } else {
                if (document.getElementById('cashFlowModalTitle')) document.getElementById('cashFlowModalTitle').textContent = 'Catat Transaksi Kas Baru';
                if (document.getElementById('cf_id_field')) document.getElementById('cf_id_field').value = '';
                if (document.getElementById('cf_tanggal_field')) document.getElementById('cf_tanggal_field').value = '<?php echo date('Y - m - d'); ?>';
                if (modalStoreSelect && allStores && allStores.length > 0) {
                    modalStoreSelect.value = allStores[0].id;
                }
            }
            showModal('modalCashflow');
        }

        // =========================================================
        // EDIT DAN HAPUS TRANSAKSI
        // =========================================================
        function editCashFlow(id) {
            const cf = allCashFlowTransactions.find(t => t.id == id);
            if (cf) showCashFlowModal(cf);
        }

        async function deleteCashFlow(id) {
            const cf = allCashFlowTransactions.find(t => t.id == id);
            if (!cf) return;

            if (!window.confirm(`Anda yakin ingin menghapus transaksi ${cf.type} ${formatRupiah(cf.amount)} (${cf.description})?`)) {
                return;
            }

            try {
                const response = await fetch('api.php?action=delete_management_cash_flow', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const result = await response.json();
                console.log(result.message);
                if (result.success) fetchCashFlowData();
            } catch (error) {
                console.error('Gagal terhubung ke server untuk delete:', error);
            }
        }

        // =========================================================
        // SIMPAN DATA (ADD/EDIT)
        // =========================================================
        const cashFlowForm = document.getElementById('cashFlowForm');
        if (cashFlowForm) {
            cashFlowForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const id = document.getElementById('cf_id_field')?.value;
                const action = id ? 'edit_management_cash_flow' : 'add_management_cash_flow';

                const formData = {
                    id: id,
                    tanggal: document.getElementById('cf_tanggal_field')?.value,
                    store_id: document.getElementById('cf_store_id_field')?.value,
                    type: document.getElementById('cf_type_field')?.value,
                    description: document.getElementById('cf_description_field')?.value,
                    amount: document.getElementById('cf_amount_field')?.value,
                    notes: document.getElementById('cf_notes_field')?.value,
                };

                const messageEl = document.getElementById('cashFlowMessage');
                if (messageEl) messageEl.classList.add('hidden');

                try {
                    const response = await fetch(`api.php?action=${action}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    });
                    const result = await response.json();

                    if (messageEl) {
                        messageEl.textContent = result.message;
                        messageEl.classList.remove('hidden');
                        messageEl.classList.add(result.success ? 'text-green-600' : 'text-red-600');
                    }

                    if (result.success) {
                        setTimeout(() => { hideModal('modalCashflow'); fetchCashFlowData(); }, 1500);
                    }
                } catch (error) {
                    if (messageEl) {
                        messageEl.textContent = 'Gagal terhubung ke server.';
                        messageEl.classList.remove('hidden');
                        messageEl.classList.add('text-red-600');
                    }
                    console.error("Cash Flow Form Submission Error:", error);
                }
            });
        }
        // =========================================================
        // C. LOGIKA MANAJEMEN STORE (DIPERTAHANKAN)
        // =========================================================
        async function fetchStores() {
            const tableBody = document.getElementById('storesTableBody');
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500">Memuat data...</td></tr>';

            try {
                const response = await fetch('api.php?action=get_stores');
                const result = await response.json();
                allStores = result.data || [];

                if (result.success && allStores.length > 0) {
                    tableBody.innerHTML = '';
                    allStores.forEach(store => {
                        const row = tableBody.insertRow();
                        row.className = 'hover:bg-gray-50 transition duration-150 ease-in-out';
                        row.innerHTML = `
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${store.id}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 font-medium">${store.store_name}</td>
                            <td class="px-4 py-3 text-sm text-gray-800">${store.address || '-'}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                <button onclick="editStore(${store.id})" class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold mr-2 bg-indigo-100 px-2 py-1 rounded">Edit</button>
                                <button onclick="deleteStore(${store.id})" class="text-red-600 hover:text-red-900 text-xs font-semibold bg-red-100 px-2 py-1 rounded">Hapus</button>
                            </td>
                        `;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500">Tidak ada data Store.</td></tr>';
                }
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-red-500">Gagal memuat data: ${error.message}</td></tr>`;
            }
        }

        function showStoreModal(store = null) {
            document.getElementById('storeMessage').classList.add('hidden');
            document.getElementById('storeMessage').classList.remove('text-red-500', 'text-green-500');
            const form = document.getElementById('storeForm');
            form.reset();

            if (store) {
                document.getElementById('storeModalTitle').textContent = 'Edit Store: ' + store.store_name;
                document.getElementById('store_id_field').value = store.id;
                document.getElementById('store_name_field').value = store.store_name;
                document.getElementById('store_address_field').value = store.address;
            } else {
                document.getElementById('storeModalTitle').textContent = 'Tambah Store Baru';
                document.getElementById('store_id_field').value = '';
            }
            showModal('storeModal');
        }

        function editStore(id) {
            const store = allStores.find(s => s.id === id);
            if (store) showStoreModal(store);
        }

        async function deleteStore(id) {
            if (!confirm('Anda yakin ingin menghapus Store ini? Semua data setoran yang terikat akan kehilangan relasi ke store ini.')) return;

            try {
                const response = await fetch('api.php?action=delete_store', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const result = await response.json();
                alert(result.message);
                if (result.success) fetchStores();
            } catch (error) {
                alert('Gagal terhubung ke server.');
            }
        }

        document.getElementById('storeForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const id = document.getElementById('store_id_field').value;
            const action = id ? 'edit_store' : 'add_store';
            const formData = {
                id: id,
                store_name: document.getElementById('store_name_field').value,
                address: document.getElementById('store_address_field').value,
            };

            const messageEl = document.getElementById('storeMessage');
            messageEl.classList.add('hidden');

            try {
                const response = await fetch(`api.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                const result = await response.json();
                messageEl.textContent = result.message;
                messageEl.classList.remove('hidden');
                messageEl.classList.add(result.success ? 'text-green-600' : 'text-red-600');

                if (result.success) {
                    setTimeout(() => { hideModal('storeModal'); fetchStores(); }, 1500);
                }
            } catch (error) {
                messageEl.textContent = 'Gagal terhubung ke server.';
                messageEl.classList.remove('hidden');
                messageEl.classList.add('text-red-600');
            }
        });


        // =========================================================
        // D. LOGIKA MANAJEMEN EMPLOYEE (DIPERTAHANKAN)
        // =========================================================

        async function fetchEmployees() {
            const tableBody = document.getElementById('employeesTableBody');
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Memuat data...</td></tr>';

            try {
                const response = await fetch('api.php?action=get_employees');
                const result = await response.json();
                allEmployees = result.data || [];

                if (result.success && allEmployees.length > 0) {
                    tableBody.innerHTML = '';
                    allEmployees.forEach(employee => {
                        const row = tableBody.insertRow();
                        row.className = 'hover:bg-gray-50 transition duration-150 ease-in-out';
                        const isActive = employee.is_active == 1;
                        const statusClass = isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        const statusText = isActive ? 'Aktif' : 'Nonaktif';
                        const toggleButtonText = isActive ? 'Nonaktifkan' : 'Aktifkan';
                        const toggleButtonClass = isActive ? 'text-red-600 bg-red-100' : 'text-green-600 bg-green-100';

                        row.innerHTML = `
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${employee.id}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 font-medium">${employee.employee_name}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${employee.employee_code || '-'}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${employee.store_name || 'N/A'}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-center"><span class="px-2 py-0.5 rounded-full text-xs ${statusClass}">${statusText}</span></td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                <button onclick="editEmployee(${employee.id})" class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold mr-2 bg-indigo-100 px-2 py-1 rounded">Edit</button>
                                <button onclick="toggleEmployeeStatus(${employee.id})" class="text-xs font-semibold px-2 py-1 rounded ${toggleButtonClass}">${toggleButtonText}</button>
                            </td>
                        `;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Tidak ada data Karyawan.</td></tr>';
                }
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-red-500">Gagal memuat data: ${error.message}</td></tr>`;
            }
        }

        async function populateStoreDropdown(selectId, selectedId = null) {
            if (allStores.length === 0) {
                const response = await fetch('api.php?action=get_stores');
                const result = await response.json();
                allStores = result.data || [];
            }

            const selectEl = document.getElementById(selectId);
            selectEl.innerHTML = '<option value="" disabled selected>Pilih Store</option>';

            allStores.forEach(store => {
                const option = new Option(store.store_name, store.id);
                if (store.id == selectedId) option.selected = true;
                selectEl.appendChild(option);
            });
        }

        function showEmployeeModal(employee = null) {
            document.getElementById('employeeMessage').classList.add('hidden');
            document.getElementById('employeeMessage').classList.remove('text-red-500', 'text-green-500');
            const form = document.getElementById('employeeForm');
            form.reset();

            populateStoreDropdown('employee_store_id_field', employee ? employee.store_id : null);

            if (employee) {
                document.getElementById('employeeModalTitle').textContent = 'Edit Karyawan: ' + employee.employee_name;
                document.getElementById('employee_id_field').value = employee.id;
                document.getElementById('employee_name_field').value = employee.employee_name;
                document.getElementById('employee_code_field').value = employee.employee_code;
                document.getElementById('employee_is_active_field').checked = employee.is_active == 1;
                document.getElementById('employeeStatusDiv').classList.remove('hidden');
            } else {
                document.getElementById('employeeModalTitle').textContent = 'Tambah Karyawan Baru';
                document.getElementById('employee_id_field').value = '';
                document.getElementById('employeeStatusDiv').classList.add('hidden');
            }
            showModal('employeeModal');
        }

        function editEmployee(id) {
            const employee = allEmployees.find(e => e.id === id);
            if (employee) showEmployeeModal(employee);
        }

        async function toggleEmployeeStatus(id) {
            const employee = allEmployees.find(e => e.id === id);
            const newStatus = employee.is_active == 1 ? 0 : 1;
            const statusText = newStatus == 1 ? 'Mengaktifkan' : 'Menonaktifkan';

            if (!confirm(`Anda yakin ingin ${statusText} Karyawan ${employee.employee_name}?`)) return;

            try {
                const response = await fetch('api.php?action=edit_employee', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        employee_name: employee.employee_name,
                        store_id: employee.store_id,
                        is_active: newStatus
                    })
                });
                const result = await response.json();
                alert(result.message);
                if (result.success) fetchEmployees();
            } catch (error) {
                alert('Gagal terhubung ke server.');
            }
        }

        document.getElementById('employeeForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const id = document.getElementById('employee_id_field').value;
            const action = id ? 'edit_employee' : 'add_employee';

            const formData = {
                id: id,
                employee_name: document.getElementById('employee_name_field').value,
                employee_code: document.getElementById('employee_code_field').value,
                store_id: document.getElementById('employee_store_id_field').value,
                is_active: document.getElementById('employee_is_active_field').checked ? 1 : 0,
            };

            const messageEl = document.getElementById('employeeMessage');
            messageEl.classList.add('hidden');

            try {
                const response = await fetch(`api.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                const result = await response.json();
                messageEl.textContent = result.message;
                messageEl.classList.remove('hidden');
                messageEl.classList.add(result.success ? 'text-green-600' : 'text-red-600');

                if (result.success) {
                    setTimeout(() => { hideModal('employeeModal'); fetchEmployees(); }, 1500);
                }
            } catch (error) {
                messageEl.textContent = 'Gagal terhubung ke server.';
                messageEl.classList.remove('hidden');
                messageEl.classList.add('text-red-600');
            }
        });
        // =========================================================
        // E. DASHBOARD WALLET & CASHFLOW
        // =========================================================

        /**
         * Fungsi untuk mengambil semua data dashboard secara komprehensif.
         * Fungsi ini MENGGABUNGKAN panggilan get_dashboard_wallet dan get_cashflow_summary.
         * (Menggantikan dua definisi fetchDashboardData sebelumnya).
         */
        async function fetchDashboardData() {
            const month = document.getElementById('filter_month_dashboard').value || '<?php echo $current_month; ?>';
            const year = document.getElementById('filter_year_dashboard').value || '<?php echo $current_year; ?>';
            // Pastikan filter store_id ada. Jika tidak, ambil saja null/kosong.
            const store_id = document.getElementById('filter_store_dashboard')?.value || '';

            // --- 1. Ambil data Wallet dan Breakdown (get_dashboard_wallet) ---
            try {
                const walletResponse = await fetch(`api.php?action=get_dashboard_wallet&month=${month}&year=${year}`);
                const walletResult = await walletResponse.json();

                if (walletResult.success) {
                    updateDashboardDisplay(walletResult.data);
                } else {
                    console.error('API Error fetching dashboard wallet:', walletResult.message);
                }
            } catch (error) {
                console.error('Error fetching dashboard wallet:', error);
            }

            // --- 2. Ambil data Ringkasan Cash Flow Per Store (get_cashflow_summary) ---
            const cashflowUrl = `api.php?action=get_cashflow_summary&month=${month}&year=${year}&store_id=${store_id}`;

            try {
                const cashflowResponse = await fetch(cashflowUrl);
                const cashflowResult = await cashflowResponse.json();

                if (cashflowResult.success) {
                    // FIX: Mengatasi error 'Cannot read properties of undefined (reading 'length')' jika 'summary' kosong
                    displayDashboardSummary(cashflowResult.summary || []);
                } else {
                    console.error('API Error fetching cashflow summary:', cashflowResult.message);
                    const tableBody = document.getElementById('dashboardTableBody');
                    if (tableBody) {
                        tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-red-600">Gagal memuat ringkasan: ${cashflowResult.message}</td></tr>`;
                    }
                }
            } catch (error) {
                console.error('Error fetching cashflow summary:', error);
            }
        }

        function updateDashboardDisplay(data) {
            // Update Wallet Utama
            document.getElementById('wallet_all_income').textContent = formatRupiah(data.all_stores.total_income);
            document.getElementById('wallet_all_expense').textContent = formatRupiah(data.all_stores.total_expense);
            document.getElementById('wallet_all_balance').textContent = formatRupiah(data.all_stores.balance);
            document.getElementById('wallet_all_liter').textContent = parseFloat(data.all_stores.total_liter).toFixed(2) + ' L';

            // Update Expense Breakdown
            const expenseContainer = document.getElementById('expense_breakdown');
            expenseContainer.innerHTML = '';

            if (data.expense_breakdown && data.expense_breakdown.length > 0) {
                data.expense_breakdown.forEach(item => {
                    const percentage = ((item.amount / data.all_stores.total_expense) * 100).toFixed(1);
                    expenseContainer.innerHTML += `
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">${item.description}</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-red-600 h-2 rounded-full" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                    <div class="ml-4 text-right">
                        <p class="text-sm font-bold text-red-600">${formatRupiah(item.amount)}</p>
                        <p class="text-xs text-gray-500">${percentage}%</p>
                    </div>
                </div>
            `;
                });
            } else {
                expenseContainer.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada pengeluaran</p>';
            }

            // Update Income Breakdown
            const incomeContainer = document.getElementById('income_breakdown');
            incomeContainer.innerHTML = '';

            if (data.income_breakdown && data.income_breakdown.length > 0) {
                data.income_breakdown.forEach(item => {
                    const percentage = ((item.amount / data.all_stores.total_income) * 100).toFixed(1);
                    incomeContainer.innerHTML += `
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">${item.description}</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-green-600 h-2 rounded-full" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                    <div class="ml-4 text-right">
                        <p class="text-sm font-bold text-green-600">${formatRupiah(item.amount)}</p>
                        <p class="text-xs text-gray-500">${percentage}%</p>
                    </div>
                </div>
            `;
                });
            } else {
                incomeContainer.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada pemasukan</p>';
            }

            // Update Wallet Per Store (Logika ini seharusnya diganti dengan displayDashboardSummary)
            const storeContainer = document.getElementById('wallet_per_store');
            storeContainer.innerHTML = '';

            if (data.per_store && data.per_store.length > 0) {
                data.per_store.forEach(store => {
                    const balanceClass = store.balance >= 0 ? 'text-green-600' : 'text-red-600';
                    storeContainer.innerHTML += `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition">
                    <h4 class="font-bold text-lg mb-3 text-gray-800">${store.store_name}</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Pemasukan</p>
                            <p class="text-sm font-bold text-green-600">${formatRupiah(store.income)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Pengeluaran</p>
                            <p class="text-sm font-bold text-red-600">${formatRupiah(store.expense)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Saldo</p>
                            <p class="text-sm font-bold ${balanceClass}">${formatRupiah(store.balance)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Total Liter</p>
                            <p class="text-sm font-bold text-indigo-600">${parseFloat(store.total_liter).toFixed(2)} L</p>
                        </div>
                    </div>
                </div>
            `;
                });
            } else {
                storeContainer.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada data store</p>';
            }
        }

        // Fungsi untuk mengambil data toko dan karyawan (untuk filter)
        // 🔥 PERBAIKAN untuk error Baris 653: Menambahkan || [] pada hasil API
        async function fetchAllStoresAndEmployeesForFilter() {
            try {
                // --- Ambil Store untuk filter dan modal ---
                const storeResponse = await fetch('api.php?action=get_stores');
                const storeResult = await storeResponse.json();

                if (storeResult.success && Array.isArray(storeResult.data)) {
                    allStores = storeResult.data;

                    // FILTER: Setoran
                    const filterStoreSelectSetoran = document.getElementById('filter_store_id_setoran');
                    if (filterStoreSelectSetoran) {
                        const selectedStoreIdSetoran = '<?php echo $selected_store_id; ?>';
                        filterStoreSelectSetoran.innerHTML = '<option value="">Semua Store</option>';
                        allStores.forEach(store => {
                            const opt = new Option(store.store_name, store.id);
                            if (store.id == selectedStoreIdSetoran) opt.selected = true;
                            filterStoreSelectSetoran.appendChild(opt);
                        });
                    }

                    // FILTER: Cashflow
                    const filterStoreSelectCashFlow = document.getElementById('filter_store_cashflow');
                    if (filterStoreSelectCashFlow) {
                        filterStoreSelectCashFlow.innerHTML = '<option value="">Semua Store</option>';
                        allStores.forEach(store => {
                            const opt = new Option(store.store_name, store.id);
                            filterStoreSelectCashFlow.appendChild(opt);
                        });
                    }

                    // MODAL: Cashflow
                    const modalStoreSelectCashFlow = document.getElementById('cashflow_store_id');
                    if (modalStoreSelectCashFlow) {
                        modalStoreSelectCashFlow.innerHTML = '<option value="" disabled selected>Pilih Store</option>';
                        allStores.forEach(store => {
                            const opt = new Option(store.store_name, store.id);
                            modalStoreSelectCashFlow.appendChild(opt);
                        });
                    }
                }

                // --- Ambil Employee untuk filter setoran ---
                const employeeResponse = await fetch('api.php?action=get_employees');
                const employeeResult = await employeeResponse.json();

                if (employeeResult.success && Array.isArray(employeeResult.data)) {
                    allEmployees = employeeResult.data;

                    const filterEmployeeSelect = document.getElementById('filter_employee_id_setoran');
                    if (filterEmployeeSelect) {
                        const selectedEmployeeId = '<?php echo $selected_employee_id; ?>';
                        filterEmployeeSelect.innerHTML = '<option value="">Semua Karyawan</option>';
                        allEmployees.forEach(emp => {
                            const opt = new Option(`${emp.employee_name} (${emp.store_name})`, emp.id);
                            if (emp.id == selectedEmployeeId) opt.selected = true;
                            filterEmployeeSelect.appendChild(opt);
                        });
                    }
                }

            } catch (error) {
                console.error('Gagal memuat data store/employee:', error);
            }
        }


        // Fungsi baru untuk menampilkan ringkasan per store
        function displayDashboardSummary(summaryData) {
            const tableBody = document.getElementById('dashboardTableBody');
            let html = '';

            // 🔥 PERBAIKAN untuk error Baris 1438: Pemeriksaan null untuk mencegah error
            if (!tableBody) {
                console.error("Kesalahan Fatal: Elemen dengan ID 'dashboardTableBody' tidak ditemukan di DOM.");
                // SOLUSI NYATA: Pastikan HTML Anda memiliki <tbody id="dashboardTableBody">
                return;
            }

            if (summaryData.length === 0) {
                html = '<tr><td colspan="4" class="text-center py-4">Tidak ada data setoran dan cash flow untuk periode ini.</td></tr>';
            } else {
                summaryData.forEach(store => {
                    // Format angka ke mata uang
                    const formatRupiah = (number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);

                    html += `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-medium">${store.store_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-green-600">${formatRupiah(store.total_pemasukan)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-red-600">${formatRupiah(store.total_pengeluaran)}</td>
                    <td class="px-6 py-4 whitespace-nowrap font-bold">${formatRupiah(store.saldo_bersih)}</td>
                </tr>
            `;
                });
            }

            tableBody.innerHTML = html;
        }


        document.getElementById('filterFormDashboard').addEventListener('submit', function (e) {
            e.preventDefault();
            fetchDashboardData();
        });

        document.getElementById('resetFilterBtnDashboard').addEventListener('click', function () {
            document.getElementById('filter_month_dashboard').value = '<?php echo $current_month; ?>';
            document.getElementById('filter_year_dashboard').value = '<?php echo $current_year; ?>';
            fetchDashboardData();
        });

        function exportDashboard(type) {
            const month = document.getElementById('filter_month_dashboard').value;
            const year = document.getElementById('filter_year_dashboard').value;

            window.open(`api.php?action=export_dashboard&type=${type}&month=${month}&year=${year}`, '_blank');
        }

        // Execute on load
        document.addEventListener('DOMContentLoaded', function () {
            // Panggil semua fungsi muat data awal
            fetchAllStoresAndEmployeesForFilter();

            // Tentukan tab mana yang harus aktif saat pertama kali dibuka (default: dashboard)
            const activeTabButton = document.querySelector('.tab-button.active-tab');
            if (activeTabButton && activeTabButton.dataset.tab === 'dashboard') {
                fetchDashboardData();
            }
        });
    document.addEventListener("DOMContentLoaded", () => {
    const categorySelect = document.getElementById("cashflow_category");
    const storeSelectContainer = document.getElementById("storeSelectContainer");
    const storeSelect = document.getElementById("cashflow_store_id");
    const bbmSection = document.getElementById("bbmSimulator");
    const storeContainer = document.getElementById("storeInputs");
    const checkboxes = document.querySelectorAll(".store-check");
    const hargaInput = document.getElementById("bbm_harga");
    const pajakInput = document.getElementById("bbm_pajak");
    const totalInput = document.getElementById("bbm_total");
    const info = document.getElementById("bbm_info");
    const nominalField = document.getElementById("cashflow_amount");

    let isManualEdit = false;

    // tampilkan / sembunyikan simulator dan store select
    categorySelect.addEventListener("change", () => {
        if (categorySelect.value === "bbm") {
            bbmSection.classList.remove("hidden");
            storeSelectContainer.classList.add("hidden"); // HILANGKAN store select
            storeSelect.removeAttribute("required");
            storeSelect.value = ""; // Reset value
            
            // Reset semua checkbox ketika pilih BBM
            checkboxes.forEach(cb => cb.checked = false);
            storeContainer.innerHTML = "";
            totalInput.value = "";
            nominalField.value = "";
            resetStorePercentages();
            isManualEdit = false;
        } else {
            bbmSection.classList.add("hidden");
            storeSelectContainer.classList.remove("hidden"); // TAMPILKAN store select
            storeSelect.setAttribute("required", "required");
            
            storeContainer.innerHTML = "";
            totalInput.value = "";
            nominalField.value = "";
            resetStorePercentages();
            isManualEdit = false;
        }
    });

    // Event listener untuk edit manual nominal field
    nominalField.addEventListener("input", () => {
        if (categorySelect.value === "bbm") {
            isManualEdit = true;
            const manualValue = parseFloat(nominalField.value) || 0;
            totalInput.value = manualValue;
            updatePercentagesFromManual(manualValue);
        }
    });

    // generate input jumlah drigen saat store dicentang
    checkboxes.forEach(cb => {
        cb.addEventListener("change", () => {
            storeContainer.classList.toggle("hidden", ![...checkboxes].some(c => c.checked));

            storeContainer.innerHTML = "";
            checkboxes.forEach(box => {
                if (box.checked) {
                    const div = document.createElement("div");
                    div.className = "grid grid-cols-2 gap-2";
                    div.innerHTML = `
                        <label class="text-sm font-medium">${box.nextElementSibling.textContent.split(' - ')[0]} (Drigen)</label>
                        <input type="number" name="jumlah_drigen[${box.value}]" class="drigen-input border rounded p-1" min="0" value="0">
                    `;
                    storeContainer.appendChild(div);
                }
            });
            
            if ([...checkboxes].some(c => c.checked)) {
                hitungBBM();
            } else {
                totalInput.value = "";
                nominalField.value = "";
                resetStorePercentages();
            }
        });
    });

    // fungsi hitung total BBM (otomatis dari drigen)
    function hitungBBM() {
        if (isManualEdit) return;

        const harga = parseFloat(hargaInput.value) || 0;
        const pajak = parseFloat(pajakInput.value) || 0;
        const inputs = storeContainer.querySelectorAll(".drigen-input");

        let totalDrigen = 0;
        inputs.forEach(i => totalDrigen += parseFloat(i.value) || 0);
        
        if (totalDrigen === 0) {
            totalInput.value = "";
            nominalField.value = "";
            info.textContent = "";
            resetStorePercentages();
            isManualEdit = false;
            return;
        }

        const totalHarga = totalDrigen * (harga + pajak);
        const pembulatan = Math.round(totalHarga / 100) * 100;
        const selisih = pembulatan - totalHarga;
        totalInput.value = pembulatan.toFixed(0);
        nominalField.value = pembulatan.toFixed(0);

        updateStorePercentages(inputs, totalDrigen, pembulatan);
        info.textContent = `Pembulatan ${selisih >= 0 ? '+' : ''}${selisih.toFixed(0)} (dibagi proporsional antar store)`;
        isManualEdit = false;
    }

    // Fungsi untuk update persentase ketika nominal di-edit manual
    function updatePercentagesFromManual(manualNominal) {
        const inputs = storeContainer.querySelectorAll(".drigen-input");
        let totalDrigen = 0;
        inputs.forEach(i => totalDrigen += parseFloat(i.value) || 0);

        if (totalDrigen === 0) {
            const checkedStores = [...checkboxes].filter(cb => cb.checked);
            const equalShare = manualNominal / checkedStores.length;
            
            checkedStores.forEach(box => {
                const storeText = box.nextElementSibling;
                const persentase = (100 / checkedStores.length);
                storeText.innerHTML = `
                    ${storeText.textContent.split(' - ')[0]} 
                    - ${persentase.toFixed(1)}% (Rp ${Math.round(equalShare).toLocaleString()})
                `;
            });
            info.textContent = `Dibagi rata ke ${checkedStores.length} store`;
        } else {
            updateStorePercentages(inputs, totalDrigen, manualNominal);
            info.textContent = `Manual input - proporsi berdasarkan jumlah drigen`;
        }
    }

    // Fungsi untuk update persentase dan nominal di checkbox store
    function updateStorePercentages(inputs, totalDrigen, totalNominal) {
        checkboxes.forEach(box => {
            if (box.checked) {
                const storeId = box.value;
                const input = Array.from(inputs).find(i => i.name.includes(storeId));
                const drigen = parseFloat(input?.value) || 0;
                const persentase = totalDrigen > 0 ? (drigen / totalDrigen * 100) : 0;
                const nominal = totalNominal * (drigen / totalDrigen);
                
                const storeText = box.nextElementSibling;
                const storeName = storeText.textContent.split(' - ')[0];
                storeText.innerHTML = `
                    ${storeName} 
                    - ${persentase.toFixed(1)}% (Rp ${Math.round(nominal).toLocaleString()})
                `;
            }
        });
    }

    // Fungsi untuk reset persentase
    function resetStorePercentages() {
        checkboxes.forEach(box => {
            const storeText = box.nextElementSibling;
            const originalText = storeText.textContent.split(' - ')[0];
            storeText.textContent = originalText;
        });
    }

    // Event listeners
    [hargaInput, pajakInput].forEach(el => el.addEventListener("input", () => {
        isManualEdit = false;
        hitungBBM();
    }));
    
    document.addEventListener("input", e => {
        if (e.target.classList.contains("drigen-input")) {
            isManualEdit = false;
            hitungBBM();
        }
    });
});
    </script>
</body>

</html>