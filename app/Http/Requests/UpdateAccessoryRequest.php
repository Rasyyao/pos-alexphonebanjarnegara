<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccessoryRequest extends FormRequest
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
        return [
            'name'                    => ['required','string','max:100'],
            'category'                => ['nullable','string','max:80'],
            'stock_qty'               => ['required','integer','min:0'],
            'purchase_price'          => ['required','numeric','min:0'],
            'purchase_payment_method' => ['required','in:cash,transfer,split'],
            'purchase_cash'           => ['nullable','numeric','min:0'],
            'purchase_transfer'       => ['nullable','numeric','min:0'],
        ];
    }
}
