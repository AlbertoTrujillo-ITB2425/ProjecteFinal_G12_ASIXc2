Aquí teniu una guia ràpida i directa per desplegar el nostre projecte **CybePyme**. Està tot automatitzat perquè només hàgim de llançar una comanda i llestos.

### 1. Connexió al Servidor (SSH)

Primer, hem d'entrar a la màquina d'AWS.

1.  **Baixeu la clau d'accés (`vockey.pem`)**:
    [https://drive.google.com/file/d/10NKAE9waCh9OtFUaGFFJ-ZP5xLfD7hV5/view?usp=sharing](https://drive.google.com/file/d/10NKAE9waCh9OtFUaGFFJ-ZP5xLfD7hV5/view?usp=sharing)

2.  **Doneu-li els permisos correctes** (només lectura per a tu):
    ```bash
    chmod 600 vockey.pem
    ```

3.  **Connecteu-vos al servidor**:
    ```bash
    ssh -i "vockey.pem" ubuntu@ec2-32-194-186-97.compute-1.amazonaws.com
    ```

Un cop a dins, assegureu-vos que els ports **80 (HTTP)** i **443 (HTTPS)** estan oberts al Security Group d'AWS.

### 2. Instal·lació (1 comanda i a córrer)

Ja dins del servidor, executeu això. No cal clonar el repo ni instal·lar res a mà.

#### Amb `curl` (recomanat):
```bash
curl -sSL https://raw.githubusercontent.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/main/scripts/project_setup.sh | sudo bash
```

#### O amb `wget`:
```bash
wget -qO- https://raw.githubusercontent.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/main/scripts/project_setup.sh | sudo bash
```

**Què fa l'script?**
*   Instal·la Docker, clona el repo, crea el `.env`, ajusta el `docker-compose.yml` i aixeca tots els contenidors.
*   Al final, us donarà la **IP pública** per accedir a la plataforma.

### 3. (Opcional) Posar-li HTTPS

Si voleu que la connexió sigui segura, necessiteu un domini que apunti a la IP del servidor. Un cop el tingueu, llanceu aquest altre script:

```bash
curl -sSL https://raw.githubusercontent.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/main/scripts/enable_https.sh | sudo bash
```

L'script ho configurarà tot automàticament (Nginx, certificat SSL, etc.) i ja tindreu la web amb el cadenat verd! 🔒

Qualsevol dubte, ho comentem!

---
---

## Versió per a Professors (Formal i Actualitzada)

Benvolguts professors,

A continuació, es presenta el manual de desplegament del projecte **CyberAudit Hub**, dissenyat per a una execució automatitzada i eficient.

### 1. Accés a l'Entorn de Desplegament

El primer pas consisteix a establir una connexió segura amb el servidor assignat mitjançant SSH.

1.  **Obtenció de la Clau d'Accés**: La clau privada (`vockey.pem`), necessària per a l'autenticació, es troba disponible al següent enllaç:
    [URL de la clau vockey.pem](https://drive.google.com/file/d/10NKAE9waCh9OtFUaGFFJ-ZP5xLfD7hV5/view?usp=sharing)

2.  **Ajust de Permisos**: Per motius de seguretat, és imperatiu restringir els permisos del fitxer de la clau.
    ```bash
    chmod 600 vockey.pem
    ```

3.  **Connexió al Servidor**: Executeu la següent comanda per accedir al terminal del servidor.
    ```bash
    ssh -i "vockey.pem" ubuntu@ec2-32-194-186-97.compute-1.amazonaws.com
    ```

**Requisits de Xarxa**: Un cop establerta la connexió, cal verificar que el tallafocs de l'entorn (ex. AWS Security Group) permet trànsit entrant als ports `80/tcp` i `443/tcp`.

### 2. Procediment d'Instal·lació Automatitzada

Un cop dins del servidor, el desplegament de l'arquitectura es realitza mitjançant un únic script d'inicialització.

#### Opció 1: Mitjançant `curl` (Mètode recomanat)
```bash
curl -sSL https://raw.githubusercontent.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/main/scripts/project_setup.sh | sudo bash
```

#### Opció 2: Mitjançant `wget`
```bash
wget -qO- https://raw.githubusercontent.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/main/scripts/project_setup.sh | sudo bash
```

Aquest script s'encarrega de validar el sistema, instal·lar dependències (Docker), gestionar el codi font, configurar l'entorn (`.env`, `docker-compose.yml`) i orquestrar tots els serveis. En finalitzar, informa de la URL d'accés.

### 3. (Opcional) Activació del Protocol HTTPS

Per a un entorn de producció, es facilita un segon script per configurar HTTPS amb un certificat de Let's Encrypt. Aquest pas requereix un nom de domini públic apuntant a la IP del servidor.

```bash
curl -sSL https://raw.githubusercontent.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7/main/scripts/enable_https.sh | sudo bash
```

El procés automatitza la instal·lació de Nginx, la generació del certificat SSL i la configuració de la renovació automàtica, assegurant una connexió segura a llarg termini.
