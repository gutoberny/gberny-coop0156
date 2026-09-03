<?php

namespace App\Enums;

enum StatusAnalise: string
{
    case PENDENTE = 'pendente';
    case APROVADO = 'aprovado';
    case REPROVADO = 'reprovado';
    case PROCESSANDO_CONTRATACAO = 'processando_contratacao';
    case CONTRATADO = 'contratado';

    /**
     * Apenas análises aprovadas podem seguir para a tela de simulação
     * e para a contratação.
     */
    public function permiteSimulacao(): bool
    {
        return $this === self::APROVADO;
    }

    /**
     * Rótulo legível para exibição na interface.
     */
    public function rotulo(): string
    {
        return match ($this) {
            self::PENDENTE => 'Pendente',
            self::APROVADO => 'Aprovado',
            self::REPROVADO => 'Reprovado',
            self::PROCESSANDO_CONTRATACAO => 'Processando contratação',
            self::CONTRATADO => 'Contratado',
        };
    }
}
