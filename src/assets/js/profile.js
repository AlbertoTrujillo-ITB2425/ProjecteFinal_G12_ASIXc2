/**
 * Profile Logic - SOC G12
 * Maneja navegación por pestañas e interacciones con el backend.
 */

const ProfileModule = (function() {
    // Selectores actualizados
    const selectors = {
        tabs: document.querySelectorAll('[data-tab]'),
        contents: document.querySelectorAll('.tab-content'),
        avatarInput: document.getElementById('avatar-input'),
        avatarPreview: document.getElementById('avatar-preview'),
        inputName: document.getElementById('input-name'),
        btnSaveProfile: document.getElementById('btn-save-profile'),
        inputNewPass: document.getElementById('new-pass'),
        headerName: document.getElementById('header-name')
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

        // Guardar cambios del perfil (Nombre)
        if (selectors.btnSaveProfile) {
            selectors.btnSaveProfile.addEventListener('click', handleSaveProfile);
        }

        // Manejo de Avatar
        if (selectors.avatarInput) {
            selectors.avatarInput.addEventListener('change', handleAvatarChange);
        }
    };

    // Cambiar entre pestañas
    const switchTab = (tabId, activeBtn) => {
        selectors.contents.forEach(content => content.classList.add('hidden'));
        selectors.tabs.forEach(btn => btn.classList.remove('active'));

        const targetContent = document.getElementById(`content-${tabId}`);
        if (targetContent) {
            targetContent.classList.remove('hidden');
        }
        activeBtn.classList.add('active');
    };

    // --- ACCIÓN: GUARDAR NOMBRE ---
    const handleSaveProfile = async () => {
        const name = selectors.inputName.value.trim();
        if (!name) return alert("El nombre no puede estar vacío");

        const originalText = selectors.btnSaveProfile.innerText;
        selectors.btnSaveProfile.innerText = "PROCESANDO...";
        selectors.btnSaveProfile.disabled = true;

        const formData = new FormData();
        formData.append('action', 'update_name');
        formData.append('name', name);

        try {
            const response = await fetch('core/profile_actions.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                // Actualizar el nombre en la UI superior
                if (selectors.headerName) selectors.headerName.innerText = name.toUpperCase();
                showNotification("Perfil actualizado", "success");
            } else {
                showNotification(result.message, "error");
            }
        } catch (error) {
            showNotification("Error de conexión con el SOC", "error");
        } finally {
            selectors.btnSaveProfile.innerText = originalText;
            selectors.btnSaveProfile.disabled = false;
        }
    };

    // --- ACCIÓN: CAMBIAR PASSWORD (puedes llamarla desde un botón en el HTML) ---
    const handleUpdatePassword = async () => {
        const pass = selectors.inputNewPass.value;
        if (pass.length < 6) return alert("Password demasiado corta");

        const formData = new FormData();
        formData.append('action', 'update_password');
        formData.append('password', pass);

        try {
            const response = await fetch('core/profile_actions.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.status === 'success') {
                showNotification("Contraseña actualizada", "success");
                selectors.inputNewPass.value = "";
            } else {
                showNotification(result.message, "error");
            }
        } catch (e) {
            showNotification("Error en el cambio de credenciales", "error");
        }
    };

    // Manejo de imagen de perfil (Previsualización local)
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

    // Utilidad: Notificación rápida
    const showNotification = (msg, type) => {
        // Podrías usar un Toast estilo SOC aquí
        alert(`${type.toUpperCase()}: ${msg}`);
    };

    // Retorno público (Exponemos métodos que necesitemos llamar desde fuera)
    return {
        init: init,
        updatePassword: handleUpdatePassword
    };
})();

// Ejecutar al cargar el DOM
document.addEventListener('DOMContentLoaded', ProfileModule.init);
