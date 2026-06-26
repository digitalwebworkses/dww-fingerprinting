# DWW Fingerprinting

Sistema de trazabilidad documental para WordPress y WooCommerce.

## Descripción

DWW Fingerprinting genera automáticamente una copia personalizada de un documento PDF para cada compra realizada en WooCommerce.

Cada documento queda asociado a un fingerprint único, permitiendo su trazabilidad y facilitando la identificación del origen de posibles filtraciones.

La entrega se realiza mediante enlaces seguros protegidos por token, con caducidad y límite máximo de descargas configurable.

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

## Estado del proyecto

**Versión actual:** `0.2.0`

**Estado:** MVP funcional.

Actualmente el plugin permite proteger documentos PDF vendidos mediante WooCommerce mediante una entrega segura y completamente trazable.

## Requisitos

- WordPress
- WooCommerce
- PHP 8.1 o superior
- Composer
- FPDI

## Instalación

1. Clonar el repositorio.

2. Instalar las dependencias:

```bash
composer install
```

3. Activar el plugin desde el panel de WordPress.

4. Configurar un producto indicando el PDF origen que será personalizado.

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
Registro del Fingerprint
        │
        ▼
Generación del Token Seguro
        │
        ▼
Entrega del Documento
```

## Estructura del proyecto

```text
includes/
│
├── admin/
├── migrations/
│
├── class-download-handler.php
├── class-download-token-db.php
├── class-fingerprint-db.php
├── class-fingerprint-generator.php
├── class-migration-manager.php
├── class-order-downloads.php
├── class-pdf-processor.php
├── class-plugin.php
├── class-product-settings.php
└── class-woocommerce-integration.php
```

## Roadmap

Próximas funcionalidades previstas:

- Dashboard avanzado.
- Estadísticas.
- Gestión de fingerprints.
- Gestión de tokens.
- Revocación de documentos.
- Regeneración de enlaces de descarga.
- Sistema de auditoría.
- API REST.

## Changelog

Consulta el historial completo de cambios en [CHANGELOG.md](CHANGELOG.md).

## Licencia

Pendiente de definir.