<?php

declare(strict_types=1);

/**
 * Contrato de providers de cupom.
 *
 * Implementações ativas: InternalCouponProvider.
 * Stub preparado: VTEXCouponProvider.
 */
interface CouponProviderInterface
{
    /**
     * Gera código de cupom.
     *
     * Sucesso: dados['codigo'] contém o código gerado.
     *
     * @param array<string, mixed> $data
     */
    public function generateCode(array $data): IntegrationResult;

    /** Valida cupom existente. Sucesso indica cupom utilizável; dados['cupom'] quando aplicável. */
    public function validate(string $code): IntegrationResult;

    /** Indica se o provider está configurado e disponível para uso. */
    public function isAvailable(): IntegrationResult;
}
