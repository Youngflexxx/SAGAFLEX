<?php
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// ID del perfil a visitar (si no hay GET, redirige a home)
$profileId = $_GET['user_id'] ?? header("Location: home.php");

// Obtener datos del usuario
$stmtUser = $pdo->prepare("SELECT username, email, created_at, bio FROM users WHERE id = ?");
$stmtUser->execute([$profileId]);
$userProfile = $stmtUser->fetch();

if (!$userProfile) die("Usuario no encontrado");

// Obtener posts de ESTE usuario
$stmtPosts = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmtPosts->execute([$profileId]);
$posts = $stmtPosts->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?= htmlspecialchars($userProfile['username']) ?> / Sagaflex</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">

<div class="container mx-auto max-w-2xl border-x border-gray-100 min-h-screen">
    
    <div class="sticky top-0 bg-white/90 backdrop-blur z-10 px-4 py-2 flex items-center gap-4 border-b border-gray-100">
        <a href="home.php" class="p-2 rounded-full hover:bg-gray-100">⬅️</a>
        <div>
            <h2 class="font-bold text-lg leading-tight"><?= htmlspecialchars($userProfile['username']) ?></h2>
            <p class="text-xs text-gray-500"><?= count($posts) ?> posts</p>
        </div>
    </div>

    <div class="bg-indigo-200 h-32 w-full"></div>
    <div class="px-4 relative mb-4">
        <div class="w-32 h-32 bg-white rounded-full absolute -top-16 p-1">
            <div class="w-full h-full bg-indigo-600 rounded-full flex items-center justify-center text-4xl text-white font-bold">
                <?= strtoupper(substr($userProfile['username'], 0, 1)) ?>
            </div>
        </div>
        <div class="h-16 flex justify-end items-center">
            <?php if($profileId == $_SESSION['user_id']): ?>
                <button class="border border-gray-300 font-bold px-4 py-2 rounded-full hover:bg-gray-50 text-sm">
                    Editar perfil
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="px-4 mb-6">
        <h1 class="font-bold text-xl">@<?= htmlspecialchars($userProfile['username']) ?></h1>
        <p class="text-gray-700 mt-2"><?= htmlspecialchars($userProfile['bio'] ?? 'Sin biografía aún.') ?></p>
        <div class="flex gap-4 mt-3 text-gray-500 text-sm">
            <span>📅 Se unió en <?= date("F Y", strtotime($userProfile['created_at'])) ?></span>
        </div>
    </div>

    <div class="flex border-b border-gray-100 font-bold text-gray-500">
        <div class="flex-1 text-center py-3 hover:bg-gray-50 cursor-pointer text-gray-900 border-b-4 border-indigo-600">
            Posts
        </div>
        <div class="flex-1 text-center py-3 hover:bg-gray-50 cursor-pointer">
            Respuestas
        </div>
        <div class="flex-1 text-center py-3 hover:bg-gray-50 cursor-pointer">
            Likes
        </div>
    </div>

    <div>
        <?php if(empty($posts)): ?>
            <div class="p-8 text-center text-gray-500">Este usuario aún no ha posteado nada.</div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex-shrink-0 flex items-center justify-center font-bold text-indigo-600">
                             <?= strtoupper(substr($userProfile['username'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold"><?= htmlspecialchars($userProfile['username']) ?></span>
                                <span class="text-gray-500 text-sm">· <?= date("d M", strtotime($post['created_at'])) ?></span>
                            </div>
                            <p class="mt-1 text-gray-900"><?= htmlspecialchars($post['content']) ?></p>
                            <div class="mt-2 text-indigo-600 text-xs font-bold bg-indigo-50 inline-block px-2 rounded">
                                <?= $post['category'] ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>