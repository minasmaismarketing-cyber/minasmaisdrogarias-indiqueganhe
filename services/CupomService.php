<?php

declare(strict_types=1);

class CupomService
{
    public const IMPORT_MAX_BYTES = 2_097_152; // 2 MB
    public const IMPORT_MAX_CODE_LEN = 64;
    public const INDICADOR_DESCONTO = 10.0;

    private CupomRepository $repository;
    private HistoricoCupom $historicoModel;
    private Campanha $campanhaModel;
    private EventLogger $eventLogger;
    private ?CouponProviderInterface $couponProvider = null;

    public function __construct()
    {
        $this->repository = new CupomRepository();
        $this->historicoModel = new HistoricoCupom();
        $this->campanhaModel = new Campanha();
        $this->eventLogger = new EventLogger();
    }

    public function setCouponProvider(CouponProviderInterface $provider): void
    {
        $this->couponProvider = $provider;
    }

    /**
     * Atribui um cupom do estoque da campanha ao indicador (transacional + FOR UPDATE).
     *
     * @throws RuntimeException estoque esgotado ou falha de atribuição
     */
    public function assignFromPoolForIndicacao(
        int $indicacaoId,
        int $usuarioId,
        ?int $campanhaId = null,
        ?string $adminEmail = null
    ): int {
        if ($this->repository->cupomExisteParaIndicacao($indicacaoId)) {
            $existing = $this->repository->findByIndicacao($indicacaoId);

            return (int) ($existing['id'] ?? 0);
        }

        if ($this->repository->indicadorJaPossuiCupomAtribuido($usuarioId)) {
            throw new RuntimeException('Indicador já possui benefício liberado nesta campanha.');
        }

        if ($campanhaId === null || $campanhaId <= 0) {
            $campanhaId = $this->resolveCampanhaIdForIndicacao($indicacaoId);
        }

        $db = Database::getConnection();
        $ownTransaction = !$db->inTransaction();

        if ($ownTransaction) {
            $db->beginTransaction();
        }

        try {
            $cupom = $this->repository->findDisponivelForUpdate($campanhaId);
            if ($cupom === null) {
                throw new RuntimeException('Não há cupons disponíveis para esta campanha.');
            }

            $cupomId = (int) $cupom['id'];
            $this->repository->assignToIndicacao($cupomId, $usuarioId, $indicacaoId);

            $this->historicoModel->create([
                'cupom_id' => $cupomId,
                'status_anterior' => Cupom::STATUS_DISPONIVEL,
                'status_novo' => Cupom::STATUS_DISPONIVEL,
                'descricao' => 'Cupom atribuído ao indicador (10% OFF)',
                'usuario_admin' => $adminEmail,
            ]);

            $this->eventLogger->logCupomCriado($usuarioId, $cupomId, (string) $cupom['codigo']);

            if ($ownTransaction) {
                $db->commit();
            }

            return $cupomId;
        } catch (Throwable $e) {
            if ($ownTransaction && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /** Compatibilidade: atribui do estoque em vez de gerar código interno. */
    public function generateForIndicacao(int $indicacaoId, int $usuarioId): ?int
    {
        return $this->assignFromPoolForIndicacao($indicacaoId, $usuarioId);
    }

    /**
     * @param array{name?: string, tmp_name?: string, error?: int, size?: int, type?: string} $file
     * @return array{
     *   total_linhas: int,
     *   validos: list<string>,
     *   duplicados_arquivo: list<string>,
     *   existentes_banco: list<string>,
     *   invalidos: list<string>,
     *   a_importar: list<string>,
     *   resumo: array<string, int>
     * }
     */
    public function validateCsvImport(array $file, int $campanhaId): array
    {
        if ($campanhaId <= 0 || $this->campanhaModel->findById($campanhaId) === null) {
            throw new InvalidArgumentException('Campanha inválida.');
        }

        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Falha no upload do arquivo.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::IMPORT_MAX_BYTES) {
            throw new InvalidArgumentException('Arquivo inválido ou maior que 2 MB.');
        }

        $name = (string) ($file['name'] ?? '');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            throw new InvalidArgumentException('Aceito apenas arquivo .csv');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new InvalidArgumentException('Upload inválido.');
        }

        $mime = (string) ($file['type'] ?? '');
        $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel', ''];
        if ($mime !== '' && !in_array(strtolower($mime), $allowedMimes, true)) {
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected = $finfo ? (string) finfo_file($finfo, $tmp) : '';
                if ($finfo) {
                    finfo_close($finfo);
                }
                if ($detected !== '' && !in_array($detected, ['text/csv', 'text/plain', 'application/csv'], true)) {
                    throw new InvalidArgumentException('Tipo de arquivo inválido. Envie um CSV.');
                }
            }
        }

        $raw = file_get_contents($tmp);
        if ($raw === false) {
            throw new InvalidArgumentException('Não foi possível ler o arquivo.');
        }

        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        $delimiter = $this->detectCsvDelimiter($raw);
        $lines = preg_split("/\r\n|\n|\r/", $raw) ?: [];
        $totalLinhas = 0;
        $seen = [];
        $validos = [];
        $duplicadosArquivo = [];
        $invalidos = [];
        $headerAliases = ['codigo', 'cupom', 'code'];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $totalLinhas++;
            $parts = str_getcsv($line, $delimiter);
            $codigo = trim((string) ($parts[0] ?? ''));

            if ($index === 0 && in_array(strtolower($codigo), $headerAliases, true)) {
                $totalLinhas--;
                continue;
            }

            if ($codigo === '') {
                continue;
            }

            if (!$this->isSafeCouponCode($codigo)) {
                $invalidos[] = $codigo;
                continue;
            }

            $key = mb_strtolower($codigo);
            if (isset($seen[$key])) {
                $duplicadosArquivo[] = $codigo;
                continue;
            }

            $seen[$key] = true;
            $validos[] = $codigo;
        }

        $existentes = $this->repository->filterExistingCodigos($validos);
        $existentesSet = [];
        foreach ($existentes as $codigo) {
            $existentesSet[mb_strtolower($codigo)] = true;
        }

        $aImportar = [];
        foreach ($validos as $codigo) {
            if (!isset($existentesSet[mb_strtolower($codigo)])) {
                $aImportar[] = $codigo;
            }
        }

        return [
            'total_linhas' => $totalLinhas,
            'validos' => $validos,
            'duplicados_arquivo' => array_values(array_unique($duplicadosArquivo)),
            'existentes_banco' => $existentes,
            'invalidos' => array_values(array_unique($invalidos)),
            'a_importar' => $aImportar,
            'resumo' => [
                'total_linhas' => $totalLinhas,
                'validos' => count($validos),
                'duplicados_arquivo' => count(array_unique($duplicadosArquivo)),
                'existentes_banco' => count($existentes),
                'invalidos' => count(array_unique($invalidos)),
                'a_importar' => count($aImportar),
            ],
        ];
    }

    /**
     * @param list<string> $codigos
     * @return array{imported: int, skipped: int}
     */
    public function confirmCsvImport(int $campanhaId, array $codigos, ?string $adminEmail = null): array
    {
        unset($adminEmail);

        if ($campanhaId <= 0 || $this->campanhaModel->findById($campanhaId) === null) {
            throw new InvalidArgumentException('Campanha inválida.');
        }

        $this->assertEstoqueSchemaReady();

        $recebidos = count($codigos);
        $safe = [];
        foreach ($codigos as $codigo) {
            $codigo = trim((string) $codigo);
            if ($this->isSafeCouponCode($codigo)) {
                $safe[] = $codigo;
            }
        }

        $safe = array_values(array_unique($safe));
        if ($safe === []) {
            Logger::info('Cupom import confirm', [
                'endpoint' => '/admin/cupons/importar/confirmar',
                'campanha_id' => $campanhaId,
                'quantidade_recebida' => $recebidos,
                'quantidade_inserida' => 0,
                'quantidade_ignorada' => $recebidos,
            ]);

            return ['imported' => 0, 'skipped' => $recebidos];
        }

        $existentes = $this->repository->filterExistingCodigos($safe);
        $existentesSet = [];
        foreach ($existentes as $codigo) {
            $existentesSet[mb_strtolower($codigo)] = true;
        }

        $toInsert = [];
        foreach ($safe as $codigo) {
            if (!isset($existentesSet[mb_strtolower($codigo)])) {
                $toInsert[] = $codigo;
            }
        }

        if ($toInsert === []) {
            Logger::info('Cupom import confirm', [
                'endpoint' => '/admin/cupons/importar/confirmar',
                'campanha_id' => $campanhaId,
                'quantidade_recebida' => $recebidos,
                'quantidade_inserida' => 0,
                'quantidade_ignorada' => count($safe),
            ]);

            return ['imported' => 0, 'skipped' => count($safe)];
        }

        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $imported = 0;
            foreach (array_chunk($toInsert, 200) as $chunk) {
                $imported += $this->repository->bulkInsertEstoque(
                    $campanhaId,
                    $chunk,
                    Cupom::TIPO_PERCENTUAL,
                    self::INDICADOR_DESCONTO,
                    'IMPORT_CSV'
                );
            }
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Logger::error('Falha na importação CSV de cupons', [
                'endpoint' => '/admin/cupons/importar/confirmar',
                'campanha_id' => $campanhaId,
                'quantidade_recebida' => $recebidos,
                'quantidade_inserida' => 0,
                'quantidade_ignorada' => 0,
                'exception' => $e->getMessage(),
            ]);
            throw new RuntimeException($this->humanizeImportException($e));
        }

        Logger::info('Cupom import confirm', [
            'endpoint' => '/admin/cupons/importar/confirmar',
            'campanha_id' => $campanhaId,
            'quantidade_recebida' => $recebidos,
            'quantidade_inserida' => $imported,
            'quantidade_ignorada' => count($safe) - count($toInsert),
        ]);

        return [
            'imported' => $imported,
            'skipped' => count($safe) - count($toInsert),
        ];
    }

    /** Garante schema da Sprint 4.4 antes do INSERT de estoque. */
    private function assertEstoqueSchemaReady(): void
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT COLUMN_NAME, IS_NULLABLE, CHARACTER_MAXIMUM_LENGTH
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'cupons'
               AND COLUMN_NAME IN ('codigo', 'usuario_id', 'indicacao_id')"
        );
        $cols = [];
        foreach ($stmt->fetchAll() as $row) {
            $cols[(string) $row['COLUMN_NAME']] = $row;
        }

        if ($cols === []) {
            throw new RuntimeException('Tabela cupons não encontrada.');
        }

        $usuarioNullable = (($cols['usuario_id']['IS_NULLABLE'] ?? 'NO') === 'YES');
        $indicacaoNullable = (($cols['indicacao_id']['IS_NULLABLE'] ?? 'NO') === 'YES');
        $codigoLen = (int) ($cols['codigo']['CHARACTER_MAXIMUM_LENGTH'] ?? 0);

        if (!$usuarioNullable || !$indicacaoNullable || $codigoLen < 64) {
            throw new RuntimeException(
                'Estrutura do banco incompatível com estoque de cupons. Execute database/migration_sprint_4_4_cupons_estoque.sql na Hostinger.'
            );
        }
    }

    private function humanizeImportException(Throwable $e): string
    {
        $message = $e->getMessage();

        if (
            str_contains($message, "doesn't have a default value")
            || str_contains($message, 'cannot be null')
            || str_contains($message, 'atribuido_em')
            || str_contains($message, 'usuario_id')
            || str_contains($message, 'indicacao_id')
        ) {
            return 'Estrutura do banco incompatível com estoque de cupons. Execute database/migration_sprint_4_4_cupons_estoque.sql na Hostinger.';
        }

        if (str_contains($message, 'Duplicate') || str_contains($message, '1062')) {
            return 'Alguns códigos já existem. Revalide o arquivo e tente novamente.';
        }

        return 'Falha ao importar cupons. Nenhuma alteração foi aplicada.';
    }

    public function deleteDisponivel(int $cupomId): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if (
            $cupom['status'] !== Cupom::STATUS_DISPONIVEL
            || $cupom['usuario_id'] !== null
            || $cupom['indicacao_id'] !== null
        ) {
            return false;
        }

        return $this->repository->deleteDisponivel($cupomId);
    }

    /** @param list<int> $ids */
    public function deleteDisponiveisBatch(array $ids): int
    {
        return $this->repository->deleteDisponiveisBatch($ids);
    }

    private function resolveCampanhaIdForIndicacao(int $indicacaoId): int
    {
        $indicacao = (new Indicacao())->findById($indicacaoId);
        if ($indicacao === null) {
            throw new RuntimeException('Indicação não encontrada');
        }

        $campanha = $this->campanhaModel->findForIndicacao($indicacao);
        if ($campanha === null) {
            throw new RuntimeException('Nenhuma campanha vinculada à indicação.');
        }

        return (int) $campanha['id'];
    }

    /** Detecta `,` ou `;` (Excel pt-BR vs Google Sheets / Excel UTF-8). */
    private function detectCsvDelimiter(string $raw): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $raw) ?: [];
        $commas = 0;
        $semicolons = 0;
        $checked = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $commas += substr_count($line, ',');
            $semicolons += substr_count($line, ';');
            $checked++;

            if ($checked >= 20) {
                break;
            }
        }

        return $semicolons > $commas ? ';' : ',';
    }

    private function isSafeCouponCode(string $codigo): bool
    {
        if ($codigo === '' || mb_strlen($codigo) > self::IMPORT_MAX_CODE_LEN) {
            return false;
        }

        $first = $codigo[0];
        if (in_array($first, ['=', '+', '-', '@'], true)) {
            return false;
        }

        return (bool) preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]*$/', $codigo);
    }

    public function reserve(int $cupomId, ?string $adminEmail = null): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if ($cupom['status'] !== Cupom::STATUS_DISPONIVEL || $cupom['usuario_id'] === null) {
            return false;
        }

        $statusAnterior = $cupom['status'];
        $this->repository->updateStatus($cupomId, Cupom::STATUS_RESERVADO);

        $this->historicoModel->create([
            'cupom_id' => $cupomId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Cupom::STATUS_RESERVADO,
            'descricao' => 'Cupom reservado',
            'usuario_admin' => $adminEmail,
        ]);

        $this->eventLogger->logCupomReservado((int) $cupom['usuario_id'], $cupomId, $cupom['codigo']);

        return true;
    }

    public function use(int $cupomId, ?string $adminEmail = null): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if (!in_array($cupom['status'], [Cupom::STATUS_DISPONIVEL, Cupom::STATUS_RESERVADO], true)) {
            return false;
        }

        if ($cupom['usuario_id'] === null) {
            return false;
        }

        $statusAnterior = $cupom['status'];
        $this->repository->updateStatus($cupomId, Cupom::STATUS_UTILIZADO);

        $this->historicoModel->create([
            'cupom_id' => $cupomId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Cupom::STATUS_UTILIZADO,
            'descricao' => 'Cupom utilizado',
            'usuario_admin' => $adminEmail,
        ]);

        $this->eventLogger->logCupomUtilizado((int) $cupom['usuario_id'], $cupomId, $cupom['codigo']);

        return true;
    }

    public function cancel(int $cupomId, ?string $motivo = null, ?string $adminEmail = null): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if ($cupom['status'] === Cupom::STATUS_UTILIZADO) {
            return false;
        }

        $statusAnterior = $cupom['status'];
        $this->repository->updateStatus($cupomId, Cupom::STATUS_CANCELADO);

        $this->historicoModel->create([
            'cupom_id' => $cupomId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Cupom::STATUS_CANCELADO,
            'descricao' => $motivo ?? 'Cupom cancelado',
            'usuario_admin' => $adminEmail,
        ]);

        if ($cupom['usuario_id'] !== null) {
            $this->eventLogger->logCupomCancelado((int) $cupom['usuario_id'], $cupomId, $cupom['codigo']);
        }

        return true;
    }

    public function expire(int $cupomId, ?string $adminEmail = null): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if ($cupom['status'] !== Cupom::STATUS_DISPONIVEL) {
            return false;
        }

        $statusAnterior = $cupom['status'];
        $this->repository->updateStatus($cupomId, Cupom::STATUS_EXPIRADO);

        $this->historicoModel->create([
            'cupom_id' => $cupomId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Cupom::STATUS_EXPIRADO,
            'descricao' => 'Cupom expirado',
            'usuario_admin' => $adminEmail,
        ]);

        if ($cupom['usuario_id'] !== null) {
            $this->eventLogger->logCupomExpirado((int) $cupom['usuario_id'], $cupomId, $cupom['codigo']);
        }

        return true;
    }

    public function reactivate(int $cupomId, ?string $adminEmail = null): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if (!in_array($cupom['status'], [Cupom::STATUS_CANCELADO, Cupom::STATUS_EXPIRADO], true)) {
            return false;
        }

        $statusAnterior = $cupom['status'];
        $this->repository->updateStatus($cupomId, Cupom::STATUS_DISPONIVEL);

        $this->historicoModel->create([
            'cupom_id' => $cupomId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Cupom::STATUS_DISPONIVEL,
            'descricao' => 'Cupom reativado',
            'usuario_admin' => $adminEmail,
        ]);

        return true;
    }

    public function checkExpiredCoupons(): int
    {
        return $this->repository->checkExpirados();
    }

    public function getHistory(int $cupomId): array
    {
        return $this->repository->getHistorico($cupomId);
    }
}
