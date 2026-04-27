# 🤖 Manual: Cómo Crear un Chatbot con Chatbase para tu Servicio de Seguridad

> **Nivel:** Principiante · **Tiempo estimado:** 30–45 minutos · **Coste:** Gratuito (plan Free)

---

## ¿Qué es Chatbase?

Chatbase es una plataforma no-code que te permite crear chatbots de IA personalizados entrenados con **tu propia documentación**: PDFs, páginas web, texto personalizado y preguntas frecuentes. El chatbot se integra en tu web con una sola línea de código y todo el procesamiento ocurre en los servidores de Chatbase, sin afectar al rendimiento de tu página.

---

## Requisitos previos

- Una cuenta de correo electrónico (o cuenta de Google)
- Los documentos de tu servicio de seguridad en formato **PDF, DOCX o TXT** (guías, FAQs, pasos del proceso, etc.)
- Acceso al HTML de tu página web para pegar el script de embed

---

## Paso 1 — Crear una cuenta en Chatbase

1. Visita [https://www.chatbase.co](https://www.chatbase.co)
2. Haz clic en **"Sign Up"** (esquina superior derecha)
3. Elige registrarte con tu cuenta de **Google** o con email y contraseña
4. Confirma tu correo si es necesario y entra al **Dashboard**

> 💡 El plan gratuito incluye 1 chatbot, 20 mensajes/día y hasta 400.000 caracteres de base de conocimiento. Suficiente para empezar.

---

## Paso 2 — Crear un nuevo chatbot

1. En el Dashboard, haz clic en el botón **"New Chatbot"**
2. Serás redirigido a la página de **fuentes de datos** (el "cerebro" del bot)

Verás cuatro pestañas para añadir información:

| Pestaña | Descripción | Recomendado para |
|---------|-------------|-----------------|
| **Files** | Sube PDFs, DOCX, TXT | Guías del servicio, manuales |
| **Text** | Pega texto directamente | Descripciones cortas, políticas |
| **Website** | Introduce una URL para que Chatbase la rastree | Tu web o página de FAQs |
| **Q&A** | Define pares pregunta-respuesta | Preguntas frecuentes específicas |

---

## Paso 3 — Añadir la base de conocimiento

### Opción A: Subir archivos (recomendado)

1. Haz clic en la pestaña **"Files"**
2. Arrastra o selecciona tus documentos (PDF, DOCX, TXT)
   - Guías de uso del servicio de seguridad
   - Pasos del proceso de instalación/activación
   - Preguntas frecuentes
3. Espera a que los archivos se procesen (barra de progreso)

### Opción B: Añadir tu web

1. Haz clic en la pestaña **"Website"**
2. Introduce la URL de tu página de servicios o FAQs
3. Haz clic en **"Fetch links"** para que Chatbase rastree el contenido
4. Selecciona las páginas que quieres incluir y haz clic en **"Add links"**

### Opción C: Añadir Q&A manualmente

1. Haz clic en la pestaña **"Q&A"**
2. Haz clic en **"Add Q&A"**
3. Escribe la pregunta exacta que podría hacer un usuario y su respuesta ideal
4. Repite para cada par pregunta-respuesta

**Ejemplo para un servicio de seguridad:**
```
Pregunta: ¿Cómo activo la autenticación de dos factores?
Respuesta: Para activar 2FA, ve a tu panel de control → Configuración → 
           Seguridad → Autenticación en dos pasos y sigue los 3 pasos indicados.
```

---

## Paso 4 — Crear el chatbot

1. Una vez añadidas las fuentes, haz clic en **"Create Chatbot"** (botón azul, esquina superior derecha)
2. Chatbase procesará toda la información y creará el bot (puede tardar 1–2 minutos)
3. Aparecerá una **vista previa en vivo** donde ya puedes hacerle preguntas de prueba

---

## Paso 5 — Configurar el comportamiento (Settings)

Haz clic en la pestaña **"Settings"** en la parte superior.

### 5.1 Información general

- **Name:** Dale un nombre a tu chatbot (ej. *"Asistente de Seguridad"*)
- **Default message:** El primer mensaje que verá el usuario (ej. *"¡Hola! Soy el asistente de [Tu Empresa]. ¿En qué puedo ayudarte hoy?"*)

### 5.2 Modelo de IA (Model)

En el menú lateral izquierdo, haz clic en **"Model"**:

| Campo | Recomendación |
|-------|---------------|
| **Model** | `GPT-3.5-turbo` (más rápido y económico) |
| **Temperature** | `0` – `0.3` (respuestas más precisas y consistentes) |
| **Instructions** | Escribe aquí el comportamiento del bot |

**Ejemplo de instrucciones para un servicio de seguridad:**
```
Eres el asistente virtual de [Nombre de tu empresa], especializado en 
servicios de ciberseguridad. Tu objetivo es:

1. Explicar de forma clara y sencilla los servicios que ofrecemos
2. Guiar a los usuarios paso a paso en procesos técnicos
3. Responder únicamente sobre los servicios de seguridad de nuestra empresa
4. Si no conoces la respuesta, indica al usuario que contacte con soporte

Usa un tono profesional pero cercano. Evita tecnicismos innecesarios.
Responde siempre en el mismo idioma que el usuario.
```

### 5.3 Límite de respuestas

- Activa **"Restrict to trained data"** para que el bot solo responda sobre tus servicios y no invente información

---

## Paso 6 — Personalizar la apariencia

En **Settings → Chat Interface**:

- **Bot name:** Nombre visible en el chat
- **Bot avatar:** Sube el logo o icono de tu empresa
- **User message color:** Color de los mensajes del usuario
- **Bot message color:** Color de las respuestas del bot
- **Chat bubble color:** Color del botón flotante en tu web
- **Initial messages:** Mensajes de bienvenida automáticos al abrir el chat

> 🎨 Elige colores que coincidan con la paleta de tu web para una experiencia coherente.

---

## Paso 7 — Probar el chatbot

Antes de publicarlo, pruébalo a fondo:

1. Vuelve a la pestaña **"Chatbot"** (vista previa en vivo)
2. Hazle preguntas reales que podría hacer un cliente:
   - *"¿Cómo funciona vuestro servicio de monitorización?"*
   - *"¿Qué pasos debo seguir para instalar el agente?"*
   - *"¿Qué incluye el plan básico?"*
3. Si las respuestas son incorrectas o incompletas:
   - Vuelve a **Sources** y añade más documentación
   - Ajusta las **instrucciones** en Settings → Model
   - Añade pares **Q&A** específicos para esas preguntas

---

## Paso 8 — Publicar y obtener el código de embed

1. Ve a la pestaña **"Connect"** (en la barra superior)
2. Haz clic en **"Embed"**
3. Cambia el estado a **"Public"** (botón toggle)
4. Elige el tipo de integración:

### Opción A: Widget flotante (recomendado ✅)

El bot aparece como una burbuja en la esquina inferior derecha de tu web.

```html
<script>
  window.embeddedChatbotConfig = {
    chatbotId: "TU_CHATBOT_ID",
    domain: "www.chatbase.co"
  }
</script>
<script
  src="https://www.chatbase.co/embed.min.js"
  chatbotId="TU_CHATBOT_ID"
  domain="www.chatbase.co"
  defer>
</script>
```

### Opción B: iFrame (embebido estático)

El chat se muestra como un componente fijo dentro de una sección de tu web.

```html
<iframe
  src="https://www.chatbase.co/chatbot-iframe/TU_CHATBOT_ID"
  width="100%"
  style="height: 100%; min-height: 700px"
  frameborder="0">
</iframe>
```

> ⚠️ Reemplaza `TU_CHATBOT_ID` con el ID real que te proporciona Chatbase.

---

## Paso 9 — Integrar en tu web HTML

### Para webs en HTML estático

Pega el script del **widget flotante** justo antes del cierre `</body>` de tu HTML:

```html
  ...contenido de tu web...

  <!-- Chatbase Widget -->
  <script>
    window.embeddedChatbotConfig = {
      chatbotId: "TU_CHATBOT_ID",
      domain: "www.chatbase.co"
    }
  </script>
  <script
    src="https://www.chatbase.co/embed.min.js"
    chatbotId="TU_CHATBOT_ID"
    domain="www.chatbase.co"
    defer>
  </script>
</body>
</html>
```

### Para WordPress

1. Instala el plugin **"Insert Headers and Footers"** (gratuito)
2. Ve a **Ajustes → Insert Headers and Footers**
3. Pega el script en la sección **"Scripts in Footer"**
4. Guarda los cambios

### Para Webflow

1. Ve a **Project Settings → Custom Code**
2. Pega el script en **"Footer Code"**
3. Publica el proyecto

---

## Paso 10 — Verificar la integración

1. Abre tu web en el navegador
2. Deberías ver una **burbuja de chat** en la esquina inferior derecha
3. Haz clic en ella y envía un mensaje de prueba
4. Verifica que las respuestas son correctas y que el diseño encaja con tu web

---

## Gestión y mejora continua

### Ver las conversaciones

En el Dashboard de Chatbase, ve a **"Conversations"** para revisar:
- Qué preguntas hacen los usuarios
- Dónde falla el bot (respuestas incorrectas o "no sé")
- Patrones de uso más frecuentes

### Añadir más fuentes de datos

Cuando tengas nueva documentación:
1. Ve a **Sources**
2. Haz clic en **"Add Source"**
3. Sube los nuevos archivos
4. Haz clic en **"Retrain"** para actualizar el bot

### Mejorar respuestas específicas

Si detectas que el bot falla en alguna pregunta:
1. Ve a **Sources → Q&A**
2. Añade el par pregunta-respuesta exacto
3. Las respuestas Q&A tienen prioridad sobre el contenido extraído de documentos

---

## Resumen de planes

| Plan | Precio | Chatbots | Mensajes/mes | Caracteres |
|------|--------|----------|--------------|------------|
| **Free** | 0 €/mes | 1 | 20/día | 400.000 |
| **Hobby** | ~19 €/mes | 2 | 2.000 | 11 millones |
| **Standard** | ~49 €/mes | 5 | 5.000 | 11 millones |
| **Unlimited** | ~99 €/mes | 10 | Sin límite | 11 millones |

> Para empezar, el plan **Free** es más que suficiente para validar el chatbot. Escala cuando el volumen de usuarios lo requiera.

---

## Solución de problemas frecuentes

| Problema | Causa probable | Solución |
|----------|----------------|----------|
| El bot no aparece en la web | Script mal pegado | Verifica que el script está antes de `</body>` |
| El bot responde cosas incorrectas | Base de conocimiento incompleta | Añade más documentación o Q&A específicos |
| El bot habla de temas fuera del servicio | Instructions poco restrictivas | Añade "Responde SOLO sobre [tema]" en las instrucciones |
| El widget no carga en móvil | Conflicto con otros scripts | Asegúrate de que el script tiene el atributo `defer` |
| Los PDF no se procesan | Formato no compatible | Asegúrate de que el PDF tiene texto seleccionable (no escaneado) |

---

*Manual creado para implementar Chatbase en servicios de ciberseguridad · Versión 2026*
