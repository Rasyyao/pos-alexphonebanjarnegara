<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyClosingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'closing_date'     => ['required', 'date'],
            'cash_physical'    => ['required', 'string'],
            'atm_physical'     => ['required', 'string'],
            'expense_physical' => ['required', 'string'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }
}
