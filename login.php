<?php
session_start();
// Jika sudah login, redirect langsung ke admin.php
if (isset($_SESSION['user_id'])) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .login-card {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="login-card bg-white p-8 rounded-lg w-full max-w-sm">
        <h1 class="text-3xl font-bold text-center mb-6 text-indigo-600">Admin Login</h1>
        
        <form id="loginForm">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" id="username" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <p id="loginMessage" class="text-center text-red-500 mb-4 hidden"></p>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Login
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const messageEl = document.getElementById('loginMessage');
            
            messageEl.textContent = 'Memproses...';
            messageEl.classList.remove('hidden', 'text-red-500', 'text-green-500');
            messageEl.classList.add('text-indigo-500');

            try {
                const response = await fetch('api.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username: username, password: password })
                });

                const result = await response.json();

                if (result.success) {
                    messageEl.textContent = 'Login berhasil! Mengarahkan...';
                    messageEl.classList.remove('text-red-500', 'text-indigo-500');
                    messageEl.classList.add('text-green-500');
                    window.location.href = 'admin.php'; // Redirect ke panel admin
                } else {
                    messageEl.textContent = result.message || 'Username atau password salah.';
                    messageEl.classList.remove('text-green-500', 'text-indigo-500');
                    messageEl.classList.add('text-red-500');
                }
            } catch (error) {
                messageEl.textContent = 'Gagal terhubung ke server.';
                messageEl.classList.remove('text-green-500', 'text-indigo-500');
                messageEl.classList.add('text-red-500');
            }
        });
    </script>
</body>
</html>