# LinuxServer nginx image includes s6 and PUID/PGID mapping
FROM lscr.io/linuxserver/nginx:latest

# Install sqlite3 + PHP (Alpine)
RUN apk add --no-cache \
    sqlite \
    php82 \
    php82-fpm \
    php82-pdo \
    php82-pdo_sqlite \
    php82-sqlite3 \
    php82-json \
    php82-session

# Custom site content and init scripts
COPY root/ /
RUN chmod +x /custom-cont-init.d/10-init /custom-cont-init.d/20-db-init /etc/services.d/php-fpm/run

EXPOSE 80
