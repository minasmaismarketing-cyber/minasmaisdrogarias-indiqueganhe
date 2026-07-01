<?php

declare(strict_types=1);

/**
 * Catálogo oficial de integrações externas do Indique e Ganhe.
 *
 * Classificação
 * -------------
 * - ATIVA: utilizada em runtime com fluxo funcional.
 * - PREPARADA: infraestrutura pronta; dependência externa ainda parcial ou manual.
 * - STUB: contrato definido; implementação externa pendente.
 * - LEGADO: mantida por compatibilidade histórica; não usar em novos fluxos.
 *
 * | Integração        | Status    | Ponto de entrada                         |
 * |-------------------|-----------|------------------------------------------|
 * | Cupom interno     | ATIVA     | InternalCouponProvider / CupomService    |
 * | API KOBE          | ATIVA     | ApiIndicacaoController → ReferralService |
 * | AppsFlyer         | PREPARADA | AppsFlyerService / admin AppsFlyer       |
 * | VTEX              | STUB      | VTEXCouponProvider                       |
 * | WhatsApp          | STUB      | WhatsAppNotificationProvider             |
 */
final class Integrations
{
    public const STATUS_ATIVA = 'ativa';
    public const STATUS_PREPARADA = 'preparada';
    public const STATUS_STUB = 'stub';
    public const STATUS_LEGADO = 'legado';

    /** @return array<string, string> */
    public static function catalog(): array
    {
        return [
            'cupom_interno' => self::STATUS_ATIVA,
            'api_kobe' => self::STATUS_ATIVA,
            'appsflyer' => self::STATUS_PREPARADA,
            'vtex' => self::STATUS_STUB,
            'whatsapp' => self::STATUS_STUB,
        ];
    }

    private function __construct()
    {
    }
}
