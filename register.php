<?php session_start(); if(isset($_SESSION['user_id'])) header("Location: home.php"); ?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro / Sagaflex</title>
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
    <style> body { background-color: #5F727B; color: #fff; } .saga-input { background-color: #37474F; border: 1px solid #546E7A; color: white; } .saga-input:focus { border-color: #FFD700; outline: none; box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3); } </style>
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <img class="mx-auto h-32 w-auto object-contain" src="logo-gold.png" alt="Sagaflex">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-white">Únete a la Saga</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-saga-card py-8 px-4 shadow-2xl rounded-2xl sm:px-10 border border-saga-light/10">
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-900/40 border-l-4 border-red-500 p-4 mb-6 text-red-200 text-sm font-bold"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <form class="space-y-6" action="actions.php" method="POST">
                <input type="hidden" name="action" value="register">
                <div>
                    <label class="block text-sm font-bold text-saga-light">Usuario (@handle)</label>
                    <div class="mt-2"><input name="username" type="text" required class="saga-input block w-full rounded-lg shadow-sm sm:text-sm p-3"></div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-saga-light">Email</label>
                    <div class="mt-2"><input name="email" type="email" required class="saga-input block w-full rounded-lg shadow-sm sm:text-sm p-3"></div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-saga-light">Contraseña</label>
                    <div class="mt-2"><input name="password" type="password" required class="saga-input block w-full rounded-lg shadow-sm sm:text-sm p-3"></div>
                </div>
                <div>
                    <button type="submit" class="flex w-full justify-center rounded-full bg-saga-gold px-4 py-3 text-lg font-bold text-black shadow-lg hover:bg-saga-goldhover transition transform hover:scale-[1.02]">
                        Registrarse
                    </button>
                </div>
            </form>

            <p class="mt-8 text-center text-sm text-saga-light">
                ¿Ya tienes cuenta? <a href="login.php" class="font-bold text-saga-gold hover:underline">Entrar</a>
            </p>
        </div>
    </div>
</body>
</html>