<?php

declare(strict_types=1);

class ApiIndicacaoController extends Controller
{
    private Usuario $usuarioModel;
    private Campanha $campanhaModel;
    private ApiLog $apiLogModel;
    private EventLogger $eventLogger;
    private ReferralService $referralService;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->campanhaModel = new Campanha();
        $this->apiLogModel = new ApiLog();
        $this->eventLogger = new EventLogger();
        $this->referralService = new ReferralService();
    }

    /**
     * Confirmar cadastro do indicado via API
     * POST /api/indicacao/confirmar-cadastro
     */
    public function confirmarCadastro(): void
    {
        $endpoint = '/api/indicacao/confirmar-cadastro';
        $ip = $this->getClientIp();
        $payload = $this->getJsonPayload();
        $response = null;
        $statusCode = 200;

        try {
            $requiredFields = ['codigoIndicador', 'cpfIndicado', 'emailIndicado', 'telefoneIndicado', 'tipoEvento', 'plataforma'];
            foreach ($requiredFields as $field) {
                if (empty($payload[$field])) {
                    $response = [
                        'success' => false,
                        'message' => "Campo obrigatório: {$field}",
                    ];
                    $statusCode = 400;
                    $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                    $this->sendJsonResponse($response, $statusCode);
                    return;
                }
            }

            $codigoIndicador = $this->sanitize($payload['codigoIndicador']);
            $cpfIndicado = Validator::onlyDigits($this->sanitize($payload['cpfIndicado']));
            $emailIndicado = $this->sanitize($payload['emailIndicado']);
            $telefoneIndicado = Validator::onlyDigits($this->sanitize($payload['telefoneIndicado']));
            $tipoEvento = strtoupper($this->sanitize($payload['tipoEvento']));
            $plataforma = strtoupper($this->sanitize($payload['plataforma']));

            $validTiposEvento = ['INSTALL', 'REENGAGEMENT', 'UNKNOWN'];
            if (!in_array($tipoEvento, $validTiposEvento, true)) {
                $response = [
                    'success' => false,
                    'message' => 'tipoEvento inválido. Valores aceitos: INSTALL, REENGAGEMENT, UNKNOWN',
                ];
                $statusCode = 400;
                $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                $this->sendJsonResponse($response, $statusCode);
                return;
            }

            $validPlataformas = ['ANDROID', 'IOS', 'WEB'];
            if (!in_array($plataforma, $validPlataformas, true)) {
                $response = [
                    'success' => false,
                    'message' => 'plataforma inválida. Valores aceitos: ANDROID, IOS, WEB',
                ];
                $statusCode = 400;
                $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                $this->sendJsonResponse($response, $statusCode);
                return;
            }

            $campanhaAtiva = $this->campanhaModel->findActive();
            if ($campanhaAtiva === null) {
                $response = [
                    'success' => false,
                    'message' => 'Nenhuma campanha ativa encontrada',
                ];
                $statusCode = 400;
                $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                $this->sendJsonResponse($response, $statusCode);
                return;
            }

            $result = $this->referralService->registerApiIndication(
                $codigoIndicador,
                $cpfIndicado,
                $emailIndicado,
                $telefoneIndicado,
                $tipoEvento
            );

            $statusCode = (int) ($result['status_code'] ?? 200);
            unset($result['status_code']);

            if ($result['success'] ?? false) {
                $indicador = $this->usuarioModel->findByCodigo($codigoIndicador);
                if ($indicador !== null) {
                    $this->eventLogger->logIndicadoCadastrado((int) $indicador['id'], 'Indicado via API');
                }
            }

            $response = $result;
            $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
            $this->sendJsonResponse($response, $statusCode);
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Erro interno do servidor.',
            ];
            $statusCode = 500;
            $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
            $this->sendJsonResponse($response, $statusCode);
        }
    }

    private function getJsonPayload(): array
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $data ?? [];
    }

    private function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    private function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }

        return trim($ip);
    }

    private function logApiCall(string $endpoint, array $payload, ?array $response, string $ip, int $statusCode): void
    {
        $this->apiLogModel->create([
            'endpoint' => $endpoint,
            'payload' => $payload,
            'response' => $response,
            'ip' => $ip,
            'status_code' => $statusCode,
        ]);
    }

    private function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
