<?php
declare(strict_types=1);

/*
 * Public entry point for the standalone installer.
 * The actual installer remains in the application root so its private paths
 * (storage, .env, vendor and bootstrap) resolve relative to the package root.
 */
require dirname(__DIR__) . '/setup.php';
