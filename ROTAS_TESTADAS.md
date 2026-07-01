# ROTAS_TESTADAS.md — Fase 1.2

**Data:** 01/07/2026 12:34

**Resumo:** 38 PASS, 0 FAIL

| Método | Rota | Status | Controller | Action | Middleware | Params | Esperado |
|--------|------|--------|------------|--------|------------|--------|----------|
| GET | `/` | PASS | HomeController | index | - | - | HomeController@index |
| GET | `/login` | PASS | AuthController | loginForm | - | - | AuthController@loginForm |
| POST | `/login` | PASS | AuthController | login | - | - | AuthController@login |
| GET | `/cadastro` | PASS | AuthController | registerForm | - | - | AuthController@registerForm |
| POST | `/cadastro` | PASS | AuthController | register | - | - | AuthController@register |
| GET | `/dashboard` | PASS | DashboardController | index | - | - | DashboardController@index |
| GET | `/perfil` | PASS | ProfileController | index | - | - | ProfileController@index |
| GET | `/indicacoes` | PASS | IndicacoesController | index | - | - | IndicacoesController@index |
| GET | `/convite` | PASS | ConviteController | index | - | - | ConviteController@index |
| POST | `/convite/participar` | PASS | ConviteController | participar | - | - | ConviteController@participar |
| GET | `/admin` | PASS | AdminController | index | - | - | AdminController@index |
| GET | `/admin/usuarios` | PASS | AdminController | usuarios | - | - | AdminController@usuarios |
| GET | `/admin/usuarios/5` | PASS | AdminController | showUsuario | - | {"id":"5"} | AdminController@showUsuario |
| GET | `/admin/campanhas` | PASS | AdminController | campanhas | - | - | AdminController@campanhas |
| GET | `/admin/campanhas/criar` | PASS | CampanhasController | create | - | - | CampanhasController@create |
| GET | `/admin/validacoes` | PASS | ValidacaoController | adminIndex | - | - | ValidacaoController@adminIndex |
| GET | `/admin/cupons` | PASS | CuponsController | adminIndex | - | - | CuponsController@adminIndex |
| GET | `/admin/appsflyer` | PASS | AppsFlyerController | adminIndex | - | - | AppsFlyerController@adminIndex |
| GET | `/admin/validacoes/12` | PASS | ValidacaoController | show | - | {"id":"12"} | ValidacaoController@show |
| GET | `/admin/cupons/7` | PASS | CuponsController | show | - | {"id":"7"} | CuponsController@show |
| GET | `/admin/appsflyer/3` | PASS | AppsFlyerController | show | - | {"id":"3"} | AppsFlyerController@show |
| GET | `/admin/campanhas/editar/5` | PASS | CampanhasController | edit | - | {"id":"5"} | CampanhasController@edit |
| POST | `/admin/campanhas/editar/5` | PASS | CampanhasController | edit | - | {"id":"5"} | CampanhasController@edit |
| POST | `/admin/campanhas/ativar/1` | PASS | CampanhasController | activate | - | {"id":"1"} | CampanhasController@activate |
| POST | `/admin/campanhas/desativar/2` | PASS | CampanhasController | deactivate | - | {"id":"2"} | CampanhasController@deactivate |
| POST | `/admin/campanhas/duplicar/4` | PASS | CampanhasController | duplicate | - | {"id":"4"} | CampanhasController@duplicate |
| POST | `/admin/campanhas/excluir/9` | PASS | CampanhasController | delete | - | {"id":"9"} | CampanhasController@delete |
| POST | `/api/indicacao/confirmar-cadastro` | PASS | ApiIndicacaoController | confirmarCadastro | ApiAuth | - | ApiIndicacaoController@confirmarCadastro |
| GET | `/notificacoes` | PASS | NotificacoesController | index | - | - | NotificacoesController@index |
| GET | `/cadastro-indicado` | PASS | IndicadosController | cadastro | - | - | IndicadosController@cadastro |
| GET | `/finalizado` | PASS | IndicadosController | finalizado | - | - | IndicadosController@finalizado |
| GET | `/admin/cupons/abc` | PASS | - | - | - | - | - |
| GET | `/rota-inexistente` | PASS | - | - | - | - | - |
| PATTERN | `/campanha/minhas-mais` | PASS | RoutePattern | /campanha/{slug} | - | {"slug":"minhas-mais"} | {"slug":"minhas-mais"} |
| PATTERN | `/ref/MM48271` | PASS | RoutePattern | /ref/{codigo} | - | {"codigo":"MM48271"} | {"codigo":"MM48271"} |
| PATTERN | `/evt/550e8400-e29b-41d4-a716-446655440000` | PASS | RoutePattern | /evt/{uuid} | - | {"uuid":"550e8400-e29b-41d4-a716-446655440000"} | {"uuid":"550e8400-e29b-41d4-a716-446655440000"} |
| PUT | `/recurso/10` | PASS | HomeController | index | - | {"id":"10"} | HomeController@index |
| DELETE | `/recurso/10` | PASS | HomeController | index | - | {"id":"10"} | HomeController@index |
