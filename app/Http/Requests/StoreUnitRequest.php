<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'brand_name'     => ['required','string','max:100'],
            'model_name'     => ['required','string','max:150'],
            'unit_type'      => ['required','in:baru,second'],
            'grade'          => ['nullable','in:A,B,C,D,E'],
            'ram'            => ['nullable','string','max:20'],
            'rom'            => ['nullable','string','max:20'],
            'color'          => ['nullable','string','max:50'],
            'imei'           => ['nullable','string','max:20','unique:units,imei'],
            'serial_number'  => ['nullable','string','max:50'],
            'purchase_price'          => ['required','numeric','min:0'],
            'purchase_date'           => ['required','date'],
            'purchase_payment_method' => ['nullable','in:cash,transfer'],
            'purchase_cash'           => ['required','numeric','min:0'],
            'purchase_transfer'       => ['required','numeric','min:0'],
            'notes'                   => ['nullable','string'],
        ];
    }
}
