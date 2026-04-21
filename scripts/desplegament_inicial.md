¡Por supuesto! Aquí tienes una guía de instalación diseñada para ser muy visual, profesional y fácil de seguir para cualquier persona, independientemente de su nivel técnico.

He utilizado iconos, un lenguaje claro y un formato paso a paso para que el proceso sea intuitivo y sin complicaciones.

---

# 🚀 Guía Rápida de Despliegue: CyberAudit Hub

Bienvenido a la guía de instalación de **CyberAudit Hub**. En solo dos pasos, tendrás la plataforma funcionando en tu propio servidor Ubuntu. ¡Empecemos!

### **📋 Requisitos Previos**

Antes de empezar, asegúrate de tener:

1.  **Un servidor con Ubuntu** (por ejemplo, una instancia de AWS, DigitalOcean, etc.).
2.  **Acceso a internet** desde el servidor.
3.  **Permisos en tu firewall** para los puertos `80` (HTTP) y `443` (HTTPS).

---

### **Paso 1: 🖥️ Conéctate a tu Servidor**

Para empezar, necesitas acceder al terminal de tu servidor.

1.  **Descarga la clave de acceso (`vockey.pem`)** desde el siguiente enlace seguro:
    > **[🔑 Descargar Clave de Acceso (vockey.pem)](https://drive.google.com/file/d/10NKAE9waCh9OtFUaGFFJ-ZP5xLfD7hV5/view?usp=sharing)**

2.  **Abre un terminal** en tu ordenador y ve a la carpeta donde descargaste la clave. A continuación, ejecuta este comando para protegerla:
    ```bash
    chmod 600 vockey.pem
    ```
    *(Este paso es crucial para que la conexión funcione correctamente).*

3.  Ahora, **conéctate al servidor** usando el siguiente comando:
    ```bash
    ssh -i "vockey.pem" ubuntu@ec2-32-194-186-97.compute-1.amazonaws.com
    ```

¡Genial! Si todo ha ido bien, ya estás dentro del terminal de tu servidor.

---

### **Paso 2: 🚀 Instala CyberAudit Hub con un Solo Comando**

Ahora viene la magia. Este comando instalará y configurará todo por ti.

*   Copia el siguiente comando, pégalo en el terminal de tu servidor y pulsa `Enter`.

    ```bash
    curl -sSL https://raw.githubusercontent.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/main/scripts/project_setup.sh | sudo bash
    ```

    > **¿Qué hace este comando?**
    > *   ✅ **Verifica** que tu sistema es compatible.
    > *   🐳 **Instala Docker** si no lo tienes.
    > *   📂 **Descarga** el código de CyberAudit Hub.
    > *   ⚙️ **Configura** automáticamente todo lo necesario.
    > *   ▶️ **Inicia** la plataforma.

*   Espera unos minutos mientras el instalador trabaja. Cuando termine, verás un mensaje de confirmación con la URL para acceder a tu nueva plataforma.

    ```
    ✅ ¡Éxito! CyberAudit Hub está operativo.
       Accede desde: http://<TU_IP_PÚBLICA>
    ```

---

### **Paso 3 (Opcional): 🔒 Activa la Seguridad HTTPS**

Para que tu sitio web sea seguro y muestre el candado verde en el navegador, puedes activar HTTPS.

**Requisito:** Necesitas un **nombre de dominio** (ej. `mi-dominio.com`) que apunte a la IP de tu servidor.

1.  Ejecuta este comando en el terminal de tu servidor:
    ```bash
    curl -sSL https://raw.githubusercontent.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/main/scripts/enable_https.sh | sudo bash
    ```

2.  El script te pedirá dos cosas:
    *   Tu **nombre de dominio**.
    *   Tu **email** (para notificaciones sobre el certificado).

3.  ¡Y ya está! El script configurará automáticamente un certificado SSL gratuito, activará la redirección a `https://` y lo renovará por ti para que no tengas que preocuparte de nada.

---

### **🎉 ¡Felicidades!**

Has desplegado **CyberAudit Hub** de forma exitosa. Ya puedes acceder a la plataforma desde tu navegador y empezar a utilizarla.
