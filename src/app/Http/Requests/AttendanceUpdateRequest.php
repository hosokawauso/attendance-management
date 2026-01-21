<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
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
            'end_work' => ['nullable', 'date_format:H:i'],
            'rests.*.start' => ['nullable', 'date_format:H:i'],
            'rests.*.end' => ['nullable', 'date_format:H:i'],
            'remarks' => ['required', 'string', 'max:255'],
            'rests.*.requested_start_rest' => ['nullable','date_format:H:i'],
            'rests.*.requested_end_rest'   => ['nullable','date_format:H:i'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $start = $this->input('start_work');
            $end = $this->input('end_work');
            $rests = $this->input('rests', []);

            if (!is_array($rests)) {
                $rests = [];
            }

            if($start && $end && $start > $end) {
                $validator->errors()->add('start_work', '出勤時間もしくは退勤時間が不適切な値です');
            }

            foreach ($rests as $rest) {
                if(!empty($rest['start'])) {
                    if($start && $rest['start'] < $start) {
                    $validator->errors()->add('rests', '休憩時間が不適切な値です');
                    }
                    if($end && $rest['start'] > $end) {
                    $validator->errors()->add('rests', '休憩時間が不適切な値です');
                    }
                }
            }

            foreach($rests as $rest) {
                if(!empty($rest['end']) && $end && $rest['end'] > $end) {
                    $validator->errors()->add('rests', '休憩時間もしくは退勤時間が不適切な値です');
                }
            }

        });
    }
    public function messages(): array
    {
        return [
            'remarks.required' => '備考を記入してください',
        ];
    }
}
