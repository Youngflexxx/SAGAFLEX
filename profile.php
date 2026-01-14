<?php
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$profileId = $_GET['user_id'] ?? header("Location: home.php");

// Obtener datos del usuario
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$profileId]);
$userProfile = $stmtUser->fetch();

if (!$userProfile) die("Usuario no encontrado");

// LÓGICA DE PRIVACIDAD DE POSTS
// Si es mi perfil, veo todo. Si es otro, solo lo público.
if ($profileId == $_SESSION['user_id']) {
    $sql = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM posts WHERE user_id = ? AND is_private = 0 ORDER BY created_at DESC";
}
$stmtPosts = $pdo->prepare($sql);
$stmtPosts->execute([$profileId]);
$posts = $stmtPosts->fetchAll();

// Verificar si estamos en "Modo Edición"
$isEditing = isset($_GET['edit']) && $_GET['edit'] == 'true' && $profileId == $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de @<?= htmlspecialchars($userProfile['username']) ?></title>
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

    <div class="bg-indigo-600 h-32 w-full relative overflow-hidden">
        <div class="absolute inset-0 opacity-20 bg-[url('SAGAFLEXLOGO.jpg')] bg-center bg-cover"></div>
    </div>

    <div class="px-4 relative mb-6">
        
        <div class="w-32 h-32 rounded-full absolute -top-16 border-4 border-white overflow-hidden bg-white">
            <img src="uploads/<?= htmlspecialchars($userProfile['profile_picture'] ?? 'default.png') ?>" 
                 class="w-full h-full object-cover" 
                 alt="Foto de perfil">
        </div>

        <div class="h-16 flex justify-end items-center">
            <?php if($profileId == $_SESSION['user_id']): ?>
                <?php if(!$isEditing): ?>
                    <a href="profile.php?user_id=<?= $profileId ?>&edit=true" 
                       class="border border-gray-300 font-bold px-4 py-2 rounded-full hover:bg-gray-50 text-sm transition">
                        Editar perfil
                    </a>
                <?php else: ?>
                    <a href="profile.php?user_id=<?= $profileId ?>" 
                       class="text-red-500 font-bold text-sm mr-4 hover:underline">
                        Cancelar
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="mt-2">
            <?php if ($isEditing): ?>
                <form action="actions.php" method="POST" enctype="multipart/form-data" class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <label class="block text-sm font-bold text-gray-700 mb-2">Cambiar Foto</label>
                    <input type="file" name="profile_pic" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 mb-4"/>
                    
                    <label class="block text-sm font-bold text-gray-700 mb-2">Biografía</label>
                    <textarea name="bio" rows="3" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Cuéntanos sobre ti..."><?= htmlspecialchars($userProfile['bio']) ?></textarea>
                    
                    <button type="submit" class="mt-3 bg-black text-white font-bold px-6 py-2 rounded-full hover:bg-gray-800 transition">
                        Guardar cambios
                    </button>
                </form>
            <?php else: ?>
                <h1 class="font-bold text-xl text-gray-900">@<?= htmlspecialchars($userProfile['username']) ?></h1>
                <p class="text-gray-700 mt-2 whitespace-pre-wrap"><?= htmlspecialchars($userProfile['bio'] ?? 'Sin biografía.') ?></p>
                <div class="flex gap-4 mt-3 text-gray-500 text-sm">
                    <span>📅 Se unió en <?= date("F Y", strtotime($userProfile['created_at'])) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex border-b border-gray-100 font-bold text-gray-500 mt-4">
        <div class="flex-1 text-center py-3 text-gray-900 border-b-4 border-indigo-600 cursor-pointer">Posts</div>
        <div class="flex-1 text-center py-3 hover:bg-gray-50 cursor-pointer">Respuestas</div>
        <div class="flex-1 text-center py-3 hover:bg-gray-50 cursor-pointer">Media</div>
        <div class="flex-1 text-center py-3 hover:bg-gray-50 cursor-pointer">Likes</div>
    </div>

    <div>
        <?php if(empty($posts)): ?>
            <div class="p-8 text-center text-gray-500">
                <?= ($profileId == $_SESSION['user_id']) ? "Aún no has publicado nada." : "Este usuario no tiene posts visibles." ?>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                            <img src="uploads/<?= htmlspecialchars($userProfile['profile_picture'] ?? 'default.png') ?>" class="w-full h-full object-cover">
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold"><?= htmlspecialchars($userProfile['username']) ?></span>
                                <span class="text-gray-500 text-sm">· <?= date("d M", strtotime($post['created_at'])) ?></span>
                                <?php if($post['is_private']): ?>
                                    <span class="bg-gray-200 text-gray-600 text-xs px-1 rounded">🔒 Privado</span>
                                <?php endif; ?>
                            </div>
                            <p class="mt-1 text-gray-900 whitespace-pre-wrap"><?= htmlspecialchars($post['content']) ?></p>
                            <span class="text-indigo-600 text-xs font-bold">#<?= $post['category'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</body>
</html>