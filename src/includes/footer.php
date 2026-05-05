<footer class="mt-20 pb-10 text-center text-slate-600 text-[10px] uppercase tracking-[0.3em]">
    &copy; 2026 CyberPyme SOC - Sistema de Vigilancia Activa G12
</footer>

<script>
function toggleTheme() {
    const body = document.body;
    const isLight = body.classList.toggle('light-mode');
    
    if (isLight) {
        body.classList.remove('bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))]');
        body.classList.add('bg-slate-50');
    } else {
        body.classList.add('bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))]');
        body.classList.remove('bg-slate-50');
    }
    
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    console.log("Tema cambiado a:", isLight ? 'Claro' : 'Oscuro');
}

// Aplicar tema al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('theme') === 'light') {
        document.body.classList.add('light-mode', 'bg-slate-50');
        document.body.classList.remove('bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))]');
    }
});
</script>
