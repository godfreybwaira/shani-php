# Setting up a Virtual Host server in nginx

```
# Begin server block
## Redirect HTTP requests on port 80 to HTTPS on port 443
server {
    listen 80;
    listen [::]:80;

    server_name dev.shani.v2.local www.dev.shani.v2.local;

    return 301 https://$server_name$request_uri;
}
```

```
## HTTPS server block on port 443
server {
    listen 443 ssl;
    listen [::]:443 ssl;

    server_name dev.shani.v2.local www.dev.shani.v2.local;

    root /var/www/html/dev/shani/v2;
    index index.php;

    # Include the SSL certificate and key
    include snippets/self-signed.conf;
    include snippets/ssl-params.conf;

    # This rewrite rule applies to every request not starting with /index.php
      rewrite ^ /index.php?$query_string last;

    # Process PHP files using PHP-FPM
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        # Depending on your PHP-FPM version, adjust the socket path:
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        # Alternatively:
        # fastcgi_pass 127.0.0.1:9000;
    }

    # Deny access to .ht* files (if any exist)
    location ~ /\.ht {
        deny all;
    }
}
# End server block
```

## Create a file named self-signed.conf in /nginx/snippets and add the following content
```
ssl_certificate /var/www/html/dev/shani/v2/config/ssl/server.crt;
ssl_certificate_key /var/www/html/dev/shani/v2/config/ssl/server.key;

```
## Create a file named ssl-params.conf in /nginx/snippets and add the following content
```

ssl_protocols TLSv1.2 TLSv1.3;
ssl_prefer_server_ciphers on;
ssl_ciphers HIGH:!aNULL:!MD5;
ssl_session_cache shared:SSL:10m;
ssl_session_timeout 10m;

```