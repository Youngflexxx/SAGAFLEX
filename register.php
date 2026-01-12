<?php session_start(); if(isset($_SESSION['user_id'])) header("Location: home.php"); ?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <title>Registro / Sagaflex</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <h2 class="mt-10 text-center text-3xl font-bold leading-9 tracking-tight text-indigo-600">Sagaflex</h2>
        <h2 class="mt-2 text-center text-xl font-bold leading-9 tracking-tight text-gray-900">Únete hoy mismo</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <form class="space-y-6" action="actions.php" method="POST">
            <input type="hidden" name="action" value="register">
            
            <div>
                <label class="block text-sm font-medium leading-6 text-gray-900">Usuario (@handle)</label>
                <div class="mt-2">
                    <input name="username" type="text" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium leading-6 text-gray-900">Correo Electrónico</label>
                <div class="mt-2">
                    <input name="email" type="email" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium leading-6 text-gray-900">Contraseña</label>
                <div class="mt-2">
                    <input name="password" type="password" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3">
                </div>
            </div>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500">Registrarse</button>
            </div>
        </form>

        <p class="mt-10 text-center text-sm text-gray-500">
            ¿Ya tienes cuenta?
            <a href="login.php" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Entrar</a>
        </p>
    </div>
</div>
</body>
</html>