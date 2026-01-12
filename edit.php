<?php
require 'db.php';
session_start();

// Validar sesión y propiedad del post
if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) header("Location: index.php");

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$post = $stmt->fetch();

if (!$post) die("Post no encontrado o no tienes permiso.");
?>

<!DOCTYPE html>
<html lang="es">
<body>
    <h1>Editar Post</h1>
    <form action="actions.php" method="POST">
        <input type="hidden" name="action" value="update_post">
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
        
        <textarea name="content" rows="5" cols="40" required><?= htmlspecialchars($post['content']) ?></textarea><br>
        
        <select name="category">
            <option value="General" <?= $post['category'] == 'General' ? 'selected' : '' ?>>General</option>
            <option value="Tech" <?= $post['category'] == 'Tech' ? 'selected' : '' ?>>Tech</option>
            <option value="Random" <?= $post['category'] == 'Random' ? 'selected' : '' ?>>Random</option>
        </select>
        
        <button type="submit">Guardar Cambios</button>
        <a href="home.php">Cancelar</a>
    </form>
</body>
</html>