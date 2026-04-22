<?php
include 'db_conn.php';
include 'views/header.php';

$ip = htmlspecialchars($_GET['ip'] ?? '');
?>

<style>
  /* ── Terminal SSH ── */
  #ssh-terminal {
    background: #0a0a0f;
    color: #00ff9f;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 12px;
    padding: 12px;
    height: 340px;
    overflow-y: auto;
    border-radius: 0 0 8px 8px;
    white-space: pre-wrap;
    word-break: break-all;
  }
  #ssh-input-line {
    display: flex;
    align-items: center;
    background: #0a0a0f;
    border-top: 1px solid #1e3a2f;
    padding: 6px 12px;
    border-radius: 0 0 8px 8px;
  }
  #ssh-prompt { color: #00ff9f; margin-right: 6px; font-family: monospace; font-size: 12px; white-space: nowrap; }
  #ssh-cmd { flex: 1; background: transparent; border: none; outline: none; color: #fff; font-family: monospace; font-size: 12px; }

  /* Spinner */
  .spin { animation: spin 1s linear infinite; display: inline-block; }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* Nmap output */
  #nmap-output {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    background: #000;
    color: #34d399;
    padding: 12px;
    height: 220px;
    overflow-y: auto;
    white-space: pre-wrap;
    border-radius: 0 0 8px 8px;
  }

  /* Risk badges */
  .badge-critico  { background:#7f1d1d; color:#fca5a5; }
  .badge-alto     { background:#431407; color:#fb923c; }
  .badge-medio    { background:#422006; color:#fbbf24; }
  .badge-bajo     { background:#052e16; color:#4ade80; }
</style>

<main class="max-w-7xl mx-auto py-8 px-4">

  <!-- Header -->
  <div class="mb-8 flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold text-white">
        <i class="fas fa-satellite-dish text-sky-500 mr-2"></i> Auditoría Avanzada
      </h1>
      <p class="text-slate-400 text-sm mt-1">Superficie de ataque externa · Forense SSH · Terminal remota.</p>
    </div>
    <a href="index.php" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all">
      <i class="fas fa-arrow-left mr-2"></i> Volver al Hub
    </a>
  </div>

  <!-- Search bar -->
  <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-8 shadow-lg">
    <div class="flex gap-4">
      <input id="scan-ip" type="text" value="<?= $ip ?>" placeholder="IP o dominio objetivo (ej: 192.168.1.1 o ejemplo.com)…"
             class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-sky-500 outline-none">
      <button onclick="startScan()" class="bg-sky-600 hover:bg-sky-500 text-white px-8 py-2 rounded-lg font-bold shadow-lg shadow-sky-500/20 transition-all">
        <i class="fas fa-crosshairs mr-2"></i>Escanear
      </button>
    </div>
  </div>

  <!-- Main grid (shown only after scan) -->
  <div id="results-area" class="<?= $ip ? '' : 'hidden' ?>">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- LEFT COLUMN -->
      <div class="space-y-6">

        <!-- Nmap panel -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg">
          <div class="bg-slate-800 px-4 py-3 flex justify-between items-center">
            <h3 class="font-bold text-white text-sm">
              <i class="fas fa-network-wired text-sky-500 mr-2"></i> Reconocimiento Nmap (Caja Negra)
            </h3>
            <span id="nmap-status" class="text-xs text-slate-400"></span>
          </div>
          <div id="nmap-output">Introduce una IP y pulsa Escanear…</div>
        </div>

        <!-- Recommendations -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-lg">
          <h3 class="font-bold text-white mb-4 text-sm">
            <i class="fas fa-clipboard-check text-emerald-500 mr-2"></i> Recomendaciones de Mitigación
          </h3>
          <div id="recomendaciones" class="space-y-3">
            <p class="text-slate-500 text-xs">Esperando resultados del escaneo…</p>
          </div>
        </div>

        <!-- Firewall helper (actúa sobre el target real) -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-lg">
          <h3 class="font-bold text-white text-sm mb-1">
            <i class="fas fa-shield-halved text-amber-500 mr-2"></i> Asistente de Firewall UFW
          </h3>
          <p class="text-xs text-slate-400 mb-4">Aplica reglas UFW directamente en el servidor objetivo vía SSH.</p>

          <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase">Usuario SSH</label>
                <input id="fw-user" type="text" value="root" class="w-full bg-slate-950 border border-slate-700 rounded px-3 py-2 text-white text-sm outline-none focus:ring-1 focus:ring-amber-500">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase">Contraseña</label>
                <input id="fw-pass" type="password" class="w-full bg-slate-950 border border-slate-700 rounded px-3 py-2 text-white text-sm outline-none focus:ring-1 focus:ring-amber-500">
              </div>
            </div>

            <div class="bg-slate-950 rounded p-3 text-xs font-mono text-amber-300 space-y-1">
              <div>ufw default deny incoming</div>
              <div>ufw allow 80/tcp   <span class="text-slate-500"># HTTP</span></div>
              <div>ufw allow 443/tcp  <span class="text-slate-500"># HTTPS</span></div>
              <div>ufw deny 3306/tcp  <span class="text-slate-500"># Aísla MySQL</span></div>
              <div>ufw --force enable</div>
            </div>

            <button onclick="applyFirewall()"
                    class="w-full bg-amber-600 hover:bg-amber-500 text-white font-bold py-2 rounded-lg text-sm shadow-lg transition-all flex justify-center items-center gap-2">
              <i class="fas fa-bolt"></i> Aplicar en <span id="fw-target-label" class="font-mono"><?= $ip ?: '—' ?></span>
            </button>

            <div id="fw-result" class="hidden text-xs font-mono bg-black rounded p-3 text-emerald-400 whitespace-pre-wrap max-h-32 overflow-y-auto"></div>
          </div>
        </div>

      </div><!-- /LEFT -->

      <!-- RIGHT COLUMN -->
      <div class="space-y-6">

        <!-- Forensics via SSH -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-lg p-4">
          <h3 class="font-bold text-white text-lg mb-1">
            <i class="fas fa-microscope text-purple-500 mr-2"></i> Análisis Forense en Vivo
          </h3>
          <p class="text-xs text-slate-400 mb-4">Inspecciona conexiones, usuarios activos e intentos de intrusión vía SSH.</p>

          <div id="forensics-form" class="space-y-3 bg-slate-950 p-4 rounded-xl border border-slate-800">
            <div id="forensics-error" class="hidden text-xs text-red-400 bg-red-500/10 p-2 rounded border border-red-500/20"></div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase">Usuario Linux</label>
                <input id="f-user" type="text" value="root" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white text-sm outline-none">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase">Contraseña</label>
                <input id="f-pass" type="password" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white text-sm outline-none">
              </div>
            </div>
            <button onclick="runForensics()"
                    class="w-full bg-purple-600 hover:bg-purple-500 text-white font-bold py-2 rounded-lg text-sm shadow-lg shadow-purple-500/20 transition-all flex justify-center items-center gap-2">
              <i class="fas fa-key"></i> Inspección Profunda
            </button>
          </div>

          <div id="forensics-results" class="hidden space-y-3 mt-3">
            <div>
              <p class="text-xs text-red-400 font-bold uppercase tracking-wider mb-1">Ataques recientes (auth.log)</p>
              <div id="f-ataques" class="bg-black p-3 rounded border border-red-500/30 text-[10px] font-mono text-red-400 h-20 overflow-y-auto whitespace-pre-wrap"></div>
            </div>
            <div>
              <p class="text-xs text-sky-400 font-bold uppercase tracking-wider mb-1">Conexiones establecidas</p>
              <div id="f-conexiones" class="bg-black p-3 rounded border border-sky-500/30 text-[10px] font-mono text-sky-300 h-20 overflow-y-auto whitespace-pre-wrap"></div>
            </div>
            <div>
              <p class="text-xs text-emerald-400 font-bold uppercase tracking-wider mb-1">Usuarios autenticados</p>
              <div id="f-usuarios" class="bg-black p-3 rounded border border-emerald-500/30 text-[10px] font-mono text-emerald-300 h-16 overflow-y-auto whitespace-pre-wrap"></div>
            </div>
            <button onclick="resetForensics()" class="text-xs text-slate-500 hover:text-white transition-all">
              <i class="fas fa-rotate-left mr-1"></i> Nueva conexión
            </button>
          </div>
        </div>

        <!-- SSH Terminal -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-lg overflow-hidden">
          <div class="bg-slate-800 px-4 py-3 flex justify-between items-center">
            <h3 class="font-bold text-white text-sm">
              <i class="fas fa-terminal text-green-400 mr-2"></i> Terminal SSH Interactiva
            </h3>
            <div class="flex items-center gap-2">
              <span id="term-status" class="text-xs text-slate-500">Desconectado</span>
              <button id="term-disconnect-btn" onclick="disconnectTerminal()" class="hidden text-xs bg-red-700 hover:bg-red-600 text-white px-2 py-1 rounded transition-all">Desconectar</button>
            </div>
          </div>

          <!-- Login form -->
          <div id="term-login" class="p-4 bg-slate-950 space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase">Usuario</label>
                <input id="t-user" type="text" value="root" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white text-sm outline-none focus:ring-1 focus:ring-green-500">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1 uppercase">Contraseña</label>
                <input id="t-pass" type="password" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white text-sm outline-none focus:ring-1 focus:ring-green-500">
              </div>
            </div>
            <button onclick="connectTerminal()"
                    class="w-full bg-green-700 hover:bg-green-600 text-white font-bold py-2 rounded-lg text-sm flex justify-center items-center gap-2 transition-all">
              <i class="fas fa-plug"></i> Conectar Terminal
            </button>
            <div id="term-error" class="hidden text-xs text-red-400 bg-red-500/10 p-2 rounded border border-red-500/20"></div>
          </div>

          <!-- Active terminal (hidden until connected) -->
          <div id="term-active" class="hidden">
            <div id="ssh-terminal"></div>
            <div id="ssh-input-line">
              <span id="ssh-prompt">$</span>
              <input id="ssh-cmd" type="text" autocomplete="off" spellcheck="false"
                     placeholder="escribe un comando y pulsa Enter…"
                     onkeydown="handleTermKey(event)">
            </div>
          </div>
        </div>

      </div><!-- /RIGHT -->
    </div>
  </div><!-- /results-area -->
</main>

<script>
/* ====================================================
   ESTADO GLOBAL
==================================================== */
let currentIp = <?= json_encode($ip) ?>;
let sshSessionId = null;
let cmdHistory = [];
let histIdx = -1;

/* ====================================================
   1. ESCANEO NMAP ASÍNCRONO
==================================================== */
function startScan() {
  const ip = document.getElementById('scan-ip').value.trim();
  if (!ip) return alert('Introduce una IP o dominio.');
  currentIp = ip;

  // Update URL without reload
  history.pushState({}, '', `scanner.php?ip=${encodeURIComponent(ip)}`);

  // Show results area
  document.getElementById('results-area').classList.remove('hidden');
  document.getElementById('fw-target-label').textContent = ip;

  // Reset panels
  document.getElementById('nmap-output').textContent = '⏳ Iniciando escaneo nmap -F -sV…';
  document.getElementById('nmap-status').innerHTML = '<span class="spin">⟳</span> Escaneando…';
  document.getElementById('recomendaciones').innerHTML = '<p class="text-slate-500 text-xs">Esperando resultados…</p>';

  fetch(`api/scan_async.php?ip=${encodeURIComponent(ip)}`)
    .then(r => r.json())
    .then(data => {
      document.getElementById('nmap-output').textContent = data.nmap || 'Sin resultados.';
      document.getElementById('nmap-status').textContent = '✓ Completado';
      renderRecomendaciones(data.recomendaciones || []);
    })
    .catch(() => {
      document.getElementById('nmap-output').textContent = 'Error al contactar el servidor de escaneo.';
      document.getElementById('nmap-status').textContent = '✗ Error';
    });
}

function renderRecomendaciones(recs) {
  const colorMap = { CRÍTICO: 'badge-critico', ALTO: 'badge-alto', MEDIO: 'badge-medio', BAJO: 'badge-bajo' };
  const container = document.getElementById('recomendaciones');
  if (!recs.length) {
    container.innerHTML = '<p class="text-slate-500 text-xs">Sin recomendaciones.</p>';
    return;
  }
  container.innerHTML = recs.map(r => `
    <div class="bg-slate-950 p-3 rounded border border-slate-800 flex items-start gap-3">
      <span class="text-xs font-bold px-2 py-1 rounded ${colorMap[r.riesgo] || 'badge-bajo'}">${r.riesgo}</span>
      <p class="text-sm text-slate-300">${r.msg}</p>
    </div>`).join('');
}

// Auto-scan si hay IP en URL
if (currentIp) startScan();

/* ====================================================
   2. ANÁLISIS FORENSE
==================================================== */
function runForensics() {
  const user = document.getElementById('f-user').value.trim();
  const pass = document.getElementById('f-pass').value;
  const errEl = document.getElementById('forensics-error');
  errEl.classList.add('hidden');

  if (!user || !pass) { errEl.textContent = 'Completa usuario y contraseña.'; errEl.classList.remove('hidden'); return; }

  const btn = event.target;
  btn.innerHTML = '<span class="spin">⟳</span> Conectando…';
  btn.disabled = true;

  fetch('api/forensics.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ ip: currentIp, user, pass })
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'error') {
      errEl.textContent = data.message;
      errEl.classList.remove('hidden');
      btn.innerHTML = '<i class="fas fa-key"></i> Inspección Profunda';
      btn.disabled = false;
      return;
    }
    document.getElementById('f-ataques').textContent    = data.ataques    || 'Sin ataques detectados.';
    document.getElementById('f-conexiones').textContent = data.conexiones || 'Sin conexiones.';
    document.getElementById('f-usuarios').textContent   = data.usuarios   || 'Sin usuarios.';
    document.getElementById('forensics-form').classList.add('hidden');
    document.getElementById('forensics-results').classList.remove('hidden');
  })
  .catch(() => {
    errEl.textContent = 'Error de red.';
    errEl.classList.remove('hidden');
    btn.innerHTML = '<i class="fas fa-key"></i> Inspección Profunda';
    btn.disabled = false;
  });
}

function resetForensics() {
  document.getElementById('forensics-form').classList.remove('hidden');
  document.getElementById('forensics-results').classList.add('hidden');
}

/* ====================================================
   3. FIREWALL
==================================================== */
function applyFirewall() {
  const user = document.getElementById('fw-user').value.trim();
  const pass = document.getElementById('fw-pass').value;
  const resultEl = document.getElementById('fw-result');

  if (!user || !pass) { alert('Introduce credenciales SSH para el servidor objetivo.'); return; }
  if (!currentIp)     { alert('Primero introduce y escanea una IP.'); return; }

  resultEl.textContent = '⏳ Aplicando reglas…';
  resultEl.classList.remove('hidden');

  fetch('firewall_exec.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ host: currentIp, user, pass })
  })
  .then(r => r.json())
  .then(data => {
    resultEl.textContent = (data.status === 'success' ? '✓ ' : '✗ ') + data.message + (data.output ? '\n\n' + data.output : '');
    resultEl.style.color = data.status === 'success' ? '#4ade80' : '#f87171';
  })
  .catch(() => { resultEl.textContent = '✗ Error de red.'; resultEl.style.color = '#f87171'; });
}

/* ====================================================
   4. TERMINAL SSH INTERACTIVA
==================================================== */
function termWrite(text, cls = '') {
  const term = document.getElementById('ssh-terminal');
  const line = document.createElement('span');
  if (cls) line.className = cls;
  line.textContent = text;
  term.appendChild(line);
  term.scrollTop = term.scrollHeight;
}

function connectTerminal() {
  const user = document.getElementById('t-user').value.trim();
  const pass = document.getElementById('t-pass').value;
  const errEl = document.getElementById('term-error');
  errEl.classList.add('hidden');

  if (!user || !pass) { errEl.textContent = 'Completa usuario y contraseña.'; errEl.classList.remove('hidden'); return; }
  if (!currentIp)     { errEl.textContent = 'Escanea primero una IP objetivo.'; errEl.classList.remove('hidden'); return; }

  document.getElementById('term-status').textContent = '⟳ Conectando…';

  fetch('api/ssh_session.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'connect', ip: currentIp, user, pass })
  })
  .then(r => r.json())
  .then(data => {
    if (data.status !== 'ok') {
      errEl.textContent = data.message;
      errEl.classList.remove('hidden');
      document.getElementById('term-status').textContent = 'Error';
      return;
    }
    sshSessionId = data.session_id;
    document.getElementById('term-login').classList.add('hidden');
    document.getElementById('term-active').classList.remove('hidden');
    document.getElementById('term-disconnect-btn').classList.remove('hidden');
    document.getElementById('term-status').innerHTML = `<span class="text-green-400">● Conectado</span> como <b>${user}@${currentIp}</b>`;
    document.getElementById('ssh-prompt').textContent = `${user}@${currentIp}:~$`;
    document.getElementById('ssh-terminal').textContent = '';
    termWrite(`Conectado a ${currentIp}\n`, 'text-green-400');
    document.getElementById('ssh-cmd').focus();
  })
  .catch(() => {
    errEl.textContent = 'Error de red al intentar conectar.';
    errEl.classList.remove('hidden');
    document.getElementById('term-status').textContent = 'Error';
  });
}

function handleTermKey(e) {
  const input = document.getElementById('ssh-cmd');
  if (e.key === 'ArrowUp') {
    e.preventDefault();
    if (histIdx < cmdHistory.length - 1) { histIdx++; input.value = cmdHistory[cmdHistory.length - 1 - histIdx]; }
    return;
  }
  if (e.key === 'ArrowDown') {
    e.preventDefault();
    if (histIdx > 0) { histIdx--; input.value = cmdHistory[cmdHistory.length - 1 - histIdx]; }
    else { histIdx = -1; input.value = ''; }
    return;
  }
  if (e.key !== 'Enter') return;

  const cmd = input.value.trim();
  input.value = '';
  histIdx = -1;
  if (!cmd) return;

  cmdHistory.push(cmd);
  const prompt = document.getElementById('ssh-prompt').textContent;
  termWrite(`\n${prompt} ${cmd}\n`, 'text-slate-300');

  if (cmd === 'clear') { document.getElementById('ssh-terminal').textContent = ''; return; }
  if (cmd === 'exit')  { disconnectTerminal(); return; }

  fetch('api/ssh_session.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'exec', session_id: sshSessionId, cmd })
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'ok') {
      termWrite(data.output || '', 'text-emerald-300');
    } else {
      termWrite(data.message + '\n', 'text-red-400');
    }
  })
  .catch(() => termWrite('Error de comunicación con el servidor.\n', 'text-red-400'));
}

function disconnectTerminal() {
  if (sshSessionId) {
    fetch('api/ssh_session.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'disconnect', session_id: sshSessionId })
    }).catch(() => {});
    sshSessionId = null;
  }
  document.getElementById('term-active').classList.add('hidden');
  document.getElementById('term-login').classList.remove('hidden');
  document.getElementById('term-disconnect-btn').classList.add('hidden');
  document.getElementById('term-status').textContent = 'Desconectado';
  document.getElementById('t-pass').value = '';
}
</script>

<?php include 'views/footer.php'; ?>
