<?php
require 'db.php';
session_start();

// Si el usuario ya tiene sesión, lo mandamos directo a su Home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

// CONSULTA SQL: Solo traemos posts PÚBLICOS (is_private = 0)
// Esto es vital para la seguridad/privacidad en la vista de invitados
$stmt = $pdo->query("
    SELECT posts.*, users.username 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.is_private = 0 
    ORDER BY posts.created_at DESC
");
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Sagaflex</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900">

    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
        <div class="container mx-auto max-w-4xl px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-indigo-600 rounded-md flex items-center justify-center text-white font-bold text-xl">S</div>
                <span class="text-xl font-bold text-indigo-600 tracking-tight">Sagaflex</span>
            </div>

            <div class="flex gap-4">
                <a href="login.php" class="px-4 py-2 font-bold text-gray-700 hover:bg-gray-100 rounded-full transition">
                    Iniciar Sesión
                </a>
                <a href="register.php" class="px-4 py-2 font-bold bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition shadow-sm">
                    Registrarse
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto max-w-2xl min-h-screen border-x border-gray-100 mt-2">
        
        <div class="p-8 border-b border-gray-100 text-center bg-gray-50">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Lo que está pasando ahora</h1>
            <p class="text-gray-500 mb-6">Únete a Sagaflex para participar en la conversación.</p>
            <a href="register.php" class="inline-block px-8 py-3 bg-indigo-600 text-white font-bold rounded-full hover:bg-indigo-700 transition">
                Crear cuenta
            </a>
        </div>

        <div class="pb-20">
            <?php if (empty($posts)): ?>
                <div class="p-10 text-center text-gray-400">
                    Aún no hay publicaciones públicas. ¡Sé el primero en registrarte!
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold flex-shrink-0">
                                <?= strtoupper(substr($post['username'], 0, 1)) ?>
                            </div>
                            
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-gray-900"><?= htmlspecialchars($post['username']) ?></span>
                                    <span class="text-gray-500 text-sm">· <?= date("d M", strtotime($post['created_at'])) ?></span>
                                </div>
                                
                                <p class="text-gray-900 whitespace-pre-wrap leading-normal text-[15px]"><?= htmlspecialchars($post['content']) ?></p>
                                
                                <div class="mt-3 flex items-center gap-4 text-gray-500 text-sm">
                                    <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs font-medium">
                                        #<?= htmlspecialchars($post['category']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <div class="fixed bottom-0 w-full bg-indigo-600 py-3 text-white z-40">
        <div class="container mx-auto max-w-4xl px-4 flex justify-between items-center">
            <div class="hidden sm:block">
                <p class="font-bold text-lg">No te pierdas lo que está pasando</p>
                <p class="text-sm opacity-90">Los usuarios de Sagaflex son los primeros en enterarse.</p>
            </div>
            <div class="flex gap-3 w-full sm:w-auto justify-center">
                <a href="login.php" class="px-4 py-1.5 border border-white font-bold rounded-full hover:bg-white/10 transition">Log in</a>
                <a href="register.php" class="px-4 py-1.5 bg-white text-indigo-600 font-bold rounded-full hover:bg-gray-100 transition">Sign up</a>
            </div>
        </div>
    </div>

</body>
</html>