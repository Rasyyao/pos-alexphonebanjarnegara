<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreCapitalRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'description' => ['required','string','max:255'],
            'amount'      => ['required','numeric','min:1'],
            'type'        => ['required','in:initial,addition,purchase,withdrawal'],
            'entry_date'  => ['required','date'],
        ];
    }
}
