<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AKY LANCHES | Cardápio Digital & Pedidos</title>
    
    <!-- Favicon com o ícone oficial -->
    <link rel="icon" type="image/svg+xml" href="/images/aky_lanches_icon.svg">

    <!-- Estilos e Scripts Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div id="app"></div>
</body>
</html>

