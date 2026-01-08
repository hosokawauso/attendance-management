<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'start_work' => ['nullable','date_format:H:i'],
            'end_work'   => ['nullable', 'date_format:H:i', 'after_or_equal:start_work'],
            'rests.*.start' => ['nullable','date_format:H:i'],
            'rests.*.end'   => ['nullable','date_format:H:i', 'after_or_equal:rests.*.start'],
            'remarks'    => ['required','string', 'max:255'],

        ];
    }

    public function messages()
    {
        return [
            'end_work.after_or_equal'=>'出勤時間もしくは退勤時間が不適切な値です',
            'rests.*.end.after_or_equal'=>'休憩時間が不適切な値です',
            'remarks.required' => '備考を記入してください',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $endWork = $this->input('end_work');
            if (!$endWork) return;

            foreach (($this->input('rests') ?? []) as $i =>$rest) {
                $rs = $rest['start'] ?? null;
                $re = $rest['end'] ?? null;

                if ($rs && $rs > $endWork) {
                    $validator->errors()->add("rests.$i.start", '休憩時間が不適切な値です');
                }

                if ($re && $re > $endWork) {
                    $validator->errors()->add("rests.$i.end", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }
}
