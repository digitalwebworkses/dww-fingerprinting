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
- Permisos de escritura sobre el directorio `wp-content/uploads`.

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

No es necesaria ninguna acción adicional tras actualizar el plugin.

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