<?php
declare(strict_types=1);

/**
 * CSRF token endpoint.
 *
 * Static HTML cannot bake a per-session token into the page, so the browser
 * asks for one here when a form is submitted (or on first interaction) and the
 * value is placed in the hidden `_token` field.
 *
 * GET  -> {"ok":true,"token":"..."}
 * POST -> rotates the token and returns a new one.
 */

use App\Api\Endpoint;
use App\Core\Config;
use App\Core\Session;

require_once __DIR__ . '/_bootstrap.php';

Endpoint::guard();

$request = Endpoint::request();
$field   = (string) Config::get('security.csrf_field', '_token');

if ($request->isPost()) {
    Session::rotateCsrf();
}

Endpoint::respond([
    'ok'    => true,
    'token' => Session::csrfToken(),
    'field' => $field,
]);