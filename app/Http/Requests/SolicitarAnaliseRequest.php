<?php

namespace App\Http\Requests;

use App\Enums\TipoCredito;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SolicitarAnaliseRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'digits:11'],
            'renda_mensal' => ['required', 'numeric', 'min:0'],
            'tipo_credito' => ['required', Rule::enum(TipoCredito::class)],
            'valor_solicitado' => ['required', 'numeric', 'gt:0'],

            // Opcionais: o enunciado não os exige na solicitação, mas quando
            // informados enriquecem o cadastro do cliente criado no fluxo.
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Normaliza o CPF antes da validação: a interface aplica máscara
     * (000.000.000-00) e a regra `digits:11` espera apenas dígitos.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/\D/', '', (string) $this->input('cpf')),
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cpf.digits' => 'O CPF deve conter exatamente 11 dígitos numéricos.',
            'tipo_credito.enum' => 'O tipo de crédito deve ser pessoal, imobiliario ou automotivo.',
            'valor_solicitado.gt' => 'O valor solicitado deve ser maior que zero.',
        ];
    }
}
