<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        $t = auth()->user()->is_admin;


        return auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('POST')) {
            $return = [
                'email' => ['required', 'string', 'email', 'unique:users'],
                //'password' => ['required', 'confirmed', Password::defaults()],
                'password' => ['required', 'confirmed',
                    //Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()
                    Password::min(8)->letters()->numbers()->symbols()
                ],
                'password_confirmation' => ['required'],
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'digits_between:10,11', 'unique:users'],
                'nickname' => ['nullable', 'string', 'max:255'],
                'is_agree_promotion' => ['nullable', 'boolean'],
            ];
        }

        if ($this->isMethod('PUT')) {
            $return = [
                'email' => ['nullable', 'string', 'email', 'unique:users,email,' . $this->id],
                'password' => ['nullable', 'confirmed',
                    //Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()
                    Password::min(8)->letters()->numbers()->symbols()
                ],
                'password_confirmation' => ['nullable'],
                'name' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'digits_between:10,11', 'unique:users,phone,' . $this->id],
                'nickname' => ['nullable', 'string', 'max:255'],
                'is_agree_promotion' => ['nullable', 'boolean'],
            ];
        }

        return $return;
    }
}