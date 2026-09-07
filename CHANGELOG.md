# Changelog

Todos los cambios importantes de este proyecto se documentan en este archivo.

El formato está basado en **Keep a Changelog** y este proyecto sigue **Semantic Versioning**.

---

## [Unreleased]

## [1.0.0] - 2026-09-07

Primera versión estable y distribuible de DWW Fingerprinting.

### License

- Publicado bajo GPL-2.0-or-later, conservando Digital Web Works la autoría y el copyright.

### Fixed

- Preservado el contexto Multi-Asset completo durante la generación de PDF.
- Eliminada la variación temporal entre payload, HMAC y chunks.
- Consumo atómico del límite de descargas.
- Normalizados los nombres de metadatos Multi-Asset en EPUB.
- Limpieza de archivos parciales cuando falla el procesamiento o el registro.

### Security

- Clave de integridad persistente e independiente de los salts de WordPress.
- Compatibilidad de verificación con documentos HMAC legacy.
- Almacenamiento privado configurable fuera de la raíz pública.
- Límites defensivos de tamaño, estructura y compresión durante la verificación.
- Logging detallado desactivado por defecto.

### Changed

- Generación idempotente con bloqueo por fingerprint y restricción única.
- Migración de base de datos 0.6.0 no destructiva ante duplicados históricos.
- Suite automatizada de ida y vuelta para los ocho formatos soportados.
- Validación y empaquetado de releases reforzados.

## [0.9.4] - 2026-07-16

### Added

#### Dashboard profesional

- Nuevo Dashboard completamente rediseñado.
- Widgets modulares y reutilizables.
- Información operativa centralizada.
- Indicadores visuales del estado del sistema.
- Acciones rápidas para las operaciones habituales.

#### Administración

- Nueva experiencia de usuario unificada en todo el panel.
- Rediseño de Fingerprints.
- Rediseño del detalle de fingerprints.
- Rediseño del verificador documental.
- Rediseño de DWW Doctor.
- Rediseño de la gestión de la REST API.
- Rediseño de la configuración de productos WooCommerce.

#### Producto

- Gestión visual de activos maestros.
- Mejor integración con WooCommerce.
- Simplificación del flujo de configuración.
- Mejor soporte Multi-Asset.

### Changed

- Unificación completa del lenguaje visual del plugin.
- Centralización del CSS administrativo.
- Eliminación de estilos inline.
- Refactorización de las páginas administrativas.
- Mejora del rendimiento del Dashboard.
- Simplificación del código de la interfaz administrativa.
- Mejora de la mantenibilidad del panel.

### Improved

- Experiencia de usuario en toda la administración.
- Navegación entre pantallas.
- Consistencia visual.
- Presentación de información.
- Responsive del panel administrativo.
- Accesibilidad general de la interfaz.

### QA

- Validación completa del flujo documental.
- Validación del Dashboard.
- Validación de DWW Doctor.
- Validación de la REST API.
- Validación del sistema Multi-Asset.
- Validación de la generación documental.
- Validación del sistema de tokens.
- Validación del motor de verificación.
- Validación de la integración completa con WooCommerce.

### Fixed

- Correcciones menores detectadas durante el proceso de QA.
- Ajustes de estabilidad del Dashboard.
- Correcciones de compatibilidad entre componentes administrativos.
- Optimización de la carga de recursos.

### Status

- Proyecto declarado **Feature Complete (Beta)**.
- Inicio de la fase previa al sistema de licencias.

---

## [0.9.3] - 2026-07-05

### Added

#### DWW Doctor

- Sistema modular de Health Checks.
- `Health_Check_Manager`.
- Registro automático de checks.
- Puntuación global del sistema.
- Clasificación por estados.
- Diagnóstico centralizado.
- Reparaciones automáticas.
- Página administrativa **DWW Doctor**.

#### REST API

- Infraestructura REST desacoplada.
- `REST_API_Manager`.
- `REST_API_Registry`.
- `REST_Endpoint_Abstract`.
- Endpoint `GET /health`.
- Endpoint `GET /stats`.
- Endpoint `GET /fingerprint/{fingerprint_id}`.
- Endpoint `POST /verify`.
- Adaptador `REST_Verification_Response`.

#### Autenticación

- Autenticación mediante API Key.
- Soporte para cabecera `X-DWW-API-Key`.
- Soporte para `Authorization: Bearer`.
- Página de administración de la REST API.
- Generación y regeneración de API Keys.

#### Extensibilidad

- Hooks públicos para el motor documental.
- Hooks públicos para la REST API.
- Filtros para personalización de respuestas REST.

### Changed

- Separación completa entre Core y REST API.
- Arquitectura del sistema de diagnóstico desacoplada del núcleo.
- Compatibilidad de verificación con documentos SHA256 legacy.
- Compatibilidad con SHA256-HMAC.
- Mejor reutilización del motor de verificación desde interfaces externas.
- Mejora de la arquitectura para futuras integraciones.

### Security

- Autenticación mediante API Key.
- Compatibilidad con autenticación Bearer.
- Validación centralizada de permisos REST.
- Compatibilidad segura con documentos legacy.
- Refuerzo del proceso de verificación documental.

### Fixed

- Correcciones durante la reconstrucción del Payload.
- Correcciones de compatibilidad entre versiones del Payload.
- Correcciones durante la verificación mediante REST API.
- Mejor clasificación de estados de verificación.
- Correcciones menores durante el proceso de hardening.

---

## [0.9.2] - 2026-07-02

### Added

- Soporte completo para OpenDocument (ODT, ODS y ODP).
- Soporte completo para verificación de OpenDocument.
- Motor de extracción documental unificado (`Fingerprint_Extractor`).
- Motor de verificación (`Fingerprint_Verifier`).
- Sistema de integridad documental (`Fingerprint_Integrity`).
- Trust Score para auditoría documental.
- Informe reutilizable de verificación (`Verification_Report`).
- Payload Hash integrado en el proceso de fingerprinting.
- Clase `Fingerprint_Payload`.
- Validación previa de archivos (`File_Validator`).
- Validación de contenedores ZIP.
- Validación estructural por formato documental.
- Carga XML segura centralizada.
- Protección del almacenamiento (`Storage_Security`).
- Refactorización del procesamiento EPUB.
- Refactorización del procesamiento OpenDocument.
- Refactorización del procesamiento Office Open XML.
- Verificador documental integrado en el panel de administración.
- Auditoría documental basada en evidencias.

### Changed

- Refactorización completa del motor de verificación.
- Separación de responsabilidades entre Verifier, Integrity y Trust Score.
- Separación de la presentación mediante `Verification_Report`.
- Arquitectura basada en payloads verificables.
- Unificación de la carga segura de XML.
- Mejoras de seguridad durante el procesamiento documental.
- Reducción y simplificación de múltiples clases del núcleo.

### Security

- Verificación de Payload Hash.
- Detección de manipulación documental.
- Validación de documentos corruptos.
- Validación de estructuras ZIP.
- Protección frente a XML malformado.
- Refuerzo del almacenamiento interno.

### Fixed

- Mejor detección de documentos manipulados.
- Mejor clasificación de estados de verificación.
- Correcciones durante la verificación de múltiples formatos.
- Correcciones menores de estabilidad durante el hardening.

---

## [0.9.1] - 2026-07-01

### Added

- Soporte para Microsoft Office Open XML.
- Handler DOCX.
- Handler XLSX.
- Handler PPTX.
- Processor común `Office_Open_XML_Processor`.
- Inserción de fingerprints en propiedades personalizadas de Office.
- Soporte para metadatos invisibles en DOCX, XLSX y PPTX.

### Changed

- Ampliado el motor Multi-Handler para soportar la familia Office Open XML.
- Actualizada la interfaz de producto para mostrar DOCX, XLSX y PPTX como formatos disponibles.

### Fixed

- Implementados correctamente `supports()` y `validate()` en handlers Office.

---

## [0.9.0] - 2026-06-30

### Added

- Arquitectura Multi-Asset para WooCommerce.
- Soporte para múltiples activos por producto.
- Clase `Product_Asset`.
- Fingerprints independientes por activo.
- Tokens independientes por activo.
- Contadores de descargas independientes.
- Adaptación completa del panel de administración para múltiples formatos.
- Visualización del formato del activo en Dashboard, listados y detalle.
- Manejo seguro de excepciones durante el proceso de fingerprinting.

### Changed

- Refactorización completa del flujo de generación documental.
- La integración con WooCommerce ahora trabaja sobre activos en lugar de documentos únicos.
- La generación de archivos se basa en handlers independientes por formato.
- Los nombres de los archivos generados pasan a ser aleatorios.
- Eliminadas las descargas nativas de WooCommerce cuando DWW Fingerprinting está activo.
- Eliminado el watermark visible de las entregas PDF.

### Removed

- Arquitectura centrada exclusivamente en PDF.
- Métodos y código legado relacionados con documentos únicos.
- Funciones obsoletas (`get_source_pdf()` y utilidades asociadas).

### Fixed

- Mejor gestión de errores durante la generación de documentos.
- Correcciones en la generación de múltiples activos.
- Mejoras de estabilidad durante el procesamiento por activo.

---

## [0.3.0] - 2026-06-26

### Added

- Dashboard administrativo con estadísticas generales.
- Componentes reutilizables para la interfaz (`Admin_UI`).
- Gestión centralizada de recursos del panel (`Admin_Assets`).
- Página de detalle de fingerprints.
- Navegación integrada entre listado y detalle.
- Indicadores visuales del estado de los tokens mediante badges.
- Búsqueda mejorada de fingerprints.
- Arquitectura modular para el área de administración.

### Changed

- Reorganización completa del panel de administración.
- Separación de responsabilidades entre Dashboard, Fingerprints y componentes comunes.
- Mejora de la experiencia de usuario.
- Simplificación de la navegación.
- Presentación optimizada de fingerprints y tokens.

### Fixed

- Corregida la navegación entre listado y detalle.
- Eliminadas advertencias relacionadas con páginas internas.
- Mejorada la presentación de fingerprints largos.

---

## [0.2.0] - 2026-06-26

### Added

- Sistema de entrega segura mediante tokens.
- Integración completa con WooCommerce.
- Personalización automática de documentos PDF.
- Configuración por producto.
- Descargas protegidas en el área **Mi cuenta**.
- Caducidad configurable.
- Límite de descargas.
- Sistema de migraciones de base de datos.
- `Migration_Manager`.

---

## [0.1.0] - 2026-06-26

### Added

- Primera prueba de concepto.
- Generación de fingerprints.
- Personalización de documentos PDF mediante FPDI.
- Registro de documentos protegidos.


---

## Próxima versión

### 1.0.0

- Motor de licencias.
- Documentación completa.
- Preparación para distribución comercial.
