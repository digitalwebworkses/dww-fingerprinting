# DWW Fingerprinting

Motor de fingerprinting documental para WordPress y WooCommerce.

DWW Fingerprinting genera copias personalizadas de documentos digitales vendidos mediante WooCommerce, asignando a cada archivo un fingerprint único que permite su trazabilidad completa.

El sistema está diseñado mediante una arquitectura extensible basada en handlers, permitiendo incorporar nuevos formatos de documento sin modificar el núcleo del motor.

---

# Características

## Fingerprinting

- Fingerprints únicos por activo.
- Copias personalizadas por compra.
- Asociación documento → pedido → cliente.
- Registro permanente de trazabilidad.

## Entrega segura

- Tokens independientes por activo.
- Caducidad configurable.
- Límite de descargas.
- Revocación de tokens.
- Regeneración de enlaces.
- Descarga protegida.

## WooCommerce

- Integración completa.
- Configuración por producto.
- Soporte para múltiples activos por producto.
- Descarga integrada en "Mi cuenta".
- Flujo completamente automático.

## Administración

- Dashboard.
- Estadísticas.
- Listado de fingerprints.
- Búsqueda avanzada.
- Vista de detalle.
- Gestión de tokens.
- Historial de actividad.
- Componentes reutilizables.

## Arquitectura

- Motor basado en handlers.
- Sistema Multi-Asset.
- Migraciones automáticas.
- Arquitectura modular.
- Preparado para nuevos formatos documentales.

---

# Formatos soportados

## Actualmente

- ✅ PDF
- ✅ EPUB

## Próximamente

- 🚧 DOCX
- 🚧 XLSX
- 🚧 PPTX
- 📋 ODT
- 📋 ODS
- 📋 ODP

---

# Estado del proyecto

**Versión actual:** `0.9.1`

Formatos soportados:

- PDF
- EPUB
- DOCX
- XLSX
- PPTX

Estado:

**Beta avanzada.**

El núcleo del motor se encuentra completamente operativo y estable.

Actualmente el sistema soporta múltiples activos por producto, fingerprints independientes, tokens independientes y trazabilidad completa de la entrega documental.

Las siguientes versiones estarán orientadas a ampliar el número de formatos soportados y endurecer los mecanismos de protección documental.

---

# Requisitos

- WordPress
- WooCommerce
- PHP 8.1 o superior
- Composer

---

# Instalación

```bash
composer install
```

Activar el plugin desde WordPress.

Configurar un producto indicando los activos que serán protegidos.

---

# Flujo de funcionamiento

```text
WooCommerce

        │

        ▼

Compra realizada

        │

        ▼

Motor de Fingerprinting

        │

        ▼

Handler del formato

        │

        ▼

Generación del fingerprint

        │

        ▼

Registro en Base de Datos

        │

        ▼

Creación del token

        │

        ▼

Entrega segura

        │

        ▼

Auditoría
```

---

# Arquitectura

```text
WooCommerce
        │
        ▼
WooCommerce Integration
        │
        ▼
Fingerprint Manager
        │
        ├───────────────┐
        ▼               ▼
 PDF Handler      EPUB Handler
        │               │
        ▼               ▼
 Processor       Processor
        │
        ▼
Storage
        │
        ▼
Download Tokens
        │
        ▼
Secure Delivery
```

---

# Principales componentes

- Fingerprint Manager
- Fingerprint Generator
- Product Asset
- Download Handler
- Download Token DB
- Fingerprint DB
- Logger
- WooCommerce Integration
- Dashboard
- Fingerprints Administration
- Migration Manager

---

# Roadmap

Consultar:

**ROADMAP.md**

---

# Historial de cambios

Consultar:

**CHANGELOG.md**

---

# Licencia

Pendiente.