# Jats-uts-tuunich

## Tuunich: tienda e inventario de joyería

Aplicación web local en PHP y MariaDB para mostrar joyería artesanal e
industrial y operar las funciones de clientes y administración.

### Pantallas incluidas

El directorio `joyeria/publico/` contiene las páginas públicas:

- `index.php`: catálogo con búsqueda, categoría, tipo, material y paginación.
- `producto.php`: descripción, material, precio, existencias y cantidad.
- `login.php` y `registro.php`: inicio de sesión y registro de clientes.
- `carrito.php`: carrito de sesión, cantidades, métodos de pago y compra.
- `cuenta.php`: perfil e historial de compras.
- `admin_productos.php`: alta, edición, existencias e inactivación de piezas.
- `admin_usuarios.php`: administración de clientes.
- `admin_reportes.php`: reportes diarios y mensuales.

### Arquitectura

Las páginas de `publico/` son entradas ligeras. Cargan
`aplicacion/bootstrap.php` y delegan cada solicitud a un controlador. La
estructura MVC se organiza en `aplicacion/Controladores/`,
`aplicacion/Modelos/` y `aplicacion/Vistas/`; las reglas de negocio están en
`aplicacion/Servicios/` y las funciones compartidas en
`aplicacion/Soporte/`. La configuración de la conexión local está en
`configuracion/database.php`. La base relaciona clientes, categorías, métodos
de pago, carritos y ventas; también conserva movimientos de inventario y los
registros históricos existentes.

Las vistas PHP de `aplicacion/Vistas/` generan el HTML dinámico. Los estilos CSS
están en `publico/estilos/`, los archivos JavaScript en `publico/scripts/` y los
esquemas y migraciones SQL en `base_datos/`. Así se mantiene MVC para el código
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
   sudo mariadb < joyeria/base_datos/schema.sql
   ```

   La configuración local usa `127.0.0.1`, base `joyeria` y el usuario limitado
   `tuunich_local` sin contraseña. Es solo para desarrollo local; no uses esa
   configuración en un servidor público.
2. Si ya tenías una base creada con el esquema anterior, aplica una sola vez
   `joyeria/base_datos/upgrade_003_nombre_usuario.sql`.
3. Crea el administrador local:

   ```sh
   php8.3 joyeria/herramientas/create_admin.php
   ```

   Usuario: `admin`. Contraseña: `12345678`. Son credenciales de demostración
   local; no despliegues la aplicación con ellas.
4. Inicia el servidor de desarrollo con el PHP que tenga `pdo_mysql` habilitado:

   ```sh
   php8.3 -S 0.0.0.0:8000 -t joyeria/publico
   ```

5. Abre <http://127.0.0.1:8000>, inicia sesión como administrador y agrega
   productos con sus fotografías. Los clientes pueden crear una cuenta desde la
   portada. El catálogo estará vacío hasta que se agreguen productos.

Si el puerto `8000` ya está ocupado, detén el servidor anterior con Ctrl+C o usa
otro puerto, por ejemplo `8001`.

El servidor integrado de PHP es solo para desarrollo. En producción, configura
un servidor web con HTTPS, apunta el document root a `joyeria/publico/` y
mantén `aplicacion/`, `configuracion/`, `base_datos/` y `herramientas/` fuera
del directorio público. Las imágenes subidas se guardan en `publico/cargas/`
con nombres aleatorios y formatos limitados.

### Actualizar una instalación existente

Haz primero una copia de seguridad de la base de datos. Para una instalación
que todavía usa el esquema inicial, aplica las migraciones en orden, una sola
vez cada una:

```sh
sudo mariadb joyeria < joyeria/base_datos/upgrade_001.sql
sudo mariadb joyeria < joyeria/base_datos/upgrade_002_normalized.sql
sudo mariadb joyeria < joyeria/base_datos/upgrade_003_nombre_usuario.sql
sudo mariadb joyeria < joyeria/base_datos/upgrade_004_solo_admin_cliente.sql
```

No ejecutes estas migraciones en una instalación nueva: importa
`joyeria/base_datos/schema.sql`. Las migraciones alteran tablas y no son
reversibles automáticamente; pruébalas primero sobre una copia de la base
existente. Al actualizar una instalación desplegada, cambia también el
document root a `publico/` y mueve las imágenes existentes de `public/uploads/`
a `publico/cargas/`; la aplicación reconoce los valores `uploads/...` que ya
estén guardados en la base de datos.

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
