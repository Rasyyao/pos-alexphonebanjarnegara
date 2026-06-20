<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedCategories = ['operasional', 'gaji', 'sewa', 'listrik', 'lainnya'];
        $user = $this->user();
        $role = $user ? (is_string($user->role) ? $user->role : ($user->role->value ?? '')) : '';
        if ($role === 'superadmin') {
            $allowedCategories[] = 'tarik_owner';
        }

        return [
            'description'    => ['required', 'string', 'max:255'],
            'amount'         => ['required', 'string'],
            'category'       => ['required', 'in:' . implode(',', $allowedCategories)],
            'expense_date'   => ['required', 'date'],
            'notes'          => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'in:cash,transfer'],
        ];
    }
}
