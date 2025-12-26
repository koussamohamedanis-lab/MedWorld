<?php

namespace App\Http\Requests;

use App\Traits\JsonValidationErrorTrait;
use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends FormRequest
{
    use JsonValidationErrorTrait;

    public function authorize(): bool
    {
        return true;
    }
}
