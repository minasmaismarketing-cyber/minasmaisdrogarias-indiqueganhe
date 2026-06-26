<?php

declare(strict_types=1);

class ReferralService
{
    public const SESSION_REF = 'indicacao_ref';

    public function __construct(
        private Usuario $usuarioModel = new Usuario(),
        private Indicacao $indicacaoModel = new Indicacao()
    ) {
    }

    public function inviteLink(string $codigo): string
    {
        return url('/convite?ref=' . urlencode($codigo));
    }

    /** @return array{valid: bool, user: array<string, mixed>|null, error: string} */
    public function validateCode(string $codigo, ?int $loggedUserId = null): array
    {
        $codigo = strtoupper(trim($codigo));

        if ($codigo === '' || !preg_match('/^MM[A-Z0-9]{6}$/', $codigo)) {
            return ['valid' => false, 'user' => null, 'error' => 'Código de indicação inválido.'];
        }

        $referrer = $this->usuarioModel->findByCodigo($codigo);

        if ($referrer === null) {
            return ['valid' => false, 'user' => null, 'error' => 'Código de indicação inválido.'];
        }

        if ($loggedUserId !== null && (int) $referrer['id'] === $loggedUserId) {
            return ['valid' => false, 'user' => null, 'error' => 'Você não pode usar seu próprio código.'];
        }

        return ['valid' => true, 'user' => $referrer, 'error' => ''];
    }

    public function storeRefInSession(string $codigo): void
    {
        Session::set(self::SESSION_REF, strtoupper(trim($codigo)));
    }

    public function getRefFromSession(): ?string
    {
        $ref = Session::get(self::SESSION_REF);

        return is_string($ref) && $ref !== '' ? $ref : null;
    }

    public function clearRefFromSession(): void
    {
        Session::remove(self::SESSION_REF);
    }

    public function logShare(int $userId, string $codigo): void
    {
        $this->indicacaoModel->logShare($userId, strtoupper(trim($codigo)));
        Logger::info('Referral share logged', ['user_id' => $userId, 'codigo' => $codigo]);
    }

    public function handleLinkAccess(string $codigo): array
    {
        $validation = $this->validateCode($codigo, Auth::id());

        if (!$validation['valid'] || $validation['user'] === null) {
            return $validation;
        }

        $this->storeRefInSession($codigo);
        $this->indicacaoModel->registerLinkAccess((int) $validation['user']['id'], $codigo);

        Logger::info('Referral link accessed', [
            'codigo' => $codigo,
            'referrer_id' => (int) $validation['user']['id'],
        ]);

        return $validation;
    }

    public function attachRegistrationToReferral(
        int $newUserId,
        string $nome,
        string $telefone,
        string $cpf
    ): void {
        $ref = $this->getRefFromSession();

        if ($ref === null) {
            return;
        }

        $validation = $this->validateCode($ref);

        if (!$validation['valid'] || $validation['user'] === null) {
            $this->clearRefFromSession();
            return;
        }

        $referrer = $validation['user'];

        if ((string) $referrer['cpf'] === $cpf) {
            $this->clearRefFromSession();
            return;
        }

        if ($this->indicacaoModel->phoneAlreadyIndicated($telefone)) {
            $this->clearRefFromSession();
            return;
        }

        if ($this->indicacaoModel->findActiveByReferrerAndPhone((int) $referrer['id'], $telefone) !== null) {
            $this->clearRefFromSession();
            return;
        }

        $this->indicacaoModel->completeRegistration(
            (int) $referrer['id'],
            $ref,
            $nome,
            $telefone
        );

        $this->clearRefFromSession();

        Logger::info('Referral registration linked', [
            'referrer_id' => (int) $referrer['id'],
            'new_user_id' => $newUserId,
            'codigo' => $ref,
        ]);
    }
}
