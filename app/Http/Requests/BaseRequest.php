<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
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
        $methodFunction = explode('@', $this->route()?->getActionName())[1];
        if (method_exists($this, $methodFunction)) {
            return $this->$methodFunction();
        }
        return [];
    }
}
