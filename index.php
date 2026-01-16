<?php
require 'db.php';
session_start();
if (isset($_SESSION['user_id'])) { header("Location: home.php"); exit; }
$stmt = $pdo->query("SELECT posts.*, users.username, users.profile_picture FROM posts JOIN users ON posts.user_id = users.id WHERE posts.is_private = 0 ORDER BY posts.created_at DESC");
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido / Sagaflex</title>
    <link rel="icon" type="image/png" href="favicon.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        saga: { main: '#5F727B', card: '#455A64', light: '#CFD8DC', gold: '#FFD700', goldhover: '#E6C200', input: '#37474F' }
                    }
                }
            }
        }
    </script>
    <style> body { background-color: #5F727B; color: #fff; } </style>
</head>
<body>

    <header class="sticky top-0 z-50 bg-saga-main/90 backdrop-blur border-b border-saga-light/10">
        <div class="container mx-auto max-w-4xl px-4 py-4 flex justify-between items-center">
            <div><img src="logo-gold.png" alt="Sagaflex" class="h-16 w-auto object-contain"></div>
            <div class="flex gap-4">
                <a href="login.php" class="px-6 py-2 font-bold text-saga-light hover:bg-saga-card rounded-full transition border border-saga-light/30">Entrar</a>
                <a href="register.php" class="px-6 py-2 font-bold bg-saga-gold text-black rounded-full hover:bg-saga-goldhover transition shadow-md">Registrarse</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto max-w-2xl min-h-screen border-x border-saga-light/10 bg-saga-card mt-6 rounded-t-2xl overflow-hidden shadow-2xl">
        <div class="p-12 border-b border-saga-light/10 text-center bg-gradient-to-b from-saga-main to-saga-card">
            <h1 class="text-5xl font-extrabold text-saga-gold mb-6">Lo que está pasando</h1>
            <p class="text-saga-light mb-10 text-xl">Únete a la comunidad dorada.</p>
            <a href="register.php" class="inline-block px-10 py-4 bg-saga-gold text-black font-extrabold text-xl rounded-full hover:bg-saga-goldhover transition shadow-lg transform hover:scale-105">Crear cuenta</a>
        </div>

        <div class="pb-24 bg-saga-main">
            <?php foreach ($posts as $post): ?>
                <article class="p-6 border-b border-saga-light/10 bg-saga-card">
                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-full overflow-hidden border border-saga-light/20 flex-shrink-0">
                            <img src="uploads/<?= htmlspecialchars($post['profile_picture'] ?? 'default.png') ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-bold text-white text-lg"><?= htmlspecialchars($post['username']) ?></span>
                                <span class="text-saga-light text-sm">· <?= date("d M", strtotime($post['created_at'])) ?></span>
                            </div>
                            <p class="text-gray-200 text-base"><?= htmlspecialchars($post['content']) ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>