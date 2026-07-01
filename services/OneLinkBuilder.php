<?php

declare(strict_types=1);

/**
 * Montagem de links OneLink AppsFlyer — Sprint 3.1.
 *
 * Parâmetros oficiais AppsFlyer (OneLink):
 * - deep_link_value  → destino principal do deep link (código indicador)
 * - deep_link_sub1   → sub-parâmetro 1 (código indicador / ref)
 * - deep_link_sub2   → sub-parâmetro 2 (ID interno do usuário indicador)
 * - deep_link_sub3   → sub-parâmetro 3 (campanha)
 * - deep_link_sub4   → sub-parâmetro 4 (origem do convite)
 * - pid              → media source (attribution)
 * - c                → nome da campanha (attribution)
 *
 * Apenas estes parâmetros entram na query string (sem duplicatas de chave).
 *
 * @see https://dev.appsflyer.com/hc/docs/onelink-attribution-parameters
 */
final class OneLinkBuilder
{
    private const ORIGEM_CONVITE = 'indique_ganhe';

    /** @var list<string> */
    private const QUERY_PARAM_KEYS = [
        'pid',
        'c',
        'deep_link_value',
        'deep_link_sub1',
        'deep_link_sub2',
        'deep_link_sub3',
        'deep_link_sub4',
    ];

    /** @var array<string, mixed> */
    private array $config;

    public function __construct()
    {
        $this->config = $this->loadConfig();
    }

    /**
     * Gera URL OneLink completa para convite de indicação.
     *
     * @param array<string, mixed> $usuario Registro do usuário (modelo Usuario)
     * @return array{
     *     template: string,
     *     params: array<string, string>,
     *     url: string,
     *     codigo_indicador: string,
     *     usuario_id: string,
     *     campanha: string,
     *     origem: string
     * }
     */
    public function buildInviteLink(array $usuario): array
    {
        $template = trim((string) ($this->config['onelink_template'] ?? ''));
        $codigo = strtoupper(trim((string) ($usuario['codigo_indicador'] ?? '')));
        $usuarioId = trim((string) ($usuario['id'] ?? ''));
        $mediaSource = trim((string) ($this->config['default_media_source'] ?? ''));
        $campanha = trim((string) ($this->config['default_campaign'] ?? ''));
        $origem = self::ORIGEM_CONVITE;

        $params = $this->buildQueryParams($codigo, $usuarioId, $mediaSource, $campanha, $origem);

        return [
            'template' => $template,
            'params' => $params,
            'url' => $this->composeUrl($template, $params),
            'codigo_indicador' => $codigo,
            'usuario_id' => $usuarioId,
            'campanha' => $campanha,
            'origem' => $origem,
        ];
    }

    /** @return array<string, string> */
    private function buildQueryParams(
        string $codigo,
        string $usuarioId,
        string $mediaSource,
        string $campanha,
        string $origem
    ): array {
        $params = [
            'pid' => $mediaSource,
            'c' => $campanha,
            'deep_link_value' => $codigo,
            'deep_link_sub1' => $codigo,
            'deep_link_sub2' => $usuarioId,
            'deep_link_sub3' => $campanha,
            'deep_link_sub4' => $origem,
        ];

        $ordered = [];

        foreach (self::QUERY_PARAM_KEYS as $key) {
            $value = trim($params[$key] ?? '');

            if ($value !== '') {
                $ordered[$key] = $value;
            }
        }

        return $ordered;
    }

    /** @param array<string, string> $params */
    private function composeUrl(string $template, array $params): string
    {
        if ($template === '') {
            return '';
        }

        if ($params === []) {
            return $template;
        }

        $separator = str_contains($template, '?') ? '&' : '?';

        return $template . $separator . http_build_query($params);
    }

    /** @return array<string, mixed> */
    private function loadConfig(): array
    {
        return AppsFlyerConfig::all();
    }
}
