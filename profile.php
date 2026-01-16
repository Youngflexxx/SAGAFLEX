<?php
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$profileId = $_GET['user_id'] ?? header("Location: home.php");

// Datos Usuario
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$profileId]);
$userProfile = $stmtUser->fetch();

if (!$userProfile) die("Usuario no encontrado");

// SQL Posts + LIKES (Corazones Dorados)
if ($profileId == $_SESSION['user_id']) {
    $sql = "SELECT posts.*, (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as total_likes, (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) as liked_by_me FROM posts WHERE user_id = ? ORDER BY created_at DESC";
    $params = [$_SESSION['user_id'], $profileId];
} else {
    $sql = "SELECT posts.*, (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as total_likes, (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) as liked_by_me FROM posts WHERE user_id = ? AND is_private = 0 ORDER BY created_at DESC";
     $params = [$_SESSION['user_id'], $profileId];
}
$stmtPosts = $pdo->prepare($sql);
$stmtPosts->execute($params);
$posts = $stmtPosts->fetchAll();

$isEditing = isset($_GET['edit']) && $_GET['edit'] == 'true' && $profileId == $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de @<?= htmlspecialchars($userProfile['username']) ?></title>
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
    <style> 
        body { background-color: #5F727B; color: #fff; } 
        .saga-input { background-color: #37474F; border: 1px solid #546E7A; color: white; } 
        .saga-input:focus { border-color: #FFD700; outline: none; box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3); }
        .like-anim:active { transform: scale(1.3); transition: transform 0.1s; }
    </style>
</head>
<body>

<div class="container mx-auto max-w-2xl border-x border-saga-light/10 min-h-screen bg-saga-card">
    
    <div class="sticky top-0 bg-saga-card/90 backdrop-blur z-10 px-4 py-3 flex items-center gap-4 border-b border-saga-light/10">
        <a href="home.php" class="p-2 rounded-full hover:bg-saga-main text-saga-light transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        </a>
        <div>
            <h2 class="font-bold text-lg leading-tight text-white"><?= htmlspecialchars($userProfile['username']) ?></h2>
            <p class="text-xs text-saga-light/70"><?= count($posts) ?> posts</p>
        </div>
    </div>

    <div class="bg-gradient-to-r from-saga-main to-saga-input h-40 w-full"></div>

    <div class="px-4 relative mb-6">
        <div class="w-32 h-32 rounded-full absolute -top-16 border-4 border-saga-card overflow-hidden bg-saga-main">
            <img src="uploads/<?= htmlspecialchars($userProfile['profile_picture'] ?? 'default.png') ?>" class="w-full h-full object-cover">
        </div>

        <div class="h-16 flex justify-end items-center">
            <?php if($profileId == $_SESSION['user_id']): ?>
                <?php if(!$isEditing): ?>
                    <a href="profile.php?user_id=<?= $profileId ?>&edit=true" 
                       class="border border-saga-gold font-bold px-5 py-2 rounded-full hover:bg-saga-gold/10 text-saga-gold text-sm transition">
                        Editar perfil
                    </a>
                <?php else: ?>
                    <a href="profile.php?user_id=<?= $profileId ?>" class="text-red-300 font-bold text-sm mr-4 hover:underline">Cancelar</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <?php if ($isEditing): ?>
                <form action="actions.php" method="POST" enctype="multipart/form-data" class="bg-saga-main p-6 rounded-xl border border-saga-light/10 space-y-4">
                    <input type="hidden" name="action" value="update_profile">
                    <div>
                        <label class="block text-sm font-bold text-saga-gold mb-2">Cambiar Foto</label>
                        <input type="file" name="profile_pic" class="block w-full text-sm text-saga-light file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-saga-gold file:text-black hover:file:bg-saga-goldhover cursor-pointer"/>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-saga-gold mb-2">Biografía</label>
                        <textarea name="bio" rows="3" class="saga-input w-full p-3 rounded-lg resize-none" placeholder="Cuéntanos sobre ti..."><?= htmlspecialchars($userProfile['bio']) ?></textarea>
                    </div>
                    <button type="submit" class="bg-saga-gold text-black font-bold px-6 py-2 rounded-full hover:bg-saga-goldhover transition shadow-md w-full sm:w-auto">
                        Guardar cambios
                    </button>
                </form>
            <?php else: ?>
                <h1 class="font-extrabold text-2xl text-white">@<?= htmlspecialchars($userProfile['username']) ?></h1>
                <p class="text-gray-200 mt-3 whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($userProfile['bio'] ?? 'Sin biografía.') ?></p>
                <div class="flex gap-4 mt-4 text-saga-light/70 text-sm items-center">
                     <span>📅 Se unió en <?= date("F Y", strtotime($userProfile['created_at'])) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-saga-main mt-6">
        <?php if(empty($posts)): ?>
            <div class="p-12 text-center text-saga-light">
                <?= ($profileId == $_SESSION['user_id']) ? "Aún no has publicado nada." : "Este usuario no tiene posts visibles." ?>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="p-6 border-b border-saga-light/10 hover:bg-saga-card/30 transition cursor-pointer bg-saga-card">
                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-full overflow-hidden border border-saga-light/20 bg-saga-card flex-shrink-0">
                            <img src="uploads/<?= htmlspecialchars($userProfile['profile_picture'] ?? 'default.png') ?>" class="w-full h-full object-cover">
                        </div>
                        
                        <div class="flex-1 min-w-0">
                             <div class="flex items-center gap-2 truncate mb-1">
                                <span class="font-bold text-white text-[15px]"><?= htmlspecialchars($userProfile['username']) ?></span>
                                <span class="text-saga-light text-sm flex-shrink-0">· <?= date("d M", strtotime($post['created_at'])) ?></span>
                                <?php if($post['is_private']): ?>
                                    <span class="ml-1 text-xs bg-saga-input border border-saga-light/20 text-saga-light px-2 py-0.5 rounded-full">🔒 Privado</span>
                                <?php endif; ?>
                            </div>
                            <p class="mt-1 text-gray-200 text-[15px] leading-relaxed whitespace-pre-wrap break-words"><?= htmlspecialchars($post['content']) ?></p>
                            
                            <div class="mt-4 flex items-center gap-12 text-saga-light text-sm select-none">
                                 <form action="actions.php" method="POST" class="inline">
                                    <input type="hidden" name="action" value="toggle_like">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    
                                    <button type="submit" class="group flex items-center gap-2 transition like-anim <?= $post['liked_by_me'] ? 'text-saga-gold' : 'text-saga-light hover:text-saga-gold' ?>">
                                        <?php if($post['liked_by_me']): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 drop-shadow-[0_0_8px_rgba(255,215,0,0.5)]">
                                              <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                            </svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        <?php endif; ?>
                                        <span class="font-bold"><?= $post['total_likes'] > 0 ? $post['total_likes'] : '' ?></span>
                                    </button>
                                </form>

                                <span class="text-xs font-bold text-saga-gold border border-saga-gold/30 px-3 py-1 rounded-full uppercase tracking-wider"><?= $post['category'] ?></span>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</body>
</html>