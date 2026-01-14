<?php
require 'db.php';
session_start();

// 1. Seguridad
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// 2. Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: home.php"); exit; }

// 3. Obtener post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$post = $stmt->fetch();

if (!$post) {
    die("<div class='p-10 text-center font-sans'>❌ Error: No tienes permiso para editar esto. <a href='home.php' class='text-blue-500 underline'>Volver</a></div>");
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Post / Sagaflex</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">

<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="home.php">
            <img class="mx-auto h-16 w-auto object-contain" src="SAGAFLEXLOGO.jpg" alt="Sagaflex">
        </a>
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">
            Editar publicación
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-xl sm:px-10 border border-gray-100">
            
            <form class="space-y-6" action="actions.php" method="POST">
                <input type="hidden" name="action" value="update_post">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">

                <div>
                    <label for="content" class="block text-sm font-bold leading-6 text-gray-900">Contenido</label>
                    <div class="mt-2">
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="6" 
                            required
                            class="block w-full rounded-md border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 resize-none"><?= htmlspecialchars($post['content']) ?></textarea>
                    </div>
                </div>

                <div>
                    <label for="category" class="block text-sm font-bold leading-6 text-gray-900">Categoría</label>
                    <select id="category" name="category" class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 bg-white">
                        <option value="General" <?= $post['category'] == 'General' ? 'selected' : '' ?>>General</option>
                        <option value="Tech" <?= $post['category'] == 'Tech' ? 'selected' : '' ?>>Tech</option>
                        <option value="Random" <?= $post['category'] == 'Random' ? 'selected' : '' ?>>Random</option>
                    </select>
                </div>

                <div class="relative flex items-start py-2">
                    <div class="flex h-6 items-center">
                        <input 
                            id="is_private" 
                            name="is_private" 
                            type="checkbox" 
                            value="1"
                            <?= $post['is_private'] ? 'checked' : '' ?>
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 cursor-pointer">
                    </div>
                    <div class="ml-3 text-sm leading-6">
                        <label for="is_private" class="font-bold text-gray-900 cursor-pointer">Post Privado</label>
                        <p class="text-gray-500">Si activas esto, solo tú podrás ver esta publicación.</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="flex-1 flex justify-center rounded-full bg-indigo-600 px-4 py-2.5 text-sm font-bold leading-6 text-white shadow-sm hover:bg-indigo-500 transition">
                        Guardar Cambios
                    </button>
                    <a href="home.php" class="flex-1 flex justify-center rounded-full bg-white px-4 py-2.5 text-sm font-bold leading-6 text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>