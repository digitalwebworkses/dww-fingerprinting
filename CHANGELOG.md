# Changelog

Todos los cambios importantes de este proyecto se documentan en este archivo.

El formato está basado en **Keep a Changelog** y este proyecto sigue **Semantic Versioning**.

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