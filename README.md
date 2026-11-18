# Jats-uts-tuunich

## Tuunich: tienda e inventario de joyería

Aplicación web en PHP y MySQL para mostrar joyería artesanal e industrial y
operar las funciones básicas de clientes, vendedores y administradores.

### Pantallas incluidas

El directorio `joyeria/public/` contiene las diez páginas requeridas:

- `index.php`: catálogo con búsqueda, categoría, tipo, material y paginación.
- `producto.php`: descripción, material, precio, existencias y cantidad.
- `login.php` y `registro.php`: inicio de sesión y registro de clientes.
- `carrito.php`: carrito de sesión, cantidades, métodos de pago y compra.
- `cuenta.php`: perfil, historial de compras y crédito.
- `venta_vendedor.php`: ventas atendidas y consulta de actividad propia.
- `admin_productos.php`: alta, edición, existencias e inactivación de piezas.
- `admin_usuarios.php`: usuarios, vendedores, límites de crédito y abonos.
- `admin_reportes.php`: reportes diarios y mensuales.

### Arquitectura

Las páginas de `public/` son entradas ligeras. Cargan `app/bootstrap.php` y
delegan cada solicitud a un controlador; las vistas se encuentran en
`app/Views/`, las reglas de negocio en `app/Services/` y todo el SQL de la
aplicación en `app/Models/`. La configuración de la conexión está en
`config/database.php`. El esquema normalizado usa entidades relacionadas para
clientes, vendedores, categorías, métodos de pago, carritos, ventas y crédito;
también guarda movimientos de inventario y abonos para conservar su historial.

Los estilos y scripts están separados en `joyeria/public/assets/` (`estilos.css`,
`validaciones.js`, `carrito.js` y `catalogo.js`). Los métodos
de pago se registran solamente: no hay conexión con bancos ni pasarelas. No se
incluyen envíos, facturación, devoluciones ni pagos a plazos.

### Requisitos

- PHP 8.0 o posterior con `pdo_mysql`, `fileinfo` y sesiones.
- MySQL 8.0.16 o posterior.

### Instalación nueva

1. Importa el esquema. La cuenta de MySQL necesita permiso para crear la base
   de datos:

   ```sh
   mysql -u tu_usuario -p < joyeria/database/schema.sql
   ```

2. Configura `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` en el entorno del
   servidor. `DB_HOST` y `DB_NAME` son opcionales; por defecto se usan
   `127.0.0.1` y `joyeria`. No guardes credenciales en el repositorio.
3. Crea la primera cuenta administradora desde la raíz del proyecto:

   ```sh
   php joyeria/bin/create_admin.php
   ```

   El script solicita el nombre, correo y contraseña sin incluir la contraseña
   en los argumentos del comando.
4. Inicia el servidor de desarrollo:

   ```sh
   php -S 127.0.0.1:8000 -t joyeria/public
   ```

5. Abre <http://127.0.0.1:8000>, inicia sesión como administrador y agrega
   productos. El catálogo estará vacío hasta entonces.

El servidor integrado de PHP es solo para desarrollo. En producción, configura
un servidor web con HTTPS, apunta el document root a `joyeria/public/` y
mantén `app/`, `config/`, `database/` y `bin/` fuera del directorio público.
Las imágenes subidas se guardan en `public/uploads/` con nombres aleatorios y
formatos limitados.

### Actualizar una instalación existente

Haz primero una copia de seguridad de la base de datos. Para una instalación
que todavía usa el esquema inicial, aplica las migraciones en orden, una sola
vez cada una. La primera agrega campos usados por la aplicación anterior; la
segunda normaliza las tablas para la arquitectura MVC:

```sh
mysql -u tu_usuario -p joyeria < joyeria/database/upgrade_001.sql
mysql -u tu_usuario -p joyeria < joyeria/database/upgrade_002_normalized.sql
```

No ejecutes estas migraciones en una instalación nueva: importa
`joyeria/database/schema.sql`. Las migraciones alteran tablas y no son
reversibles automáticamente; pruébalas primero sobre una copia de la base
existente.

### Seguridad e integridad

- Las contraseñas se guardan con `password_hash()` y se comprueban con
  `password_verify()`.
- Las acciones protegidas validan rol y estado de cuenta en el servidor.
- Los formularios de escritura usan token CSRF y consultas preparadas.
- Las ventas bloquean filas de inventario y saldo de crédito durante una
  transacción; detalle, descuento de existencias y actualización del crédito
  se confirman juntos o se revierten juntos.
- Los usuarios y productos con historial no se borran físicamente: se
  desactivan.
- Los abonos de crédito se registran y no pueden superar el saldo pendiente.
- No se capturan ni almacenan datos de tarjetas.

### Próximas mejoras recomendadas

1. Agregar pruebas automatizadas de integración para permisos por rol, límites
   de crédito, existencias concurrentes y reportes.
2. Añadir registro de auditoría para cambios administrativos y abonos.
3. Incorporar paginación real en paneles con muchos registros y exportación
   controlada de reportes.
4. Configurar copias de seguridad de la base de datos, límites de intentos de
   inicio de sesión y política de privacidad/términos publicados.
5. Revisar accesibilidad y diseño con usuarios reales antes de producción.
