window.generateFullReport = function(auditorName) {

    // Obtener datos del escaneo
    const target =
        document.getElementById('target')?.value || 'Unknown';

    const logs =
        document.getElementById('console-output')?.innerText || '';

    const aiAnalysis =
        window.currentAIReport ||
        "No se pudo recuperar el análisis de la IA.";

    // Crear formulario dinámico
    const form = document.createElement('form');

    form.method = 'POST';

    // IMPORTANTE:
    // Ruta absoluta para evitar duplicación:
    // /modules/scanner/modules/scanner/
    form.action = '/modules/scanner/generate_report.php';

    // Abrir PDF en nueva pestaña
    form.target = '_blank';

    // Datos a enviar
    const data = {
        target: target,
        auditor: auditorName || "Security Operator",
        ai_analysis: aiAnalysis,
        logs: logs
    };

    // Crear inputs ocultos
    Object.entries(data).forEach(([key, value]) => {

        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = key;
        input.value = value;

        form.appendChild(input);
    });

    // Añadir formulario temporalmente
    document.body.appendChild(form);

    // Enviar formulario
    form.submit();

    // Limpiar DOM
    document.body.removeChild(form);
};
