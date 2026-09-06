# Instalación

## Introducción

DWW Fingerprinting se instala como cualquier otro plugin de WordPress.

Una vez activado, únicamente será necesario asociar uno o varios activos maestros a los productos de WooCommerce que se deseen proteger.

No requiere configuraciones adicionales para comenzar a funcionar.

---

# Requisitos

Antes de instalar el plugin, asegúrate de que el entorno cumple los siguientes requisitos mínimos:

- WordPress 6.8 o superior.
- WooCommerce.
- PHP 8.1 o superior.
- Composer.
- Permisos para crear el directorio privado de almacenamiento junto a la raíz pública de WordPress.

---

# Instalación

## 1. Instalar el plugin

Sube el paquete ZIP desde el panel de administración de WordPress o copia el directorio del plugin dentro de:

```text
wp-content/plugins/
```

---

## 2. Activar el plugin

Accede a:

**Plugins → Plugins instalados**

y activa **DWW Fingerprinting**.

Durante la activación el sistema creará automáticamente las tablas necesarias para su funcionamiento.

También generará una clave de integridad propia y creará, siempre que los permisos del servidor lo permitan, un almacenamiento no accesible desde la web:

```text
../dww-fingerprinting-private/
```

La ubicación puede fijarse explícitamente en `wp-config.php`:

```php
define('DWW_FP_STORAGE_DIR', '/ruta/privada/dww-fingerprinting');
```

La ruta configurada debe ser absoluta, escribible por PHP y estar fuera de la raíz pública. Si el servidor no permite crearla, el plugin utiliza el almacenamiento legado dentro de uploads y DWW Doctor muestra una advertencia para que el administrador configure una ruta privada.

Los documentos creados antes del cambio conservan su ruta registrada y continúan siendo descargables. Si todavía existe almacenamiento legado dentro de uploads, DWW Doctor lo identifica como público para que pueda migrarse o retirarse de forma controlada; el plugin no mueve automáticamente documentos comerciales existentes.

---

## 3. Configurar un producto

Edita cualquier producto de WooCommerce y activa:

**DWW Fingerprinting**

A continuación:

- añade uno o varios activos maestros;
- selecciona el formato correspondiente;
- guarda el producto.

A partir de ese momento todas las ventas utilizarán el motor de fingerprinting.

---

# Funcionamiento

Una vez configurado un producto, el proceso es completamente automático.

Cuando un cliente realiza una compra:

1. WooCommerce procesa el pedido.
2. DWW Fingerprinting genera una copia personalizada.
3. Se crea un fingerprint único.
4. Se registra la trazabilidad en la Base de Datos.
5. Se genera un token de descarga.
6. El cliente descarga el documento desde su área **Mi cuenta**.

No es necesario ejecutar tareas manuales.

---

# Actualizaciones

Las nuevas versiones del plugin mantienen automáticamente la estructura interna de la Base de Datos mediante el sistema de migraciones integrado.

La migración 0.6.0 añade unicidad a los fingerprints. Si una instalación antigua contiene duplicados, la migración se detiene y DWW Doctor informa de la versión pendiente; no elimina ni combina registros comerciales automáticamente.

La clave de integridad se conserva en la base de datos y debe incluirse en las copias de seguridad. Cambiar los salts de WordPress no invalida los documentos nuevos. Los documentos antiguos sin identificador de clave siguen verificándose mediante el mecanismo de compatibilidad legado.

No es necesaria ninguna acción adicional tras actualizar el plugin.

Para activar logs técnicos de forma temporal puede definirse en `wp-config.php`:

```php
define('DWW_FP_DEBUG', true);
```

El logging detallado está desactivado por defecto para evitar almacenar datos operativos o personales innecesarios en producción.

---

# Desinstalación

La desactivación del plugin no elimina los datos almacenados.

Los fingerprints, registros y configuraciones permanecen disponibles hasta que sean eliminados explícitamente por el administrador.

---

# Resolución de problemas

Si el sistema detecta algún problema de configuración, puede utilizarse la herramienta:

**DWW Fingerprinting → DWW Doctor**

El sistema realizará un diagnóstico completo del entorno y mostrará las posibles incidencias detectadas, así como las reparaciones automáticas disponibles.

---

# Soporte

Para incidencias, consultas técnicas o información comercial, ponte en contacto con Digital Web Works.
