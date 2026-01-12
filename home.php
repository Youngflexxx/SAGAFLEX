<?php
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// Obtener posts con Join
$stmt = $pdo->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
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
        /* Ocultar scrollbar visualmente pero permitir scroll */
        .hide-scrollbar::-webkit-scrollbar { display: none; } 
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-white text-gray-900">

<div class="container mx-auto max-w-7xl flex min-h-screen">
    
    <header class="hidden md:flex w-1/4 flex-col justify-between sticky top-0 h-screen p-4 border-r border-gray-100 overflow-y-auto hide-scrollbar">
        <div class="space-y-6">
            <div class="pl-2">
                <a href="home.php" class="text-3xl font-bold text-indigo-600 hover:text-indigo-700 transition">Sagaflex</a>
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

    <main class="w-full md:w-2/4 border-r border-gray-100 min-h-screen">
        
        <div class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-gray-100 p-4 flex justify-between items-center md:hidden">
            <h2 class="text-lg font-bold">Inicio</h2>
            <a href="actions.php?action=logout" class="text-sm text-red-500 font-bold">Salir</a>
        </div>

        <div class="border-b border-gray-100 p-4" v-scope="{ chars: 0, max: 280, content: '' }">
            <form action="actions.php" method="POST">
                <input type="hidden" name="action" value="create_post">
                <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex-shrink-0"></div>
                    <div class="flex-1">
                        <textarea 
                            name="content"
                            v-model="content"
                            @input="chars = $el.value.length"
                            :maxlength="max"
                            class="w-full border-none focus:ring-0 text-xl resize-none placeholder-gray-500 outline-none" 
                            rows="2" 
                            placeholder="¿Qué está pasando?"></textarea>
                        
                        <div class="flex justify-between items-center mt-2 border-t pt-2 border-gray-50">
                            <div class="flex gap-2 items-center">
                                <select name="category" class="text-sm text-indigo-600 font-bold bg-indigo-50 rounded-full px-3 py-1 outline-none cursor-pointer hover:bg-indigo-100">
                                    <option value="General">General</option>
                                    <option value="Tech">Tech</option>
                                    <option value="Random">Random</option>
                                </select>
                                <label class="flex items-center gap-1 text-sm text-gray-500 cursor-pointer select-none">
                                    <input type="checkbox" name="is_private" value="1" class="rounded text-indigo-600 focus:ring-indigo-600"> 
                                    <span>🔒</span>
                                </label>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-sm transition-colors" :class="chars > 260 ? 'text-red-500 font-bold' : 'text-gray-400'">
                                    {{ max - chars }}
                                </span>
                                <button type="submit" :disabled="chars === 0" 
                                    class="bg-indigo-600 text-white font-bold px-4 py-2 rounded-full disabled:opacity-50 hover:bg-indigo-700 transition">
                                    Postear
                                </button>
                            </div>
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
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-600">
                                <?= strtoupper(substr($post['username'], 0, 1)) ?>
                            </div>
                        </a>
                        <div class="flex-1 min-w-0"> <div class="flex justify-between items-start">
                                <div class="flex items-center gap-1 truncate">
                                    <a href="profile.php?user_id=<?= $post['user_id'] ?>" class="font-bold hover:underline truncate">
                                        <?= htmlspecialchars($post['username']) ?>
                                    </a>
                                    <span class="text-gray-500 text-sm flex-shrink-0">· <?= date("d M", strtotime($post['created_at'])) ?></span>
                                    <?php if($post['is_private']): ?>
                                        <span class="ml-1 text-[10px] bg-gray-200 text-gray-600 px-1 rounded flex-shrink-0">🔒</span>
                                    <?php endif; ?>
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
                            
                            <div class="mt-3 flex items-center gap-12 text-gray-500 text-sm">
                                <button class="group flex items-center gap-2 hover:text-pink-500 transition">
                                    <span>❤️</span> <span class="text-xs">0</span>
                                </button>
                                <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full text-xs font-medium">
                                    #<?= $post['category'] ?>
                                </span>
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
                    <p class="text-xs text-gray-500 flex justify-between">
                        <span>Tecnología · Tendencia</span>
                        <span>...</span>
                    </p>
                    <p class="font-bold text-gray-900">#SagaflexLaunch</p>
                    <p class="text-xs text-gray-500">10.5K posts</p>
                </div>

                <div class="cursor-pointer hover:bg-gray-100 p-2 rounded transition -mx-2">
                    <p class="text-xs text-gray-500">Programación · Tendencia</p>
                    <p class="font-bold text-gray-900">#PHPisAlive</p>
                    <p class="text-xs text-gray-500">52K posts</p>
                </div>
                
                <div class="cursor-pointer hover:bg-gray-100 p-2 rounded transition -mx-2">
                    <p class="text-xs text-gray-500">Música · Tendencia</p>
                    <p class="font-bold text-gray-900">Daft Punk</p>
                    <p class="text-xs text-gray-500">Trending forever</p>
                </div>
            </div>
            
            <div class="mt-4 text-indigo-600 text-sm cursor-pointer hover:underline">Mostrar más</div>
        </div>

        <div class="mt-6 text-xs text-gray-400 leading-relaxed px-2">
            Condiciones de Servicio Política de Privacidad Política de cookies Accesibilidad Información de anuncios © 2024 Sagaflex, Inc.
        </div>
    </aside>

</div>

</body>
</html>