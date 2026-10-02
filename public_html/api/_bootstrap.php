<?php
declare(strict_types=1);

/**
 * Bootstrap loader for the JSON endpoints in this directory.
 *
 * These scripts are reached directly by the browser (the .htaccess serves real
 * files as-is), so unlike public_html/index.php they have to load the
 * application themselves before any App\ class can be used.
 *
 * The search walks upwards because the project is deployed in two shapes: with
 * app/ beside public_html/, and with app/ inside public_html/.
 */

$__sh_dir = __DIR__;

while (true) {
    if (is_file($__sh_dir . '/app/bootstrap.php')) {
        require_once $__sh_dir . '/app/bootstrap.php';

        return;
    }

    $__sh_parent = rtrim(str_replace('\\', '/', dirname($__sh_dir)), '/');
    if ($__sh_parent === $__sh_dir || $__sh_parent === '') {
        break;
    }

    $__sh_dir = $__sh_parent;
}

http_response_code(500);
header('Content-Type: application/json; charset=UTF-8');
echo '{"ok":false,"error":"Application bootstrap could not be located"}';
