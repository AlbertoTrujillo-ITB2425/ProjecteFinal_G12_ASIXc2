<script src="https://unpkg.com/@solana/web3.js@latest/lib/index.iife.min.js"></script>

<script>
async function payWithPhantom() {
    const btn = document.getElementById('phantom-btn');
    const btnText = document.getElementById('phantom-btn-text');
    const statusText = document.getElementById('wallet-status');
    
    // 1. Comprobar si Phantom está instalado
    const provider = window.phantom?.solana;
    if (!provider?.isPhantom) {
        alert("¡No detectamos Phantom Wallet! Por favor, instala la extensión.");
        window.open('https://phantom.app/', '_blank');
        return;
    }

    try {
        // Cambiar UI a cargando
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        btnText.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Conectando...';

        // 2. Conectar la Billetera
        const resp = await provider.connect();
        const userWallet = resp.publicKey.toString();
        statusText.innerHTML = `<span class="text-emerald-500">Conectado: ${userWallet.substring(0,4)}...${userWallet.substring(userWallet.length-4)}</span>`;

        // 3. Preparar la conexión a Solana (Usamos Devnet para pruebas)
        btnText.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Aprobando pago...';
        const connection = new solanaWeb3.Connection(solanaWeb3.clusterApiUrl('devnet'), 'confirmed');

        // DIRECCIÓN DE PRUEBAS DE TU PROYECTO (Cámbiala por la tuya en Mainnet luego)
        const projectWallet = new solanaWeb3.PublicKey("7aXv...PON_TU_WALLET_AQUI...x9Z"); 

        /* * NOTA TÉCNICA: Para transferir USDC real (un token SPL) se requiere compilar un 
         * script más complejo con @solana/spl-token. 
         * Para esta demo de validación, pediremos una transferencia en SOL equivalente 
         * a céntimos en Devnet para que Phantom salte y el usuario vea el flujo real.
         */
        const transaction = new solanaWeb3.Transaction().add(
            solanaWeb3.SystemProgram.transfer({
                fromPubkey: resp.publicKey,
                toPubkey: projectWallet,
                lamports: solanaWeb3.LAMPORTS_PER_SOL * 0.01 // Monto demo en Devnet
            })
        );

        // 4. Configurar la transacción
        const { blockhash } = await connection.getLatestBlockhash();
        transaction.recentBlockhash = blockhash;
        transaction.feePayer = resp.publicKey;

        // 5. Pedirle a Phantom que firme y envíe
        const signedTransaction = await provider.signTransaction(transaction);
        const signature = await connection.sendRawTransaction(signedTransaction.serialize());
        
        statusText.innerHTML = `<span class="text-sky-500">Confirmando en blockchain...</span>`;
        await connection.confirmTransaction(signature);

        // 6. Éxito: Actualizar UI y (en un futuro) la base de datos
        btn.classList.replace('bg-[#AB9FF2]', 'bg-emerald-500');
        btnText.innerHTML = '<i class="fas fa-check-circle"></i> Pago Completado';
        statusText.innerHTML = `<span class="text-emerald-500 font-bold">¡Bienvenido a CyberAudit Pro!</span>`;
        
        // Aquí harías una llamada fetch() a tu PHP para actualizar 'is_premium = 1' en tu BD
        // fetch('upgrade_account.php', { method: 'POST', body: JSON.stringify({ wallet: userWallet }) });

    } catch (err) {
        console.error(err);
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
        btnText.innerText = 'Pagar Suscripción con Phantom';
        
        if (err.code === 4001) {
            statusText.innerHTML = `<span class="text-red-500">Transacción rechazada por el usuario.</span>`;
        } else {
            statusText.innerHTML = `<span class="text-red-500">Error: Fondos insuficientes o red congestionada.</span>`;
        }
    }
}

async function ejecutarFirewall() {
    const btn = document.getElementById('btn-exec');
    const outputContainer = document.getElementById('ssh-output-container');
    const output = document.getElementById('ssh-output');
    
    const host = document.getElementById('ssh-ip').value;
    const user = document.getElementById('ssh-user').value;
    const pass = document.getElementById('ssh-pass').value;

    if(!pass) { alert("Necesitas ingresar la contraseña"); return; }

    // Cambiar estado del botón
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Ejecutando...';
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    outputContainer.classList.remove('hidden');
    output.innerHTML = '> Estableciendo conexión SSH con ' + host + '...\n';

    try {
        const response = await fetch('firewall_exec.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ host, user, pass })
        });
        
        const data = await response.json();
        
        if(data.status === 'success') {
            output.innerHTML += '<span class="text-sky-500">> Autenticación correcta. Ejecutando comandos...</span>\n\n';
            // Convertimos los saltos de línea a <br> para HTML
            output.innerHTML += data.output.replace(/\n/g, '<br>');
            btn.innerHTML = '<i class="fas fa-check"></i> Aplicado con Éxito';
            btn.classList.replace('bg-orange-600', 'bg-emerald-600');
        } else {
            output.innerHTML += '<span class="text-red-500">> ERROR: ' + data.message + '</span>';
            btn.innerHTML = '<i class="fas fa-rotate-right"></i> Reintentar';
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    } catch (err) {
        output.innerHTML += '<span class="text-red-500">> ERROR CRÍTICO: Fallo en la comunicación con el servidor local.</span>';
        btn.innerHTML = '<i class="fas fa-rotate-right"></i> Reintentar';
    }
}
</script>
<footer class="mt-5 p-4 text-center text-muted border-top border-dark">
    <small>&copy; 2026 CYBERAUDIT ELITE - Advanced Security Framework</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
