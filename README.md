# Jats-uts-tuunich

## Tuunich: tienda e inventario de joyería

Aplicación web local en PHP y MariaDB para mostrar joyería artesanal e
industrial y operar las funciones de clientes y administración.

### Pantallas incluidas

El directorio `joyeria/htdocs/` contiene el sitio listo para publicar:

- `index.php`: catálogo con búsqueda, categoría, tipo, material y paginación.
- `product.php`: descripción, material, precio, existencias y cantidad.
- `login.php` y `register.php`: inicio de sesión y registro de clientes.
- `cart.php`: carrito de sesión, cantidades, métodos de pago y compra.
- `account.php`: perfil e historial de compras.
- `admin_products.php`: alta, edición, existencias e inactivación de piezas.
- `admin_users.php`: administración de clientes.
- `admin_reports.php`: reportes diarios y mensuales.

### Arquitectura

Las páginas de `htdocs/` son entradas ligeras. Cargan
`app/bootstrap.php` y delegan cada solicitud a un controlador. La estructura
MVC se organiza en `htdocs/app/Controllers/`, `htdocs/app/Models/` y
`htdocs/app/Views/`; las reglas de negocio están en `htdocs/app/Services/` y
las funciones compartidas en `htdocs/app/Support/`. La configuración de la
conexión está en `htdocs/config/database.php`. La base relaciona clientes, categorías, métodos
de pago, carritos y ventas; también conserva movimientos de inventario y los
registros históricos existentes.

Las vistas PHP de `htdocs/app/Views/` generan el HTML dinámico. Los estilos CSS
y archivos JavaScript están en `htdocs/assets/`, y los esquemas y migraciones
SQL en `database/`. Así se mantiene MVC para el código
PHP y se separan los recursos por tipo. El sistema tiene dos roles:
administrador y cliente. La portada muestra el catálogo y el acceso; los
clientes agregan piezas al carrito y completan una compra simulada. No hay
conexión con bancos ni pasarelas. El administrador puede gestionar productos y
clientes, incluyendo sus imágenes.

### Contacto

- Correo genérico: `contacto@tuunich.example`
- Teléfono genérico: `+52 000 000 0000`

Estos datos son marcadores de ejemplo; reemplázalos por los datos reales antes
de publicar la aplicación.

### Requisitos

- PHP 8.0 o posterior con `pdo_mysql`, `fileinfo` y sesiones.
- MariaDB o MySQL ejecutándose localmente.

### Instalación nueva

1. Inicia el servicio local de MariaDB e instala el esquema:

   ```sh
   sudo service mariadb start
   sudo mariadb -e "CREATE DATABASE IF NOT EXISTS joyeria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS 'tuunich_local'@'127.0.0.1' IDENTIFIED BY ''; GRANT ALL PRIVILEGES ON joyeria.* TO 'tuunich_local'@'127.0.0.1';"
   sudo mariadb joyeria < joyeria/database/schema.sql
   ```

   La configuración local usa `127.0.0.1`, base `joyeria` y el usuario limitado
   `tuunich_local` sin contraseña. Es solo para desarrollo local; no uses esa
   configuración en un servidor público.
2. Si ya tenías una base creada con el esquema anterior, aplica una sola vez
   `joyeria/database/upgrade_003_username.sql`.
3. Crea el administrador local:

   ```sh
   php8.3 joyeria/bin/create_admin.php
   ```

   Usuario: `admin`. Contraseña: `12345678`. Son credenciales de demostración
   local; no despliegues la aplicación con ellas.
4. Inicia el servidor de desarrollo con el PHP que tenga `pdo_mysql` habilitado:

   ```sh
   php8.3 -S 0.0.0.0:8000 -t joyeria/htdocs
   ```

5. Abre <http://127.0.0.1:8000>, inicia sesión como administrador y agrega
   productos con sus fotografías. Los clientes pueden crear una cuenta desde la
   portada. El catálogo estará vacío hasta que se agreguen productos.

Si el puerto `8000` ya está ocupado, detén el servidor anterior con Ctrl+C o usa
otro puerto, por ejemplo `8001`.

El servidor integrado de PHP es solo para desarrollo. Para publicar, usa
`joyeria/htdocs/` como document root. Las carpetas `app/` y `config/` están
dentro de ese directorio para ajustarse al FTP de InfinityFree; sus archivos
`.htaccess` bloquean el acceso desde el navegador. Las imágenes se guardan en
`htdocs/uploads/` con nombres aleatorios y formatos limitados.

### Publicar en InfinityFree

InfinityFree no ejecuta este repositorio Git como despliegue automático. Sube
los archivos con FTP (por ejemplo, FileZilla) y conserva GitHub como repositorio
de código:

1. En el panel de InfinityFree, crea una base MySQL y anota el host, nombre,
   usuario y contraseña que muestra el panel. Abre esa base en phpMyAdmin e
   importa `joyeria/database/schema.sql`.
2. Crea `joyeria/htdocs/config/database.local.php` a partir de
   `joyeria/htdocs/config/database.example.php`. Verifica que el nombre de la base sea
   `if0_43518410_sistema_tuunich` y reemplaza el host, usuario y contraseña por
   los datos de InfinityFree. Este archivo está excluido de Git; no publiques
   ni compartas la contraseña.
3. Por FTP, abre la carpeta `htdocs/` de InfinityFree y sube **todo el
   contenido** de `joyeria/htdocs/` directamente allí. Esto ya incluye
   `app/`, `config/`, `assets/` y `uploads/`, por lo que no necesitas crear
   carpetas fuera de `htdocs/`. Conserva los archivos `.htaccess` de la raíz,
   `app/`, `config/` y `uploads/`. No subas `.git/`, `bin/` ni el archivo SQL;
   importa el esquema directamente desde phpMyAdmin. En FileZilla, habilita la
   visualización y transferencia de archivos ocultos para subir los `.htaccess`.
4. Genera localmente el hash de una contraseña segura (mínimo 10 caracteres):

   ```sh
   php -r 'fwrite(STDERR, "Contraseña: "); echo password_hash(trim(fgets(STDIN)), PASSWORD_DEFAULT), PHP_EOL;'
   ```

   Escribe la contraseña cuando el comando la solicite. En phpMyAdmin, ejecuta
   el siguiente SQL reemplazando el correo y el hash:

   ```sql
   INSERT INTO users (username, name, email, password_hash, `role`, is_active)
   VALUES ('admin', 'Administrador', 'admin@tudominio.com', '<HASH_GENERADO>', 'admin', 1);
   ```

   Entra al dominio y comprueba el catálogo. No uses las credenciales de
   demostración locales.

El alojamiento debe permitir PHP con `pdo_mysql`, MariaDB/MySQL y archivos
`.htaccess`. Las reglas incluidas bloquean el acceso web a `app/`, `config/`
y al archivo auxiliar `_bootstrap.php`.

### Actualizar una instalación existente

Haz primero una copia de seguridad de la base de datos. Para una instalación
que todavía usa el esquema inicial, aplica las migraciones en orden, una sola
vez cada una:

```sh
sudo mariadb joyeria < joyeria/database/upgrade_001.sql
sudo mariadb joyeria < joyeria/database/upgrade_002_normalized.sql
sudo mariadb joyeria < joyeria/database/upgrade_003_username.sql
sudo mariadb joyeria < joyeria/database/upgrade_004_admin_customer_roles.sql
```

No ejecutes estas migraciones en una instalación nueva: importa
`joyeria/database/schema.sql`. Las migraciones alteran tablas y no son
reversibles automáticamente; pruébalas primero sobre una copia de la base
existente. Al actualizar una instalación desplegada, usa `htdocs/` como
document root y mueve las imágenes existentes de `publico/cargas/` a
`htdocs/uploads/`. La aplicación reconoce tanto los valores nuevos
`uploads/...` como los anteriores `cargas/...` guardados en la base de datos.

### Seguridad e integridad

- Las contraseñas se guardan con `password_hash()` y se comprueban con
  `password_verify()`.
- Las acciones protegidas validan rol y estado de cuenta en el servidor.
- Los formularios de escritura usan token CSRF y consultas preparadas.
- Las ventas bloquean filas de inventario durante una transacción; detalle y
  descuento de existencias se confirman juntos o se revierten juntos.
- Los usuarios y productos con historial no se borran físicamente: se
  desactivan.
- El pago con tarjeta es una simulación: acepta el número `1234567891234567`,
  vencimiento `1234` y CVV `123`; el titular debe coincidir con el nombre y
  apellidos registrados en la cuenta. Los datos se validan en la solicitud y
  nunca se guardan; no ingreses datos de una tarjeta real.

### Próximas mejoras recomendadas

1. Agregar pruebas automatizadas de integración para permisos por rol,
   existencias concurrentes y reportes.
2. Añadir registro de auditoría para cambios administrativos.
3. Incorporar paginación real en paneles con muchos registros y exportación
   controlada de reportes.
4. Configurar copias de seguridad de la base de datos, límites de intentos de
   inicio de sesión y política de privacidad/términos publicados.
5. Revisar accesibilidad y diseño con usuarios reales antes de producción.
