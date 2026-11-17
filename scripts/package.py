#!/usr/bin/env python3
"""Build a single Linux folder without runtime data or generated credentials."""
import argparse
from html.parser import HTMLParser
from pathlib import Path
import re
import urllib.parse
import urllib.request
import zipfile

ROOT = Path(__file__).resolve().parents[1]
ROOT_FILES = ['README.md', 'LEEME-PRIMERO.txt', 'LICENSE', 'compose.yaml', '.env.example',
              'Instalar.sh', 'Iniciar.sh', 'Detener.sh', 'Desinstalar.sh', 'Diagnostico.sh']


class Stylesheets(HTMLParser):
    def __init__(self):
        super().__init__()
        self.urls = []

    def handle_starttag(self, tag, attributes):
        attrs = dict(attributes)
        if tag == 'link' and attrs.get('rel') == 'stylesheet' and attrs.get('href'):
            self.urls.append(attrs['href'])


def snapshot_files(path, site_url):
    html = path.read_text()
    assets = {}
    parser = Stylesheets()
    parser.feed(html)
    for index, url in enumerate(parser.urls):
        parsed = urllib.parse.urlparse(url)
        if url.startswith(site_url + '/') and parsed.path.endswith('.css'):
            name = 'preview-assets/wordpress-' + str(index) + '.css'
            with urllib.request.urlopen(url, timeout=30) as response:
                assets[name] = response.read()
            html = html.replace(url.replace('&', '&amp;'), name).replace(url, name)
    html = re.sub(r'<script\b[^>]*>.*?</script>', '', html, flags=re.S | re.I)
    theme = 'wp-content/themes/oldbearstudios/assets/'
    html = html.replace(site_url + '/' + theme, theme)
    plugin = 'wp-content/plugins/oldbear-studio/assets/'
    html = html.replace(site_url + '/' + plugin, plugin)
    html = html.replace(site_url + '/#', '#')
    html = html.replace('href="' + site_url + '/diario/"', 'href="#diario"')
    html = html.replace('href="' + site_url + '/"', 'href="#contenido"')
    html = html.replace('</body>', '<script src="' + theme + 'site.js" defer></script>\n</body>')
    assets['Vista-previa.html'] = html.encode()
    return assets


def add_bytes(archive, name, content, executable=False):
    info = zipfile.ZipInfo(name)
    info.create_system = 3
    info.external_attr = (0o100755 if executable else 0o100644) << 16
    info.compress_type = zipfile.ZIP_DEFLATED
    archive.writestr(info, content)


def package(snapshot=None, site_url='http://localhost:8080'):
    output = ROOT / 'dist'
    output.mkdir(exist_ok=True)
    files = [ROOT / name for name in ROOT_FILES]
    for folder in ['scripts', 'tests', 'wp-content']:
        files.extend(path for path in (ROOT / folder).rglob('*') if path.is_file()
                     and '__pycache__' not in path.parts and path.suffix != '.pyc')
    extras = snapshot_files(snapshot, site_url) if snapshot else {}
    with zipfile.ZipFile(output / 'OldBearStudios-Elementor-Linux.zip', 'w') as archive:
        for file in sorted(files):
            add_bytes(archive, 'OldBearStudios/' + file.relative_to(ROOT).as_posix(),
                      file.read_bytes(), file.suffix == '.sh')
        for name, content in extras.items():
            add_bytes(archive, 'OldBearStudios/' + name, content)
    for folder, filename in [('themes/oldbearstudios', 'oldbearstudios-theme.zip'),
                              ('plugins/oldbear-studio', 'oldbear-studio-plugin.zip')]:
        source = ROOT / 'wp-content' / folder
        with zipfile.ZipFile(output / filename, 'w') as archive:
            for file in sorted(source.rglob('*')):
                if file.is_file():
                    add_bytes(archive, source.name + '/' + file.relative_to(source).as_posix(), file.read_bytes())
    with zipfile.ZipFile(output / 'OldBearStudios-Elementor-Linux.zip') as archive:
        names = archive.namelist()
        assert not any('/data/' in name or name.endswith('/.env') or name.endswith('/ACCESO.txt') for name in names)
        assert all(name.startswith('OldBearStudios/') for name in names)
        if snapshot:
            assert 'OldBearStudios/Vista-previa.html' in names
    print(output / 'OldBearStudios-Elementor-Linux.zip')


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--snapshot', type=Path)
    parser.add_argument('--site-url', default='http://localhost:8080')
    args = parser.parse_args()
    package(args.snapshot, args.site_url.rstrip('/'))
