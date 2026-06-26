# DWW Fingerprinting

Sistema de trazabilidad documental para WordPress y WooCommerce.

## Descripción

DWW Fingerprinting genera automáticamente una copia personalizada de un documento PDF para cada compra realizada en WooCommerce.

Cada documento queda asociado a un fingerprint único, permitiendo su trazabilidad y facilitando la identificación del origen de posibles filtraciones.

La entrega se realiza mediante enlaces seguros protegidos por token, con caducidad y límite máximo de descargas configurable.

El proyecto está desarrollado con una arquitectura modular, preparada para evolucionar hacia una solución completa de gestión documental y auditoría.

---

## Características

- Integración completa con WooCommerce.
- Configuración individual por producto.
- Selección del PDF origen desde la Biblioteca Multimedia de WordPress.
- Generación automática de documentos personalizados.
- Personalización del documento con los datos del comprador.
- Generación de fingerprints únicos.
- Registro completo de cada documento generado.
- Descargas protegidas mediante token seguro.
- Caducidad automática de los enlaces.
- Límite configurable de descargas.
- Descarga integrada en el área **Mi cuenta** de WooCommerce.
- Sistema de migraciones de base de datos.
- Panel de administración propio.
- Dashboard con estadísticas generales.
- Listado de fingerprints con búsqueda.
- Vista detallada de cada documento protegido.
- Indicadores visuales del estado de los tokens.
- Arquitectura preparada para futuras ampliaciones.

---

## Estado del proyecto

**Versión actual:** `0.3.0`

**Estado:** MVP funcional.

### Fases completadas

- ✅ Fase 1 — Investigación técnica PDF.
- ✅ Fase 2 — Generación de fingerprints.
- ✅ Fase 3 — Sistema de tokens de descarga.
- ✅ Fase 4 — Integración con WooCommerce.
- ✅ Fase 5 — Panel de administración.

Actualmente el plugin permite vender documentos PDF mediante WooCommerce con una entrega completamente personalizada, segura y trazable.

---

## Requisitos

- WordPress
- WooCommerce
- PHP 8.1 o superior
- Composer
- FPDI

---

## Instalación

1. Clonar el repositorio.

2. Instalar las dependencias:

```bash
composer install
```

3. Activar el plugin desde el panel de WordPress.

4. Configurar un producto indicando el PDF origen que será personalizado.

---

## Arquitectura

```text
WooCommerce
        │
        ▼
Compra realizada
        │
        ▼
Generación del PDF personalizado
        │
        ▼
Inserción del Fingerprint
        │
        ▼
Registro en Base de Datos
        │
        ▼
Generación del Token Seguro
        │
        ▼
Entrega del Documento
        │
        ▼
Descarga controlada
```

---

## Estructura del proyecto

```text
includes/
│
├── admin/
│   ├── class-admin-assets.php
│   ├── class-admin-menu.php
│   ├── class-admin-ui.php
│   ├── class-dashboard-page.php
│   ├── class-fingerprint-detail-page.php
│   └── class-fingerprints-page.php
│
├── migrations/
│   └── class-migration-020.php
│
├── class-download-handler.php
├── class-download-token-db.php
├── class-fingerprint-db.php
├── class-fingerprint-generator.php
├── class-installer.php
├── class-logger.php
├── class-migration-manager.php
├── class-order-downloads.php
├── class-pdf-processor.php
├── class-plugin.php
├── class-product-settings.php
├── class-test-runner.php
└── class-woocommerce-integration.php
```

---

## Roadmap

### Fase 6 — Gestión documental

Objetivos previstos:

- Descarga directa desde el panel de administración.
- Regeneración de tokens.
- Revocación de enlaces.
- Historial de acciones.
- Auditoría de documentos.
- Acciones masivas.
- Mejoras de experiencia de usuario.

### Fases posteriores

- API REST.
- Exportación de registros.
- Integración con almacenamiento externo.
- Estadísticas avanzadas.
- Automatización de auditorías.

---

## Changelog

Consulta el historial completo de cambios en `CHANGELOG.md`.

---

## Licencia

Pendiente de definir.