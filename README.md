# OldBearStudios — edición Elementor

Esta rama `elementor` adapta la landing a **Elementor Free 3.34.2** e incorpora **OldBear Studio Kit 2.0**: catálogo con filtros y API, roadmap, widgets personalizados y panel de gestión. **Todo el sitio se construye con Elementor**: páginas, cabecera, pie y página 404.

## Arrancar en Linux

Extrae `OldBearStudios-Elementor-Linux.zip` y ejecuta `bash Instalar.sh`. El instalador mantiene el funcionamiento de la edición principal: Docker, PHP, Apache, MariaDB, claves automáticas en `ACCESO.txt` y datos en `data/`. Además descarga Elementor Free y prepara una portada editable con dos juegos y tres hitos marcados como **Demo**.

- Web: http://localhost:8080/
- Panel: http://localhost:8080/wp-admin/
- Correo de pruebas: http://localhost:8025/

`Iniciar.sh`, `Detener.sh`, `Diagnostico.sh` y `Desinstalar.sh` mantienen sus funciones. El borrado de datos exige escribir BORRAR OLDBEAR. Docker y sus imágenes compartidas se conservan.

## Editar y demostrar

En el panel abre **OldBear Studio → Editar landing con Elementor**. Desde ahí también se editan la página Diario, la política de privacidad, la cabecera, el pie y la página 404. Elementor Free no incluye Theme Builder: cabecera, pie y 404 son plantillas de Elementor (Plantillas → Plantillas guardadas) que el tema muestra en todo el sitio. Solo el logotipo, el nombre del sitio y los enlaces del menú (Apariencia → Menús) quedan fuera de Elementor. Los juegos, las entradas y el roadmap se gestionan desde sus apartados.

El Diario es una página de Elementor con el widget **OldBear · Diario**, no la página de entradas de WordPress, porque Elementor no permite editar esa página.

Lee [la guía de demostración y arquitectura](wp-content/plugins/oldbear-studio/README.md) para recorrer los filtros, los widgets y el progreso de hitos. No necesitas Elementor Pro.

La demo está claramente identificada. Puedes retirarla desde OldBear Studio sin eliminar proyectos propios. Repetir el instalador conserva el diseño de Elementor ya editado y no duplica la portada.

## Instalar en un hosting

Instala WordPress 6.8+, PHP 8.2+ y Elementor Free. Sube/activa el tema `oldbearstudios` y el plugin `oldbear-studio`. Desde OldBear Studio pulsa **Preparar landing Elementor**: se crea un borrador. Edítalo, publícalo y selecciónalo en Ajustes → Lectura. Después pulsa **Preparar cabecera, pie, 404, diario y privacidad con Elementor**. Las herramientas no sobrescriben diseños de Elementor existentes.

El formulario de contacto requiere destinatario, política de privacidad publicada y activación en Ajustes → Generales. En hosting configura correo real con SMTP. La instalación local usa Mailpit.

## Validación y paquetes

GitHub Actions mantiene las pruebas de WordPress/contacto y del instalador Linux, y añade pruebas de Elementor y Studio Kit. Genera ZIP del tema, plugin y paquete Linux con vista previa estática. Esa vista muestra el diseño; la API, filtros y administración requieren WordPress en marcha.

`bash scripts/check.sh` verifica PHP y WordPress. Para las pruebas adicionales: `docker compose run --rm cli wp eval-file /opt/oldbear-tests/elementor.php`. La prueba crea contenido temporal y lo retira al terminar.

Esta rama está separada de `main`. Las ilustraciones siguen siendo SVG originales y el paquete no incluye una licencia de Elementor Pro ni credenciales reales.
