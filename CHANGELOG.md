# Changelog

Todas las modificaciones importantes de este proyecto se documentan en este archivo.

El formato está basado en **Keep a Changelog** y el proyecto sigue **Semantic Versioning**.

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
- Mejora de la experiencia de usuario del panel.
- Simplificación de la navegación eliminando páginas ocultas.
- Presentación optimizada de documentos y tokens.

### Fixed

- Corregida la navegación entre listado y detalle.
- Eliminadas advertencias relacionadas con páginas internas del administrador.
- Mejorada la presentación de nombres de archivo y fingerprints largos.

---

## [0.2.0] - 2026-06-26

### Added

- Sistema de entrega segura mediante tokens.
- Integración completa con WooCommerce.
- Personalización automática de documentos PDF.
- Configuración por producto.
- Página **Mi cuenta** con documentos protegidos.
- Descargas limitadas por número de usos.
- Caducidad configurable de enlaces.
- Sistema de migraciones de base de datos.
- Primer gestor de migraciones (`Migration_Manager`).

---

## [0.1.0]

### Added

- Primera prueba de concepto.
- Generación de fingerprints.
- Personalización de PDF mediante FPDI.
- Registro de documentos protegidos.