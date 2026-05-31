<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'sale_date'              => ['required','date'],
            'items'                  => ['required','array','min:1'],
            'items.*.unit_id'        => ['nullable','exists:units,id'],
            'items.*.accessory_id'   => ['nullable','exists:accessories,id'],
            'items.*.selling_price'  => ['required','numeric','min:0'],
            'items.*.quantity'       => ['required','integer','min:1'],
            'payments'               => ['required','array','min:1'],
            'payments.*.method'      => ['required','in:cash,transfer,utang'],
            'payments.*.amount'      => ['required','numeric','min:1'],
        ];
    }
}
