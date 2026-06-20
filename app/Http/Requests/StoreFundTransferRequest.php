<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFundTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gate handled by role:superadmin middleware
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => $this->cleanMoney($this->amount),
        ]);
    }

    private function cleanMoney(mixed $val): mixed
    {
        if (is_string($val)) {
            return preg_replace('/[^0-9]/', '', $val);
        }
        return $val;
    }

    public function rules(): array
    {
        return [
            'direction'     => ['required', 'in:cash_to_atm,atm_to_cash'],
            'amount'        => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    $direction = $this->input('direction');
                    if ($direction === 'cash_to_atm') {
                        $finance = app(\App\Services\FinanceService::class);
                        $saldo = $finance->saldoSplit();
                        if ($value > $saldo['saldoKas']) {
                            $fail('Saldo Kas tidak mencukupi untuk melakukan transfer ini. Saldo Kas saat ini: Rp ' . number_format($saldo['saldoKas'], 0, ',', '.'));
                        }
                    } elseif ($direction === 'atm_to_cash') {
                        $finance = app(\App\Services\FinanceService::class);
                        $saldo = $finance->saldoSplit();
                        if ($value > $saldo['saldoAtm']) {
                            $fail('Saldo ATM tidak mencukupi untuk melakukan transfer ini. Saldo ATM saat ini: Rp ' . number_format($saldo['saldoAtm'], 0, ',', '.'));
                        }
                    }
                }
            ],
            'description'   => ['nullable', 'string', 'max:255'],
            'transfer_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'direction.required' => 'Pilih arah mutasi dana.',
            'direction.in'       => 'Arah mutasi tidak valid.',
            'amount.required'    => 'Jumlah wajib diisi.',
            'amount.min'         => 'Jumlah harus lebih dari 0.',
            'transfer_date.required' => 'Tanggal mutasi wajib diisi.',
        ];
    }
}
