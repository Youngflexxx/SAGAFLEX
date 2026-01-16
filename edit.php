<?php
require 'db.php';
session_start();

// 1. Seguridad
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: home.php"); exit; }

// 2. Obtener post y validar propiedad
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$post = $stmt->fetch();

if (!$post) {
    die("<div class='p-10 text-center font-sans' style='background-color:#5F727B; color:white;'>❌ Error: No tienes permiso o el post no existe. <a href='home.php' class='underline'>Volver</a></div>");
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar / Sagaflex</title>
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
    </style>
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="home.php">
            <img class="mx-auto h-24 w-auto object-contain" src="logo-gold.png" alt="Sagaflex">
        </a>
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-white">
            Editar publicación
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-saga-card py-8 px-4 shadow-2xl rounded-2xl sm:px-10 border border-saga-light/10">
            
            <form class="space-y-6" action="actions.php" method="POST">
                <input type="hidden" name="action" value="update_post">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">

                <div>
                    <label class="block text-sm font-bold leading-6 text-saga-gold">Contenido</label>
                    <div class="mt-2">
                        <textarea name="content" rows="6" required class="saga-input block w-full rounded-lg shadow-sm sm:text-sm p-3 resize-none"><?= htmlspecialchars($post['content']) ?></textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold leading-6 text-saga-gold">Categoría</label>
                    <select name="category" class="saga-input mt-2 block w-full rounded-lg shadow-sm sm:text-sm p-3 cursor-pointer">
                        <option value="General" <?= $post['category'] == 'General' ? 'selected' : '' ?>>General</option>
                        <option value="Tech" <?= $post['category'] == 'Tech' ? 'selected' : '' ?>>Tech</option>
                        <option value="Random" <?= $post['category'] == 'Random' ? 'selected' : '' ?>>Random</option>
                    </select>
                </div>

                <div class="relative flex items-start py-2">
                    <div class="flex h-6 items-center">
                        <input id="is_private" name="is_private" type="checkbox" value="1" <?= $post['is_private'] ? 'checked' : '' ?> class="h-5 w-5 rounded border-saga-light/30 text-saga-gold focus:ring-saga-gold bg-saga-input cursor-pointer accent-saga-gold">
                    </div>
                    <div class="ml-3 text-sm leading-6">
                        <label for="is_private" class="font-bold text-white cursor-pointer">Post Privado</label>
                        <p class="text-saga-light/70">Solo tú podrás ver esta publicación.</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="flex-1 flex justify-center rounded-full bg-saga-gold px-4 py-3 text-sm font-bold leading-6 text-black shadow-lg hover:bg-saga-goldhover transition transform hover:scale-105">
                        Guardar
                    </button>
                    <a href="home.php" class="flex-1 flex justify-center rounded-full bg-transparent border border-saga-light/30 px-4 py-3 text-sm font-bold leading-6 text-saga-light shadow-sm hover:bg-saga-main transition">
                        Cancelar
                    </a>
                </div>
            </form>

        </div>
    </div>
</body>
</html>