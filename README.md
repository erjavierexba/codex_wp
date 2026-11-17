# OldBearStudios — WordPress

Landing en español para un estudio de videojuegos. Tema propio, sin Elementor, fuentes externas ni dependencias de compilación. Fondo tinta, acentos ámbar e ilustraciones SVG originales.

## Arrancar en local

Descarga y extrae **OldBearStudios-Linux.zip** o clona este repositorio. Abre un terminal en la carpeta y ejecuta:

```bash
bash Instalar.sh
```

El instalador prepara Docker y Compose v2 si faltan en Ubuntu 24.04 y derivados. En otras distribuciones Linux, instala esos dos requisitos primero. La primera instalación necesita Internet y puede pedir permisos de administrador para Docker. PHP, Apache, WordPress y MariaDB se ejecutan en contenedores.

Las contraseñas se generan automáticamente. Encontrarás los datos de acceso en **ACCESO.txt** (solo legible por tu usuario). La web se abre en http://localhost:8080; el panel está en http://localhost:8080/wp-admin/ y el correo de desarrollo, capturado por Mailpit, en http://localhost:8025.

| Archivo | Función |
| --- | --- |
| `Instalar.sh` | Prepara requisitos, claves, servicios y WordPress. Se puede repetir. |
| `Iniciar.sh` | Inicia la instalación existente y abre la web. |
| `Detener.sh` | Detiene todos los servicios y conserva los datos. |
| `Desinstalar.sh` | Elimina contenedores; permite conservar o borrar los datos. |
| `Diagnostico.sh` | Muestra estado y registros. |
| `Vista-previa.html` | Vista estática de la portada, incluida en el ZIP generado, para abrir sin instalación. |

La web, los archivos subidos y la base de datos viven en **data/wordpress/** y **data/mysql/**, dentro de la misma carpeta. Detén los servicios antes de moverla o copiarla. Para una copia completa incluye `data/`, `.env` y `ACCESO.txt`; algunos archivos de datos requieren permisos de administrador. Las claves y datos están excluidos del repositorio y del ZIP de distribución.

El borrado completo exige escribir **BORRAR OLDBEAR**. El desinstalador solo elimina los contenedores y datos de esta instalación. Docker y las imágenes descargadas son recursos compartidos del sistema y se conservan. Los servicios no arrancan automáticamente al reiniciar Linux.

Los puertos están limitados a 127.0.0.1: este paquete es para desarrollo local. Para usar otros puertos en la primera instalación: `OLDBEAR_WEB_PORT=8081 OLDBEAR_MAIL_PORT=8026 bash Instalar.sh`. En una instalación existente cambia los puertos y `WP_URL` en `.env` y repite el instalador. No arranques simultáneamente una copia de una instalación sin cambiar su `COMPOSE_PROJECT_NAME` y sus puertos.

## Editar el sitio

- **Apariencia → Personalizar → OldBearStudios**: titular, textos, etiqueta del estudio, correo público y enlaces opcionales a Steam, itch.io, Discord y tráiler. Los enlaces vacíos no se muestran.
- **Apariencia → Personalizar → Identidad del sitio**: nombre, logotipo e icono.
- **Juegos → Añadir juego**: título, extracto, imagen destacada, descripción por bloques y ficha (estado, género, plataformas y enlace de tienda). Los juegos publicados se muestran automáticamente en la portada. No se crean juegos ficticios.
- **Entradas**: diario de desarrollo. Solo aparece en portada cuando publicas entradas; el instalador elimina exclusivamente la entrada de ejemplo de una instalación recién creada.
- **Páginas**: inicio, diario y contenido legal. La portada usa una plantilla propia; los textos principales se editan en Personalizar, no en el contenido de la página Inicio.
- **Ajustes → Generales → Contacto OldBearStudios**: destinatario y activación del formulario.
- **Ajustes → Privacidad**: selecciona y publica tu política antes de activar el formulario. La página creada por WordPress es un borrador y debe adaptarse a tu actividad real.

Los dos paneles gráficos que aparecen mientras no hay juegos son conceptos visuales de muestra, identificados como tales. Sustitúyelos publicando tus juegos. No se incluyen cifras, premios, clientes, testimonios, fechas de lanzamiento ni enlaces sociales inventados.

## Instalar en un hosting WordPress

1. Instala WordPress 6.6 o superior con PHP 8.2 o superior y HTTPS.
2. Sube `wp-content/themes/oldbearstudios` a `wp-content/themes/`.
3. Sube `wp-content/plugins/oldbear-studio` a `wp-content/plugins/`.
4. Activa **OldBear Studio** en Plugins y **OldBearStudios** en Apariencia.
5. Configura nombre, enlaces y textos. Publica tus juegos y tu política de privacidad.
6. Verifica el correo destinatario, configura un servicio SMTP con un plugin de correo y activa el formulario.
7. Prueba el envío real y las páginas legales antes de publicar.

El tema y el plugin también pueden comprimirse por separado e instalarse desde el panel. No subas `.env`, los scripts ni Docker al directorio público. No se incluye el núcleo de WordPress, credenciales reales, licencias comerciales ni configuración de un hosting concreto.

## Contacto y privacidad

El formulario tiene validación en servidor, nonce, campo trampa, límite por IP y correo, consentimiento y mensajes de error accesibles. No guarda el mensaje en WordPress; lo entrega a `wp_mail`. Un resultado correcto significa que WordPress aceptó el envío, no que el servidor destinatario lo haya entregado. Mailpit captura todos los mensajes únicamente en desarrollo.

Los límites conservan hashes con sal de la IP y del correo durante una hora. Añade este tratamiento y el de tu proveedor de correo a tu política. Si usas un proxy inverso, los límites por IP pueden agrupar usuarios: revisa la configuración de red antes de producción. Excluye la portada de cachés de página que superen la duración del nonce y las respuestas con `?contact=` de toda caché.

El paquete no carga analítica ni servicios de terceros. Si añades embeds, marketing o herramientas externas, configura sus requisitos de privacidad. Los textos legales dependen de tu actividad: no se inventan datos del responsable.

## Validación

`bash scripts/check.sh` ejecuta sintaxis PHP y una comprobación integrada de WordPress en la instalación local. La prueba comprueba el tema, el tipo de contenido, las páginas esenciales, el saneamiento de los ajustes y los rechazos de contacto. No envía correos ni publica contenido de muestra.

Las pruebas de Linux se ejecutan con `python3 -m unittest discover -s tests -p test_linux.py -v`. Comprueban claves privadas, reinstalación, rutas con espacios y apóstrofos, preservación de datos y confirmación de borrado.

GitHub Actions comprueba además el ciclo completo con Docker real: instalación, reinicio, reinstalación sin duplicar páginas y desinstalación con borrado en un entorno de prueba nuevo. Empaqueta la carpeta Linux, tema y plugin por separado, incluyendo una vista HTML exportada de WordPress. No despliega el sitio.

Para regenerar los ZIP: `python3 scripts/package.py`. Con una captura de la portada puedes añadir `--snapshot /ruta/portada.html --site-url http://localhost:8080` mientras el WordPress local está disponible. La vista estática incluye recursos locales; no ofrece administración ni envía formularios.

## Estructura

- `wp-content/themes/oldbearstudios/`: presentación, estilos, SVG y plantillas.
- `wp-content/plugins/oldbear-studio/`: juegos, metadatos y formulario. Los datos permanecen al cambiar de tema.
- `scripts/`: instalación local y comprobaciones.
- `tests/`: comprobación de integración por WP-CLI.

Código e ilustraciones originales del paquete: GPL-2.0-or-later. Sustituye los conceptos visuales si ya tienes identidad y arte propios.

