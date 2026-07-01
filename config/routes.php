<?php

declare(strict_types=1);

/** @var Router $router */

$router->get('/', 'HomeController', 'index');
$router->get('/404', 'ErrorController', 'notFound');

$router->get('/cadastro', 'AuthController', 'registerForm');
$router->post('/cadastro', 'AuthController', 'register');
$router->get('/login', 'AuthController', 'loginForm');
$router->post('/login', 'AuthController', 'login');
$router->post('/logout', 'AuthController', 'logout');
$router->get('/esqueci-senha', 'AuthController', 'forgotForm');
$router->post('/esqueci-senha', 'AuthController', 'forgot');
$router->get('/redefinir-senha', 'AuthController', 'resetForm');
$router->post('/redefinir-senha', 'AuthController', 'reset');

$router->get('/convite', 'ConviteController', 'index');
$router->post('/convite/participar', 'ConviteController', 'participar');

$router->get('/dashboard', 'DashboardController', 'index');
$router->post('/dashboard/compartilhar', 'DashboardController', 'share');

$router->get('/perfil', 'ProfileController', 'index');
$router->post('/perfil', 'ProfileController', 'update');
$router->post('/perfil/senha', 'ProfileController', 'changePassword');
$router->post('/perfil/excluir', 'ProfileController', 'delete');

$router->get('/indicacoes', 'IndicacoesController', 'index');

$router->get('/meus-premios', 'PremiosController', 'index');

$router->get('/configuracoes', 'ConfiguracoesController', 'index');
$router->post('/configuracoes', 'ConfiguracoesController', 'update');

// Admin routes
$router->get('/admin', 'AdminController', 'index');
$router->get('/admin/usuarios', 'AdminController', 'usuarios');
$router->get('/admin/usuarios/{id}', 'AdminController', 'showUsuario');
$router->post('/admin/usuarios/bloquear', 'AdminController', 'blockUsuario');
$router->post('/admin/usuarios/desbloquear', 'AdminController', 'unblockUsuario');
$router->get('/admin/campanhas', 'AdminController', 'campanhas');
$router->get('/admin/campanhas/{id}', 'CampanhasController', 'show');
$router->get('/admin/indicacoes', 'AdminController', 'indicacoes');
$router->get('/admin/indicacoes/{id}', 'AdminController', 'showIndicacao');
$router->get('/admin/configuracoes', 'AdminController', 'configuracoes');
// Validação de indicações (Etapa 13 - Motor de Validação)
$router->get('/admin/validacoes', 'ValidacaoController', 'adminIndex');
$router->get('/admin/validacoes/{id}', 'ValidacaoController', 'show');
$router->post('/admin/validacoes/iniciar', 'ValidacaoController', 'start');
$router->post('/admin/validacoes/aprovar', 'ValidacaoController', 'approve');
$router->post('/admin/validacoes/rejeitar', 'ValidacaoController', 'reject');
$router->post('/admin/validacoes/cancelar', 'ValidacaoController', 'cancel');

// Cupons (Etapa 14 - Sistema de Cupons)
$router->get('/admin/cupons', 'CuponsController', 'adminIndex');
$router->get('/admin/cupons/{id}', 'CuponsController', 'show');
$router->post('/admin/cupons/cancelar', 'CuponsController', 'cancel');
$router->post('/admin/cupons/expirar', 'CuponsController', 'expire');
$router->post('/admin/cupons/reativar', 'CuponsController', 'reactivate');
$router->get('/meus-cupons', 'CuponsController', 'userIndex');

// API (Etapa 15 - API de Confirmação do Indicado)
$router->post('/api/indicacao/confirmar-cadastro', 'ApiIndicacaoController', 'confirmarCadastro', 'ApiAuth');

// AppsFlyer (Etapa 16 - Preparação para Integração AppsFlyer)
$router->get('/admin/appsflyer', 'AppsFlyerController', 'adminIndex');
$router->get('/admin/appsflyer/{id}', 'AppsFlyerController', 'show');
$router->post('/admin/appsflyer/validar', 'AppsFlyerController', 'validate');
$router->post('/admin/appsflyer/rejeitar', 'AppsFlyerController', 'reject');

// Campanhas routes
$router->get('/admin/campanhas/criar', 'CampanhasController', 'create');
$router->post('/admin/campanhas/criar', 'CampanhasController', 'create');
$router->get('/admin/campanhas/editar/{id}', 'CampanhasController', 'edit');
$router->post('/admin/campanhas/editar/{id}', 'CampanhasController', 'edit');
$router->post('/admin/campanhas/ativar/{id}', 'CampanhasController', 'activate');
$router->post('/admin/campanhas/desativar/{id}', 'CampanhasController', 'deactivate');
$router->post('/admin/campanhas/duplicar/{id}', 'CampanhasController', 'duplicate');
$router->post('/admin/campanhas/excluir/{id}', 'CampanhasController', 'delete');

// Notifications
$router->get('/notificacoes', 'NotificacoesController', 'index');

// Indicados routes (fluxo alternativo /cadastro-indicado)
$router->get('/cadastro-indicado', 'IndicadosController', 'cadastro');
$router->post('/salvar-indicado', 'IndicadosController', 'salvar');
$router->get('/finalizado', 'IndicadosController', 'finalizado');
