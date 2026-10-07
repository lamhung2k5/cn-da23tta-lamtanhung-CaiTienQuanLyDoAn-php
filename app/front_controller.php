<?php

// A migrated public wrapper supplies this key; GET/POST never select a route.
if (!isset($mvcRoutePath) || !is_string($mvcRoutePath)) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

require_once __DIR__ . '/Router.php';
$mvcRouter = new Router(require __DIR__ . '/../config/routes.php');
$mvcRoute = $mvcRouter->find($mvcRoutePath);

// Unknown routes must not initialize sessions, load legacy code or open PDO.
if ($mvcRoute === null) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

// Special startup paths are added only with a verified module migration.
if (($mvcRoute['startup'] ?? 'standard') !== 'standard'
    || isset($mvcRoute['pre_session'])) {
    throw new LogicException('This route requires unsupported MVC startup.');
}

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/View.php';
$mvcRouter->dispatch($mvcRoute);
