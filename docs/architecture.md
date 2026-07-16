# Arquitectura

Documento dirigido a desarrolladores.
Este documento describe las decisiones de diseño y la arquitectura general del proyecto. Para información sobre instalación o uso, consultar el resto de la documentación disponible en docs/.

## Introducción

DWW Fingerprinting ha sido diseñado como una plataforma modular de fingerprinting documental para WordPress y WooCommerce.

Su objetivo es proporcionar un sistema capaz de generar documentos personalizados, mantener la trazabilidad completa de cada copia distribuida y permitir su verificación posterior, independientemente del formato documental utilizado.

Desde el inicio del proyecto se tomó la decisión de evitar arquitecturas específicas para un único formato (como PDF) y construir un motor completamente extensible mediante componentes desacoplados.

---

# Filosofía

La arquitectura se basa en cinco principios fundamentales:

- Responsabilidad única.
- Bajo acoplamiento.
- Alta cohesión.
- Extensibilidad.
- Reutilización.

Cada componente del sistema realiza una única tarea y se comunica con el resto mediante interfaces claramente definidas.

Gracias a este enfoque, la incorporación de nuevos formatos documentales no requiere modificar el núcleo del sistema.

---

# Arquitectura general

El flujo completo del sistema puede resumirse de la siguiente manera:

```text
WooCommerce

        │

        ▼

Producto

        │

        ▼

Activo maestro

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

Documento personalizado

        │

        ▼

Payload

        │

        ▼

Fingerprint

        │

        ▼

Base de Datos

        │

        ▼

Token

        │

        ▼

Entrega segura

        │

        ▼

Verificación
```

El núcleo del sistema nunca trabaja directamente con un formato documental concreto.

Toda la lógica específica queda delegada en los Handlers y Processors.

---

# Componentes principales

La arquitectura se organiza alrededor de varios subsistemas independientes.

## WooCommerce Integration

Gestiona la integración con WooCommerce.

Sus responsabilidades incluyen:

- configuración de productos;
- generación automática tras la compra;
- integración con pedidos;
- entrega al cliente;
- gestión de activos maestros.

---

## Fingerprint Manager

Es el coordinador principal del sistema.

No modifica documentos directamente.

Su función consiste en:

- localizar el Handler adecuado;
- preparar el contexto;
- coordinar el proceso;
- almacenar el resultado;
- registrar la operación.

Puede considerarse el punto de entrada del motor de fingerprinting.

---

## Handlers

Cada formato documental dispone de un Handler propio.

Un Handler conoce exclusivamente cómo trabajar con un determinado tipo de documento.

Por ejemplo:

- PDF
- EPUB
- DOCX
- XLSX
- PPTX
- ODT
- ODS
- ODP

Todos implementan la misma interfaz, permitiendo que el motor principal permanezca completamente desacoplado de los formatos soportados.

Añadir un nuevo formato consiste únicamente en crear un nuevo Handler y registrarlo.

---

## Processors

Los Processors contienen la lógica específica de modificación documental.

Mientras que un Handler decide si puede trabajar con un documento, el Processor es quien realiza realmente la personalización.

Esta separación evita duplicar código cuando varios formatos comparten una misma tecnología.

Por ejemplo:

- Office Open XML comparte un único Processor para DOCX, XLSX y PPTX.
- OpenDocument comparte otro Processor para ODT, ODS y ODP.

---

# Multi-Asset

Cada producto puede contener múltiples activos maestros.

Cada activo:

- mantiene su propio fingerprint;
- genera su propio documento personalizado;
- dispone de un token independiente;
- controla sus propias descargas.

Esto permite distribuir varios formatos desde un único producto WooCommerce manteniendo una trazabilidad completamente independiente para cada uno.

---

# Payload documental

Cada documento incorpora un Payload interno.

El Payload contiene toda la información necesaria para identificar el documento:

- fingerprint;
- pedido;
- cliente;
- producto;
- formato;
- versión;
- metadatos internos.

El Payload constituye el elemento central del sistema de verificación.

---

# Fingerprint

Cada documento generado recibe un identificador único.

Este identificador permite relacionar el documento con:

- el pedido;
- el comprador;
- el producto;
- el activo maestro;
- el historial de auditoría.

El fingerprint permanece asociado al documento durante todo su ciclo de vida.

---

# Verificación documental

El proceso de verificación es completamente independiente del proceso de generación.

Durante la verificación el sistema:

- extrae el Payload;
- valida su estructura;
- reconstruye la información documental;
- verifica la integridad;
- compara los datos con la Base de Datos;
- calcula el Trust Score;
- genera un informe completo.

Gracias a esta separación, el motor de verificación puede reutilizarse desde la interfaz administrativa, la REST API o futuras integraciones externas.

---

# Entrega segura

La distribución de documentos no depende de WooCommerce Downloads.

Cada activo dispone de un token independiente.

Los tokens permiten controlar:

- fecha de expiración;
- límite de descargas;
- revocación;
- regeneración.

Este sistema permite invalidar un enlace comprometido sin necesidad de volver a generar el documento.

---

# Auditoría

Todas las operaciones relevantes quedan registradas.

Entre otras:

- generación documental;
- creación de tokens;
- regeneración;
- revocación;
- descargas;
- errores;
- verificaciones.

Este historial constituye la base de la trazabilidad documental del sistema.

---

# Administración

La interfaz administrativa está organizada en módulos independientes.

Actualmente incluye:

- Dashboard
- Fingerprints
- Verificación documental
- DWW Doctor
- REST API

Cada pantalla mantiene responsabilidades claramente separadas, favoreciendo el mantenimiento y la evolución del producto.

---

# REST API

La REST API reutiliza completamente el núcleo del sistema.

No implementa lógica específica de fingerprinting.

Simplemente expone los servicios existentes mediante endpoints autenticados.

Esta decisión evita duplicar código y garantiza un comportamiento idéntico entre la interfaz web y las integraciones externas.

---

# Extensibilidad

El sistema ha sido diseñado para crecer mediante nuevos componentes.

Entre otros:

- nuevos formatos documentales;
- nuevos Handlers;
- nuevos Processors;
- nuevos Health Checks;
- nuevos endpoints REST;
- nuevos sistemas de almacenamiento;
- nuevas integraciones.

El objetivo es que la evolución del producto no requiera modificar el núcleo existente.

---

# Añadir un nuevo formato documental

La incorporación de un nuevo formato sigue siempre el mismo proceso:

1. Crear un Handler.
2. Implementar el Processor correspondiente.
3. Registrar el Handler.
4. Definir la extracción del Payload.
5. Incorporar la lógica de verificación.

No es necesario modificar el Fingerprint Manager ni el resto del motor.

---

# Objetivo de la arquitectura

La arquitectura de DWW Fingerprinting persigue un objetivo muy concreto:

Construir una plataforma profesional de fingerprinting documental que permita evolucionar el producto durante años sin necesidad de rediseñar el núcleo del sistema.

Todas las decisiones de diseño adoptadas durante el desarrollo responden a este principio: mantener un sistema modular, desacoplado, reutilizable y preparado para crecer.