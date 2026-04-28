<?php
/**
 * ============================================================================
 * MÓDULO DE TRADUCCIÓN (GOOGLE TRANSLATE STANDALONE)
 * ============================================================================
 * Archivo restaurado / recreado para el Proyecto Final G7 / G12
 * ============================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traductor G12</title>
    <style>
        /* Estilos básicos para adaptar el widget al diseño oscuro */
        .translator-container {
            background-color: #0f172a;
            border: 1px solid #1e293b;
            padding: 20px;
            border-radius: 12px;
            display: inline-block;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f8fafc;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #0ea5e9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        /* Ajustes cosméticos para el banner de Google */
        body { top: 0 !important; }
        .goog-te-banner-frame { display: none !important; }
    </style>
</head>
<body>

    <div class="translator-container">
        <div class="title"><i class="fas fa-language"></i> Seleccionar Idioma</div>
        <div id="google_translate_element"></div>
    </div>

    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'es', // Idioma base de tu web
                includedLanguages: 'es,en,fr,zh-CN,de,it,pt', // Idiomas permitidos
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');
        }
    </script>
    
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>
</html>
