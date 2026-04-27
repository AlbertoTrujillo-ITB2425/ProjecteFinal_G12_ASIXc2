
## Resumen rápido de comandos

### Postfix (S10)

```bash
# Entrar al contenedor
docker exec -it s10_postfix bash

# Dentro del contenedor:
postfix status                        # Estado del servicio
ss -tlnp | grep -E "25|587"          # Puertos activos
cat /var/spool/mail/root              # Correos recibidos por root
tail -f /var/log/mail.log             # Logs en tiempo real
```

### Snort (S11)

```bash
# Entrar al contenedor
docker exec -it s11_snort bash

# Dentro del contenedor:
ps aux | grep snort                   # Verificar proceso activo
snort -T -c /etc/snort/snort.conf    # Validar configuración
ls /var/log/snort/                    # Ver archivos de log
tail -f /var/log/snort/alert          # Alertas en tiempo real
```

### Desde el host AWS (sin entrar al contenedor)

```bash
docker logs -f s11_snort              # Snort en tiempo real
docker logs s10_postfix               # Logs de Postfix
docker logs --tail 50 s10_postfix     # Últimas 50 líneas de Postfix
```

