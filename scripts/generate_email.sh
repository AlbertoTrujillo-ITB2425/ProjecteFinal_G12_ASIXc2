#!/bin/bash
# Enviar un correo de prueba desde el contenedor Postfix

CONTAINER="s10_postfix"

echo "Enviando correo de prueba..."

# Usar mailx o sendmail dentro del contenedor
docker exec $CONTAINER bash -c '
echo "Este es un correo de prueba generado automáticamente por el SOC System." | mail -s "[SOC ALERT] Prueba de Sistema" root@localhost
'

echo "Correo enviado a root@localhost en el contenedor Postfix."
