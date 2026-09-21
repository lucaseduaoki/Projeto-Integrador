<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreelaJá | <?= htmlspecialchars($tituloPagina ?? 'Plataforma', ENT_QUOTES, 'UTF-8') ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // As classes "blue-*" e "gray-*" usadas nas views apontam para a paleta da marca
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blue: {
                            50: '#eef5f1', 100: '#d8e8df', 200: '#b3d1c1', 500: '#2f7a5f',
                            600: '#1f5e4b', 700: '#174a3b', 800: '#123a2f', 900: '#0d2a22'
                        },
                        gray: {
                            50: '#f7f4ee', 100: '#ece7dd', 200: '#ddd6c8', 300: '#c6bdac',
                            400: '#a0968a', 500: '#7c7367', 600: '#5f574d',
                            700: '#463f37', 800: '#2e2924', 900: '#1d1a17'
                        }
                    },
                    fontFamily: {
                        sans: ['"Public Sans"', 'system-ui', 'sans-serif'],
                        display: ['Fraunces', 'Georgia', 'serif']
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Public Sans', system-ui, sans-serif; }
        h1, h2, .font-display { font-family: 'Fraunces', Georgia, serif; letter-spacing: -0.01em; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
