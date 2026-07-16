# API Reference

## Introducción

DWW Fingerprinting expone una API REST que permite consultar el estado del sistema, verificar documentos y acceder a la información de fingerprints desde aplicaciones externas.

La API reutiliza completamente el núcleo del sistema, por lo que todas las operaciones producen exactamente los mismos resultados que la interfaz administrativa.

Todas las respuestas se devuelven en formato JSON.

---

# URL base

```text
https://example.com/wp-json/dww/v1/
```

---

# Autenticación

Todas las peticiones requieren autenticación mediante una API Key válida.

Se admiten dos mecanismos de autenticación.

## API Key

```http
X-DWW-API-Key: YOUR_API_KEY
```

## Bearer Token

```http
Authorization: Bearer YOUR_API_KEY
```

---

# Endpoints

## Health

Obtiene el estado general del sistema.

### Endpoint

```http
GET /health
```

### Ejemplo

```http
GET /wp-json/dww/v1/health
```

### Respuesta

```json
{
    "status": "ok",
    "score": 100
}
```

---

## Stats

Obtiene estadísticas generales del sistema.

### Endpoint

```http
GET /stats
```

### Respuesta

```json
{
    "fingerprints": 152,
    "downloads": 391,
    "active_tokens": 104
}
```

---

## Fingerprint

Consulta un fingerprint concreto.

### Endpoint

```http
GET /fingerprint/{fingerprint_id}
```

### Ejemplo

```http
GET /fingerprint/FP-6c2f8d...
```

### Respuesta

```json
{
    "fingerprint": "...",
    "customer": "...",
    "product": "...",
    "status": "active"
}
```

---

## Verify

Verifica un documento protegido.

### Endpoint

```http
POST /verify
```

### Content-Type

```text
multipart/form-data
```

### Parámetros

| Campo | Tipo | Descripción |
|--------|------|-------------|
| document | File | Documento a verificar |

### Respuesta

```json
{
    "valid": true,
    "trust_score": 100,
    "status": "verified"
}
```

---

# Códigos de respuesta

| Código | Significado |
|---------|-------------|
| 200 | Operación correcta |
| 400 | Solicitud incorrecta |
| 401 | API Key inválida |
| 403 | Acceso denegado |
| 404 | Recurso inexistente |
| 500 | Error interno |

---

# Seguridad

La API incorpora las mismas medidas de seguridad que el resto del sistema.

Entre otras:

- autenticación mediante API Key;
- autenticación Bearer;
- validación de permisos;
- validación de documentos;
- validación del Payload;
- verificación de integridad;
- protección frente a documentos manipulados.

---

# Compatibilidad

La API mantiene compatibilidad con documentos generados mediante versiones anteriores del Payload cuando resulta técnicamente posible.

También mantiene compatibilidad con documentos protegidos mediante SHA256 Legacy y SHA256-HMAC.

---

# Extensibilidad

La infraestructura REST ha sido diseñada para facilitar la incorporación de nuevos endpoints.

Las nuevas funcionalidades deben implementarse reutilizando siempre los servicios existentes del núcleo del sistema, evitando duplicar lógica de negocio dentro de los controladores REST.

---

# Versionado

La API se publica bajo el namespace:

```text
/wp-json/dww/v1/
```

Las futuras versiones incompatibles utilizarán un nuevo namespace (`v2`, `v3`, etc.), manteniendo la compatibilidad con clientes existentes siempre que sea posible.

---

# Soporte

Para incidencias técnicas, consultas sobre integración o información comercial, contacta con Digital Web Works.