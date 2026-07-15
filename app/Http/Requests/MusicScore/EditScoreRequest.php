<?php

namespace App\Http\Requests\MusicScore;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class EditScoreRequest extends FormRequest
{
    /**
     * peso del fichero en MB * 1000 (conversión a kilobites)
     * @var $maxKilobites - peso máximo autorizado por el servidor para un pdf
     */
    protected $maxKilobitesPDF = 50 * 1000;
    protected $maxKilobitesImage = 50 * 1000;

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
            'name' => 'string',
            'description' => 'string',
            'pdf' => [
                File::types(['pdf'])
                    ->max($this->maxKilobitesPDF),
            ],
            'cover' => [
                File::types(['png','jpg', 'jpeg'])
                    ->max($this->maxKilobitesImage),
            ],
            //'owner_id' => '', //este ya no se lee (si no podrían robarse partituras unos a otros)
            'composer_id' => [
                'required', 
                function($attribute, $value, $fail) {
                    is_array($value) || is_integer($value)?: $fail("Must be array or integer");
                },
                'exists:composers'
            ], //esta puede ser array de ints o int
            'instrument_id' => [
                'required', 
                function($attribute, $value, $fail) {
                    is_array($value) || is_integer($value)?: $fail("Must be array or integer");
                },
                'exists:instruments'
            ], //esta puede ser array de ints o int
            'style_id' => [
                'required', 
                function($attribute, $value, $fail) {
                    is_array($value) || is_integer($value)?: $fail("Must be array or integer");
                },
                'exists:style_musics'
            ], //esta puede ser array de ints o int
            'links' => 'array', //tiene que ser un array lleno o no recibirse
        ];
    }
}
