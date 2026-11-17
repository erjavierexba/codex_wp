# OldBear Studio Kit 2.0

Plugin de WordPress para estudios de videojuegos. Funciona con Elementor Free 3.34.2, sin Elementor Pro. El catálogo, roadmap y contacto también se insertan mediante shortcodes cuando Elementor no está activo.

## Demostración en cinco minutos

1. Instala el paquete de la rama `elementor` y abre **OldBear Studio** en el panel.
2. Pulsa **Editar landing con Elementor**. La página contiene contenedores y widgets nativos, además de widgets de la categoría OldBear Studio. Los botones de **Todo el sitio en Elementor** abren la cabecera, el pie, la 404, el Diario y la privacidad.
3. Cambia el titular del widget Portada. Reordena secciones, sustituye el emblema con el widget Imagen o modifica los encabezados nativos.
4. Filtra los juegos por género/plataforma, busca un nombre y prueba una búsqueda sin coincidencias. La respuesta usa una API pública de solo lectura.
5. En **Roadmap**, cambia el estado de un hito. Se mueve de columna y cambia el progreso calculado.
6. Edita el estado o las taxonomías de un juego: el catálogo invalida automáticamente su caché.
7. Retira la demo desde el panel y publica tus propios proyectos. Los registros de muestra están marcados como Demo y se retiran a la papelera.

Los ejemplos de bosque y dunas son conceptos visuales para probar funciones, no anuncios de productos reales. La demo no inventa clientes, premios, ventas, testimonios ni fechas de lanzamiento.

## Widgets

| Widget | Controles |
| --- | --- |
| OldBear · Portada | Titular, descripción, etiqueta, ilustración, texto alternativo, botón y enlace; acento y tamaño adaptable |
| OldBear · Catálogo de juegos | Juegos por página, filtros y acento |
| OldBear · Roadmap | Juego, límite de hitos y acento |
| OldBear · Contacto | Titular, introducción, acento y tamaño del titular |
| OldBear · Diario | Entradas por página, paginación, imagen destacada y textos sin entradas |
| OldBear · Marca | Emblema y lema (usa el logotipo de Apariencia si existe) |
| OldBear · Menú | Menú de WordPress y texto del botón móvil |
| OldBear · Redes | URL de Steam, itch.io y Discord |
| OldBear · Créditos del pie | Texto legal con {year} y {site}, enlace a privacidad y volver arriba |

Los widgets se renderizan en PHP y se declaran dinámicos. Las piezas editoriales usan widgets nativos de Elementor. Ninguna página es un bloque HTML incrustado ni un shortcode que encapsule su contenido.

## Todo el sitio en Elementor

Elementor Free no tiene Theme Builder. `includes/class-layout.php` guarda tres plantillas de Elementor (cabecera, pie y 404) y el tema las imprime mediante el filtro `oldbear_theme_part`. Se renderizan antes de `wp_head` para que su CSS cargue en la cabecera. Si Elementor está desactivado, el tema vuelve a su cabecera y pie en PHP. `wp oldbear studio-demo` y el botón del panel crean las plantillas, la página Diario y la versión Elementor de la política de privacidad. No sobrescriben diseños existentes y desactivan las fuentes y colores por defecto de Elementor para que los widgets nativos hereden el estilo del tema.

## Contenido y permisos

- `oldbear_game`: juegos con editor de bloques, extracto, imagen destacada, estado y URL de tienda.
- `ob_genre` y `ob_platform`: taxonomías con REST y panel nativo.
- `ob_milestone`: hitos publicados con estado permitido y relación con un juego. Los borradores no se muestran.
- La gestión de contenido usa las capacidades nativas de WordPress; la preparación de la landing y la demo requieren `manage_options` y nonce.
- La creación de portada es idempotente: mantiene el documento editado. Desde el panel crea un borrador; el instalador local publica la nueva página y la selecciona como portada, conservando las páginas anteriores.

Los metadatos de género/plataforma de la versión 1 siguen en la base de datos; en esta rama se usan las taxonomías para los filtros. Asigna los términos desde la ficha de cada juego antiguo.

## API y extensiones

`GET /wp-json/oldbear/v1/games?genre=exploracion&platform=linux&search=bosque&page=1&per_page=6`

Devuelve registros públicos, total, páginas y tarjetas renderizadas en servidor. Máximo 12 elementos por petición y búsqueda de 80 bytes. Los borradores, privados y juegos protegidos por contraseña quedan fuera. No hay endpoint público de escritura.

El catálogo usa `WP_Query`, una caché de cinco minutos versionada por cambios de contenido/taxonomías/metadatos y las APIs nativas de WordPress. No hace consultas SQL construidas desde parámetros. La interfaz cancela peticiones previas y conserva los resultados si falla la red. Sin JavaScript, el formulario usa parámetros GET y renderiza en servidor.

Shortcodes:

```text
[oldbear_games per_page="6" filters="yes"]
[oldbear_roadmap game="0" limit="30"]
[oldbear_contact]
```

## Arquitectura

- `oldbear-studio.php`: compatibilidad con la primera versión, contacto y arranque.
- `includes/class-kit.php`: carga modular, recursos y registro de widgets.
- `includes/class-content.php`: roadmap, metadatos, permisos y taxonomías.
- `includes/class-catalog.php`: consultas, caché, REST y render compartido.
- `includes/widgets.php`: controles, estilos adaptables y render de Elementor.
- `includes/class-layout.php`: plantillas de cabecera, pie y 404, y soporte de Elementor para juegos.
- `includes/class-starter.php`: creación de documentos mediante la API de Elementor y datos demo.
- `includes/class-admin.php`: panel y herramientas protegidas.
- `assets/`: CSS acotado, JavaScript nativo con integración en el ciclo del editor e ilustraciones propias.

La lógica de negocio vive en el plugin, separada del tema. No se borran juegos, hitos, ajustes ni documentos al desactivar o eliminar el plugin. Se conservan para recuperación; puedes retirar previamente la demo a la papelera. El contacto mantiene la validación, consentimiento, nonce, campo trampa y límites de envío de la versión anterior. En producción configura SMTP y privacidad.

## Comprobaciones

GitHub Actions instala WordPress y Elementor, activa el tema/plugin y verifica widgets, documentos guardados/renderizados, filtros combinados, paginación, exclusión de borradores, invalidación de caché, escape de títulos, progreso real y rechazos por permisos/nonce. También mantiene las pruebas de contacto y el ciclo de instalación/desinstalación Linux. No se requiere una licencia Pro.
