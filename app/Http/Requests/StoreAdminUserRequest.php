<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'     => ['required','string','max:100'],
            'username' => ['required','string','max:50','unique:users,username'],
            'password' => ['required','string','min:6'],
            'role'     => ['required','in:superadmin,admin'],
        ];
    }
}
