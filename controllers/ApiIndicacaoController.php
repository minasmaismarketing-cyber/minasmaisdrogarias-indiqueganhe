<?php

declare(strict_types=1);

class ApiIndicacaoController extends Controller
{
    private Usuario $usuarioModel;
    private Indicado $indicadoModel;
    private Campanha $campanhaModel;
    private ApiLog $apiLogModel;
    private EventLogger $eventLogger;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->indicadoModel = new Indicado();
        $this->campanhaModel = new Campanha();
        $this->apiLogModel = new ApiLog();
        $this->eventLogger = new EventLogger();
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
            // Validate required fields
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

            // Sanitize data
            $codigoIndicador = $this->sanitize($payload['codigoIndicador']);
            $cpfIndicado = $this->sanitize($payload['cpfIndicado']);
            $emailIndicado = $this->sanitize($payload['emailIndicado']);
            $telefoneIndicado = $this->sanitize($payload['telefoneIndicado']);
            $customerIdVtex = $this->sanitize($payload['customerIdVtex'] ?? '');
            $appsflyerId = $this->sanitize($payload['appsflyerId'] ?? '');
            $tipoEvento = strtoupper($this->sanitize($payload['tipoEvento']));
            $plataforma = strtoupper($this->sanitize($payload['plataforma']));

            // Validate tipoEvento
            $validTiposEvento = ['INSTALL', 'REENGAGEMENT', 'UNKNOWN'];
            if (!in_array($tipoEvento, $validTiposEvento)) {
                $response = [
                    'success' => false,
                    'message' => 'tipoEvento inválido. Valores aceitos: INSTALL, REENGAGEMENT, UNKNOWN',
                ];
                $statusCode = 400;
                $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                $this->sendJsonResponse($response, $statusCode);
                return;
            }

            // Validate plataforma
            $validPlataformas = ['ANDROID', 'IOS', 'WEB'];
            if (!in_array($plataforma, $validPlataformas)) {
                $response = [
                    'success' => false,
                    'message' => 'plataforma inválida. Valores aceitos: ANDROID, IOS, WEB',
                ];
                $statusCode = 400;
                $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                $this->sendJsonResponse($response, $statusCode);
                return;
            }

            // Check if indicator code exists
            $indicador = $this->usuarioModel->findByCodigo($codigoIndicador);
            if ($indicador === null) {
                $response = [
                    'success' => false,
                    'message' => 'Código do indicador não encontrado',
                ];
                $statusCode = 404;
                $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                $this->sendJsonResponse($response, $statusCode);
                return;
            }

            // Check if campaign is active
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

            // Check if CPF already participated
            $cpfExistente = $this->indicadoModel->findByCpf($cpfIndicado);
            if ($cpfExistente !== null) {
                $response = [
                    'success' => false,
                    'message' => 'CPF já participou do programa',
                ];
                $statusCode = 400;
                $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                $this->sendJsonResponse($response, $statusCode);
                return;
            }

            // Check if CPF is same as indicator
            if ($cpfIndicado === $indicador['cpf']) {
                $response = [
                    'success' => false,
                    'message' => 'CPF do indicado não pode ser igual ao CPF do indicador',
                ];
                $statusCode = 400;
                $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                $this->sendJsonResponse($response, $statusCode);
                return;
            }

            // Check if email is duplicate
            $emailExistente = $this->indicadoModel->findByEmail($emailIndicado);
            if ($emailExistente !== null) {
                $response = [
                    'success' => false,
                    'message' => 'E-mail já cadastrado',
                ];
                $statusCode = 400;
                $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                $this->sendJsonResponse($response, $statusCode);
                return;
            }

            // Check if phone is duplicate
            $telefoneExistente = $this->indicadoModel->findByTelefone($telefoneIndicado);
            if ($telefoneExistente !== null) {
                $response = [
                    'success' => false,
                    'message' => 'Telefone já cadastrado',
                ];
                $statusCode = 400;
                $this->logApiCall($endpoint, $payload, $response, $ip, $statusCode);
                $this->sendJsonResponse($response, $statusCode);
                return;
            }

            // Determine status based on tipoEvento
            $status = Indicado::STATUS_AGUARDANDO_VALIDACAO;
            $motivo = null;

            if ($tipoEvento === 'REENGAGEMENT') {
                $status = Indicado::STATUS_INVALIDADO;
                $motivo = 'APP_JA_EXISTENTE';
            } elseif ($tipoEvento === 'UNKNOWN') {
                $status = Indicado::STATUS_EM_ANALISE;
            }

            // Create indicado record
            $indicadoId = $this->indicadoModel->create([
                'usuario_id' => $indicador['id'],
                'nome' => 'Indicado via API',
                'cpf' => $cpfIndicado,
                'email' => $emailIndicado,
                'telefone' => $telefoneIndicado,
                'status' => $status,
                'motivo' => $motivo,
                'customer_id_vtex' => $customerIdVtex,
                'appsflyer_id' => $appsflyerId,
                'tipo_evento' => $tipoEvento,
                'plataforma' => $plataforma,
            ]);

            // Log event
            $this->eventLogger->logIndicadoCadastrado($indicador['id'], 'Indicado via API');

            // Prepare response
            if ($status === Indicado::STATUS_AGUARDANDO_VALIDACAO) {
                $response = [
                    'success' => true,
                    'status' => $status,
                    'message' => 'Cadastro recebido com sucesso.',
                ];
            } elseif ($status === Indicado::STATUS_INVALIDADO) {
                $response = [
                    'success' => false,
                    'status' => $status,
                    'motivo' => $motivo,
                    'message' => 'Indicação inválida.',
                ];
                $statusCode = 400;
            } else {
                $response = [
                    'success' => true,
                    'status' => $status,
                    'message' => 'Cadastro recebido e em análise.',
                ];
            }

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
        
        // Check for proxy headers
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
