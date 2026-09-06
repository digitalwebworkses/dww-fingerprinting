# DWW Fingerprinting

![Version](https://img.shields.io/badge/version-0.9.4-blue) ![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4) ![WordPress](https://img.shields.io/badge/WordPress-6.8+-21759B) ![WooCommerce](https://img.shields.io/badge/WooCommerce-Compatible-96588A) ![License](https://img.shields.io/badge/license-Commercial-red) ![Status](https://img.shields.io/badge/status-Release_Candidate-orange)

Motor profesional de trazabilidad documental para WordPress y WooCommerce.

DWW Fingerprinting genera copias personalizadas de documentos digitales vendidos mediante WooCommerce, asignando a cada archivo un fingerprint único y un payload verificable que permite garantizar su autenticidad, integridad y trazabilidad.

Diseñado para academias online, editoriales y autores, automatiza completamente el ciclo de vida del documento: generación, entrega segura, auditoría y verificación.

---

# Casos de uso

DWW Fingerprinting está orientado a cualquier proyecto que necesite distribuir documentación digital de forma segura.

Entre otros:

- Academias online.
- Editoriales.
- Autores independientes.
- Venta de ebooks.
- Venta de documentación privada.
- Formación corporativa.
- Distribución de documentación técnica.
- Entrega segura de contenidos digitales.

---

# Características

## Fingerprinting documental

- Fingerprints únicos por documento.
- Copias personalizadas por compra.
- Asociación documento → pedido → cliente.
- Payload documental firmado.
- Payload Hash para verificación de integridad.
- Registro permanente de trazabilidad.

## Verificación

- Extracción automática de fingerprints.
- Verificación de autenticidad.
- Comparación con Base de Datos.
- Verificación del Payload Hash.
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
- Procesamiento seguro de documentos.

## WooCommerce

- Integración completa.
- Configuración por producto.
- Soporte Multi-Asset.
- Descarga integrada en "Mi cuenta".
- Flujo completamente automático.

## Administración

- Dashboard operativo.
- Gestión de fingerprints.
- Gestión de tokens.
- Historial de actividad.
- Verificación documental.
- DWW Doctor.
- Health Check.
- Reparaciones automáticas.
- REST API integrada.

## Arquitectura

- Arquitectura modular.
- Sistema basado en Handlers.
- Processors independientes.
- Sistema Multi-Asset.
- REST API.
- Componentes desacoplados.

Más información en:

- docs/architecture.md

---

# Estado del proyecto

**Versión actual:** `0.9.4`

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

**Feature Complete (Beta)**

El núcleo del motor se considera completo y estable.

Actualmente el sistema dispone de:

- Motor de fingerprint documental.
- Soporte para ocho formatos documentales.
- Integración completa con WooCommerce.
- Descarga segura mediante tokens.
- Motor de verificación documental.
- Trust Score.
- Auditoría documental.
- DWW Doctor.
- Health Check.
- Reparaciones automáticas.
- REST API completa.
- Autenticación mediante API Key.
- Arquitectura completamente modular.

Las siguientes fases estarán orientadas a la finalización del producto comercial mediante la incorporación del sistema de licencias, SDK, Webhooks y herramientas adicionales para integraciones externas.

---

# REST API

La API REST permite integrar DWW Fingerprinting con aplicaciones externas sin necesidad de acceder a la interfaz de WordPress.

## Endpoints disponibles

| Endpoint | Método | Descripción |
|----------|:------:|-------------|
| `/health` | GET | Estado del sistema |
| `/stats` | GET | Estadísticas del motor |
| `/verify` | POST | Verificación documental |
| `/fingerprint/{id}` | GET | Consulta de un fingerprint |

## Autenticación

La API admite dos mecanismos de autenticación:

- `X-DWW-API-Key`
- `Authorization: Bearer <API_KEY>`

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

1. Instalar el plugin.
2. Activarlo desde WordPress.
3. Asociar uno o varios activos maestros a los productos de WooCommerce.
4. El sistema gestionará automáticamente la generación, entrega y trazabilidad de los documentos.

---

# Flujo de trabajo

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
        ├───────────────┐
        ▼               ▼

Doctor          REST API

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
        ├───────────────┬───────────────┬───────────────┐
        ▼               ▼               ▼               ▼
Fingerprint Integrity  Trust Score   Doctor       REST API
        │
        ▼
Verification Report
```

---

# Estado del desarrollo

| Fase | Estado |
|------|:------:|
| Core Engine | ✅ |
| Multi-Asset | ✅ |
| Secure Delivery | ✅ |
| Verification Engine | ✅ |
| Hardening | ✅ |
| Product Readiness | ✅ |
| Licensing | ⏳ |

---

# Documentación

- docs/installation.md
- docs/user-guide.md
- docs/developer-guide.md
- docs/architecture.md
- docs/api-reference.md
- docs/roadmap.md

---

# Historial de cambios

Consultar:

`CHANGELOG.md`

---

# Licencia

Licenciamiento comercial pendiente.

© Digital Web Works
