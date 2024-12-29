<?php

namespace WalkerChiu\Point\Models\Forms;

use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use WalkerChiu\Core\Models\Forms\FormRequest;

class RecordFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (string) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'wallet_id'      => trans('php-point::record.wallet_id'),
            'value_original' => trans('php-point::record.value_original'),
            'value'          => trans('php-point::record.value'),
            'end_at'         => trans('php-point::record.end_at'),
            'is_enabled'     => trans('php-point::record.is_enabled')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'wallet_id'      => ['required','integer','min:1','exists:'.config('wk-core.table.point.wallets').',id'],
            'value_original' => 'required|numeric',
            'value'          => 'required|numeric',
            'end_at'         => 'nullable|date|date_format:Y-m-d H:i:s',
            'is_enabled'     => 'boolean'
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','string','exists:'.config('wk-core.table.point.records').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'             => trans('php-core::validation.required'),
            'id.string'               => trans('php-core::validation.string'),
            'id.exists'               => trans('php-core::validation.exists'),
            'wallet_id.required'      => trans('php-core::validation.required'),
            'wallet_id.integer'       => trans('php-core::validation.integer'),
            'wallet_id.min'           => trans('php-core::validation.min'),
            'wallet_id.exists'        => trans('php-core::validation.exists'),
            'value_original.required' => trans('php-core::validation.required'),
            'value_original.numeric'  => trans('php-core::validation.numeric'),
            'value.required'          => trans('php-core::validation.required'),
            'value.numeric'           => trans('php-core::validation.numeric'),
            'end_at.date'             => trans('php-core::validation.date'),
            'end_at.date_format'      => trans('php-core::validation.date_format'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
    }
}
