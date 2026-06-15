<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'purchase_price'    => $this->cleanMoney($this->purchase_price),
            'purchase_cash'     => $this->cleanMoney($this->purchase_cash),
            'purchase_transfer' => $this->cleanMoney($this->purchase_transfer),
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
        $unitId = $this->route('unit')?->id;
        return [
            'brand_name'     => ['required','string','max:100'],
            'model_name'     => ['required','string','max:150'],
            'unit_type'      => ['required','in:baru,second'],
            'grade'          => ['nullable','in:A,B,C,D,E'],
            'ram'            => ['nullable','string','max:20'],
            'rom'            => ['nullable','string','max:20'],
            'color'          => ['nullable','string','max:50'],
            'imei'           => ['nullable','string','max:20',"unique:units,imei,{$unitId}"],
            'serial_number'  => ['nullable','string','max:50'],
            'purchase_price'          => ['required','numeric','min:0'],
            'purchase_date'           => ['required','date'],
            'purchase_payment_method' => ['required','in:cash,transfer,split'],
            'purchase_cash'           => ['nullable','numeric','min:0'],
            'purchase_transfer'       => ['nullable','numeric','min:0'],
            'notes'                   => ['nullable','string'],
            'status'                  => ['nullable','in:ready,sold,returned'],
        ];
    }
}
