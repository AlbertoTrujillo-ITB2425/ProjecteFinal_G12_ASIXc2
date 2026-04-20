# 📝 Guía de Despliegue Rápido: CyberAudit Hub

## Pasos de Instalación

### Paso 1: Preparar el entorno
Accede a tu servidor o máquina local mediante terminal (SSH o directo). Asegúrate de tener permisos de `sudo`.

### Paso 2: Crear el script
Crea un archivo llamado `deploy.sh`:
```bash
nano deploy.sh
```

### Paso 3: Pegar el código
Copia y pega el siguiente script actualizado dentro del editor, luego guarda y cierra (`Ctrl+O`, `Enter`, `Ctrl+X`):

```bash
#!/bin/bash

# Colores para la terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # Sin color

echo -e "${BLUE}===========================================${NC}"
echo -e "${BLUE}   CyberAudit Hub - Instalador Automático  ${NC}"
echo -e "${BLUE}        Despliegue en Puerto 8080          ${NC}"
echo -e "${BLUE}===========================================${NC}"

# 1. Función para instalar Docker y Git si no existen
install_dependencies() {
    echo -e "${YELLOW}[+] Instalando dependencias (Git, Docker)...${NC}"
    sudo apt-get update -y
    sudo apt-get install -y git curl lsof ca-certificates gnupg

    # Añadir clave GPG oficial de Docker
    sudo install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    sudo chmod a+r /etc/apt/keyrings/docker.gpg

    # Añadir repositorio de Docker
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

    # Instalar Docker
    sudo apt-get update -y
    sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

    # Añadir usuario actual al grupo docker (para no usar sudo siempre)
    sudo usermod -aG docker $USER
    echo -e "${GREEN}[+] Dependencias instaladas correctamente.${NC}"
    echo -e "${YELLOW}[!] AVISO: Si es la primera vez que instalas Docker, es posible que necesites cerrar sesión y volver a entrar, o ejecutar 'newgrp docker' para que los permisos surtan efecto.${NC}"
}

# 2. Verificar dependencias e instalar si falta
for cmd in docker git; do
    if ! [ -x "$(command -v $cmd)" ]; then
        echo -e "${YELLOW}[!] $cmd no encontrado. Iniciando instalación...${NC}"
        install_dependencies
        break
    fi
done

# 3. Verificar si el puerto 8080 está ocupado (de forma segura)
if command -v lsof >/dev/null 2>&1 && lsof -Pi :8080 -sTCP:LISTEN -t >/dev/null 2>&1 ; then
    echo -e "${YELLOW}Aviso: El puerto 8080 ya está en uso. Revisa tus servicios.${NC}"
fi

# 4. Gestión del repositorio
REPO_URL="https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git"
TARGET_DIR="ProjecteFinal_G7"

if [ -d "$TARGET_DIR" ]; then
    echo -e "${BLUE}[+] Entrando en la carpeta existente...${NC}"
    cd "$TARGET_DIR" || exit
    git pull origin main
else
    echo -e "${GREEN}[+] Clonando repositorio...${NC}"
    git clone $REPO_URL
    cd "$TARGET_DIR" || exit
fi

# 5. Arreglar permisos y preparar directorios
echo -e "${GREEN}[+] Configurando entorno y permisos...${NC}"
mkdir -p redis_data ldap_config config/nginx g7_src/views
sudo chown -R $USER:$USER .

# 6. Levantar Docker (Usando 'docker compose' moderno en lugar de 'docker-compose')
echo -e "${GREEN}[+] Iniciando contenedores (Build)...${NC}"
docker compose up -d --build

# 7. Finalización
echo -e "${BLUE}===========================================${NC}"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ ¡CyberAudit Hub está operativo!${NC}"
    echo -e "${BLUE}Acceso:${NC} http://localhost:8080"
    echo -e "${YELLOW}Nota:${NC} Si no carga, revisa el mapeo de puertos en docker-compose.yml"
    echo -e "${BLUE}Servicios actuales:${NC}"
    docker compose ps
else
    echo -e "${RED}❌ El despliegue ha fallado.${NC}"
    echo -e "${YELLOW}Tip:${NC} Si el error es de permisos de Docker, ejecuta 'newgrp docker' y vuelve a correr el script."
fi
echo -e "${BLUE}===========================================${NC}"
```

### Paso 4: Dar permisos de ejecución y lanzar
Ejecuta los siguientes comandos:
```bash
chmod +x deploy.sh
./deploy.sh
```

---

## ⚠️ Notas importantes

1. **Permisos de Docker:** Si el script instala Docker por primera vez, al intentar levantar los contenedores podría dar error de permisos. Si esto ocurre, simplemente ejecuta el comando `newgrp docker` en tu terminal y vuelve a ejecutar `./deploy.sh`. (O bien, cierra la sesión SSH y vuelve a entrar).
2. **Sistema Operativo:** La instalación automática de Docker está preparada para **Ubuntu/Debian**. Si usas CentOS, Amazon Linux, Fedora o similar, deberás instalar Docker manualmente antes de correr el script.
3. **Docker Compose V2:** Se ha cambiado `docker-compose` por `docker compose` (sin el guion). Las versiones recientes de Docker integran compose como un plugin, por lo que el comando antiguo suele dar error de *"command not found"* en instalaciones limpias.
