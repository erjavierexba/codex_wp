# OldBearStudios — WordPress

Landing en español para un estudio de videojuegos. Tema propio, sin Elementor, fuentes externas ni dependencias de compilación. Fondo tinta, acentos ámbar e ilustraciones SVG originales.

## Arrancar en local

Necesitas Docker con Compose y un terminal Bash.

1. Clona este repositorio y entra en él.
2. Copia `.env.example` a `.env` y cambia las contraseñas, el usuario y el correo.
3. Ejecuta `bash scripts/setup.sh`.
4. Abre http://localhost:8080 y http://localhost:8080/wp-admin/.
5. El correo de desarrollo se recibe en http://localhost:8025 (Mailpit; nunca se envía a Internet).

`docker compose up -d` reinicia una instalación ya configurada. `docker compose down` conserva los datos. No uses `down -v` si quieres conservarlos. Los puertos están limitados a 127.0.0.1; esta configuración es para desarrollo, no para exponerla a Internet.

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

El flujo de GitHub Actions realiza estas mismas comprobaciones y empaqueta tema y plugin por separado. No despliega el sitio.

## Estructura

- `wp-content/themes/oldbearstudios/`: presentación, estilos, SVG y plantillas.
- `wp-content/plugins/oldbear-studio/`: juegos, metadatos y formulario. Los datos permanecen al cambiar de tema.
- `scripts/`: instalación local y comprobaciones.
- `tests/`: comprobación de integración por WP-CLI.

Código e ilustraciones originales del paquete: GPL-2.0-or-later. Sustituye los conceptos visuales si ya tienes identidad y arte propios.
