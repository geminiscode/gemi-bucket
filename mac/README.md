# Gemi-Bucket
Backend PHP nativo para almacenamiento seguro de archivos multimedia mediante hashes únicos.  
Diseñado como un sistema multi-inquilino (multi-tenant), con control de acceso por dominios autorizados y almacenamiento jerárquico por cliente.

---

## 🛠️ Requisitos del Sistema

- MacOS
- XAMPP (Apache + PHP)
- Acceso a terminal con permisos de administrador (`sudo`)

---

## 🔧 Configuración de Permisos (Solo en MacOS)

Si estás usando este proyecto en **MacOS con XAMPP**, es posible que encuentres problemas de escritura al intentar crear carpetas o archivos desde PHP (por ejemplo: `storage/`, `tenants/tenants_map.json`, etc.).

Para solucionarlo, ejecuta estos comandos en la raíz del proyecto desde el Terminal:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/gemi-bucket
sudo chown -R $(whoami):daemon .
sudo find . -type d -exec chmod 775 {} \;
sudo find . -type f -exec chmod 664 {} \;