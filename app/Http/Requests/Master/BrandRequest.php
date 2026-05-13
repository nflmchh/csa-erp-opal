<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')?->id;

        return [
            'name'        => ['required', 'string', 'max:100'],
            'code' => [
                'required', 'string', 'max:10',
                \Illuminate\Validation\Rule::unique('brands')->whereNull('deleted_at')->ignore($this->brand)
            ],'description' => ['nullable', 'string', 'max:500'],
            'logo'        => ['nullable', 'image', 'max:2048'],
            'is_active'   => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code'      => strtoupper($this->code),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
