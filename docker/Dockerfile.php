FROM php:8.2-fpm-alpine

# Install necessary packages
RUN apk add --no-cache \
    supervisor \
    bash

# Create log directory
RUN mkdir -p /var/log/supervisor

# Copy startup script
COPY docker/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

# Create data directory with proper permissions
RUN mkdir -p /usr/share/nginx/html/api/data && \
    chown -R www-data:www-data /usr/share/nginx/html/api/data

# Use custom startup script
CMD ["/usr/local/bin/startup.sh"]