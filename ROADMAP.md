# Roadmap

Estado del desarrollo de **DWW Fingerprinting**.

---

# ✅ Fase 1 — Investigación y prueba de concepto

Estado: **Completada**

## Objetivos

- Investigación de tecnologías de fingerprinting.
- Evaluación de FPDI.
- Primera personalización de documentos PDF.
- Validación de la viabilidad del proyecto.

---

# ✅ Fase 2 — Núcleo del motor

Estado: **Completada**

## Objetivos

- Arquitectura base.
- Generador de fingerprints.
- Registro en base de datos.
- Asociación documento ↔ pedido ↔ cliente.

---

# ✅ Fase 3 — Entrega segura

Estado: **Completada**

## Objetivos

- Tokens de descarga.
- Caducidad.
- Límite de descargas.
- Descarga protegida.
- Validación de accesos.

---

# ✅ Fase 4 — Integración WooCommerce

Estado: **Completada**

## Objetivos

- Configuración por producto.
- Automatización del proceso de generación.
- Integración con pedidos.
- Área "Mi Cuenta".
- Flujo completo de compra.

---

# ✅ Fase 5 — Administración

Estado: **Completada**

## Objetivos

- Dashboard.
- Estadísticas.
- Listado de fingerprints.
- Vista de detalle.
- Gestión de tokens.
- Componentes reutilizables.
- Arquitectura modular del panel.

---

# ✅ Fase 6 — Auditoría

Estado: **Completada**

## Objetivos

- Registro de eventos.
- Historial de actividad.
- Trazabilidad.
- Auditoría de descargas.
- Historial de tokens.

---

# ✅ Fase 7 — Arquitectura Multi-Asset

Estado: **Completada**

## Objetivos

- Múltiples activos por producto.
- Arquitectura basada en handlers.
- Product_Asset.
- Fingerprints independientes.
- Tokens independientes.
- Contadores independientes.
- Adaptación completa del panel.
- Compatibilidad con múltiples formatos.

---

# ✅ Fase 8 — Office Open XML

Estado: **Completada**

## Objetivos

- Office_Open_XML_Processor.
- Handler DOCX.
- Handler XLSX.
- Handler PPTX.
- Fingerprints en propiedades personalizadas.
- Pruebas completas en Word, Excel y PowerPoint.

---

# 🚧 Fase 9 — OpenDocument

Pendiente.

## Objetivos

- Open_Document_Processor.
- Handler ODT.
- Handler ODS.
- Handler ODP.

---

# 📋 Fase 10 — Hardening

Pendiente.

## Objetivos

- Fingerprints invisibles.
- Endurecimiento frente a manipulación.
- Verificación documental.
- Detección de alteraciones.
- Auditoría avanzada.
- Mejoras criptográficas.

---

# 📋 Fase 11 — API e Integraciones

Pendiente.

## Objetivos

- REST API.
- Automatización.
- Integraciones externas.
- Hooks públicos.
- SDK.

---

# 📋 Fase 12 — Optimización

Pendiente.

## Objetivos

- Rendimiento.
- Caché.
- Optimización de memoria.
- Cobertura de tests.
- Refactorización final.
- Preparación para la versión 1.0.

---

# 🎯 Objetivo

## Versión 1.0

La versión 1.0 deberá proporcionar un motor de fingerprinting documental estable, extensible y preparado para producción, con soporte para:

- PDF
- EPUB
- DOCX
- XLSX
- PPTX
- ODT
- ODS
- ODP

mediante una arquitectura basada en handlers, con trazabilidad completa, auditoría y entrega segura de documentos.