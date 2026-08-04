<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreelaJá | <?= htmlspecialchars($tituloPagina ?? 'Plataforma', ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#2563EB', hover: '#1D4ED8' },
                        secondary: { DEFAULT: '#7C3AED', hover: '#6D28D9' },
                        success: '#16A34A',
                        danger: '#DC2626',
                        warning: '#D97706',
                        neutral: { 50: '#F8FAFC', 100: '#F1F5F9', 800: '#1E293B' }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
