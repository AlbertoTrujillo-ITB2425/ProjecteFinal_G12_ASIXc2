# Cómo poner un chatbot con Qwen en tu página web 


---

## Antes de empezar — ¿Qué vamos a hacer exactamente?


El chatbot va a:
- Aparecer como una burbuja en la esquina de tu web 💬
- Responder preguntas sobre tus servicios de seguridad
- Usar **solo** la información que tú le des (nada inventado)

Esto es lo que vamos a montar:

```
El usuario escribe una pregunta en tu web
           ↓
El widget (un trocito de JavaScript) la manda a tu servidor
           ↓
Tu servidor le pregunta a Qwen (la IA de Alibaba)
           ↓
Qwen responde usando TUS documentos
           ↓
La respuesta aparece en el chat de tu web
```

---

## Lo que necesitas tener instalado

Antes de empezar, comprueba que tienes esto:

- **Node.js** → Escribe en la terminal: `node --version`  
  Si no te sale un número, descárgalo de https://nodejs.org (el de "LTS")
- **Un editor de código** → Visual Studio Code vale perfecto
- **Tu documentación** → Los PDFs o Word con info de tus servicios

---

## PARTE 1 — Crear la cuenta en Alibaba y conseguir la clave API

### Paso 1.1 — Registrarse

1. Entra en: **https://modelstudio.console.alibabacloud.com**
2. Haz clic en **"Login"** y luego en **"Sign Up"**
3. Pon tu email, una contraseña y ya está
4. Cuando te pida región, elige **Singapore** (es la más rápida desde España)

> 🎁 Al registrarte te dan **1 millón de tokens gratis**. Un token es como una palabra. Con 1 millón de tokens puedes tener miles y miles de conversaciones. Gratis. Sin tarjeta.

### Paso 1.2 — Conseguir la clave API

La clave API es como una contraseña secreta que permite que tu servidor use la IA de Alibaba.

1. Una vez dentro, haz clic en tu foto de perfil (arriba a la derecha)
2. Selecciona **"API Keys"**
3. Haz clic en **"Create API Key"**
4. Ponle el nombre que quieras, por ejemplo: `mi-chatbot`
5. Copia la clave. Tiene este aspecto: `sk-xxxxxxxxxxxxxxxxxxxxxxxx`

> ⚠️ **MUY IMPORTANTE:** Guarda esa clave en el bloc de notas ahora mismo. Solo se muestra una vez. Si la pierdes, tendrás que crear una nueva.

---

## PARTE 2 — Preparar tus documentos

El chatbot solo sabe lo que tú le enseñas. Si no le das información, no puede responder.

### Paso 2.1 — Convertir tus documentos a texto plano

El servidor lee archivos `.txt`. Si tienes PDFs, conviértelos así:

**En Linux/Mac (terminal):**
```bash
# Instalar la herramienta (solo una vez)
sudo apt install poppler-utils

# Convertir el PDF
pdftotext mi_documento.pdf mi_documento.txt
```

**En Windows:** Abre el PDF, selecciona todo el texto (Ctrl+A), cópialo y pégalo en el Bloc de notas. Guárdalo como `.txt`.

### Paso 2.2 — Crear la carpeta con los documentos

Crea una carpeta llamada `mis-documentos` y mete dentro todos los `.txt`. Por ejemplo:

```
mis-documentos/
├── que_hacemos.txt
├── como_contratar.txt
└── preguntas_frecuentes.txt
```

### Paso 2.3 — Cómo deben estar escritos los documentos

Cuanto más claro esté el texto, mejor responde el bot. Escríbelos así:

```
SERVICIO DE ANTIVIRUS EMPRESARIAL

Qué incluye:
Nuestro servicio de antivirus protege todos los ordenadores de tu empresa.
Incluye actualizaciones automáticas y soporte 24 horas.

Cómo se activa:
1. Te mandamos un email con el enlace de instalación
2. Haces doble clic en el archivo descargado
3. Sigues los 4 pasos del instalador
4. Listo, en 5 minutos está funcionando

Precio:
Desde 9,99 euros al mes por ordenador.
```

---

## PARTE 3 — Crear el servidor (el "cerebro" del chatbot)

Esto suena intimidante pero no lo es. Vamos a crear una carpeta con 3 archivos.

### Paso 3.1 — Crear la carpeta del proyecto

Abre la terminal y escribe esto:

```bash
# Crear la carpeta
mkdir chatbot-seguridad
cd chatbot-seguridad

# Copiar tus documentos aquí dentro
# (crea la subcarpeta y mete los .txt que preparaste antes)
mkdir knowledge-base
```

Ahora mueve tus archivos `.txt` dentro de la carpeta `knowledge-base`.

### Paso 3.2 — Instalar las dependencias

Dentro de la carpeta `chatbot-seguridad`, escribe en la terminal:

```bash
npm init -y
npm install express cors openai
```

Esto descarga las "herramientas" que necesita el servidor. Tardará un minuto.

### Paso 3.3 — Crear el archivo del servidor

Crea un archivo llamado **`server.js`** y pega exactamente esto:

```javascript
// Cargamos las herramientas que instalamos antes
const express = require('express');
const cors = require('cors');
const OpenAI = require('openai');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(cors());
app.use(express.json());

// Conectamos con Qwen usando nuestra clave API
const clienteQwen = new OpenAI({
  apiKey: process.env.QWEN_API_KEY,
  baseURL: 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1'
});

// Esta función lee todos los .txt de la carpeta knowledge-base
function leerDocumentos() {
  const carpeta = './knowledge-base';
  let todo = '';
  const archivos = fs.readdirSync(carpeta).filter(f => f.endsWith('.txt'));
  for (const archivo of archivos) {
    const contenido = fs.readFileSync(path.join(carpeta, archivo), 'utf8');
    todo += `\n\n=== ${archivo} ===\n${contenido}`;
  }
  return todo;
}

// Cargamos los documentos cuando arranca el servidor
const documentos = leerDocumentos();

// Aquí le decimos al bot cómo debe comportarse
// CAMBIA ESTO con el nombre real de tu empresa
const INSTRUCCIONES = `Eres el asistente virtual de [NOMBRE DE TU EMPRESA].
Tu trabajo es ayudar a los clientes explicando nuestros servicios de seguridad.

REGLAS QUE DEBES SEGUIR:
- Responde SOLO con información de los documentos que te doy abajo
- Si no sabes la respuesta, di: "No tengo esa información. Contacta con nosotros en soporte@tuempresa.com"
- Explica las cosas de forma sencilla, como si hablaras con alguien que no sabe de informática
- Si hay pasos que seguir, ponlos numerados (1, 2, 3...)
- Responde en el mismo idioma que el cliente
- Sé amable y profesional

INFORMACIÓN DE NUESTRA EMPRESA:
${documentos}`;

// Cuando alguien escribe en el chat, llega aquí
app.post('/api/chat', async (req, res) => {
  const { mensaje, historial = [] } = req.body;

  // Comprobamos que el mensaje no está vacío
  if (!mensaje || mensaje.trim() === '') {
    return res.status(400).json({ error: 'El mensaje está vacío' });
  }

  try {
    // Preparamos la conversación para mandársela a Qwen
    const conversacion = [
      { role: 'system', content: INSTRUCCIONES },
      ...historial.slice(-6), // Solo guardamos las últimas 3 respuestas
      { role: 'user', content: mensaje }
    ];

    // Llamamos a la IA de Qwen
    const respuesta = await clienteQwen.chat.completions.create({
      model: 'qwen-plus',
      messages: conversacion,
      temperature: 0.2, // 0 = muy preciso, 1 = muy creativo. Para soporte, bajo.
      max_tokens: 800
    });

    // Devolvemos la respuesta al widget de la web
    const textoRespuesta = respuesta.choices[0].message.content;
    res.json({ respuesta: textoRespuesta });

  } catch (error) {
    console.error('Error al llamar a Qwen:', error.message);
    res.status(500).json({ error: 'Algo salió mal en el servidor' });
  }
});

// Arrancamos el servidor en el puerto 3000
const PUERTO = process.env.PORT || 3000;
app.listen(PUERTO, () => {
  console.log(`✅ Servidor del chatbot funcionando en el puerto ${PUERTO}`);
});
```

### Paso 3.4 — Crear el archivo con la clave API

Crea un archivo llamado **`.env`** (sí, empieza con un punto) y pon esto:

```
QWEN_API_KEY=sk-xxxxxxxxxxxxxxxxxxxxxxxx
PORT=3000
```

Cambia `sk-xxxxxxxxxxxxxxxxxxxxxxxx` por tu clave real que copiaste antes.

> ⚠️ **Este archivo es secreto.** Si usas GitHub, crea un archivo `.gitignore` y escribe `.env` dentro para que no se suba nunca.

### Paso 3.5 — Probar que el servidor funciona

Instala una herramienta más para leer el `.env`:

```bash
npm install dotenv
```

Ahora arranca el servidor:

```bash
node -r dotenv/config server.js
```

Si ves este mensaje, todo va bien:
```
✅ Servidor del chatbot funcionando en el puerto 3000
```

Para probar que responde, abre **otra terminal** y escribe:

```bash
curl -X POST http://localhost:3000/api/chat \
  -H "Content-Type: application/json" \
  -d '{"mensaje": "Hola, qué servicios ofrecéis?"}'
```

Deberías ver una respuesta del bot. 🎉

---

## PARTE 4 — Crear el widget que aparece en tu web

### Paso 4.1 — Crear el archivo del widget

Crea un archivo llamado **`chatbot-widget.js`** (puede estar en la misma carpeta o directamente en tu web):

```javascript
(function () {

  // ⬇️ CAMBIA ESTO por la URL donde está tu servidor
  const URL_SERVIDOR = 'http://localhost:3000/api/chat';

  let historial = [];
  let abierto = false;

  // Los estilos del chat (colores, tamaños, etc.)
  const estilos = `
    #chat-boton {
      position: fixed; bottom: 20px; right: 20px;
      width: 58px; height: 58px; border-radius: 50%;
      background: #1a6b6b; color: white; border: none;
      font-size: 26px; cursor: pointer; z-index: 9999;
      box-shadow: 0 4px 15px rgba(0,0,0,0.25);
      transition: transform 0.2s;
    }
    #chat-boton:hover { transform: scale(1.1); }

    #chat-ventana {
      display: none; position: fixed;
      bottom: 90px; right: 20px;
      width: 340px; height: 500px;
      background: white; border-radius: 14px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.18);
      flex-direction: column; z-index: 9998;
      font-family: Arial, sans-serif; overflow: hidden;
    }
    #chat-ventana.visible { display: flex; }

    #chat-cabecera {
      background: #1a6b6b; color: white;
      padding: 14px 16px; font-weight: bold;
      font-size: 15px;
    }

    #chat-mensajes {
      flex: 1; overflow-y: auto;
      padding: 14px; display: flex;
      flex-direction: column; gap: 10px;
    }

    .mensaje {
      max-width: 80%; padding: 10px 13px;
      border-radius: 10px; font-size: 14px; line-height: 1.5;
    }
    .mensaje.bot {
      background: #f0f0f0; color: #222;
      align-self: flex-start;
    }
    .mensaje.usuario {
      background: #1a6b6b; color: white;
      align-self: flex-end;
    }
    .mensaje.cargando { color: #999; font-style: italic; }

    #chat-pie {
      display: flex; padding: 10px;
      border-top: 1px solid #ddd; gap: 8px;
    }
    #chat-input {
      flex: 1; padding: 9px 13px;
      border: 1px solid #ccc; border-radius: 20px;
      font-size: 14px; outline: none;
    }
    #chat-input:focus { border-color: #1a6b6b; }
    #chat-enviar {
      background: #1a6b6b; color: white;
      border: none; border-radius: 50%;
      width: 38px; height: 38px;
      font-size: 16px; cursor: pointer;
    }
    #chat-enviar:disabled { background: #ccc; cursor: not-allowed; }
  `;

  // Metemos los estilos en la página
  const styleTag = document.createElement('style');
  styleTag.textContent = estilos;
  document.head.appendChild(styleTag);

  // Creamos el HTML del widget
  document.body.insertAdjacentHTML('beforeend', `
    <button id="chat-boton">💬</button>
    <div id="chat-ventana">
      <div id="chat-cabecera">🛡️ Asistente de Seguridad</div>
      <div id="chat-mensajes">
        <div class="mensaje bot">
          ¡Hola! Estoy aquí para explicarte nuestros servicios de seguridad.
          ¿En qué te puedo ayudar? 😊
        </div>
      </div>
      <div id="chat-pie">
        <input id="chat-input" type="text" placeholder="Escribe tu pregunta..." />
        <button id="chat-enviar">➤</button>
      </div>
    </div>
  `);

  const boton = document.getElementById('chat-boton');
  const ventana = document.getElementById('chat-ventana');
  const mensajes = document.getElementById('chat-mensajes');
  const input = document.getElementById('chat-input');
  const enviar = document.getElementById('chat-enviar');

  // Abrir y cerrar el chat al hacer clic en la burbuja
  boton.addEventListener('click', () => {
    abierto = !abierto;
    ventana.classList.toggle('visible', abierto);
    boton.textContent = abierto ? '✕' : '💬';
  });

  // Función para añadir un mensaje a la pantalla
  function ponerMensaje(texto, quien) {
    const div = document.createElement('div');
    div.className = `mensaje ${quien}`;
    div.textContent = texto;
    mensajes.appendChild(div);
    mensajes.scrollTop = mensajes.scrollHeight;
    return div;
  }

  // Función principal: enviar mensaje al servidor
  async function enviarMensaje() {
    const texto = input.value.trim();
    if (!texto) return;

    input.value = '';
    enviar.disabled = true;
    ponerMensaje(texto, 'usuario');

    const mensajeCargando = ponerMensaje('Escribiendo...', 'bot cargando');

    try {
      const res = await fetch(URL_SERVIDOR, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mensaje: texto, historial: historial })
      });

      const datos = await res.json();
      mensajeCargando.remove();
      ponerMensaje(datos.respuesta, 'bot');

      // Guardamos el historial para que el bot recuerde la conversación
      historial.push(
        { role: 'user', content: texto },
        { role: 'assistant', content: datos.respuesta }
      );

    } catch (error) {
      mensajeCargando.remove();
      ponerMensaje('Ups, algo falló. Inténtalo de nuevo por favor.', 'bot');
    }

    enviar.disabled = false;
    input.focus();
  }

  enviar.addEventListener('click', enviarMensaje);
  input.addEventListener('keypress', e => {
    if (e.key === 'Enter') enviarMensaje();
  });

})();
```

---

## PARTE 5 — Poner el widget en tu web

### Si tu web es HTML normal

Abre el archivo HTML de tu web y añade esta línea justo antes de `</body>`:

```html
<script src="chatbot-widget.js"></script>
```

Así de simple. Si abres la web con el servidor funcionando, verás la burbuja 💬.

### Si tu web está en WordPress

1. Instala el plugin gratuito **"Insert Headers and Footers"**
2. Ve al escritorio de WordPress → **Ajustes → Insert Headers and Footers**
3. En la caja **"Footer"**, pega:
   ```html
   <script src="https://TU-DOMINIO.com/chatbot-widget.js"></script>
   ```
4. Guarda y ya está

---

## PARTE 6 — Subir el servidor a internet

Ahora mismo el servidor solo funciona en tu ordenador (`localhost`). Para que funcione en tu web real, necesitas subirlo a un servidor en la nube.

### La forma más fácil: Render.com (gratis)

1. Crea una cuenta en **https://render.com** con tu email
2. Sube tu proyecto a **GitHub** (solo los archivos, sin el `.env`)
3. En Render, haz clic en **"New Web Service"**
4. Conecta tu repositorio de GitHub
5. Pon esta configuración:
   - **Build Command:** `npm install`
   - **Start Command:** `node server.js`
6. En **"Environment Variables"**, añade:
   - Key: `QWEN_API_KEY`
   - Value: `sk-tu-clave-aqui`
7. Haz clic en **"Create Web Service"**

Render te dará una URL tipo `https://chatbot-seguridad.onrender.com`.

**Ahora cambia esto en tu `chatbot-widget.js`:**
```javascript
// Cambia esta línea
const URL_SERVIDOR = 'http://localhost:3000/api/chat';

// Por la URL que te dio Render
const URL_SERVIDOR = 'https://chatbot-seguridad.onrender.com/api/chat';
```

---

## PARTE 7 — Comprobación final ✅

Haz estas pruebas antes de darlo por terminado:

- [ ] Abro mi web y veo la burbuja 💬 en la esquina inferior derecha
- [ ] Hago clic en la burbuja y se abre el chat
- [ ] Escribo una pregunta sobre mis servicios y el bot responde correctamente
- [ ] El bot NO inventa cosas que no están en mis documentos
- [ ] Si pregunto algo que no está en los docs, el bot dice que no sabe y da el email de contacto
- [ ] El chat funciona en el móvil también

---

## Problemas típicos y cómo solucionarlos

| Qué pasa | Por qué pasa | Cómo lo arreglo |
|----------|-------------|-----------------|
| La burbuja no aparece | El script no se carga | Comprueba que la ruta al `.js` es correcta |
| El bot no responde | El servidor no está arrancado | Ejecuta `node -r dotenv/config server.js` |
| Error "401 Unauthorized" | La API Key está mal | Comprueba el `.env`, no debe tener espacios |
| El bot responde en inglés | Language no configurado | Añade "Responde siempre en español" a las instrucciones |
| El bot se inventa cosas | Documentos poco detallados | Añade más información a los `.txt` |
| Funciona en local pero no en la web | URL del servidor incorrecta | Cambia `localhost` por la URL de Render |

---

## Estructura final del proyecto

Al terminar, tu carpeta debería verse así:

```
chatbot-seguridad/
├── server.js              ← El servidor principal
├── chatbot-widget.js      ← El widget para la web
├── .env                   ← Tu clave API (¡no subir a GitHub!)
├── .gitignore             ← Contiene: .env y node_modules
├── package.json           ← Se crea solo con npm init
├── node_modules/          ← Se crea solo con npm install
└── knowledge-base/
    ├── servicios.txt
    ├── instalacion.txt
    └── faq.txt
```

---

*Manual realizado como proyecto de fin de módulo — ASIR 2025/2026*  
*Tecnologías usadas: Node.js · Express · Qwen API (Alibaba) · JavaScript vanilla*
