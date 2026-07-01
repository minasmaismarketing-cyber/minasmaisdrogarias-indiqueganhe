<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/core/Env.php';
Env::load(BASE_PATH);

require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Csrf.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Logger.php';
require_once BASE_PATH . '/core/EventLogger.php';
require_once BASE_PATH . '/core/Validator.php';
require_once BASE_PATH . '/core/RateLimiter.php';
require_once BASE_PATH . '/models/Usuario.php';
require_once BASE_PATH . '/models/SenhaRecuperacao.php';
require_once BASE_PATH . '/models/Indicacao.php';
require_once BASE_PATH . '/models/LinkIndicacao.php';
require_once BASE_PATH . '/models/Clique.php';
require_once BASE_PATH . '/models/Notificacao.php';
require_once BASE_PATH . '/models/Campanha.php';
require_once BASE_PATH . '/models/Indicado.php';
require_once BASE_PATH . '/models/Evento.php';
require_once BASE_PATH . '/enums/InstallType.php';
require_once BASE_PATH . '/enums/AppsFlyerStatus.php';
require_once BASE_PATH . '/services/IntegrationResult.php';
require_once BASE_PATH . '/services/Integrations.php';
require_once BASE_PATH . '/services/CouponProviderInterface.php';
require_once BASE_PATH . '/models/HistoricoValidacao.php';
require_once BASE_PATH . '/models/HistoricoCupom.php';
require_once BASE_PATH . '/models/Cupom.php';
require_once BASE_PATH . '/models/ApiLog.php';
require_once BASE_PATH . '/models/AppsFlyerEvent.php';
require_once BASE_PATH . '/models/ValidacaoIndicacao.php';
require_once BASE_PATH . '/repositories/CupomRepository.php';
require_once BASE_PATH . '/repositories/AppsFlyerRepository.php';
require_once BASE_PATH . '/services/InternalCouponProvider.php';
require_once BASE_PATH . '/services/CupomService.php';
require_once BASE_PATH . '/services/AdminMetricsService.php';
require_once BASE_PATH . '/services/AppsFlyerEventData.php';
require_once BASE_PATH . '/services/AppsFlyerConfig.php';
require_once BASE_PATH . '/services/AppsFlyerIntegrationLogger.php';
require_once BASE_PATH . '/services/AppsFlyerDiagnosticService.php';
require_once BASE_PATH . '/services/AppsFlyerService.php';
require_once BASE_PATH . '/services/OneLinkBuilder.php';
require_once BASE_PATH . '/services/InviteLinkService.php';
require_once BASE_PATH . '/core/ReferralService.php';
require_once BASE_PATH . '/components/Header.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/AuthMiddleware.php';
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/config/database.php';

$config = require BASE_PATH . '/config/app.php';

if (!$config['debug']) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

date_default_timezone_set(Env::get('APP_TIMEZONE', 'America/Sao_Paulo'));

set_exception_handler(static function (Throwable $e) use ($config): void {
    http_response_code(500);

    if ($config['debug']) {
        echo '<h1>Erro interno</h1>';
        echo '<pre>' . e($e->getMessage()) . '</pre>';
        echo '<pre>' . e($e->getTraceAsString()) . '</pre>';
        return;
    }

    echo 'Ocorreu um erro. Tente novamente mais tarde.';
});

Session::start();
Auth::attemptFromRememberCookie();
