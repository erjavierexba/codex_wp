"""Exercise the real shell launchers with an isolated Docker double."""
import json
import os
from pathlib import Path
import shutil
import stat
import subprocess
import sys
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[1]


class LinuxInstallerTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory(prefix="oldbear tests '")
        self.addCleanup(self.temporary.cleanup)
        self.base = Path(self.temporary.name)
        self.project = self.base / "Old Bear's Studio"
        shutil.copytree(ROOT, self.project, ignore=shutil.ignore_patterns(
            "data", ".env", "ACCESO.txt", "dist", "__pycache__", ".git", "preview", "preview-assets", "Vista-previa.html"))
        self.bin = self.base / "bin"
        self.bin.mkdir()
        self.log = self.base / "docker.jsonl"
        double = self.bin / "docker"
        double.write_text("#!" + sys.executable + "\n" + r'''
import json, os, pathlib, shutil, sys
args = sys.argv[1:]
with open(os.environ['OLDBEAR_TEST_LOG'], 'a') as output:
    output.write(json.dumps(args) + '\n')
if args and args[0] == 'run' and '--mount' in args:
    mount = args[args.index('--mount') + 1]
    source = next(item[4:] for item in mount.split(',') if item.startswith('src='))
    assert pathlib.Path(source).name == 'data'
    assert args[-1] == 'rm -rf -- /oldbear-data/wordpress /oldbear-data/mysql'
    for name in ['wordpress', 'mysql']:
        shutil.rmtree(pathlib.Path(source) / name)
sys.exit(0)
''')
        double.chmod(0o755)
        self.env = dict(os.environ, PATH=str(self.bin) + os.pathsep + os.environ['PATH'],
                        OLDBEAR_TEST_LOG=str(self.log), OLDBEAR_NO_OPEN='1')

    def run_script(self, name, *args, input=None, success=True):
        result = subprocess.run(['bash', str(self.project / name), *args],
                                cwd=self.base, env=self.env, input=input,
                                text=True, capture_output=True, timeout=20)
        if success:
            self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        else:
            self.assertNotEqual(result.returncode, 0, result.stdout + result.stderr)
        return result

    def values(self):
        return dict(line.split('=', 1) for line in (self.project / '.env').read_text().splitlines())

    def install(self):
        return self.run_script('Instalar.sh')

    def test_install_generates_private_credentials_and_all_paths(self):
        result = self.install()
        values = self.values()
        self.assertEqual(values['WP_ADMIN_USER'], 'oldbear')
        self.assertGreaterEqual(len(values['WP_ADMIN_PASSWORD']), 16)
        self.assertNotEqual(values['DB_PASSWORD'], values['DB_ROOT_PASSWORD'])
        for name in ['.env', 'ACCESO.txt']:
            self.assertEqual(stat.S_IMODE((self.project / name).stat().st_mode), 0o600)
        self.assertNotIn(values['WP_ADMIN_PASSWORD'], result.stdout + result.stderr)
        self.assertTrue((self.project / 'data/wordpress').is_dir())
        self.assertTrue((self.project / 'data/mysql').is_dir())
        self.assertTrue((self.project / 'data/.oldbear-managed').is_file())

    def test_repeat_install_preserves_credentials_and_user_data(self):
        self.install()
        original = (self.project / '.env').read_bytes()
        sentinel = self.project / 'data/wordpress/my-upload.txt'
        sentinel.write_text('keep')
        self.install()
        self.assertEqual(original, (self.project / '.env').read_bytes())
        self.assertEqual(sentinel.read_text(), 'keep')

    def test_start_requires_an_installation(self):
        self.run_script('Iniciar.sh', success=False)
        self.assertFalse(self.log.exists())

    def test_stop_and_keep_data_uninstall_preserve_state(self):
        self.install()
        for name in ['Detener.sh', 'Iniciar.sh']:
            self.run_script(name)
        self.run_script('Desinstalar.sh', '--keep-data')
        self.assertTrue((self.project / 'data/.oldbear-managed').exists())
        self.assertTrue((self.project / '.env').exists())
        calls = [json.loads(line) for line in self.log.read_text().splitlines()]
        self.assertFalse(any(call and call[0] == 'run' for call in calls if '--mount' in call))

    def test_purge_needs_explicit_confirmation(self):
        self.install()
        previous = self.log.read_bytes()
        self.run_script('Desinstalar.sh', '--purge', input='no\n')
        self.assertEqual(previous, self.log.read_bytes())
        self.assertTrue((self.project / '.env').exists())

    def test_confirmed_purge_removes_only_managed_data(self):
        self.install()
        (self.project / 'data/personal.txt').write_text('keep')
        outside = self.base / 'unrelated.txt'
        outside.write_text('keep')
        self.run_script('Desinstalar.sh', '--purge', input='BORRAR OLDBEAR\n')
        self.assertFalse((self.project / 'data/wordpress').exists())
        self.assertFalse((self.project / 'data/mysql').exists())
        self.assertFalse((self.project / '.env').exists())
        self.assertFalse((self.project / 'ACCESO.txt').exists())
        self.assertEqual(outside.read_text(), 'keep')
        self.assertEqual((self.project / 'data/personal.txt').read_text(), 'keep')

    def test_symlink_data_is_rejected(self):
        target = self.base / 'private'
        target.mkdir()
        (target / 'keep.txt').write_text('keep')
        (self.project / 'data').symlink_to(target, target_is_directory=True)
        self.run_script('Instalar.sh', success=False)
        self.assertEqual((target / 'keep.txt').read_text(), 'keep')

    def test_unmarked_data_is_rejected(self):
        (self.project / 'data').mkdir()
        self.run_script('Instalar.sh', success=False)

    def test_bad_ports_are_rejected(self):
        self.env['OLDBEAR_WEB_PORT'] = '8080;false'
        self.run_script('Instalar.sh', success=False)
        self.assertFalse((self.project / '.env').exists())


if __name__ == '__main__':
    unittest.main(verbosity=2)
