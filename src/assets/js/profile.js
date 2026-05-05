/**
 * Profile Logic - SOC G12
 * Maneja navegación por pestañas e interacciones de usuario.
 */

const ProfileModule = (function() {
    // Selectores
    const selectors = {
        tabs: document.querySelectorAll('[data-tab]'),
        contents: document.querySelectorAll('.tab-content'),
        avatarInput: document.getElementById('avatar-input'),
        avatarPreview: document.getElementById('avatar-preview')
    };

    // Inicialización
    const init = () => {
        bindEvents();
    };

    // Eventos
    const bindEvents = () => {
        // Navegación de pestañas
        selectors.tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                const target = e.currentTarget.getAttribute('data-tab');
                switchTab(target, e.currentTarget);
            });
        });

        // Previsualización de Avatar (si existe el input)
        if (selectors.avatarInput) {
            selectors.avatarInput.addEventListener('change', handleAvatarChange);
        }
    };

    // Cambiar entre pestañas
    const switchTab = (tabId, activeBtn) => {
        // Ocultar todos los contenidos
        selectors.contents.forEach(content => content.classList.add('hidden'));
        
        // Quitar estado activo de todos los botones
        selectors.tabs.forEach(btn => btn.classList.remove('active'));

        // Mostrar destino
        const targetContent = document.getElementById(`content-${tabId}`);
        if (targetContent) {
            targetContent.classList.remove('hidden');
        }
        
        activeBtn.classList.add('active');

        // Trigger opcional para Solana: si entramos a la pestaña solana, podemos refrescar balance
        if (tabId === 'solana' && typeof window.refreshSolanaBalance === 'function') {
            window.refreshSolanaBalance();
        }
    };

    // Manejo de imagen de perfil
    const handleAvatarChange = (e) => {
        const file = e.target.files[0];
        if (file && selectors.avatarPreview) {
            const reader = new FileReader();
            reader.onload = (event) => {
                selectors.avatarPreview.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    };

    // Retorno público
    return {
        init: init
    };
})();

// Ejecutar al cargar el DOM
document.addEventListener('DOMContentLoaded', ProfileModule.init);

