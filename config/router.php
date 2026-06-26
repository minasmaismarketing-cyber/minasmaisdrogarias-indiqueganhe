<?php

declare(strict_types=1);

require_once BASE_PATH . '/core/RoutePattern.php';
require_once BASE_PATH . '/core/Router.php';

$router = new Router();

require BASE_PATH . '/config/routes.php';

return $router;
