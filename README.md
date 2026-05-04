# Problemas

1. Port forwarding con WSL y Windows al abrir la pagina  (`netsh advfirewall firewall add rule name="Puerto 8080" dir=in action=allow protocol=TCP localport=8080`)
1. Creacion de api token con `openssl rand -hex 32`
1. Error al enviar datos al servidor por la API tocken problema permisos de la carpeta volumen docker (USER PHP ERA ROOT) 

```bash
docker compose down
docker compose up -d --force-recreate
docker exec -it --user root php_procesador chmod 777 /var/data
```
