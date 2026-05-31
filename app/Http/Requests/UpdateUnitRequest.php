<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }
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
            'purchase_price' => ['required','numeric','min:0'],
            'selling_price'  => ['nullable','numeric','min:0'],
            'purchase_date'  => ['required','date'],
            'notes'          => ['nullable','string'],
            'status'         => ['nullable','in:ready,sold,returned'],
            'photo'          => ['nullable','file','mimes:jpeg,jpg,png,webp','max:2048'],
            'photo_2'        => ['nullable','file','mimes:jpeg,jpg,png,webp','max:2048'],
            'photo_3'        => ['nullable','file','mimes:jpeg,jpg,png,webp','max:2048'],
        ];
    }
}
