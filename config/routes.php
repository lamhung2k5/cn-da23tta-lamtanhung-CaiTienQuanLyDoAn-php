<?php
/**
 * Register an endpoint only when its legacy script is migrated to a wrapper.
 * Route keys are exact application-relative paths, including the .php suffix.
 */
return [
    '/api/notifications.php' => [
        'file' => __DIR__ . '/../app/controllers/NotificationsController.php',
        'controller' => 'NotificationsController',
        'action' => 'api',
    ],
];
