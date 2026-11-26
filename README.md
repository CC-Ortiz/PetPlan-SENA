

Frontend Laravel/PHP y backend Spring Boot con Spring Security.

## 1. Preparar el proyecto

Requisitos: PHP 8.2 o superior, Composer, Java 21, Maven y MySQL. Inicia MySQL y crea una base de datos vacía llamada `petplan` (por ejemplo, desde phpMyAdmin). Si ya migraste una etapa anterior con Artisan, puedes reutilizarla. No importes SQL ni uses una base de datos con información real.

Desde la carpeta raíz del proyecto, en PowerShell:

```powershell
composer install
Copy-Item .env.example .env 
```

Si `APP_KEY` está vacía en `.env`, ejecuta `php artisan key:generate` una sola vez. Configura la conexión en `.env` según tu instalación:

```env
APP_ENV=local
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=petplan
DB_USERNAME=root
DB_PASSWORD=
PETPLAN_API_URL=http://127.0.0.1:8080
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Ejecuta:

```powershell
php artisan config:clear
php artisan migrate --seed
```

El comando crea las tablas pendientes y carga las cuentas de prueba, una veterinaria aprobada y el perfil del veterinario. Puedes repetirlo sin duplicar estos registros ni restablecer contraseñas. Si solo deseas crear el esquema sin datos demo, utiliza `php artisan migrate`.

## 2. Iniciar Spring Boot

Abre otra terminal PowerShell desde la raíz del proyecto:

```powershell
cd backend
$env:DB_URL='jdbc:mysql://127.0.0.1:3306/petplan'
$env:DB_USERNAME='TU_USUARIO_MYSQL'
$env:DB_PASSWORD='TU_CONTRASENA_MYSQL'
```

Utiliza los mismos datos de conexión de `.env`.
Para habilitar al administrador de demostración, copia en esta terminal el comando `PETPLAN_ADMIN_CLINICAS` que mostró el seeder. Por ejemplo, en una base recién creada suele ser:

```powershell
$env:PETPLAN_ADMIN_CLINICAS='3:1'
```


```powershell
.\mvnw.cmd spring-boot:run
```

Tomcat escucha en el puerto 8080. Se requiere Maven instalado; el proyecto no incluye Maven Wrapper. Spring Boot valida el esquema sin modificarlo.

## 3. Iniciar Laravel

En la primera terminal, desde la raíz del proyecto:

```powershell
php artisan serve
```

Abre http://127.0.0.1:8000. No se necesita npm ni una clave de integración de la API.

## 4. Cuentas de prueba

Todas tienen la contraseña inicial `Demo1234`.

| Rol | Correo |
|---|---|
| Usuario | `usuario@demo.test` |
| Veterinario | `vet@demo.test` |
| Administrador | `admin@demo.test` |
| Superadministrador | `super@demo.test` |

