<?php
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$userId = $_SESSION['user_id'];

// Obtener mi avatar
$stmtMe = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmtMe->execute([$userId]);
$myUser = $stmtMe->fetch();
$myAvatar = $myUser['profile_picture'] ?? 'default.png';

// Consulta SQL
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
$stmt->execute([$userId, $userId]);
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio / Sagaflex</title>
    <link rel="icon" type="image/png" href="favicon.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        saga: {
                            main: '#5F727B',
                            card: '#455A64',  
                            light: '#CFD8DC', 
                            gold: '#FFD700',
                            goldhover: '#E6C200',
                            input: '#37474F'  
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/petite-vue" defer init></script>
    <style> 
        body { background-color: #5F727B; color: #FFFFFF; }
        .hide-scrollbar::-webkit-scrollbar { display: none; } 
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .saga-input { background-color: #37474F; border: 1px solid #546E7A; color: white; }
        .saga-input:focus { border-color: #FFD700; outline: none; box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3); }
        .like-anim:active { transform: scale(1.3); transition: transform 0.1s; }
    </style>
</head>
<body>

<div class="container mx-auto max-w-7xl flex min-h-screen">
    
    <header class="hidden lg:flex w-1/4 flex-col justify-between sticky top-0 h-screen p-6 border-r border-saga-light/10 overflow-y-auto hide-scrollbar bg-saga-main">
        <div class="space-y-8">
            <div class="mb-4 pl-2">
                <a href="home.php" class="block">
                    <img src="logo-gold.png" alt="Sagaflex" class="h-28 w-auto object-contain -ml-4">
                </a>
            </div>
            
            <nav class="space-y-2 text-lg font-medium">
                <a href="home.php" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-saga-card text-saga-gold border border-saga-gold/20 transition hover:translate-x-1">
                    <span class="text-xl">🏠</span> <span class="font-bold">Inicio</span>
                </a>
                <a href="profile.php?user_id=<?= $_SESSION['user_id'] ?>" class="flex items-center gap-4 px-4 py-3 rounded-xl hover:bg-saga-card/50 text-saga-light transition">
                    <span class="text-xl">👤</span> <span>Perfil</span>
                </a>
            </nav>

            <button class="w-full bg-gradient-to-r from-saga-gold to-yellow-500 text-black font-extrabold text-lg py-3 rounded-full shadow-lg hover:shadow-yellow-500/30 hover:scale-[1.02] transition">
                POSTEAR
            </button>
        </div>

        <div class="mb-4">
            <a href="actions.php?action=logout" class="flex items-center gap-2 px-4 py-3 rounded-full hover:bg-red-900/30 text-red-300 font-bold transition">
                <span>🚪</span> <span>Cerrar Sesión</span>
            </a>
        </div>
    </header>

    <main class="w-full lg:w-2/4 border-r border-saga-light/10 min-h-screen bg-saga-main">
        
        <div class="sticky top-0 z-20 bg-saga-main/95 backdrop-blur border-b border-saga-light/10 p-4 flex justify-between items-center lg:hidden">
            <img src="logo-gold.png" alt="Sagaflex" class="h-10 w-auto">
            <a href="actions.php?action=logout" class="text-sm text-red-300 font-bold">Salir</a>
        </div>

        <div class="border-b border-saga-light/10 p-6" v-scope="{ chars: 0, max: 280, content: '' }">
            <form action="actions.php" method="POST">
                <input type="hidden" name="action" value="create_post">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-saga-gold/50 shadow-sm flex-shrink-0">
                        <img src="uploads/<?= htmlspecialchars($myAvatar) ?>" class="w-full h-full object-cover">
                    </div>

                    <div class="flex-1">
                        <textarea 
                            name="content" v-model="content" @input="chars = $el.value.length" :maxlength="max"
                            class="w-full bg-transparent border-none focus:ring-0 text-xl text-white resize-none placeholder-saga-light/50 outline-none" 
                            rows="2" placeholder="¿Qué cuenta tu saga hoy?"></textarea>
                        
                        <div class="flex justify-between items-center mt-4 border-t border-saga-light/10 pt-4">
                            <div class="flex gap-3 items-center">
                                <select name="category" class="text-sm font-bold bg-saga-card text-saga-gold border border-saga-gold/20 rounded-full px-3 py-1 outline-none cursor-pointer">
                                    <option value="General">General</option>
                                    <option value="Tech">Tech</option>
                                    <option value="Random">Random</option>
                                </select>
                                <label class="flex items-center gap-1 text-sm text-saga-light cursor-pointer select-none hover:text-white transition">
                                    <input type="checkbox" name="is_private" value="1" class="accent-saga-gold w-4 h-4 rounded bg-saga-input border-saga-light/30"> 
                                    <span class="ml-1">Privado</span>
                                </label>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-bold" :class="chars > 260 ? 'text-red-400' : 'text-saga-gold'">{{ max - chars }}</span>
                                <button type="submit" :disabled="chars === 0" class="bg-saga-gold text-black font-bold px-6 py-2 rounded-full disabled:opacity-50 hover:bg-yellow-400 transition shadow-md">Publicar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div>
            <?php foreach ($posts as $post): ?>
                <article class="p-6 border-b border-saga-light/10 hover:bg-saga-card/30 transition cursor-pointer">
                    <div class="flex gap-4">
                        <a href="profile.php?user_id=<?= $post['user_id'] ?>" class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full overflow-hidden border border-saga-light/20 bg-saga-card">
                                <img src="uploads/<?= htmlspecialchars($post['profile_picture'] ?? 'default.png') ?>" class="w-full h-full object-cover">
                            </div>
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center gap-2 truncate">
                                    <a href="profile.php?user_id=<?= $post['user_id'] ?>" class="font-bold text-white hover:text-saga-gold transition truncate text-lg"><?= htmlspecialchars($post['username']) ?></a>
                                    <span class="text-saga-light text-sm">· <?= date("d M", strtotime($post['created_at'])) ?></span>
                                    <?php if($post['is_private']): ?>
                                        <span class="ml-1 text-[10px] bg-saga-card border border-saga-light/20 text-saga-light px-2 py-0.5 rounded-full">🔒</span>
                                    <?php endif; ?>
                                </div>
                                <?php if($post['user_id'] == $_SESSION['user_id']): ?>
                                    <div class="flex gap-3 opacity-50 hover:opacity-100 transition">
                                        <a href="edit.php?id=<?= $post['id'] ?>" class="text-saga-light hover:text-saga-gold">✏️</a>
                                        <form action="actions.php" method="POST" onsubmit="return confirm('¿Borrar?');" class="inline">
                                            <input type="hidden" name="action" value="delete_post"><input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                            <button type="submit" class="text-saga-light hover:text-red-400">🗑️</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="mt-2 text-gray-100 leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($post['content']) ?></p>
                            
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

                                <span class="text-xs font-bold text-saga-gold border border-saga-gold/30 px-3 py-1 rounded-full uppercase"><?= $post['category'] ?></span>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <aside class="hidden lg:block w-1/4 pl-8 py-6 sticky top-0 h-screen overflow-y-auto hide-scrollbar border-l border-saga-light/10">
        <div class="sticky top-0 bg-saga-main pb-6 z-10">
            <input type="text" placeholder="Buscar..." class="w-full saga-input rounded-full py-3 px-5 transition placeholder-saga-light/50">
        </div>
        <div class="bg-saga-card/50 rounded-2xl p-6 border border-saga-light/10">
            <h3 class="font-extrabold text-xl mb-6 text-saga-gold">Tendencias Saga</h3>
            <div class="space-y-4">
                <div class="cursor-pointer hover:bg-saga-card p-3 rounded-xl transition -mx-2">
                    <p class="text-xs text-saga-light mb-1">Fantasía</p>
                    <p class="font-bold text-white">#NuevoReino</p>
                </div>
            </div>
        </div>
    </aside>
</div>
</body>
</html>