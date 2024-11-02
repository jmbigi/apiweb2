<?php

namespace App\Http\Requests\ComposerRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateComposerRequestRequest extends FormRequest
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
            
            'composer_id' => 'required|integer|exists:composers,id',
            'description' => 'required|string',
            'telephone' => 'required'|'numeric',
            'name' => 'required',
            'surname' => 'required',
            'vat_number' => 'required',
            'public_name' => 'required',   
            'street' => 'required',   
            'city' => 'required',   
            'postal_code' => 'required',   
            'country' => 'required',   
            'notification_email' => 'required|email',
        ];
    }
}