# SMTP Tunnel Configuration - Troubleshooting

## 1. Problema

El registro de usuarios no funcionaba porque Laravel no podia enviar emails de verificacion.

**Causa raiz:** El servidor esta alojado en **DigitalOcean** (Droplet: `prod-faristol-ubuntu-01`, IP: `134.209.224.219`). DigitalOcean bloquea **todos los puertos SMTP salientes** (25, 465, 587) a nivel de infraestructura de red en todos los Droplets para prevenir spam. El bloqueo no es configurable desde el sistema operativo (UFW inactivo, iptables sin reglas).

**Evidencia:**
- `nc smtp.gmail.com 587` → `Connection timed out` / `Network is unreachable`
- `nc smtp.gmail.com 465` → `Connection timed out`
- `nc smtp.gmail.com 25` → `Connection timed out`
- `nc smtp.gmail.com 993` (IMAP) → **Conectado** (no SMTP, no bloqueado)
- `nc google.com 443` (HTTPS) → **Conectado** (trafico normal funciona)

## 2. Solucion

Se creo un **tunel SSH persistente** desde este servidor a un servidor remoto (`mail.forthtrade.com` en `109.123.248.91`) que **no tiene puertos SMTP bloqueados**.

**Flujo del tunel:**
```
Laravel (ios.faristol.net)
  → 127.0.0.1:2525 (local)
    → tunel SSH (puerto 22, sin bloquear)
      → mail.forthtrade.com (109.123.248.91)
        → smtp.gmail.com:587
```

El tunel se ejecuta como **servicio systemd** con reinicio automatico (`Restart=always`), se conecta al arrancar el servidor y se reconecta si se cae.

## 3. Detalles Tecnicos

### Archivos nuevos creados

| Archivo | Funcion |
|---------|---------|
| `/etc/ssh/tunnel_keys/id_ed25519_jmbigi` | Clave privada SSH para conectar al servidor remoto (permisos `600`, propietario `root`) |
| `/etc/systemd/system/smtp-tunnel.service` | Servicio systemd que mantiene el tunel SSH activo |

### Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `.env` | `MAIL_HOST=127.0.0.1`, `MAIL_PORT=2525`, `MAIL_ENCRYPTION=null` (antes `smtp.gmail.com`, `587`, `tls`) |
| `config/mail.php` | Anadidas opciones `verify_peer => false` y `verify_peer_name => false` en el mailer `smtp` |

### Contenido del servicio systemd

```ini
[Unit]
Description=SSH Tunnel for SMTP (Gmail port 587 via mail.forthtrade.com on local 2525)
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
ExecStart=/usr/bin/ssh -i /etc/ssh/tunnel_keys/id_ed25519_jmbigi \
    -o StrictHostKeyChecking=no \
    -o ServerAliveInterval=60 \
    -o ServerAliveCountMax=3 \
    -o ExitOnForwardFailure=yes \
    -N -L 127.0.0.1:2525:smtp.gmail.com:587 \
    root@109.123.248.91
Restart=always
RestartSec=5
User=root

[Install]
WantedBy=multi-user.target
```

### Comandos utiles

```bash
# Ver estado del tunel
sudo systemctl status smtp-tunnel

# Reiniciar el tunel
sudo systemctl restart smtp-tunnel

# Ver logs del tunel
sudo journalctl -u smtp-tunnel -f

# Probar conectividad SMTP a traves del tunel
echo "EHLO test" | nc -w 3 127.0.0.1 2525

# Enviar email de prueba desde Laravel
php artisan tinker --execute="Mail::raw('Test', function(\$m) { \$m->to('tu@email.com')->subject('Test'); });"
```

### Notas importantes

- El puerto **2525** se usa localmente para evitar que Symfony intente STARTTLS automaticamente (lo cual falla porque el certificado de Gmail dice `smtp.gmail.com` pero conectamos a `127.0.0.1`). El tunel SSH ya cifra todo el trafico, por lo que STARTTLS no es necesario.
- La clave privada SSH esta fuera del directorio web y del repositorio git.
- El ticket de soporte a DigitalOcean para desbloqueo de puertos esta en `/tmp/digitalocean_support_ticket.txt`.
