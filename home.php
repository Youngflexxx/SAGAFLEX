<?php
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$userId = $_SESSION['user_id'];

// OBTENER MI AVATAR
$stmtMe = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmtMe->execute([$userId]);
$myUser = $stmtMe->fetch();
$myAvatar = $myUser['profile_picture'] ?? 'default.png';

// =================================================================================
// CONSULTA SQL AVANZADA
// 1. Trae los posts.
// 2. Trae datos del usuario (autor).
// 3. (Subconsulta) Cuenta el total de likes de cada post.
// 4. (Subconsulta) Verifica si TU (el usuario logueado) diste like (1 o 0).
// =================================================================================
$sql = "
    SELECT 
        posts.*, 
        users.username, 
        users.profile_picture,
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as total_likes,
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) as liked_by_me
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.is_private = 0 OR posts.user_id = ? 
    ORDER BY posts.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId, $userId]); // Pasamos el ID dos veces (una para el like, otra para el filtro privado)
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio / Sagaflex</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/petite-vue" defer init></script>
    <style> 
        .hide-scrollbar::-webkit-scrollbar { display: none; } 
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Animación para el corazón */
        @keyframes pop { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
        .like-anim:active { animation: pop 0.2s ease-out; }
    </style>
</head>
<body class="bg-white text-gray-900">

<div class="container mx-auto max-w-7xl flex min-h-screen">
    
    <header class="hidden md:flex w-1/4 flex-col justify-between sticky top-0 h-screen p-4 border-r border-gray-100 overflow-y-auto hide-scrollbar">
        <div class="space-y-6">
            <div class="pl-2 mb-2">
                <a href="home.php" class="block">
                    <img src="SAGAFLEXLOGO.jpg" alt="Sagaflex" class="h-20 w-auto object-contain -ml-2">
                </a>
            </div>
            <nav class="space-y-2 text-xl">
                <a href="home.php" class="flex items-center gap-4 px-4 py-3 font-semibold rounded-full hover:bg-gray-100 transition">
                    <span>🏠</span> <span class="hidden lg:inline">Inicio</span>
                </a>
                <a href="profile.php?user_id=<?= $_SESSION['user_id'] ?>" class="flex items-center gap-4 px-4 py-3 rounded-full hover:bg-gray-100 transition">
                    <span>👤</span> <span class="hidden lg:inline">Perfil</span>
                </a>
            </nav>
            <button class="w-full bg-indigo-600 text-white font-bold py-3 rounded-full shadow hover:bg-indigo-700 transition">
                <span class="hidden lg:inline">Postear</span>
                <span class="lg:hidden">+</span>
            </button>
        </div>
        <div class="mb-4">
            <a href="actions.php?action=logout" class="flex items-center gap-2 px-4 py-3 rounded-full hover:bg-red-50 text-red-600 font-bold transition">
                <span>🚪</span> <span class="hidden lg:inline">Cerrar Sesión</span>
            </a>
        </div>
    </header>

    <main class="w-full md:w-2/4 border-r border-gray-100 min-h-screen pb-20">
        
        <div class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-gray-100 p-4 flex justify-between items-center md:hidden">
            <h2 class="text-lg font-bold">Inicio</h2>
            <a href="actions.php?action=logout" class="text-sm text-red-500 font-bold">Salir</a>
        </div>

        <div class="border-b border-gray-100 p-4" v-scope="{ chars: 0, max: 280, content: '' }">
            <form action="actions.php" method="POST">
                <input type="hidden" name="action" value="create_post">
                <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 flex-shrink-0">
                        <img src="uploads/<?= htmlspecialchars($myAvatar) ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <textarea name="content" v-model="content" @input="chars = $el.value.length" :maxlength="max"
                            class="w-full border-none focus:ring-0 text-xl resize-none placeholder-gray-500 outline-none" 
                            rows="2" placeholder="¿Qué está pasando?"></textarea>
                        
                        <div class="flex justify-between items-center mt-2 border-t pt-2 border-gray-50">
                            <div class="flex gap-2 items-center">
                                <select name="category" class="text-sm text-indigo-600 font-bold bg-indigo-50 rounded-full px-3 py-1 outline-none cursor-pointer hover:bg-indigo-100">
                                    <option value="General">General</option>
                                    <option value="Tech">Tech</option>
                                    <option value="Random">Random</option>
                                </select>
                                <label class="flex items-center gap-1 text-sm text-gray-500 cursor-pointer select-none">
                                    <input type="checkbox" name="is_private" value="1" class="rounded text-indigo-600 focus:ring-indigo-600"> <span>🔒</span>
                                </label>
                            </div>
                            <button type="submit" :disabled="chars === 0" class="bg-indigo-600 text-white font-bold px-4 py-2 rounded-full disabled:opacity-50 hover:bg-indigo-700 transition">Postear</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div>
            <?php foreach ($posts as $post): ?>
                <article class="p-4 border-b border-gray-100 hover:bg-gray-50 transition cursor-pointer">
                    <div class="flex gap-3">
                        <a href="profile.php?user_id=<?= $post['user_id'] ?>" class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 bg-gray-100">
                                <img src="uploads/<?= htmlspecialchars($post['profile_picture'] ?? 'default.png') ?>" class="w-full h-full object-cover">
                            </div>
                        </a>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center gap-1 truncate">
                                    <a href="profile.php?user_id=<?= $post['user_id'] ?>" class="font-bold hover:underline truncate"><?= htmlspecialchars($post['username']) ?></a>
                                    <span class="text-gray-500 text-sm flex-shrink-0">· <?= date("d M", strtotime($post['created_at'])) ?></span>
                                    <?php if($post['is_private']): ?> <span class="ml-1 text-[10px] bg-gray-200 text-gray-600 px-1 rounded flex-shrink-0">🔒</span> <?php endif; ?>
                                </div>
                                <?php if($post['user_id'] == $_SESSION['user_id']): ?>
                                    <div class="flex gap-3 flex-shrink-0">
                                        <a href="edit.php?id=<?= $post['id'] ?>" class="text-gray-400 hover:text-blue-500 transition">✏️</a>
                                        <form action="actions.php" method="POST" onsubmit="return confirm('¿Borrar?');" class="inline">
                                            <input type="hidden" name="action" value="delete_post">
                                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                            <button type="submit" class="text-gray-400 hover:text-red-500 transition">🗑️</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <p class="mt-1 text-gray-900 text-[15px] leading-relaxed whitespace-pre-wrap break-words"><?= htmlspecialchars($post['content']) ?></p>
                            
                            <div class="mt-3 flex items-center gap-12 text-gray-500 text-sm select-none">
                                
                                <form action="actions.php" method="POST" class="inline">
                                    <input type="hidden" name="action" value="toggle_like">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    
                                    <button type="submit" class="group flex items-center gap-2 transition like-anim <?= $post['liked_by_me'] ? 'text-pink-600' : 'hover:text-pink-500' ?>">
                                        
                                        <?php if($post['liked_by_me']): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                              <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                            </svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        <?php endif; ?>

                                        <span class="text-xs font-medium"><?= $post['total_likes'] > 0 ? $post['total_likes'] : '' ?></span>
                                    </button>
                                </form>

                                <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full text-xs font-medium">#<?= $post['category'] ?></span>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <aside class="hidden lg:block w-1/4 pl-8 py-4 sticky top-0 h-screen overflow-y-auto hide-scrollbar">
        <div class="sticky top-0 bg-white pb-4 z-10">
            <input type="text" placeholder="Buscar en Sagaflex" class="w-full bg-gray-100 border-none rounded-full py-3 px-5 text-gray-700 focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
        </div>
        <div class="bg-gray-50 rounded-2xl p-4">
            <h3 class="font-bold text-xl mb-4 text-gray-900">Qué está pasando</h3>
            <div class="space-y-5">
                <div class="cursor-pointer hover:bg-gray-100 p-2 rounded transition -mx-2">
                    <p class="text-xs text-gray-500 flex justify-between"><span>Tecnología · Tendencia</span></p>
                    <p class="font-bold text-gray-900">#SagaflexLaunch</p>
                    <p class="text-xs text-gray-500">10.5K posts</p>
                </div>
            </div>
            <div class="mt-4 text-indigo-600 text-sm cursor-pointer hover:underline">Mostrar más</div>
        </div>
    </aside>

</div>
</body>
</html>