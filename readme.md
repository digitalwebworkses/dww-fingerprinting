# DWW Fingerprinting

Motor profesional de fingerprinting documental para WordPress y WooCommerce.

DWW Fingerprinting genera copias personalizadas de documentos digitales vendidos mediante WooCommerce, asignando a cada activo un fingerprint único y un payload verificable que permite garantizar la trazabilidad, autenticidad e integridad documental.

Su arquitectura modular permite incorporar nuevos formatos documentales sin modificar el núcleo del sistema.

---

# Características

## Fingerprinting

- Fingerprints únicos por activo.
- Copias personalizadas por compra.
- Asociación documento → pedido → cliente.
- Payload documental unificado.
- Payload Hash para verificación de integridad.
- Registro permanente de trazabilidad.

## Verificación

- Extracción automática de fingerprints.
- Verificación de autenticidad.
- Comparación con Base de Datos.
- Verificación de Payload Hash.
- Detección de manipulación documental.
- Trust Score.
- Informe completo de verificación.

## Entrega segura

- Tokens independientes por activo.
- Caducidad configurable.
- Límite de descargas.
- Revocación de tokens.
- Regeneración de enlaces.
- Descarga protegida.

## Seguridad

- Payload firmado.
- Validación XML segura.
- Validación ZIP.
- Validación estructural del documento.
- Protección del almacenamiento.
- Procesamiento seguro mediante handlers.

## WooCommerce

- Integración completa.
- Configuración por producto.
- Soporte Multi-Asset.
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
- Verificador documental.
- Componentes reutilizables.

## Arquitectura

- Motor basado en handlers.
- Processors independientes.
- Sistema Multi-Asset.
- Payload centralizado.
- Integridad desacoplada.
- Trust Score independiente.
- Migraciones automáticas.
- Arquitectura modular.

---

# Estado del proyecto

**Versión actual:** `0.9.2`

## Formatos soportados

| Formato | Generación | Verificación |
|----------|:----------:|:------------:|
| PDF | ✅ | ✅ |
| EPUB | ✅ | ✅ |
| DOCX | ✅ | ✅ |
| XLSX | ✅ | ✅ |
| PPTX | ✅ | ✅ |
| ODT | ✅ | ✅ |
| ODS | ✅ | ✅ |
| ODP | ✅ | ✅ |

## Estado

**Feature Complete (Pre-Release)**

El núcleo del motor se considera completo y estable.

Actualmente el sistema dispone de:

- Motor de fingerprint documental.
- Soporte para ocho formatos documentales.
- Integración completa con WooCommerce.
- Descarga segura mediante tokens.
- Motor de verificación documental.
- Trust Score.
- Auditoría documental.
- Arquitectura completamente modular.

Las siguientes fases estarán orientadas a la preparación del producto (Health Check, UX, API, CLI y sistema de licenciamiento).

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

Configurar un producto indicando los activos documentales que serán protegidos.

---

# Pipeline

```text
Compra

    │

    ▼

WooCommerce

    │

    ▼

WooCommerce Integration

    │

    ▼

Fingerprint Manager

    │

    ▼

Handler

    │

    ▼

Processor

    │

    ▼

Payload

    │

    ▼

Fingerprint

    │

    ▼

Payload Hash

    │

    ▼

Base de Datos

    │

    ▼

Token

    │

    ▼

Entrega Segura

    │

    ▼

Verificación

    │

    ▼

Trust Score
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
        ▼
Handlers
        │
        ▼
Processors
        │
        ▼
Generated Documents
        │
        ▼
Fingerprint DB
        │
        ▼
Download Tokens
        │
        ▼
Verification Engine
        │
        ├───────────────┐
        ▼               ▼
Fingerprint Integrity  Trust Score
        │
        ▼
Verification Report
```

---

# Componentes principales

- Fingerprint Manager
- Fingerprint Generator
- Fingerprint Payload
- Fingerprint Extractor
- Fingerprint Verifier
- Fingerprint Integrity
- Trust Score
- Product Asset
- Product Assets
- Download Handler
- Download Token DB
- Fingerprint DB
- File Validator
- Storage Security
- Logger
- WooCommerce Integration
- Migration Manager
- Dashboard
- Verification Report

---

# Estado del desarrollo

| Fase | Estado |
|------|:------:|
| Core Engine | ✅ |
| Multi-Asset | ✅ |
| Secure Delivery | ✅ |
| Verification Engine | ✅ |
| Hardening | ✅ |
| Product Readiness | ⏳ |
| Licensing Engine | ⏳ |

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