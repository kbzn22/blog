#!/bin/bash
# install.sh – Ejecutar DENTRO del contenedor LXC como root.
# Uso: bash /tmp/blog/install.sh
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WEB_ROOT="/var/www/blog"
NGINX_SITE="/etc/nginx/sites-available/blog"
PHP_POOL="/etc/php/8.2/fpm/pool.d/www.conf"

echo "==> [1/6] Instalando paquetes..."
apt-get update -y -q
apt-get install -y -q nginx php8.2-fpm php8.2-pgsql php8.2-mbstring

echo "==> [2/6] Creando directorios..."
mkdir -p "$WEB_ROOT/uploads"

echo "==> [3/6] Copiando archivos del proyecto..."
# Copiar todo excepto install.sh y .gitkeep
rsync -a --exclude='install.sh' --exclude='.gitkeep' \
      "$SCRIPT_DIR/" "$WEB_ROOT/"

# Aseguramos que uploads/ quede vacía de .gitkeep si se copió
rm -f "$WEB_ROOT/uploads/.gitkeep"

echo "==> [4/6] Configurando nginx..."

# Ajustar worker_processes a 1 para ahorrar RAM
sed -i 's/worker_processes\s\+[^;]*/worker_processes 1/' /etc/nginx/nginx.conf

cat > "$NGINX_SITE" << 'NGINX_EOF'
server {
    listen 80;
    server_name _;

    root /var/www/blog;
    index index.php;

    # -----------------------------------------------------------------
    # Bloquear ejecución de PHP en uploads/ (seguridad crítica)
    # Se evalúa ANTES del bloque genérico de PHP gracias al orden regex.
    # -----------------------------------------------------------------
    location ~ ^/uploads/.*\.php$ {
        deny all;
        return 403;
    }

    # Archivos estáticos en uploads/ se sirven normalmente
    location /uploads/ {
        add_header X-Content-Type-Options nosniff;
    }

    # Informe PDF
    location = /informe.pdf {
        # El archivo debe colocarse en /var/www/blog/informe.pdf
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ =404;
    }

    # PHP-FPM
    location ~ \.php$ {
        # Mitigar path-info injection
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        try_files $fastcgi_script_name =404;

        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO       $fastcgi_path_info;
    }

    # Ocultar archivos de configuración y .git
    location ~ /\.(ht|git|env) {
        deny all;
    }
}
NGINX_EOF

# Activar sitio y desactivar el default
ln -sf "$NGINX_SITE" /etc/nginx/sites-enabled/blog
rm -f /etc/nginx/sites-enabled/default

echo "==> [5/6] Configurando php-fpm (pool ondemand, 3 hijos máx.)..."
cat > "$PHP_POOL" << 'FPM_EOF'
[www]
user  = www-data
group = www-data

listen = /run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode  = 0660

; ondemand: no levanta workers en idle, ideal para 128 MB RAM
pm                   = ondemand
pm.max_children      = 3
pm.process_idle_timeout = 10s
pm.max_requests      = 200

; Logs de proceso lento (útil para debug sin coste en prod)
slowlog              = /var/log/php8.2-fpm-slow.log
request_slowlog_timeout = 5s
FPM_EOF

echo "==> [6/6] Ajustando permisos y reiniciando servicios..."
chown -R www-data:www-data "$WEB_ROOT"
chmod 755 "$WEB_ROOT"
chmod 775 "$WEB_ROOT/uploads"
# uploads/ debe ser escribible por www-data pero no listeable
chmod o-r "$WEB_ROOT/uploads"

# Validar configuraciones antes de recargar
nginx -t
php8.2-fpm --test 2>&1 | grep -E "(OK|FAILED)"

systemctl enable nginx php8.2-fpm
systemctl restart php8.2-fpm
systemctl restart nginx

echo ""
echo "============================================"
echo " Instalación completada."
echo " Blog disponible en: http://172.16.90.145/"
echo " Panel admin:        http://172.16.90.145/login.php"
echo ""
echo " PENDIENTE:"
echo "   1. Editá /var/www/blog/config.php con tus datos personales."
echo "   2. Generá tu hash de password (ver README o instrucciones)."
echo "   3. Colocá tu informe.pdf en /var/www/blog/informe.pdf"
echo "============================================"
