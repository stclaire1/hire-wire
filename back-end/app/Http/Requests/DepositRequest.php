<?php

namespace App\Http\Requests;

use App\Services\AccountService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01|max:50000',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isEmpty()) {
                $this->validateDepositAmount($validator);
            }
        });
    }

    /**
     * Validate deposit amount based on account type.
     */
    protected function validateDepositAmount(Validator $validator): void
    {
        try {
            $accountId = $this->route('id');
            $userId = Auth::id();
            $amount = $this->input('amount');

            $accountService = new AccountService();
            $account = $accountService->getAccountById($accountId, $userId);
            
            $minAmount = $account->getMinDepositAmount();
            $maxAmount = $this->getMaxDepositAmount($account->account_type);
            
            if ($amount < $minAmount) {
                $validator->errors()->add(
                    'amount', 
                    "O valor mínimo para depósito neste tipo de conta é R$ " . number_format($minAmount, 2, ',', '.')
                );
            }
            
            if ($amount > $maxAmount) {
                $validator->errors()->add(
                    'amount', 
                    "O valor máximo para depósito é R$ " . number_format($maxAmount, 2, ',', '.')
                );
            }
        } catch (\Exception $e) {
            $validator->errors()->add('account', 'Conta não encontrada ou inválida');
        }
    }

    /**
     * Get maximum deposit amount for all account types.
     */
    protected function getMaxDepositAmount(string $accountType): float
    {
        // Valor máximo fixo de R$ 50.000,00 para todos os tipos de conta
        return 50000.00;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'O valor do depósito é obrigatório.',
            'amount.numeric' => 'O valor do depósito deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'amount.max' => 'O valor excede o limite máximo permitido.',
        ];
    }
}
