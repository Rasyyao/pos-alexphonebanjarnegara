<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreCapitalRequest extends FormRequest
{
    public function authorize(): bool { return true; }
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
            'description'    => ['required','string','max:255'],
            'amount'         => ['required','numeric','min:1'],
            'type'           => ['required','in:initial,addition,purchase,withdrawal'],
            'entry_date'     => ['required','date'],
            'payment_method' => ['nullable','in:cash,transfer'],
        ];
    }
}
