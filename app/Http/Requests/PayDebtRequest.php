<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth middleware governs role-based routing checks
    }

    public function rules(): array
    {
        return [
            'type'           => ['required', 'string', 'in:full,partial'],
            'amount'         => ['required_if:type,partial', 'nullable', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:cash,transfer'],
            'payment_date'   => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'     => 'Tipe pembayaran harus dipilih.',
            'type.in'           => 'Tipe pembayaran tidak valid.',
            'amount.required_if'=> 'Jumlah cicilan harus diisi jika memilih pembayaran sebagian/cicilan.',
            'amount.numeric'    => 'Jumlah cicilan harus berupa angka.',
            'amount.min'        => 'Jumlah cicilan minimal adalah Rp 1.',
            'payment_date.required' => 'Tanggal pembayaran harus diisi.',
            'payment_date.date'     => 'Tanggal pembayaran tidak valid.',
        ];
    }
}
