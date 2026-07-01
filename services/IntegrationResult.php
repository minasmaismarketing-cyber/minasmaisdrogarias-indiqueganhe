<?php

declare(strict_types=1);

/**
 * Retorno padronizado de operações de integração externa.
 *
 * Campos oficiais: sucesso, erro, mensagem (+ dados opcionais).
 */
final class IntegrationResult
{
    /** @param array<string, mixed> $dados */
    public function __construct(
        public readonly bool $sucesso,
        public readonly bool $erro,
        public readonly string $mensagem,
        public readonly array $dados = [],
    ) {
        if ($sucesso && $erro) {
            throw new InvalidArgumentException('sucesso e erro não podem ser verdadeiros ao mesmo tempo.');
        }

        if (!$sucesso && !$erro) {
            throw new InvalidArgumentException('Informe sucesso ou erro no resultado da integração.');
        }
    }

    /** @param array<string, mixed> $dados */
    public static function ok(string $mensagem = 'Operação concluída com sucesso.', array $dados = []): self
    {
        return new self(true, false, $mensagem, $dados);
    }

    /** @param array<string, mixed> $dados */
    public static function falha(string $mensagem, array $dados = []): self
    {
        return new self(false, true, $mensagem, $dados);
    }
}
