<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreelaJá | <?= htmlspecialchars($tituloPagina ?? 'Plataforma', ENT_QUOTES, 'UTF-8') ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Paleta extraída da logo. As classes "blue-*" e "gray-*" das views apontam para ela.
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blue: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bcd9fe', 500: '#0a86fc',
                            600: '#0a63f0', 700: '#0850c8', 800: '#0b3b94', 900: '#02102d'
                        },
                        gray: {
                            50: '#f6f8fc', 100: '#eaeef6', 200: '#dbe1ee', 300: '#c0c9dc',
                            400: '#94a0b8', 500: '#6b7791', 600: '#4c5670',
                            700: '#333c55', 800: '#1a2340', 900: '#02102d'
                        }
                    },
                    borderRadius: { DEFAULT: '0.625rem', md: '0.875rem', lg: '1rem' },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        display: ['Outfit', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= URL_BASE ?>/img/favicon.png">

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        h1, h2, .font-display { font-family: 'Outfit', system-ui, sans-serif; letter-spacing: -0.01em; }
        .bg-marca { background-image: linear-gradient(135deg, #02a7fb, #0a5cf0); }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
