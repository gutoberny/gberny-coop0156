<?php

/*
 * Mensagens de validação em português.
 *
 * Cobre as regras efetivamente usadas pelos Form Requests da aplicação.
 * Regras não listadas caem no fallback definido em APP_FALLBACK_LOCALE.
 */
return [
    'accepted' => 'O campo :attribute deve ser aceito.',
    'after' => 'O campo :attribute deve conter uma data posterior a :date.',
    'before' => 'O campo :attribute deve conter uma data anterior a :date.',
    'between' => [
        'array' => 'O campo :attribute deve ter entre :min e :max itens.',
        'file' => 'O campo :attribute deve ter entre :min e :max kilobytes.',
        'numeric' => 'O campo :attribute deve estar entre :min e :max.',
        'string' => 'O campo :attribute deve ter entre :min e :max caracteres.',
    ],
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação do campo :attribute não coincide.',
    'date' => 'O campo :attribute não é uma data válida.',
    'declined' => 'O campo :attribute deve ser recusado.',
    'different' => 'Os campos :attribute e :other devem ser diferentes.',
    'digits' => 'O campo :attribute deve ter :digits dígitos.',
    'digits_between' => 'O campo :attribute deve ter entre :min e :max dígitos.',
    'email' => 'O campo :attribute deve ser um endereço de e-mail válido.',
    'enum' => 'O valor selecionado para :attribute é inválido.',
    'exists' => 'O valor selecionado para :attribute é inválido.',
    'filled' => 'O campo :attribute deve ter um valor.',
    'gt' => [
        'array' => 'O campo :attribute deve ter mais de :value itens.',
        'file' => 'O campo :attribute deve ser maior que :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser maior que :value.',
        'string' => 'O campo :attribute deve ter mais de :value caracteres.',
    ],
    'gte' => [
        'array' => 'O campo :attribute deve ter :value itens ou mais.',
        'file' => 'O campo :attribute deve ser maior ou igual a :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser maior ou igual a :value.',
        'string' => 'O campo :attribute deve ter :value caracteres ou mais.',
    ],
    'in' => 'O valor selecionado para :attribute é inválido.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'lt' => [
        'array' => 'O campo :attribute deve ter menos de :value itens.',
        'file' => 'O campo :attribute deve ser menor que :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser menor que :value.',
        'string' => 'O campo :attribute deve ter menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'O campo :attribute não deve ter mais que :value itens.',
        'file' => 'O campo :attribute deve ser menor ou igual a :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser menor ou igual a :value.',
        'string' => 'O campo :attribute deve ter :value caracteres ou menos.',
    ],
    'max' => [
        'array' => 'O campo :attribute não deve ter mais que :max itens.',
        'file' => 'O campo :attribute não deve ter mais que :max kilobytes.',
        'numeric' => 'O campo :attribute não deve ser maior que :max.',
        'string' => 'O campo :attribute não deve ter mais que :max caracteres.',
    ],
    'min' => [
        'array' => 'O campo :attribute deve ter no mínimo :min itens.',
        'file' => 'O campo :attribute deve ter no mínimo :min kilobytes.',
        'numeric' => 'O campo :attribute não pode ser menor que :min.',
        'string' => 'O campo :attribute deve ter no mínimo :min caracteres.',
    ],
    'not_in' => 'O valor selecionado para :attribute é inválido.',
    'numeric' => 'O campo :attribute deve ser um número.',
    'present' => 'O campo :attribute deve estar presente.',
    'prohibited' => 'O campo :attribute é proibido.',
    'regex' => 'O formato do campo :attribute é inválido.',
    'required' => 'O campo :attribute é obrigatório.',
    'required_if' => 'O campo :attribute é obrigatório quando :other é :value.',
    'required_with' => 'O campo :attribute é obrigatório quando :values está presente.',
    'same' => 'Os campos :attribute e :other devem coincidir.',
    'size' => [
        'array' => 'O campo :attribute deve conter :size itens.',
        'file' => 'O campo :attribute deve ter :size kilobytes.',
        'numeric' => 'O campo :attribute deve ser :size.',
        'string' => 'O campo :attribute deve ter :size caracteres.',
    ],
    'string' => 'O campo :attribute deve ser um texto.',
    'unique' => 'O campo :attribute já está em uso.',
    'url' => 'O campo :attribute deve ser uma URL válida.',

    /*
     * Nomes dos campos como aparecem nas mensagens, para que o texto saia
     * "A renda mensal não pode ser menor que 0" em vez de "renda_mensal".
     */
    'attributes' => [
        'nome' => 'nome',
        'cpf' => 'CPF',
        'email' => 'e-mail',
        'telefone' => 'telefone',
        'renda_mensal' => 'renda mensal',
        'tipo_credito' => 'tipo de crédito',
        'valor_solicitado' => 'valor solicitado',
        'cliente_id' => 'cliente',
        'per_page' => 'itens por página',
    ],
];
