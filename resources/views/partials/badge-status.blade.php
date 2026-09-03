@php
    /**
     * Badge de status de análise.
     *
     * As cores vêm da paleta de apoio do manual: magenta para reprovação,
     * amarelo para os estados de espera, verde para os desfechos positivos.
     *
     * @var \App\Enums\StatusAnalise $status
     */
    $estilos = [
        'aprovado' => 'bg-neutroClaro text-verdeEscuro',
        'contratado' => 'bg-verde text-white',
        'reprovado' => 'bg-magenta/10 text-magenta',
        'pendente' => 'bg-amarelo/25 text-tinta',
        'processando_contratacao' => 'bg-amarelo/25 text-tinta',
    ];
@endphp

<span class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold {{ $estilos[$status->value] }}">
    {{ $status->rotulo() }}
</span>
