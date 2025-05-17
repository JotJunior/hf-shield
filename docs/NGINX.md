# NGINX configuration

```nginx
server {
    listen 80;
    listen 443 ssl;
    server_name PROJECT_NAME.local;

    ssl_certificate     /path/to/your/certs/server.crt;
    ssl_certificate_key /path/to/your/certs/server.key;

    root /path/to/your/project/public;
    index index.html;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
    gzip_comp_level 6;
    gzip_min_length 1000;

    location / {
         try_files $uri $uri/ /index.html$is_args$args;
    }

    location ~* ^.*\.(jpg|svg|jpeg|gif|png|ico|css|zip|tgz|gz|rar|bz2|doc|xls|exe|pdf|ppt|txt|tar|mid|midi|wav|bmp|rtf|js)$ {
        expires 30d;
        add_header Cache-Control "public, max-age=2592000";
        #try_files $uri /index.html =404; 
        try_files $uri $uri/ /index.html$is_args$args;
    }

    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-XSS-Protection "1; mode=block";
    
    # API paths
    location ~ ^/(v1|v2|oauth|user|web\-auth|settings) {
        proxy_pass http://127.0.0.1:9501;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Swagger paths
    location ~ ^/(docs|swagger|http.json) {
        proxy_pass http://127.0.0.1:9500;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Socket.io paths
    location ^~/socket.io/ {
        # Execute proxy to access real server
        proxy_pass http://127.0.0.1:9502;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }

}
``` 