# ROADMAP del Proyecto Gemi-Bucket

## 🎯 Visión general

Gemi-Bucket es un backend en PHP nativo para el almacenamiento seguro de archivos multimedia mediante identificadores únicos (hashes). Está diseñado como un sistema **multi-inquilino**, donde cada cliente autorizado tiene su espacio aislado y gestionado mediante una estructura jerárquica y segura.

Este documento presenta el **roadmap completo del proyecto**, incluyendo:
- Fases completadas
- Fases en desarrollo
- Fases pendientes
- Objetivos técnicos de cada fase
- Archivos creados o modificados
- Resultado esperado al finalizar cada una

---

## ✅ Fase 1: Configuración Inicial y Mapeo de Inquilinos

### 📁 Archivos clave
- tenants/tenants.json
- tenants/tenants_map.json
- services/TenantService.php
- config/config.php

### ✅ Descripción
Se implementó un sistema multi-inquilino donde:
- Cada dominio autorizado se registra manualmente en tenants.json
- Se genera automáticamente un hash único por dominio
- Se crea una carpeta dedicada (storage/[hash]/)
- Se registran datos iniciales del inquilino
- Se reutiliza el mismo hash si ya existe

### 🧩 Funcionalidad alcanzada
- Generación de mapa de inquilinos
- Carpeta única por cliente
- Estructura base (config/, files/, references/)
- Reutilización de hash
- Validación de permisos y rutas

---

## ✅ Fase 2: Gestión de Referencias de Archivos

### 📁 Archivos clave
- services/ReferenceService.php
- storage/[hash]/references/ref_001.json
- interfaces/responses.php

### ✅ Descripción
Se implementó un sistema de registro de archivos subidos con las siguientes características:
- {hash, path} se guardan en JSON dentro de la carpeta references/
- Si excede el límite de tamaño, se crea un nuevo archivo (ref_002.json, etc.)
- Búsqueda rápida de archivos usando el hash
- Eliminación segura de referencias
- Totalmente integrado con respuestas estandarizadas

### 🧩 Funcionalidad alcanzada
- Registro de archivos subidos
- Rotación automática de archivos JSON
- Búsqueda y eliminación eficiente
- Uso consistente de Response::success() y Response::error()

---

## ✅ Fase 3: Subida de Archivos (UploadController)

### 📁 Archivos clave
- controllers/UploadController.php
- public/index.php (interfaz actualizada)
- interfaces/responses.php (con localStorage)

### ✅ Descripción
Se desarrolló un controlador para recibir peticiones de subida de archivos desde clientes autorizados. Incluye:
- Validación de dominio
- Recepción de archivo y ruta deseada
- Generación de hash único
- Almacenamiento físico del archivo
- Registro en archivo de referencia
- Interfaz web básica para pruebas
- Guardado de hashes en localStorage para futuras operaciones

### 🧩 Funcionalidad alcanzada
- Sistema de subida funcional
- Respuesta con hash único
- Visualización de historial de hashes
- Integración con interfaz HTML
- Manejo de sobreescribir (en análisis)

---

## 🟡 Fase 4: Servicios de Hashes y Metadatos

### 📁 Archivos a crear
- services/HashService.php
- services/MetadataService.php

### 🎯 Descripción
Implementar servicios dedicados a:
- Generar hashes más seguros y estándar (SHA-256, UUID, etc.)
- Preservar metadatos originales:
  - Nombre original
  - MIME type
  - Tamaño
  - Fecha de carga
  - Cliente responsable

### 🧩 Funcionalidad planeada
- HashService: generación de identificadores únicos y consistentes
- MetadataService: preservación de información sensible
- Mejorar seguridad en la generación de nombres internos
- Preparar terreno para auditoría y logs

---

## 🟡 Fase 5: Controladores de Lectura, Edición y Eliminación

### 📁 Archivos a crear
- controllers/FileController.php

### 🎯 Descripción
Permitir operaciones sobre archivos mediante su hash único:
- GET /file?hash=... → Devuelve el archivo
- DELETE /file?hash=... → Elimina el archivo y su referencia
- PUT /file?hash=... → Reemplaza contenido (si aplica)

### 🧩 Funcionalidad planeada
- Acceso exclusivo mediante hash
- Sin acceso directo por ruta
- Borrado seguro y trazable
- Actualización permitida solo si está habilitada

---

## 🟡 Fase 6: Seguridad y Middleware

### 📁 Archivos a crear
- middleware/AuthMiddleware.php
- .htaccess en carpetas sensibles

### 🎯 Descripción
Asegurar que solo los dominios autorizados puedan interactuar con el bucket.

### 🧩 Funcionalidad planeada
- Verificación del header Origin
- Protección contra CORS malicioso
- Logs de intentos no autorizados
- Bloqueo de rutas sensibles
- Middleware de validación de tenant

---

## 🟡 Fase 7: Punto de entrada único (API REST)

### 📁 Archivos a crear
- public/index.php (versión final)
- Enrutador centralizado

### 🎯 Descripción
Crear un único punto de entrada HTTP que enrute todas las peticiones:
- POST /upload
- GET /file?hash=...
- DELETE /file?hash=...
- Soporte futuro para autenticación, logs y expansión

### 🧩 Funcionalidad planeada
- Enrutamiento modular
- Capa única de seguridad
- Escalabilidad para nuevas funcionalidades

---

## 🟡 Fase 8: Configuración avanzada por inquilino

### 📁 Archivos a usar
- storage/[hash]/config/permissions.json
- storage/[hash]/config/tenant_config.json

### 🎯 Descripción
Dar permisos y límites personalizados por cliente:
- Tipos MIME permitidos
- Límite de tamaño por archivo
- Límite de cantidad de archivos
- Datos personalizados del cliente

### 🧩 Funcionalidad planeada
- Permisos variables por cliente
- Límites configurables
- Mantener datos de cliente en estructura interna

---

## 🟡 Fase 9: Logs del sistema

### 📁 Archivos a crear
- logs/access.log
- logs/errors.log
- logs/tenant_actions.log

### 🎯 Descripción
Registrar todas las acciones importantes del sistema:
- Accesos y peticiones recibidas
- Errores críticos
- Operaciones por cliente

### 🧩 Funcionalidad planeada
- Registro de accesos
- Auditoría de errores
- Historial de acciones por cliente
- Integridad con respuestas estandarizadas

---

## 🟡 Fase 10: Documentación técnica y guías de uso

### 📁 Archivos a crear
- README.md → Guía rápida
- docs/api.md → Documentación de endpoints
- docs/architecture.md → Arquitectura del sistema
- docs/troubleshooting.md → Solución de problemas comunes

### 🎯 Descripción
Dejar documentado cómo funciona el proyecto y cómo usarlo, ideal para nuevos desarrolladores o futuros colaboradores.

### 🧩 Funcionalidad planeada
- Documentación clara y completa
- Ejemplos de uso
- Guías de instalación y migración
- Buenas prácticas de seguridad

---

## 🧱 Resumen del estado actual

| Fase | Estado | Notas |
|------|--------|-------|
| **Fase 1** | ✅ Completada | Sistema multi-inquilino funcional |
| **Fase 2** | ✅ Completada | Gestión de referencias con rotación automática |
| **Fase 3** | ✅ Completada | UploadController funcional con interfaz de prueba |
| **Fase 4** | 🟡 Pendiente | Generación de hashes y metadatos |
| **Fase 5** | 🟡 Pendiente | FileController para GET/PUT/DELETE |
| **Fase 6** | 🟡 Pendiente | AuthMiddleware y protección por dominio |
| **Fase 7** | 🟡 Pendiente | Enrutamiento centralizado |
| **Fase 8** | 🟡 Pendiente | Configuración avanzada por cliente |
| **Fase 9** | 🟡 Pendiente | Registro de acciones y errores |
| **Fase 10** | 🟡 Pendiente | Documentación técnica |

---

## 🚀 Beneficios clave del roadmap

| Característica | Descripción |
|----------------|-------------|
| ✅ Multi-inquilino | Cada cliente tiene su espacio seguro |
| ✅ Hash único | Identificador seguro e irreversiblemente vinculado |
| ✅ Jerarquía por cliente | Estructura lógica de archivos |
| ✅ Seguridad por dominio | Solo dominios autorizados pueden acceder |
| ✅ Respuestas estandarizadas | Claras, seguras y reutilizables |
| ✅ No acceso directo | Todo pasa por el backend |
| ✅ Escalable | Listo para crecer con nuevas funcionalidades

---

## 📝 Notas finales

- Este sistema fue pensado como SaaS seguro y escalable
- El roadmap refleja un desarrollo en capas, modular y mantenible
- Cada fase estática puede probarse independientemente
- Es ideal para equipos pequeños o medianos con necesidad de control total del bucket

