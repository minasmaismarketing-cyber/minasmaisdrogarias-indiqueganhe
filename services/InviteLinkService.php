<?php

declare(strict_types=1);

/**
 * Ponto único de geração de links de convite — Sprint 3.1/3.2.
 *
 * Com `appsflyer.enabled = true`, retorna OneLink (fallback WEB se template incompleto).
 */
final class InviteLinkService
{
    private OneLinkBuilder $oneLinkBuilder;

    public function __construct()
    {
        $this->oneLinkBuilder = new OneLinkBuilder();
    }

    /**
     * Link de convite do usuário (OneLink ou fallback WEB).
     *
     * @param array<string, mixed> $usuario Registro do usuário autenticado
     */
    public function getInviteLink(array $usuario): string
    {
        $startedAt = microtime(true);
        $details = $this->buildInviteLinkDetails($usuario);
        $durationMs = (microtime(true) - $startedAt) * 1000;

        AppsFlyerIntegrationLogger::logOneLinkGenerated(
            $usuario,
            $details['codigo'],
            $details['url'],
            $details['type'],
            $durationMs,
            $details['params'] ?? []
        );

        return $details['url'];
    }

    public function getInviteLinkByCodigo(string $codigo): string
    {
        return $this->getInviteLink(['codigo_indicador' => $codigo]);
    }

    /**
     * @param array<string, mixed> $usuario
     * @return array{url: string, type: string, codigo: string, params?: array<string, string>}
     */
    public function buildInviteLinkDetails(array $usuario): array
    {
        $codigo = strtoupper(trim((string) ($usuario['codigo_indicador'] ?? '')));

        if ($codigo === '') {
            return [
                'url' => $this->buildWebLink(''),
                'type' => 'web_fallback',
                'codigo' => '',
            ];
        }

        if (!AppsFlyerConfig::isEnabled()) {
            return [
                'url' => $this->buildWebLink($codigo),
                'type' => 'web',
                'codigo' => $codigo,
            ];
        }

        $built = $this->oneLinkBuilder->buildInviteLink($usuario);
        $oneLinkUrl = trim($built['url']);

        if ($oneLinkUrl === '') {
            return [
                'url' => $this->buildWebLink($codigo),
                'type' => 'web_fallback',
                'codigo' => $codigo,
                'params' => $built['params'] ?? [],
            ];
        }

        return [
            'url' => $oneLinkUrl,
            'type' => 'onelink',
            'codigo' => $codigo,
            'params' => $built['params'] ?? [],
        ];
    }

    private function buildWebLink(string $codigo): string
    {
        return url('/convite?ref=' . urlencode($codigo));
    }
}
