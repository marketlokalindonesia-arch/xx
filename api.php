<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Pastikan sesi dimulai untuk fungsi logout dan pemeriksaan hak akses (jika diperlukan)
session_start(); 
require_once 'config.php'; // Asumsikan file ini memiliki koneksi $pdo
header('Content-Type: application/json');

// Fungsi pembantu untuk mengeluarkan respons JSON
function jsonResponse($success, $message, $data = [], $summary = [], $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = ['success' => $success, 'message' => $message];

    // Jika terjadi error (status 500) dan ada data/summary, masukkan pesan error ke data
    if ($httpCode >= 400 && $data === [] && $summary === []) {
        // Kirim pesan error sebagai 'data' jika terjadi error
        $response['error_details'] = $message; 
        unset($response['message']); // Hapus 'message' yang redundan
    } else {
        $response['data'] = $data;
        $response['summary'] = $summary;
    }
    
    // Menggabungkan data dan summary dalam satu respons untuk get_history
    if (!empty($summary) && $data === []) {
        unset($response['data']);
    } else if (empty($summary) && empty($data)) {
        unset($response['data'], $response['summary']);
    }
    
    echo json_encode($response);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- Logika untuk mengambil data JSON POST body ---
$data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Tentukan aksi default jika data JSON ada (misalnya untuk setoran)
    if (empty($action) && is_array($data) && isset($data['employee_id'])) {
        $action = 'save_setoran';
    }
}
// -------------------------------------------------------------------

// --- AUTH: Logout harus berfungsi ---
if ($action === 'logout') {
    session_unset();
    session_destroy();
    jsonResponse(true, 'Logout berhasil');
}


switch ($action) {
    // =========================================================
    // BARU: FUNGSI LOGIN ADMIN
    // =========================================================
    case 'login':
        try {
            // Cek apakah data username dan password dikirim melalui body JSON
            if (empty($data['username']) || empty($data['password'])) {
                throw new Exception("Username dan password harus diisi.");
            }

            $username = $data['username'];
            $password = $data['password'];

            // 1. Ambil data admin dari database
            $sql = "SELECT id, username, password FROM users WHERE username = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Verifikasi pengguna
            if ($user) {
                // Catatan: Asumsi password disimpan dalam format plaintext/md5/sha1 berdasarkan struktur SQL Anda.
                // Jika Anda menggunakan password_hash() (direkomendasikan), ganti perbandingan di bawah ini.
                
                // ASUMSI: Password di-hash menggunakan password_hash() saat pendaftaran user
                // JIKA MENGGUNAKAN password_hash():
                // if (password_verify($password, $user['password'])) {
                
                // JIKA MENGGUNAKAN PLAINTEXT (Hanya untuk demo/development):
                if ($password === $user['password']) {
                    
                    // Verifikasi berhasil: Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    
                    jsonResponse(true, 'Login berhasil.');
                } else {
                    jsonResponse(false, 'Username atau password salah.', [], [], 401);
                }
            } else {
                jsonResponse(false, 'Username atau password salah.', [], [], 401);
            }

        } catch (Exception $e) {
            // Error koneksi database atau error lainnya
            jsonResponse(false, 'Login gagal. ' . $e->getMessage(), [], [], 500);
        }
        break;
  // =========================================================
// BARU: FUNGSI HAPUS SETORAN
// =========================================================
case 'delete_setoran':
    try {
        if (!isset($data['id'])) {
            throw new Exception("ID setoran tidak ditemukan.");
        }
        
        $pdo->beginTransaction();
        
        // Hapus data terkait terlebih dahulu
        $stmt1 = $pdo->prepare("DELETE FROM pengeluaran WHERE setoran_id = ?");
        $stmt1->execute([$data['id']]);
        
        $stmt2 = $pdo->prepare("DELETE FROM pemasukan WHERE setoran_id = ?");
        $stmt2->execute([$data['id']]);
        
        // Hapus setoran utama
        $stmt3 = $pdo->prepare("DELETE FROM setoran WHERE id = ?");
        $stmt3->execute([$data['id']]);
        
        $pdo->commit();
        jsonResponse(true, 'Setoran berhasil dihapus');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(false, 'Gagal menghapus setoran: ' . $e->getMessage(), [], [], 500);
    }
    break;

// =========================================================
// BARU: FUNGSI DETAIL UNTUK EDIT
// =========================================================
case 'get_setoran_detail_to_edit':
    try {
        $setoran_id = $_GET['id'] ?? 0;
        if (!$setoran_id) throw new Exception("ID Setoran tidak ditemukan.");

        $stmt = $pdo->prepare("
            SELECT s.*, e.employee_name, st.store_name 
            FROM setoran s
            LEFT JOIN employees e ON s.employee_id = e.id
            LEFT JOIN stores st ON s.store_id = st.id
            WHERE s.id = ?
        ");
        $stmt->execute([$setoran_id]);
        $setoran = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$setoran) throw new Exception("Data Setoran tidak ditemukan.");

        // Detail pengeluaran & pemasukan
        $stmt_pengeluaran = $pdo->prepare("SELECT description, amount FROM pengeluaran WHERE setoran_id = ?");
        $stmt_pengeluaran->execute([$setoran_id]);
        $pengeluaran = $stmt_pengeluaran->fetchAll(PDO::FETCH_ASSOC);

        $stmt_pemasukan = $pdo->prepare("SELECT description, amount FROM pemasukan WHERE setoran_id = ?");
        $stmt_pemasukan->execute([$setoran_id]);
        $pemasukan = $stmt_pemasukan->fetchAll(PDO::FETCH_ASSOC);

        $result = [
            'setoran' => $setoran,
            'pengeluaran' => $pengeluaran,
            'pemasukan' => $pemasukan
        ];

        jsonResponse(true, 'Detail setoran berhasil dimuat', $result);
    } catch (Exception $e) {
        jsonResponse(false, 'Error: Gagal memuat detail setoran. ' . $e->getMessage(), [], [], 404);
    }
    break;


// =========================================================
// FUNGSI SAVE/UPDATE SETORAN (VERSI B - SIMPAN employee_name)
// =========================================================
case 'save_setoran':
    try {
        if (empty($data)) throw new Exception("Data setoran kosong atau format tidak valid.");

        $required_fields = [
            'employee_id', 'store_id', 'jam_masuk', 'jam_keluar', 'nomor_awal', 'nomor_akhir',
            'total_liter', 'qris', 'cash', 'total_pengeluaran', 'total_pemasukan', 'total_keseluruhan'
        ];

        foreach ($required_fields as $field) {
            if (!isset($data[$field])) throw new Exception("Field wajib '{$field}' hilang.");
        }

        $today = date('Y-m-d');
        $total_setoran_calculated = (float)$data['qris'] + (float)$data['cash'];

        // Ambil nama karyawan dan nama store untuk disimpan langsung ke tabel setoran
        $stmtEmp = $pdo->prepare("SELECT employee_name FROM employees WHERE id = ?");
        $stmtEmp->execute([$data['employee_id']]);
        $employee_name = $stmtEmp->fetchColumn() ?: 'Tidak Diketahui';

        $stmtStore = $pdo->prepare("SELECT store_name FROM stores WHERE id = ?");
        $stmtStore->execute([$data['store_id']]);
        $store_name = $stmtStore->fetchColumn() ?: 'Tidak Diketahui';

        // ----------------------------------------------------
        // CEK DATA LAMA (UPSERT)
        // ----------------------------------------------------
        $stmtCheck = $pdo->prepare("SELECT id FROM setoran WHERE tanggal = ? AND store_id = ? AND employee_id = ?");
        $stmtCheck->execute([$today, $data['store_id'], $data['employee_id']]);
        $existingSetoranId = $stmtCheck->fetchColumn();

        if ($existingSetoranId) {
            // UPDATE
            $stmtUpdate = $pdo->prepare("
                UPDATE setoran SET 
                    jam_masuk = ?, jam_keluar = ?, nomor_awal = ?, nomor_akhir = ?, 
                    total_liter = ?, qris = ?, cash = ?, total_setoran = ?, 
                    total_pengeluaran = ?, total_pemasukan = ?, total_keseluruhan = ?, 
                    employee_name = ?, store_name = ?
                WHERE id = ?
            ");
            $stmtUpdate->execute([
                $data['jam_masuk'], $data['jam_keluar'], $data['nomor_awal'], $data['nomor_akhir'],
                $data['total_liter'], $data['qris'], $data['cash'], $total_setoran_calculated,
                $data['total_pengeluaran'], $data['total_pemasukan'], $data['total_keseluruhan'],
                $employee_name, $store_name, $existingSetoranId
            ]);

            $setoran_id = $existingSetoranId;
            $pdo->exec("DELETE FROM pengeluaran WHERE setoran_id = $setoran_id");
            $pdo->exec("DELETE FROM pemasukan WHERE setoran_id = $setoran_id");
            $message = 'Data setoran berhasil diperbarui (ditimpa)';
        } else {
            // INSERT BARU
            $stmtInsert = $pdo->prepare("
                INSERT INTO setoran (
                    tanggal, employee_id, employee_name, store_id, store_name, 
                    jam_masuk, jam_keluar, nomor_awal, nomor_akhir, 
                    total_liter, qris, cash, total_setoran,
                    total_pengeluaran, total_pemasukan, total_keseluruhan
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtInsert->execute([
                $today,
                $data['employee_id'],
                $employee_name,
                $data['store_id'],
                $store_name,
                $data['jam_masuk'],
                $data['jam_keluar'],
                $data['nomor_awal'],
                $data['nomor_akhir'],
                $data['total_liter'],
                $data['qris'],
                $data['cash'],
                $total_setoran_calculated,
                $data['total_pengeluaran'],
                $data['total_pemasukan'],
                $data['total_keseluruhan']
            ]);
            $setoran_id = $pdo->lastInsertId();
            $message = 'Data setoran berhasil disimpan';
        }

        // ----------------------------------------------------
        // SIMPAN DETAIL (PENGELUARAN & PEMASUKAN)
        // ----------------------------------------------------
        if (!empty($data['pengeluaran'])) {
            $stmtPeng = $pdo->prepare("INSERT INTO pengeluaran (setoran_id, description, amount) VALUES (?, ?, ?)");
            foreach ($data['pengeluaran'] as $item) {
                $stmtPeng->execute([$setoran_id, $item['description'], $item['amount']]);
            }
        }

        if (!empty($data['pemasukan'])) {
            $stmtMasuk = $pdo->prepare("INSERT INTO pemasukan (setoran_id, description, amount) VALUES (?, ?, ?)");
            foreach ($data['pemasukan'] as $item) {
                $stmtMasuk->execute([$setoran_id, $item['description'], $item['amount']]);
            }
        }

        jsonResponse(true, $message, ['id' => $setoran_id]);
    } catch (Exception $e) {
        jsonResponse(false, 'Error: Gagal menyimpan data setoran. ' . $e->getMessage(), [], [], 500);
    }
    break;


// =========================================================
// GET HISTORY SETORAN
// =========================================================
case 'get_history':
    try {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $employee_id_filter = $_GET['employee_id'] ?? '';
        $store_id_filter = $_GET['store_id'] ?? '';

        $where_clause = "YEAR(s.tanggal) = ? AND MONTH(s.tanggal) = ?";
        $params = [$year, $month];

        if (!empty($employee_id_filter)) {
            $where_clause .= " AND s.employee_id = ?";
            $params[] = $employee_id_filter;
        }
        if (!empty($store_id_filter)) {
            $where_clause .= " AND s.store_id = ?";
            $params[] = $store_id_filter;
        }

        // Langsung ambil dari tabel setoran (tanpa join)
        $sql_history = "
            SELECT * FROM setoran s
            WHERE {$where_clause}
            ORDER BY s.tanggal DESC, s.jam_masuk DESC
        ";
        $stmt = $pdo->prepare($sql_history);
        $stmt->execute($params);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Summary
        $sql_summary = "
            SELECT
                COUNT(id) AS count_history,
                SUM(total_liter) AS total_liter,
                SUM(cash) AS total_cash,
                SUM(qris) AS total_qris,
                SUM(total_setoran) AS total_setoran,
                SUM(total_pengeluaran) AS total_pengeluaran,
                SUM(total_pemasukan) AS total_pemasukan,
                SUM(total_keseluruhan) AS total_keseluruhan
            FROM setoran s
            WHERE {$where_clause}
        ";
        $stmtSum = $pdo->prepare($sql_summary);
        $stmtSum->execute($params);
        $summary = $stmtSum->fetch(PDO::FETCH_ASSOC);

        jsonResponse(true, 'Data history berhasil dimuat', ['setoran' => $history], $summary);
    } catch (Exception $e) {
        jsonResponse(false, 'Error: Gagal memuat history. ' . $e->getMessage(), [], [], 500);
    }
    break;


// =========================================================
// DETAIL SETORAN (READ ONLY)
// =========================================================
case 'get_setoran_detail':
    try {
        $setoran_id = $_GET['id'] ?? 0;
        if (!$setoran_id) throw new Exception("ID Setoran tidak ditemukan.");

        $stmt = $pdo->prepare("SELECT * FROM setoran WHERE id = ?");
        $stmt->execute([$setoran_id]);
        $setoran = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$setoran) throw new Exception("Data Setoran tidak ditemukan.");

        $stmt_pengeluaran = $pdo->prepare("SELECT description, amount FROM pengeluaran WHERE setoran_id = ?");
        $stmt_pengeluaran->execute([$setoran_id]);
        $pengeluaran = $stmt_pengeluaran->fetchAll(PDO::FETCH_ASSOC);

        $stmt_pemasukan = $pdo->prepare("SELECT description, amount FROM pemasukan WHERE setoran_id = ?");
        $stmt_pemasukan->execute([$setoran_id]);
        $pemasukan = $stmt_pemasukan->fetchAll(PDO::FETCH_ASSOC);

        $result = [
            'setoran' => [$setoran],
            'pengeluaran' => $pengeluaran,
            'pemasukan' => $pemasukan
        ];

        jsonResponse(true, 'Detail setoran berhasil dimuat', $result);
    } catch (Exception $e) {
        jsonResponse(false, 'Error: Gagal memuat detail setoran. ' . $e->getMessage(), [], [], 404);
    }
    break;

    // =========================================================
    // B. STORE MANAGEMENT
    // =========================================================
    case 'get_stores':
        try {
            $stmt = $pdo->query("SELECT id, store_name, address FROM stores ORDER BY store_name ASC");
            jsonResponse(true, 'Daftar Store berhasil dimuat', $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            jsonResponse(false, 'Error: ' . $e->getMessage(), [], [], 500);
        }
        break;

    case 'add_store':
        try {
            $stmt = $pdo->prepare("INSERT INTO stores (store_name, address) VALUES (?, ?)");
            $stmt->execute([$data['store_name'], $data['address'] ?? null]);
            jsonResponse(true, 'Store baru berhasil ditambahkan');
        } catch (Exception $e) {
            jsonResponse(false, 'Gagal menambahkan Store: ' . $e->getMessage(), [], [], 500);
        }
        break;

    case 'edit_store':
        try {
            $stmt = $pdo->prepare("UPDATE stores SET store_name = ?, address = ? WHERE id = ?");
            $stmt->execute([$data['store_name'], $data['address'] ?? null, $data['id']]);
            jsonResponse(true, 'Store berhasil diperbarui');
        } catch (Exception $e) {
            jsonResponse(false, 'Gagal memperbarui Store: ' . $e->getMessage(), [], [], 500);
        }
        break;

    case 'delete_store':
        try {
            $stmt = $pdo->prepare("DELETE FROM stores WHERE id = ?");
            $stmt->execute([$data['id']]);
            jsonResponse(true, 'Store berhasil dihapus');
        } catch (Exception $e) {
            jsonResponse(false, 'Gagal menghapus Store: ' . $e->getMessage(), [], [], 500);
        }
        break;


    // =========================================================
    // C. EMPLOYEE MANAGEMENT (DIPERBAIKI UNTUK FILTER STORE)
    // =========================================================
    case 'get_employees':
        try {
            // Ambil store_id dari URL (yang dikirim oleh JavaScript)
            $store_id_filter = $_GET['store_id'] ?? '';
            
            // 1. Definisikan SQL dasar
            $sql = "
                SELECT e.*, s.store_name 
                FROM employees e
                LEFT JOIN stores s ON e.store_id = s.id
            ";
            
            $params = [];
            $where_clause = "";
            $where_conditions = [];

            // Tambahkan filter store_id jika ada
            if (!empty($store_id_filter)) {
                $where_conditions[] = "e.store_id = ?";
                $params[] = $store_id_filter;
            }

            // Filter default: hanya karyawan aktif
            $where_conditions[] = "e.is_active = 1";


            // 2. Gabungkan klausa WHERE
            if (!empty($where_conditions)) {
                $where_clause = " WHERE " . implode(" AND ", $where_conditions);
            }
            
            // 3. Gabungkan dan eksekusi query
            $sql .= $where_clause . " ORDER BY e.employee_name ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            jsonResponse(true, 'Daftar Karyawan berhasil dimuat', $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            jsonResponse(false, 'Error: ' . $e->getMessage(), [], [], 500);
        }
        break;

    case 'add_employee':
        try {
            if (empty($data['store_id'])) throw new Exception("Store harus dipilih.");
            $stmt = $pdo->prepare("INSERT INTO employees (employee_name, employee_code, store_id, is_active) VALUES (?, ?, ?, 1)");
            $stmt->execute([$data['employee_name'], $data['employee_code'] ?? null, $data['store_id']]);
            jsonResponse(true, 'Karyawan baru berhasil ditambahkan');
        } catch (Exception $e) {
            jsonResponse(false, 'Gagal menambahkan Karyawan: ' . $e->getMessage(), [], [], 500);
        }
        break;

    case 'edit_employee':
        try {
            if (empty($data['store_id'])) throw new Exception("Store harus dipilih.");
            $stmt = $pdo->prepare("UPDATE employees SET employee_name = ?, employee_code = ?, store_id = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$data['employee_name'], $data['employee_code'] ?? null, $data['store_id'], $data['is_active'], $data['id']]);
            jsonResponse(true, 'Karyawan berhasil diperbarui');
        } catch (Exception $e) {
            jsonResponse(false, 'Gagal memperbarui Karyawan: ' . $e->getMessage(), [], [], 500);
        }
        break;
        
  // =========================================================
    // D. MANAGEMENT CASH FLOW (NEW)
    // =========================================================
    
    // ASUMSI: Tabel baru bernama 'cash_flow_management'
    
 case 'get_management_cash_flow':
    try {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $store_id_filter = $_GET['store_id'] ?? '';

        // =======================
        // FILTER DASAR
        // =======================
        $where_clause = "YEAR(cfm.tanggal) = ? AND MONTH(cfm.tanggal) = ?";
        $params = [$year, $month];
        if (!empty($store_id_filter)) {
            $where_clause .= " AND cfm.store_id = ?";
            $params[] = $store_id_filter;
        }

        // =======================
        // 1️⃣ DATA MANUAL
        // =======================
        $sql_cf = "
            SELECT 
                cfm.id,
                cfm.tanggal,
                s.store_name,
                cfm.description,
                cfm.amount,
                cfm.type,
                'manual' AS source
            FROM cash_flow_management cfm
            LEFT JOIN stores s ON cfm.store_id = s.id
            WHERE {$where_clause}
        ";

        // =======================
        // 2️⃣ DATA OTOMATIS DARI SETORAN
        // =======================
        $store_clause = '';
        $store_params = [];
        if (!empty($store_id_filter)) {
            $store_clause = "AND st.store_id = ?";
            $store_params[] = $store_id_filter;
        }

        $sql_setoran = "
            SELECT 
                st.id,
                st.tanggal,
                s.store_name,
                CONCAT('Setoran Harian - ', st.employee_name) AS description,
                st.total_pemasukan AS amount,
                'Pemasukan' AS type,
                'setoran' AS source
            FROM setoran st
            LEFT JOIN stores s ON st.store_id = s.id
            WHERE YEAR(st.tanggal) = ? AND MONTH(st.tanggal) = ?
            {$store_clause}

            UNION ALL

            SELECT 
                st.id,
                st.tanggal,
                s.store_name,
                CONCAT('Setoran Harian - ', st.employee_name) AS description,
                st.total_pengeluaran AS amount,
                'Pengeluaran' AS type,
                'setoran' AS source
            FROM setoran st
            LEFT JOIN stores s ON st.store_id = s.id
            WHERE YEAR(st.tanggal) = ? AND MONTH(st.tanggal) = ?
            {$store_clause}

            UNION ALL

            SELECT 
                st.id,
                st.tanggal,
                s.store_name,
                CONCAT('Total Setoran Harian - ', st.employee_name) AS description,
                st.total_setoran AS amount,
                'Setoran' AS type,
                'setoran' AS source
            FROM setoran st
            LEFT JOIN stores s ON st.store_id = s.id
            WHERE YEAR(st.tanggal) = ? AND MONTH(st.tanggal) = ?
            {$store_clause}
        ";

        // =======================
        // 3️⃣ GABUNGKAN SEMUA DATA
        // =======================
        $sql = "({$sql_cf}) UNION ALL ({$sql_setoran}) ORDER BY tanggal DESC, id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge(
            $params,
            [$year, $month], $store_params,
            [$year, $month], $store_params,
            [$year, $month], $store_params
        ));
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // =======================
        // 4️⃣ HITUNG RINGKASAN
        // =======================
        $total_pemasukan = 0;
        $total_pengeluaran = 0;
        $total_setoran = 0;
        $summary_per_store = [];

        foreach ($transactions as $t) {
            $store = $t['store_name'] ?? 'Tanpa Store';
            if (!isset($summary_per_store[$store])) {
                $summary_per_store[$store] = [
                    'pemasukan' => 0,
                    'pengeluaran' => 0,
                    'setoran' => 0,
                    'saldo' => 0
                ];
            }

            $amount = (float)$t['amount'];

            if ($t['type'] === 'Pemasukan') {
                $total_pemasukan += $amount;
                $summary_per_store[$store]['pemasukan'] += $amount;
            } elseif ($t['type'] === 'Pengeluaran') {
                $total_pengeluaran += $amount;
                $summary_per_store[$store]['pengeluaran'] += $amount;
            } elseif ($t['type'] === 'Setoran') {
                // ✅ Anggap Setoran sebagai bagian dari pemasukan
                $total_pemasukan += $amount;
                $total_setoran += $amount;
                $summary_per_store[$store]['setoran'] += $amount;
                $summary_per_store[$store]['pemasukan'] += $amount;
            }
        }

        // Hitung saldo per store
        foreach ($summary_per_store as $store => &$s) {
            $s['saldo'] = $s['pemasukan'] - $s['pengeluaran'];
        }

        $summary = [
            'total_pemasukan_manajemen' => $total_pemasukan,
            'total_pengeluaran_manajemen' => $total_pengeluaran,
            'total_setoran' => $total_setoran,
            'saldo_bersih' => $total_pemasukan - $total_pengeluaran,
            'per_store' => $summary_per_store
        ];

        jsonResponse(true, 'Data kas manajemen berhasil dimuat', $transactions, $summary);

    } catch (Exception $e) {
        jsonResponse(false, 'Error: ' . $e->getMessage(), [], [], 500);
    }
    break;


    // =========================================================
    // E. DASHBOARD WALLET & EXPORT
    // =========================================================

   case 'get_dashboard_wallet':
    try {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        // Menggunakan placeholder (?) untuk binding parameter
        $where_clause = "YEAR(s.tanggal) = ? AND MONTH(s.tanggal) = ?";
        $params = [$year, $month];

        // 1. Data All Stores (Wallet Utama)
        $sql_all = "
            SELECT
                SUM(s.total_liter) as total_liter,
                SUM(s.cash + s.qris) as total_setoran,
                SUM(s.total_pemasukan) as total_pemasukan_setoran,
                SUM(s.total_pengeluaran) as total_pengeluaran_setoran
            FROM setoran s
            WHERE {$where_clause}
        ";
        $stmt = $pdo->prepare($sql_all);
        $stmt->execute($params);
        $setoran_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ambil data cash flow management
        $sql_cf = "
            SELECT
                SUM(CASE WHEN type = 'Pemasukan' THEN amount ELSE 0 END) as pemasukan_manajemen,
                SUM(CASE WHEN type = 'Pengeluaran' THEN amount ELSE 0 END) as pengeluaran_manajemen
            FROM cash_flow_management
            WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?
        ";
        $stmt_cf = $pdo->prepare($sql_cf);
        $stmt_cf->execute([$year, $month]);
        $cf_data = $stmt_cf->fetch(PDO::FETCH_ASSOC);

        $total_income = ($setoran_data['total_setoran'] ?? 0) + ($setoran_data['total_pemasukan_setoran'] ?? 0) + ($cf_data['pemasukan_manajemen'] ?? 0);
        $total_expense = ($setoran_data['total_pengeluaran_setoran'] ?? 0) + ($cf_data['pengeluaran_manajemen'] ?? 0);

        $all_stores = [
            'total_income' => $total_income,
            'total_expense' => $total_expense,
            'balance' => $total_income - $total_expense,
            'total_liter' => $setoran_data['total_liter'] ?? 0
        ];

        // 2. Expense Breakdown (Gabungan dari pengeluaran setoran dan cash flow management)
        $sql_expense = "
            SELECT description, SUM(amount) as amount
            FROM (
                SELECT p.description, p.amount FROM pengeluaran p
                INNER JOIN setoran s ON p.setoran_id = s.id
                WHERE YEAR(s.tanggal) = ? AND MONTH(s.tanggal) = ?
                UNION ALL
                SELECT description, amount FROM cash_flow_management cfm
                WHERE cfm.type = 'Pengeluaran' AND YEAR(cfm.tanggal) = ? AND MONTH(cfm.tanggal) = ?
            ) as combined_expenses
            GROUP BY description
            ORDER BY amount DESC
        ";
        $stmt_expense = $pdo->prepare($sql_expense);
        $stmt_expense->execute(array_merge($params, $params));
        $expense_breakdown = $stmt_expense->fetchAll(PDO::FETCH_ASSOC);

        // 3. Income Breakdown
        $sql_income = "
            SELECT description, SUM(amount) as amount
            FROM (
                SELECT pm.description, pm.amount FROM pemasukan pm
                INNER JOIN setoran s ON pm.setoran_id = s.id
                WHERE YEAR(s.tanggal) = ? AND MONTH(s.tanggal) = ?
                UNION ALL
                SELECT description, amount FROM cash_flow_management cfm
                WHERE cfm.type = 'Pemasukan' AND YEAR(cfm.tanggal) = ? AND MONTH(cfm.tanggal) = ?
                UNION ALL
                SELECT 'Setoran Kasir' as description, SUM(cash + qris) as amount
                FROM setoran s2
                WHERE YEAR(s2.tanggal) = ? AND MONTH(s2.tanggal) = ?
            ) as combined_income
            GROUP BY description
            ORDER BY amount DESC
        ";
        $stmt_income = $pdo->prepare($sql_income);
        $stmt_income->execute(array_merge($params, $params, $params));
        $income_breakdown = $stmt_income->fetchAll(PDO::FETCH_ASSOC);

        // 4. Per Store Data
        $sql_store = "
            SELECT
                st.store_name,
                SUM(s.total_liter) as total_liter,
                SUM(s.cash + s.qris) as setoran,
                SUM(s.total_pemasukan) as pemasukan_setoran,
                SUM(s.total_pengeluaran) as pengeluaran_setoran
            FROM stores st
            LEFT JOIN setoran s ON st.id = s.store_id AND YEAR(s.tanggal) = ? AND MONTH(s.tanggal) = ?
            GROUP BY st.id, st.store_name
            ORDER BY st.store_name
        ";
        $stmt_store = $pdo->prepare($sql_store);
        $stmt_store->execute($params);
        $stores_raw = $stmt_store->fetchAll(PDO::FETCH_ASSOC);

        // Tambahkan data cash flow per store
        $per_store = [];
        foreach ($stores_raw as $store) {
            $store_id_query = "SELECT id FROM stores WHERE store_name = ?";
            $stmt_id = $pdo->prepare($store_id_query);
            $stmt_id->execute([$store['store_name']]);
            $store_id = $stmt_id->fetchColumn();

            $sql_cf_store = "
                SELECT
                    SUM(CASE WHEN type = 'Pemasukan' THEN amount ELSE 0 END) as pemasukan_cf,
                    SUM(CASE WHEN type = 'Pengeluaran' THEN amount ELSE 0 END) as pengeluaran_cf
                FROM cash_flow_management
                WHERE store_id = ? AND YEAR(tanggal) = ? AND MONTH(tanggal) = ?
            ";
            $stmt_cf_store = $pdo->prepare($sql_cf_store);
            $stmt_cf_store->execute([$store_id, $year, $month]);
            $cf_store = $stmt_cf_store->fetch(PDO::FETCH_ASSOC);

            $income = ($store['setoran'] ?? 0) + ($store['pemasukan_setoran'] ?? 0) + ($cf_store['pemasukan_cf'] ?? 0);
            $expense = ($store['pengeluaran_setoran'] ?? 0) + ($cf_store['pengeluaran_cf'] ?? 0);

            $per_store[] = [
                'store_name' => $store['store_name'],
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
                'total_liter' => $store['total_liter'] ?? 0
            ];
        }

        $result = [
            'all_stores' => $all_stores,
            'expense_breakdown' => $expense_breakdown,
            'income_breakdown' => $income_breakdown,
            'per_store' => $per_store
        ];

        jsonResponse(true, 'Dashboard data berhasil dimuat', $result);

    } catch (Exception $e) {
        jsonResponse(false, 'Error: ' . $e->getMessage(), [], [], 500);
    }
    break;

case 'export_dashboard':
    try {
        $type = $_GET['type'] ?? 'pdf';
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        // Ambil data yang sama dengan dashboard
        $where_clause = "YEAR(s.tanggal) = ? AND MONTH(s.tanggal) = ?";
        $params = [$year, $month];

        // Data All Stores
        $sql_all = "
            SELECT
                SUM(s.total_liter) as total_liter,
                SUM(s.cash + s.qris) as total_setoran,
                SUM(s.total_pemasukan) as total_pemasukan_setoran,
                SUM(s.total_pengeluaran) as total_pengeluaran_setoran
            FROM setoran s
            WHERE {$where_clause}
        ";
        $stmt = $pdo->prepare($sql_all);
        $stmt->execute($params);
        $setoran_data = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql_cf = "
            SELECT
                SUM(CASE WHEN type = 'Pemasukan' THEN amount ELSE 0 END) as pemasukan_manajemen,
                SUM(CASE WHEN type = 'Pengeluaran' THEN amount ELSE 0 END) as pengeluaran_manajemen
            FROM cash_flow_management
            WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?
        ";
        $stmt_cf = $pdo->prepare($sql_cf);
        $stmt_cf->execute([$year, $month]);
        $cf_data = $stmt_cf->fetch(PDO::FETCH_ASSOC);

        $total_income = ($setoran_data['total_setoran'] ?? 0) + ($setoran_data['total_pemasukan_setoran'] ?? 0) + ($cf_data['pemasukan_manajemen'] ?? 0);
        $total_expense = ($setoran_data['total_pengeluaran_setoran'] ?? 0) + ($cf_data['pengeluaran_manajemen'] ?? 0);

        if ($type === 'pdf') {
            exportPDF($year, $month, $total_income, $total_expense, $pdo);
        } else if ($type === 'excel') {
            exportExcel($year, $month, $total_income, $total_expense, $pdo);
        }

    } catch (Exception $e) {
        jsonResponse(false, 'Error: ' . $e->getMessage(), [], [], 500);
    }
    break;
case 'get_cashflow_flow':
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    $store_id = $_GET['store_id'] ?? '';
    
    $filter_store = !empty($store_id) ? $store_id : '';

    $sql_union = "
        -- 1. Total Setoran (Cash + QRIS) sebagai Pemasukan
        SELECT
            t.tanggal,
            t.store_id,
            t.total_setoran AS amount,
            'Pemasukan' AS type,
            CONCAT('Setoran Harian - ', s.store_name, ' (', t.employee_name, ')') AS description,
            'setoran' as source
        FROM
            setoran t
        JOIN
            stores s ON t.store_id = s.id
        WHERE
            MONTH(t.tanggal) = :month AND YEAR(t.tanggal) = :year
            AND (:filter_store_id = '' OR t.store_id = :filter_store_id)
            AND t.total_setoran > 0

        UNION ALL

        -- 2. Total Pengeluaran Terkait Setoran
        SELECT
            t.tanggal,
            t.store_id,
            t.total_pengeluaran AS amount,
            'Pengeluaran' AS type,
            CONCAT('Pengeluaran Setoran - ', s.store_name, ' (', t.employee_name, ')') AS description,
            'setoran_pengeluaran' as source
        FROM
            setoran t
        JOIN
            stores s ON t.store_id = s.id
        WHERE
            MONTH(t.tanggal) = :month AND YEAR(t.tanggal) = :year
            AND (:filter_store_id = '' OR t.store_id = :filter_store_id)
            AND t.total_pengeluaran > 0

        UNION ALL

        -- 3. Total Pemasukan Tambahan Terkait Setoran
        SELECT
            t.tanggal,
            t.store_id,
            t.total_pemasukan AS amount,
            'Pemasukan' AS type,
            CONCAT('Pemasukan Tambahan Setoran - ', s.store_name, ' (', t.employee_name, ')') AS description,
            'setoran_pemasukan' as source
        FROM
            setoran t
        JOIN
            stores s ON t.store_id = s.id
        WHERE
            MONTH(t.tanggal) = :month AND YEAR(t.tanggal) = :year
            AND (:filter_store_id = '' OR t.store_id = :filter_store_id)
            AND t.total_pemasukan > 0
        
        UNION ALL

        -- 4. Transaksi Cash Flow Management (Non-Setoran)
        SELECT
            tanggal,
            store_id,
            amount,
            type,
            description,
            'cash_flow' as source
        FROM
            cash_flow_management
        WHERE
            MONTH(tanggal) = :month AND YEAR(tanggal) = :year
            AND (:filter_store_id = '' OR store_id = :filter_store_id)
        
        ORDER BY tanggal ASC, type DESC
    ";

    try {
        $stmt = $pdo->prepare($sql_union);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':filter_store_id', $filter_store);
        
        $stmt->execute();
        $cashflow_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agregasi Data Cash Flow per Store
        $summary_by_store = [];
        foreach ($cashflow_data as $row) {
            $current_store_id = $row['store_id'];
            if (empty($current_store_id)) continue;

            if (!isset($summary_by_store[$current_store_id])) {
                $summary_by_store[$current_store_id] = [
                    'store_id' => $current_store_id,
                    'total_pemasukan' => 0,
                    'total_pengeluaran' => 0,
                ];
            }

            if ($row['type'] === 'Pemasukan') {
                $summary_by_store[$current_store_id]['total_pemasukan'] += $row['amount'];
            } elseif ($row['type'] === 'Pengeluaran') {
                $summary_by_store[$current_store_id]['total_pengeluaran'] += $row['amount'];
            }
        }

        // Tambahkan nama store dan hitung saldo bersih
        foreach ($summary_by_store as $key => $summary) {
            $store_stmt = $pdo->prepare("SELECT store_name FROM stores WHERE id = :id");
            $store_stmt->bindParam(':id', $summary['store_id']);
            $store_stmt->execute();
            $store_info = $store_stmt->fetch(PDO::FETCH_ASSOC);

            $summary_by_store[$key]['store_name'] = $store_info['store_name'] ?? 'N/A';
            $summary_by_store[$key]['saldo_bersih'] = 
                $summary['total_pemasukan'] - $summary['total_pengeluaran'];
        }

        jsonResponse(true, 'Cash flow data retrieved successfully.', $cashflow_data, array_values($summary_by_store));

    } catch (PDOException $e) {
        jsonResponse(false, 'Database error: ' . $e->getMessage(), [], [], 500);
    }
    break;

// Tambahkan case untuk CRUD cash flow management
case 'add_cashflow':
    $data = json_decode(file_get_contents('php://input'), true);
    
    $tanggal = $data['tanggal'] ?? '';
    $store_id = $data['store_id'] ?? '';
    $description = $data['description'] ?? '';
    $amount = $data['amount'] ?? 0;
    $type = $data['type'] ?? '';
    $category = $data['category'] ?? '';
    
    // Validasi data
    if (empty($tanggal) || empty($store_id) || empty($description) || empty($amount) || empty($type) || empty($category)) {
        jsonResponse(false, 'Semua field harus diisi');
    }
    
    if ($amount <= 0) {
        jsonResponse(false, 'Nominal harus lebih dari 0');
    }
    
    try {
        // Insert ke cash_flow_management
        $sql = "INSERT INTO cash_flow_management (tanggal, store_id, description, amount, type, category, created_at, updated_at) 
                VALUES (:tanggal, :store_id, :description, :amount, :type, :category, NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':store_id', $store_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':category', $category);
        
        if ($stmt->execute()) {
            $last_id = $pdo->lastInsertId();
            
            // Jika kategori BBM dan ada data distribusi
            if ($category === 'bbm' && isset($data['bbm_distribution'])) {
                $bbm_data = $data['bbm_distribution'];
                
                foreach ($bbm_data as $distribution) {
                    $store_id_bbm = $distribution['store_id'];
                    $jumlah_drigen = $distribution['jumlah_drigen'] ?? 0;
                    $pajak = $distribution['pajak'] ?? 0;
                    $beban = $distribution['beban'] ?? 0;
                    
                    $sql_bbm = "INSERT INTO bbm_distribution (bbm_group_id, store_id, jumlah_drigen, pajak, beban) 
                               VALUES (:bbm_group_id, :store_id, :jumlah_drigen, :pajak, :beban)";
                    
                    $stmt_bbm = $pdo->prepare($sql_bbm);
                    $stmt_bbm->bindParam(':bbm_group_id', $last_id);
                    $stmt_bbm->bindParam(':store_id', $store_id_bbm);
                    $stmt_bbm->bindParam(':jumlah_drigen', $jumlah_drigen);
                    $stmt_bbm->bindParam(':pajak', $pajak);
                    $stmt_bbm->bindParam(':beban', $beban);
                    $stmt_bbm->execute();
                }
            }
            
            jsonResponse(true, 'Transaksi berhasil ditambahkan', ['id' => $last_id]);
        } else {
            jsonResponse(false, 'Gagal menambahkan transaksi');
        }
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Database error: ' . $e->getMessage());
    }
    break;

case 'update_cashflow':
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? '';
    $tanggal = $data['tanggal'] ?? '';
    $store_id = $data['store_id'] ?? '';
    $description = $data['description'] ?? '';
    $amount = $data['amount'] ?? 0;
    $type = $data['type'] ?? '';
    $category = $data['category'] ?? '';
    
    // Validasi data
    if (empty($id) || empty($tanggal) || empty($store_id) || empty($description) || empty($amount) || empty($type) || empty($category)) {
        jsonResponse(false, 'Semua field harus diisi');
    }
    
    try {
        // Update cash_flow_management
        $sql = "UPDATE cash_flow_management 
                SET tanggal = :tanggal, store_id = :store_id, description = :description, 
                    amount = :amount, type = :type, category = :category, updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':store_id', $store_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':category', $category);
        
        if ($stmt->execute()) {
            jsonResponse(true, 'Transaksi berhasil diupdate');
        } else {
            jsonResponse(false, 'Gagal mengupdate transaksi');
        }
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Database error: ' . $e->getMessage());
    }
    break;

case 'delete_cashflow':
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? '';
    
    if (empty($id)) {
        jsonResponse(false, 'ID transaksi tidak valid');
    }
    
    try {
        // Hapus distribusi BBM terlebih dahulu (jika ada)
        $sql_delete_bbm = "DELETE FROM bbm_distribution WHERE bbm_group_id = :id";
        $stmt_bbm = $pdo->prepare($sql_delete_bbm);
        $stmt_bbm->bindParam(':id', $id);
        $stmt_bbm->execute();
        
        // Hapus transaksi cash flow
        $sql = "DELETE FROM cash_flow_management WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            jsonResponse(true, 'Transaksi berhasil dihapus');
        } else {
            jsonResponse(false, 'Gagal menghapus transaksi');
        }
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Database error: ' . $e->getMessage());
    }
    break;

case 'get_cashflow_detail':
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        jsonResponse(false, 'ID transaksi tidak valid');
    }
    
    try {
        // Ambil data transaksi
        $sql = "SELECT * FROM cash_flow_management WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $cashflow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cashflow) {
            // Jika kategori BBM, ambil data distribusi
            if ($cashflow['category'] === 'bbm') {
                $sql_bbm = "SELECT * FROM bbm_distribution WHERE bbm_group_id = :id";
                $stmt_bbm = $pdo->prepare($sql_bbm);
                $stmt_bbm->bindParam(':id', $id);
                $stmt_bbm->execute();
                $bbm_distribution = $stmt_bbm->fetchAll(PDO::FETCH_ASSOC);
                $cashflow['bbm_distribution'] = $bbm_distribution;
            }
            
            jsonResponse(true, 'Data transaksi berhasil diambil', $cashflow);
        } else {
            jsonResponse(false, 'Transaksi tidak ditemukan');
        }
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Database error: ' . $e->getMessage());
    }
    break;
default:
    jsonResponse(false, 'Aksi tidak valid atau tidak ditemukan');
}
// =========================================================
// FUNGSI EXPORT PDF & EXCEL
// =========================================================

function exportPDF($year, $month, $total_income, $total_expense, $pdo) {
    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $month_name = $months[intval($month)];

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Laporan_Keuangan_' . $month_name . '_' . $year . '.pdf"');

    // Menggunakan teknik sederhana untuk generate PDF dengan FPDF atau TCPDF
    // Karena library tidak tersedia, kita buat HTML to PDF sederhana

    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Laporan Keuangan</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #333; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            th { background-color: #4F46E5; color: white; }
            .summary { background-color: #f0f0f0; padding: 15px; margin: 20px 0; border-radius: 5px; }
            .positive { color: green; font-weight: bold; }
            .negative { color: red; font-weight: bold; }
        </style>
    </head>
    <body>
        <h1>LAPORAN KEUANGAN</h1>
        <h2 style="text-align:center;">' . $month_name . ' ' . $year . '</h2>

        <div class="summary">
            <h3>Ringkasan Keuangan</h3>
            <table>
                <tr>
                    <td><strong>Total Pemasukan:</strong></td>
                    <td class="positive">Rp ' . number_format($total_income, 0, ',', '.') . '</td>
                </tr>
                <tr>
                    <td><strong>Total Pengeluaran:</strong></td>
                    <td class="negative">Rp ' . number_format($total_expense, 0, ',', '.') . '</td>
                </tr>
                <tr>
                    <td><strong>Saldo Bersih:</strong></td>
                    <td class="' . ($total_income - $total_expense >= 0 ? 'positive' : 'negative') . '">Rp ' . number_format($total_income - $total_expense, 0, ',', '.') . '</td>
                </tr>
            </table>
        </div>

        <p style="text-align:center; margin-top:40px; font-size:12px; color:#666;">
            Dokumen ini digenerate otomatis oleh sistem Setoran Harian<br>
            Tanggal cetak: ' . date('d-m-Y H:i:s') . '
        </p>
    </body>
    </html>';

    echo $html;
    exit;
}

function exportExcel($year, $month, $total_income, $total_expense, $pdo) {
    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $month_name = $months[intval($month)];

    // Set headers untuk download Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Laporan_Keuangan_' . $month_name . '_' . $year . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Ambil detail data
    $where_clause = "YEAR(s.tanggal) = ? AND MONTH(s.tanggal) = ?";
    $params = [$year, $month];

    // Ambil semua setoran
    $sql_setoran = "
        SELECT s.tanggal, st.store_name, e.employee_name, s.total_liter, s.cash, s.qris,
               s.total_setoran, s.total_pengeluaran, s.total_pemasukan, s.total_keseluruhan
        FROM setoran s
        LEFT JOIN stores st ON s.store_id = st.id
        LEFT JOIN employees e ON s.employee_id = e.id
        WHERE {$where_clause}
        ORDER BY s.tanggal DESC
    ";
    $stmt = $pdo->prepare($sql_setoran);
    $stmt->execute($params);
    $setoran_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo '</head>';
    echo '<body>';

    echo '<h1>LAPORAN KEUANGAN - ' . $month_name . ' ' . $year . '</h1>';

    echo '<table border="1">';
    echo '<tr><td colspan="2"><strong>RINGKASAN</strong></td></tr>';
    echo '<tr><td>Total Pemasukan</td><td>Rp ' . number_format($total_income, 0, ',', '.') . '</td></tr>';
    echo '<tr><td>Total Pengeluaran</td><td>Rp ' . number_format($total_expense, 0, ',', '.') . '</td></tr>';
    echo '<tr><td>Saldo Bersih</td><td>Rp ' . number_format($total_income - $total_expense, 0, ',', '.') . '</td></tr>';
    echo '</table>';

    echo '<br><br>';

    echo '<table border="1">';
    echo '<tr>
            <th>Tanggal</th>
            <th>Store</th>
            <th>Karyawan</th>
            <th>Total Liter</th>
            <th>Cash</th>
            <th>QRIS</th>
            <th>Total Setoran</th>
            <th>Pengeluaran</th>
            <th>Pemasukan</th>
            <th>Total Bersih</th>
          </tr>';

    foreach ($setoran_list as $row) {
        echo '<tr>';
        echo '<td>' . $row['tanggal'] . '</td>';
        echo '<td>' . ($row['store_name'] ?? 'N/A') . '</td>';
        echo '<td>' . ($row['employee_name'] ?? 'N/A') . '</td>';
        echo '<td>' . number_format($row['total_liter'], 2, ',', '.') . '</td>';
        echo '<td>Rp ' . number_format($row['cash'], 0, ',', '.') . '</td>';
        echo '<td>Rp ' . number_format($row['qris'], 0, ',', '.') . '</td>';
        echo '<td>Rp ' . number_format($row['total_setoran'], 0, ',', '.') . '</td>';
        echo '<td>Rp ' . number_format($row['total_pengeluaran'], 0, ',', '.') . '</td>';
        echo '<td>Rp ' . number_format($row['total_pemasukan'], 0, ',', '.') . '</td>';
        echo '<td>Rp ' . number_format($row['total_keseluruhan'], 0, ',', '.') . '</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '</body></html>';
    exit;
}
?>