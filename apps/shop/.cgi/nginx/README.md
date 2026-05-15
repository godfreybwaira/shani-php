## Create a file named self-signed.conf in /nginx/snippets then add the following content
```
ssl_certificate /var/www/html/shani-php/config/ssl/server.crt;
ssl_certificate_key /var/www/html/shani-php/config/ssl/server.key;
```
## Create a file named ssl-params.conf in /nginx/snippets then add the following content
```
ssl_protocols TLSv1.2 TLSv1.3;
ssl_prefer_server_ciphers on;
ssl_ciphers HIGH:!aNULL:!MD5;
ssl_session_cache shared:SSL:10m;
ssl_session_timeout 10m;
```