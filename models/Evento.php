<?php

declare(strict_types=1);

class Evento extends Model
{
    public const EVENTO_LOGIN = 'LOGIN';
    public const EVENTO_CADASTRO = 'CADASTRO';
    public const EVENTO_LINK_GERADO = 'LINK_GERADO';
    public const EVENTO_LINK_COMPARTILHADO = 'LINK_COMPARTILHADO';
    public const EVENTO_LINK_CLICADO = 'LINK_CLICADO';
    public const EVENTO_CONVITE_ABERTO = 'CONVITE_ABERTO';
    public const EVENTO_PERFIL_EDITADO = 'PERFIL_EDITADO';
    public const EVENTO_SENHA_ALTERADA = 'SENHA_ALTERADA';
    public const EVENTO_INDICACAO_CRIADA = 'INDICACAO_CRIADA';
    public const EVENTO_INDICADO_CADASTRADO = 'INDICADO_CADASTRADO';
    public const EVENTO_VALIDACAO_CRIADA = 'VALIDACAO_CRIADA';
    public const EVENTO_VALIDACAO_INICIADA = 'VALIDACAO_INICIADA';
    public const EVENTO_VALIDACAO_CONCLUIDA = 'VALIDACAO_CONCLUIDA';
    public const EVENTO_VALIDACAO_APROVADA = 'VALIDACAO_APROVADA';
    public const EVENTO_VALIDACAO_INVALIDADA = 'VALIDACAO_INVALIDADA';
    public const EVENTO_VALIDACAO_CANCELADA = 'VALIDACAO_CANCELADA';
    public const EVENTO_CUPOM_CRIADO = 'CUPOM_CRIADO';
    public const EVENTO_CUPOM_CANCELADO = 'CUPOM_CANCELADO';
    public const EVENTO_CUPOM_EXPIRADO = 'CUPOM_EXPIRADO';
    public const EVENTO_CUPOM_RESERVADO = 'CUPOM_RESERVADO';
    public const EVENTO_CUPOM_UTILIZADO = 'CUPOM_UTILIZADO';
    public const EVENTO_BENEFICIO_WHATSAPP_OPENED = 'BENEFICIO_WHATSAPP_OPENED';
    public const EVENTO_APPSFLYER_EVENTO_RECEBIDO = 'APPSFLYER_EVENTO_RECEBIDO';
    public const EVENTO_APPSFLYER_EVENTO_PROCESSADO = 'APPSFLYER_EVENTO_PROCESSADO';
    public const EVENTO_APPSFLYER_EVENTO_VALIDADO = 'APPSFLYER_EVENTO_VALIDADO';
    public const EVENTO_APPSFLYER_EVENTO_REJEITADO = 'APPSFLYER_EVENTO_REJEITADO';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO eventos
            (usuario_id, evento, referencia, payload)
            VALUES
            (:usuario_id, :evento, :referencia, :payload)'
        );

        $stmt->execute([
            'usuario_id' => $data['usuario_id'] ?? null,
            'evento' => $data['evento'],
            'referencia' => $data['referencia'] ?? null,
            'payload' => $data['payload'] ? json_encode($data['payload']) : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByUsuario(int $usuarioId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM eventos 
             WHERE usuario_id = :usuario_id 
             ORDER BY created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue('usuario_id', $usuarioId);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $eventos = $stmt->fetchAll();
        
        // Decode payload
        foreach ($eventos as &$evento) {
            if ($evento['payload']) {
                $evento['payload'] = json_decode($evento['payload'], true);
            }
        }
        
        return $eventos;
    }

    public function findByTipo(string $evento, int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM eventos 
             WHERE evento = :evento 
             ORDER BY created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue('evento', $evento);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $eventos = $stmt->fetchAll();
        
        // Decode payload
        foreach ($eventos as &$evento) {
            if ($evento['payload']) {
                $evento['payload'] = json_decode($evento['payload'], true);
            }
        }
        
        return $eventos;
    }

    public function findByReferencia(string $referencia, int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM eventos 
             WHERE referencia = :referencia 
             ORDER BY created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue('referencia', $referencia);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $eventos = $stmt->fetchAll();
        
        // Decode payload
        foreach ($eventos as &$evento) {
            if ($evento['payload']) {
                $evento['payload'] = json_decode($evento['payload'], true);
            }
        }
        
        return $eventos;
    }

    public function countByUsuario(int $usuarioId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM eventos WHERE usuario_id = :usuario_id'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countByTipo(string $evento): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM eventos WHERE evento = :evento'
        );
        $stmt->execute(['evento' => $evento]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function listRecent(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, u.nome as usuario_nome 
             FROM eventos e 
             LEFT JOIN usuarios u ON e.usuario_id = u.id 
             ORDER BY e.created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $eventos = $stmt->fetchAll();
        
        // Decode payload
        foreach ($eventos as &$evento) {
            if ($evento['payload']) {
                $evento['payload'] = json_decode($evento['payload'], true);
            }
        }
        
        return $eventos;
    }

    public static function eventoLabel(string $evento): string
    {
        return match ($evento) {
            self::EVENTO_LOGIN => 'Login',
            self::EVENTO_CADASTRO => 'Cadastro',
            self::EVENTO_LINK_GERADO => 'Link Gerado',
            self::EVENTO_LINK_COMPARTILHADO => 'Link Compartilhado',
            self::EVENTO_LINK_CLICADO => 'Link Clicado',
            self::EVENTO_CONVITE_ABERTO => 'Convite Aberto',
            self::EVENTO_PERFIL_EDITADO => 'Perfil Editado',
            self::EVENTO_SENHA_ALTERADA => 'Senha Alterada',
            self::EVENTO_INDICACAO_CRIADA => 'Indicação Criada',
            self::EVENTO_INDICADO_CADASTRADO => 'Indicado Cadastrado',
            self::EVENTO_VALIDACAO_CRIADA => 'Validação Criada',
            self::EVENTO_VALIDACAO_INICIADA => 'Validação Iniciada',
            self::EVENTO_VALIDACAO_CONCLUIDA => 'Validação Concluída',
            self::EVENTO_VALIDACAO_APROVADA => 'Validação Aprovada',
            self::EVENTO_VALIDACAO_INVALIDADA => 'Validação Invalidada',
            self::EVENTO_VALIDACAO_CANCELADA => 'Validação Cancelada',
            self::EVENTO_CUPOM_CRIADO => 'Cupom Criado',
            self::EVENTO_CUPOM_CANCELADO => 'Cupom Cancelado',
            self::EVENTO_CUPOM_EXPIRADO => 'Cupom Expirado',
            self::EVENTO_CUPOM_RESERVADO => 'Cupom Reservado',
            self::EVENTO_CUPOM_UTILIZADO => 'Cupom Utilizado',
            self::EVENTO_BENEFICIO_WHATSAPP_OPENED => 'WhatsApp benefício aberto',
            self::EVENTO_APPSFLYER_EVENTO_RECEBIDO => 'AppsFlyer Evento Recebido',
            self::EVENTO_APPSFLYER_EVENTO_PROCESSADO => 'AppsFlyer Evento Processado',
            self::EVENTO_APPSFLYER_EVENTO_VALIDADO => 'AppsFlyer Evento Validado',
            self::EVENTO_APPSFLYER_EVENTO_REJEITADO => 'AppsFlyer Evento Rejeitado',
            default => $evento,
        };
    }

    public static function eventoIcon(string $evento): string
    {
        return match ($evento) {
            self::EVENTO_LOGIN => '🔐',
            self::EVENTO_CADASTRO => '👤',
            self::EVENTO_LINK_GERADO => '🔗',
            self::EVENTO_LINK_COMPARTILHADO => '📤',
            self::EVENTO_LINK_CLICADO => '👆',
            self::EVENTO_CONVITE_ABERTO => '📨',
            self::EVENTO_PERFIL_EDITADO => '✏️',
            self::EVENTO_SENHA_ALTERADA => '🔒',
            self::EVENTO_INDICACAO_CRIADA => '➕',
            self::EVENTO_INDICADO_CADASTRADO => '🎯',
            self::EVENTO_VALIDACAO_CRIADA => '📋',
            self::EVENTO_VALIDACAO_INICIADA => '🔍',
            self::EVENTO_VALIDACAO_CONCLUIDA => '✅',
            self::EVENTO_VALIDACAO_APROVADA => '✅',
            self::EVENTO_VALIDACAO_INVALIDADA => '❌',
            self::EVENTO_VALIDACAO_CANCELADA => '⚫',
            self::EVENTO_CUPOM_CRIADO => '🎟️',
            self::EVENTO_CUPOM_CANCELADO => '🚫',
            self::EVENTO_CUPOM_EXPIRADO => '⏰',
            self::EVENTO_CUPOM_RESERVADO => '🔒',
            self::EVENTO_CUPOM_UTILIZADO => '✅',
            self::EVENTO_BENEFICIO_WHATSAPP_OPENED => '💬',
            self::EVENTO_APPSFLYER_EVENTO_RECEBIDO => '📥',
            self::EVENTO_APPSFLYER_EVENTO_PROCESSADO => '⚙️',
            self::EVENTO_APPSFLYER_EVENTO_VALIDADO => '✅',
            self::EVENTO_APPSFLYER_EVENTO_REJEITADO => '❌',
            default => '📌',
        };
    }

    /** Proteção contra clique duplo (mesma indicação + usuário nos últimos N segundos). */
    public function hasRecentBeneficioWhatsappOpen(int $usuarioId, int $indicacaoId, int $withinSeconds = 15): bool
    {
        $withinSeconds = max(1, min(60, $withinSeconds));
        $stmt = $this->db->prepare(
            'SELECT id FROM eventos
             WHERE usuario_id = :usuario_id
               AND evento = :evento
               AND referencia = :referencia
               AND created_at >= DATE_SUB(NOW(), INTERVAL ' . $withinSeconds . ' SECOND)
             LIMIT 1'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'evento' => self::EVENTO_BENEFICIO_WHATSAPP_OPENED,
            'referencia' => (string) $indicacaoId,
        ]);

        return (bool) $stmt->fetch();
    }
}
