<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Atualização parcial: cada campo só é validado se estiver presente
 * no payload (`sometimes`), e as regras de unicidade ignoram o próprio
 * registro para não acusar conflito consigo mesmo.
 */
class UpdateClienteRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $clienteId = $this->route('cliente');

        return [
            'nome' => ['sometimes', 'required', 'string', 'max:255'],
            'cpf' => [
                'sometimes', 'required', 'string', 'digits:11',
                Rule::unique('clientes', 'cpf')->ignore($clienteId),
            ],
            'email' => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('clientes', 'email')->ignore($clienteId),
            ],
            'telefone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'renda_mensal' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cpf.digits' => 'O CPF deve conter exatamente 11 dígitos numéricos.',
            'cpf.unique' => 'Já existe um cliente cadastrado com este CPF.',
            'email.unique' => 'Já existe um cliente cadastrado com este e-mail.',
        ];
    }
}
