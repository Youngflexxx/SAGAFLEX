<?php
// actions.php
require 'db.php';
session_start();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- AUTENTICACIÓN (Requisito 1) ---

if ($action === 'register') {
    $user = $_POST['username'];
    $email = $_POST['email'];
    // Encriptación (Requisito 1)
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$user, $email, $pass]);
        header("Location: index.php?error=Registro exitoso, por favor inicia sesion");
    } catch (Exception $e) {
        header("Location: index.php?error=El usuario o email ya existe");
    }
}

if ($action === 'login') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: home.php");
    } else {
        header("Location: index.php?error=Credenciales incorrectas");
    }
}

if ($action === 'logout') {
    session_destroy();
    header("Location: index.php");
}

// --- CRUD DE POSTS (Requisito 2) ---

if ($action === 'create_post') {
    if (!isset($_SESSION['user_id'])) die("Acceso denegado");

    $userId = $_SESSION['user_id'];
    
    // --- VALIDACIÓN COMPLEJA PERSONALIZADA (Requisito 3) ---
    // Regla: "Anti-Spam". No más de 3 posts en 10 minutos.
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM posts 
        WHERE user_id = ? 
        AND created_at > datetime('now', '-10 minutes')
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();

    if ($result['total'] >= 3) {
        die("❌ ERROR DE REGLA DE NEGOCIO: Has publicado demasiado rápido. Espera unos minutos (Límite: 3 posts cada 10 min). <a href='home.php'>Volver</a>");
    }
    // -------------------------------------------------------

    $content = $_POST['content'];
    $category = $_POST['category'];
    $isPrivate = isset($_POST['is_private']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, category, is_private) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $content, $category, $isPrivate]);
    header("Location: home.php");
}

if ($action === 'delete_post') {
    $postId = $_POST['post_id'];
    // Validamos que el post pertenezca al usuario antes de borrar
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$postId, $_SESSION['user_id']]);
    header("Location: home.php");
}

if ($action === 'update_post') {
    $postId = $_POST['post_id'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    
    $stmt = $pdo->prepare("UPDATE posts SET content = ?, category = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$content, $category, $postId, $_SESSION['user_id']]);
    header("Location: home.php");
}
?>