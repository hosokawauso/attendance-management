<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
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
        return [
            'start_work' => ['nullable', 'date_format:H:i'],
            'end_work'   => ['nullable', 'date_format:H:i', 'after_or_equal:start_work'],
            'rests.*.start' => ['nullable', 'date_format:H:i'],
            'rests.*.end'   => ['nullable', 'date_format:H:i', 'after_or_equal:rests.*.start'],
            'remarks'    => ['nullable', 'string', 'max:255'],

        ];
    }
}
