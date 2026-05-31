<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $userId = $this->route('admin_user')?->id;
        return [
            'name'      => ['required','string','max:100'],
            'username'  => ['required','string','max:50',"unique:users,username,{$userId}"],
            'password'  => ['nullable','string','min:6'],
            'role'      => ['required','in:superadmin,admin'],
            'is_active' => ['boolean'],
        ];
    }
}
