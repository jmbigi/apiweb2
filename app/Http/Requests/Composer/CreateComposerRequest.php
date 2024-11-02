<?php

namespace App\Http\Requests\Composer;

use Illuminate\Foundation\Http\FormRequest;

class CreateComposerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            //
            'public_name' => 'required|string|unique:composers,public_name'
        ];
    }
}
