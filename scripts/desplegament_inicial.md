# **Manual d'Instal·lació CyberPyme**

Benvingut a la guia de desplegament de **CyberPyme**. Aquest document el guiarà a través d'un procés totalment automatitzat per instal·lar la plataforma en un servidor Ubuntu.

### **1. Requisits de l'Entorn**

Abans d'iniciar el procés, assegureu-vos que el vostre entorn compleix els següents requisits:

| Requisit             | Detall                                                                 | Estat |
| -------------------- | ---------------------------------------------------------------------- | :---: |
|  **Sistema Operatiu** | Un servidor amb **Ubuntu 22.04 LTS** o superior.                       |  ✅   |
|  **Accés a Internet** | Connectivitat sortint per descarregar paquets i el codi font.          |  ✅   |
|  **Tallafocs (Firewall)** | Ports **80/tcp (HTTP)** i **443/tcp (HTTPS)** permesos per al trànsit entrant. |  ✅   |

---

### **2. Accés al Servidor Mitjançant SSH**

El primer pas és establir una connexió segura amb el terminal del seu servidor.

#### **2.1. Obtenció de la Clau d'Accés**

Descarregui la clau privada (`vockey.pem`), necessària per a l'autenticació, des de l'enllaç segur proporcionat:

> **[🔑 Descarregar Clau d'Accés Segura (vockey.pem)](https://drive.google.com/file/d/10NKAE9waCh9OtFUaGFFJ-ZP5xLfD7hV5/view?usp=sharing)**

#### **2.2. Ajust de Permisos de la Clau**

Per motius de seguretat, és imprescindible restringir els permisos del fitxer de la clau. Obri un terminal al seu ordinador local i executi:

```bash
chmod 600 vockey.pem
```

*Aquesta acció garanteix que només el seu usuari tingui permís de lectura sobre la clau.*

#### **2.3. Connexió al Servidor**

Utilitzi la següent comanda per accedir al servidor, substituint `vockey.pem` per la ruta al seu fitxer si és necessari:

```bash
ssh -i "vockey.pem" ubuntu@ec2-32-194-186-97.compute-1.amazonaws.com
```

Un cop executada amb èxit, tindrà accés a la línia de comandes del servidor remot.

---

### **3. Desplegament Automatitzat de la Plataforma**

Amb la connexió establerta, iniciï la instal·lació amb una única comanda. Aquest procés és autònom i no requereix intervenció manual.

*   Copïi i executi el següent comandament al terminal del seu servidor:

    ```bash
    curl -sSL https://raw.githubusercontent.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/main/scripts/project_setup.sh | sudo bash
    ```

<details>
<summary><strong>ℹ️ Què fa exactament aquest script? (Feu clic per veure)</strong></summary>

*   ✅ **Validació del Sistema**: Comprova que el sistema operatiu sigui una versió compatible d'Ubuntu.
*   🐳 **Instal·lació de Docker**: Detecta i instal·la Docker Engine i Docker Compose si no estan presents.
*   📂 **Gestió del Codi Font**: Clona la darrera versió del repositori de **CyberPyme**.
*   ⚙️ **Configuració Automàtica**: Genera els fitxers de configuració necessaris per a un entorn de producció.
*   ▶️ **Arrencada dels Serveis**: Orquestra i aixeca tota la pila d'aplicacions amb Docker Compose.

</details>

<br>

*   El procés trigarà uns minuts. En finalitzar, es mostrarà un missatge de confirmació:

    ```
    ✅ ÈXIT: La plataforma CyberPyme ha estat desplegada correctament.
    
       Podeu accedir-hi a través de: http://<LA_VOSTRA_IP_PÚBLICA>
    ```

---

### **4. (Opcional) Activació de Seguretat amb HTTPS**

Per protegir la seva plataforma amb un certificat SSL/TLS i activar el protocol `https://`, pot executar un segon script automatitzat.

**Requisit Previ**: Disposar d'un **nom de domini** que apunti a l'adreça IP pública del seu servidor (mitjançant un registre DNS de tipus `A`).

1.  Executi el següent comandament al terminal del servidor:
    ```bash
    curl -sSL https://raw.githubusercontent.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/main/scripts/enable_https.sh | sudo bash
    ```

2.  L'script li sol·licitarà interactivament:
    *   El seu **nom de domini** (ex: `portal.lamevaempresa.com`).
    *   El seu **correu electrònic** (per a notificacions de renovació del certificat).

El procés configurarà Nginx com a *reverse proxy*, generarà un certificat de Let's Encrypt i establirà la renovació automàtica.

---

### ** Finalització**

Felicitats! Ha completat amb èxit el desplegament de **CyberPyme**. La plataforma ja està operativa i llesta per ser utilitzada.
