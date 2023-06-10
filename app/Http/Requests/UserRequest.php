<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{

    use PasswordValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'phone_number' => ['required', 'string', 'max:255'],
            // 'profile_photo_path' => ['required', 'image'],
            'address' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'integer'],
            'height' => ['required', 'integer'],
            'top_size' => ['required', 'string', 'max:255'],
            'bottom_size' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'string', 'max:255', 'in:USER,ADMIN'],
        ];
    }
}