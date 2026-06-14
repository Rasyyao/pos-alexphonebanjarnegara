<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccessoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'                    => ['required','string','max:100'],
            'category'                => ['nullable','string','max:80'],
            'stock_qty'               => ['required','integer','min:0'],
            'purchase_price'          => ['required','numeric','min:0'],
            'purchase_payment_method' => ['nullable','in:cash,transfer'],
        ];
    }
}
