# Crear y configurar el proyecto Java


## Instrucciones

No requiere Composer ni MariaDB para probar Java. Desde la raíz:
```powershell
java -version
mvnw -version
cd backend
mvnw compile
mvnw spring-boot:run
```
En la consola de Java busca la contraseña temporal generada por Spring Security. En otra terminal:

```powershell
curl.exe -i -u user:contraseñatemportal http://127.0.0.1:8081/api/comprobacion
```

Introduce la contraseña temporal en la seccion demostrada. No es una cuenta de PetPlan. Resultado esperado: HTTP 200 y JSON de comprobación. Sin credenciales, una petición con `Accept: application/json` debe rechazarse.
