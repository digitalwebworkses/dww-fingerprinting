# Guía del desarrollador

## Introducción

Este documento está dirigido a desarrolladores que deseen comprender, mantener o ampliar DWW Fingerprinting.

No describe la arquitectura general del sistema (ver `architecture.md`), sino las convenciones de desarrollo utilizadas durante el proyecto y las recomendaciones para mantener la coherencia del código.

---

# Filosofía

El proyecto se basa en varios principios fundamentales:

- Responsabilidad única.
- Componentes desacoplados.
- Reutilización.
- Extensibilidad.
- Código legible antes que código complejo.

Siempre que sea posible, una nueva funcionalidad debe añadirse creando un nuevo componente y evitando modificar el núcleo existente.

---

# Regla del proyecto

Existe una única regla que prevalece sobre cualquier otra decisión técnica durante el desarrollo de DWW Fingerprinting:

> **Cuando exista una duda entre implementar una solución rápida o una solución que preserve la arquitectura del proyecto, siempre deberá elegirse la segunda.**

La consistencia de la arquitectura tiene prioridad sobre la velocidad de desarrollo.

Las nuevas funcionalidades deben adaptarse al diseño existente, evitando introducir excepciones, dependencias innecesarias o soluciones específicas que comprometan la mantenibilidad del sistema a largo plazo.

---

# Organización del proyecto

La estructura principal del proyecto es la siguiente:

```text
assets/
docs/
includes/
languages/
tests/
vendor/
```

La mayor parte de la lógica reside dentro del directorio:

```text
includes/
```

---

# Convenciones

## Namespaces

Todas las clases pertenecen al namespace:

```php
DWW_Fingerprinting
```

---

## Clases

Cada clase debe tener una única responsabilidad.

Evitar clases excesivamente grandes o con responsabilidades mixtas.

Cuando una clase comienza a crecer demasiado, la funcionalidad deberá extraerse a componentes auxiliares.

---

## Métodos

Siempre que sea posible:

- métodos pequeños;
- nombres descriptivos;
- una única responsabilidad.

Evitar métodos excesivamente largos.

---

## Visibilidad

Utilizar siempre el menor nivel de visibilidad posible.

Por norma general:

- `private`
- `protected`
- `public`

---

# Arquitectura modular

El sistema está dividido en varios subsistemas independientes.

Entre ellos:

- Fingerprinting.
- WooCommerce.
- Descargas.
- REST API.
- Dashboard.
- Doctor.
- Verificación.
- Auditoría.

Siempre que sea posible, los módulos deben permanecer desacoplados entre sí.

---

# Fingerprinting

El núcleo documental gira alrededor de:

- Fingerprint Manager.
- Handlers.
- Processors.

Los Handlers nunca deberían contener lógica de negocio compleja.

Su función consiste únicamente en coordinar el procesamiento de un formato concreto.

Toda la manipulación documental pertenece al Processor correspondiente.

---

# Multi-Asset

Cada activo maestro debe tratarse como una entidad independiente.

Nunca asumir que un producto contiene un único documento.

Todo nuevo desarrollo deberá mantener la compatibilidad con la arquitectura Multi-Asset.

---

# Payload

El Payload constituye el contrato interno del sistema.

Toda modificación del Payload deberá mantener la compatibilidad con versiones anteriores siempre que resulte posible.

Cambios incompatibles deberán implicar una nueva versión del Payload.

---

# Base de Datos

Las modificaciones estructurales nunca deben realizarse directamente.

Toda modificación debe implementarse mediante el sistema de migraciones.

Esto garantiza la compatibilidad entre versiones.

---

# REST API

La REST API nunca debe implementar lógica de negocio.

Los endpoints únicamente reutilizan servicios existentes del núcleo.

Si una funcionalidad solo existe en un endpoint REST, probablemente esté ubicada en el lugar incorrecto.

---

# Dashboard

Las páginas administrativas deben permanecer desacopladas.

Cada pantalla debe responsabilizarse únicamente de:

- recuperar datos;
- mostrarlos;
- gestionar acciones propias.

La lógica de negocio pertenece a los servicios correspondientes.

---

# DWW Doctor

Cada comprobación debe implementarse como un Health Check independiente.

Las reparaciones automáticas deberán implementarse únicamente cuando sea posible garantizar un comportamiento seguro.

---

# Seguridad

Antes de incorporar cualquier funcionalidad nueva deben revisarse los siguientes aspectos:

- validación de entradas;
- sanitización;
- escapado de salidas;
- permisos;
- nonces;
- validación de archivos;
- protección del almacenamiento.

La seguridad siempre tiene prioridad sobre la comodidad de implementación.

---

# Añadir un nuevo formato documental

La incorporación de un nuevo formato normalmente implica:

1. Crear un Handler.
2. Implementar el Processor.
3. Registrar el Handler.
4. Añadir la extracción del Payload.
5. Añadir la verificación.
6. Incorporar pruebas funcionales.

No debería ser necesario modificar el Fingerprint Manager.

---

# Buenas prácticas

Antes de dar por finalizado un desarrollo se recomienda verificar:

- arquitectura coherente;
- separación de responsabilidades;
- ausencia de código duplicado;
- reutilización de componentes existentes;
- nomenclatura consistente;
- compatibilidad con versiones anteriores;
- validación funcional completa.

---

# Calidad del código

El proyecto prioriza:

- simplicidad;
- mantenibilidad;
- legibilidad;
- extensibilidad.

Una solución ligeramente más larga pero fácil de comprender será preferible a una implementación más compleja y difícil de mantener.

---

# Objetivo

El objetivo de DWW Fingerprinting no es únicamente proporcionar un plugin funcional.

El objetivo es construir una plataforma de fingerprinting documental sólida, mantenible y preparada para evolucionar durante años sin necesidad de rediseñar su núcleo.