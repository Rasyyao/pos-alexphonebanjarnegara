<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'  => ['required', 'string', 'max:255'],
            'amount'       => ['required', 'string'],
            'category'     => ['required', 'in:operasional,gaji,sewa,listrik,lainnya'],
            'expense_date' => ['required', 'date'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];
    }
}
