import { readdirSync, existsSync } from 'node:fs';
import { resolve } from 'node:path';
import { spawnSync } from 'node:child_process';

function run(command, arguments_) {
    const result = spawnSync(command, arguments_, { stdio: 'inherit' });
    if (result.error) throw result.error;
    if (result.status !== 0) process.exit(result.status ?? 1);
}

for (const directory of readdirSync('Modules', { withFileTypes: true })) {
    if (!directory.isDirectory()) continue;
    const config = resolve('Modules', directory.name, 'vite.config.js');
    if (existsSync(config)) {
        run(process.execPath, ['node_modules/vite/bin/vite.js', 'build', '--config', config]);
    }
}

// PHP_BINARY can point to the PHP executable on systems without PHP on PATH.
const php = process.env.PHP_BINARY ?? 'php';
if (process.platform === 'win32' && php === 'php') {
    // Windows installations may expose PHP through a .bat wrapper.
    run('cmd.exe', ['/d', '/c', 'php artisan module:panel-assets --no-interaction']);
} else {
    run(php, ['artisan', 'module:panel-assets', '--no-interaction']);
}
