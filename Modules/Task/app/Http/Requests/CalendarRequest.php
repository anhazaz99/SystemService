<?php

namespace Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class CalendarRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $rules = [
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'thoi_gian_bat_dau' => 'required|date|after_or_equal:now',
            'thoi_gian_ket_thuc' => 'required|date|after:thoi_gian_bat_dau',
            'loai_su_kien' => 'required|in:task,su_kien',
            'task_id' => 'nullable|exists:task,id',
            'nguoi_tham_gia_id' => 'required|integer',
            'loai_nguoi_tham_gia' => 'required|in:giang_vien,sinh_vien',
            'nguoi_tao_id' => 'required|integer',
            'loai_nguoi_tao' => 'required|in:giang_vien,sinh_vien',
        ];

        // Add recurring event validation if it's a recurring event
        if ($this->input('is_recurring')) {
            $rules = array_merge($rules, [
                'recurring_pattern' => 'required|in:daily,weekly,monthly,yearly',
                'recurring_interval' => 'required|integer|min:1|max:52',
                'recurring_end_date' => 'nullable|date|after:thoi_gian_bat_dau',
            ]);
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'tieu_de.required' => 'Tiêu đề sự kiện là bắt buộc.',
            'tieu_de.max' => 'Tiêu đề sự kiện không được vượt quá 255 ký tự.',
            'thoi_gian_bat_dau.required' => 'Thời gian bắt đầu là bắt buộc.',
            'thoi_gian_bat_dau.after_or_equal' => 'Thời gian bắt đầu phải từ hiện tại trở đi.',
            'thoi_gian_ket_thuc.required' => 'Thời gian kết thúc là bắt buộc.',
            'thoi_gian_ket_thuc.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'loai_su_kien.required' => 'Loại sự kiện là bắt buộc.',
            'loai_su_kien.in' => 'Loại sự kiện không hợp lệ.',
            'task_id.exists' => 'Task không tồn tại.',
            'nguoi_tham_gia_id.required' => 'Người tham gia là bắt buộc.',
            'nguoi_tham_gia_id.integer' => 'ID người tham gia phải là số nguyên.',
            'loai_nguoi_tham_gia.required' => 'Loại người tham gia là bắt buộc.',
            'loai_nguoi_tham_gia.in' => 'Loại người tham gia không hợp lệ.',
            'nguoi_tao_id.required' => 'Người tạo là bắt buộc.',
            'nguoi_tao_id.integer' => 'ID người tạo phải là số nguyên.',
            'loai_nguoi_tao.required' => 'Loại người tạo là bắt buộc.',
            'loai_nguoi_tao.in' => 'Loại người tạo không hợp lệ.',
            'recurring_pattern.required' => 'Mẫu lặp lại là bắt buộc cho sự kiện định kỳ.',
            'recurring_pattern.in' => 'Mẫu lặp lại không hợp lệ.',
            'recurring_interval.required' => 'Khoảng lặp lại là bắt buộc.',
            'recurring_interval.integer' => 'Khoảng lặp lại phải là số nguyên.',
            'recurring_interval.min' => 'Khoảng lặp lại phải lớn hơn 0.',
            'recurring_interval.max' => 'Khoảng lặp lại không được vượt quá 52.',
            'recurring_end_date.after' => 'Ngày kết thúc lặp lại phải sau thời gian bắt đầu.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateTimeConflict($validator);
            $this->validateRecurringPattern($validator);
        });
    }

    /**
     * Validate time conflict.
     */
    private function validateTimeConflict($validator): void
    {
        $startTime = $this->input('thoi_gian_bat_dau');
        $endTime = $this->input('thoi_gian_ket_thuc');
        $userId = $this->input('nguoi_tham_gia_id');
        $eventId = $this->route('calendar'); // For updates

        if ($startTime && $endTime && $userId) {
            $conflicts = \Modules\Task\app\Models\Calendar::where('nguoi_tham_gia_id', $userId)
                ->where('id', '!=', $eventId) // Exclude current event for updates
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('thoi_gian_bat_dau', [$startTime, $endTime])
                          ->orWhereBetween('thoi_gian_ket_thuc', [$startTime, $endTime])
                          ->orWhere(function ($q) use ($startTime, $endTime) {
                              $q->where('thoi_gian_bat_dau', '<=', $startTime)
                                ->where('thoi_gian_ket_thuc', '>=', $endTime);
                          });
                })
                ->count();

            if ($conflicts > 0) {
                $validator->errors()->add('time_conflict', 'Thời gian này đã có sự kiện khác.');
            }
        }
    }

    /**
     * Validate recurring pattern.
     */
    private function validateRecurringPattern($validator): void
    {
        if ($this->input('is_recurring')) {
            $pattern = $this->input('recurring_pattern');
            $interval = $this->input('recurring_interval');
            $endDate = $this->input('recurring_end_date');

            // Validate interval based on pattern
            switch ($pattern) {
                case 'daily':
                    if ($interval > 365) {
                        $validator->errors()->add('recurring_interval', 'Khoảng lặp lại hàng ngày không được vượt quá 365.');
                    }
                    break;
                case 'weekly':
                    if ($interval > 52) {
                        $validator->errors()->add('recurring_interval', 'Khoảng lặp lại hàng tuần không được vượt quá 52.');
                    }
                    break;
                case 'monthly':
                    if ($interval > 12) {
                        $validator->errors()->add('recurring_interval', 'Khoảng lặp lại hàng tháng không được vượt quá 12.');
                    }
                    break;
                case 'yearly':
                    if ($interval > 10) {
                        $validator->errors()->add('recurring_interval', 'Khoảng lặp lại hàng năm không được vượt quá 10.');
                    }
                    break;
            }

            // Validate end date if provided
            if ($endDate) {
                $startDate = $this->input('thoi_gian_bat_dau');
                $endDateCarbon = Carbon::parse($endDate);
                $startDateCarbon = Carbon::parse($startDate);

                if ($endDateCarbon->diffInDays($startDateCarbon) > 365 * 2) {
                    $validator->errors()->add('recurring_end_date', 'Sự kiện định kỳ không được kéo dài quá 2 năm.');
                }
            }
        }
    }

    /**
     * Get validated data with additional processing.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Set default values
        if (!isset($validated['nguoi_tao_id'])) {
            $validated['nguoi_tao_id'] = auth()->id();
        }

        if (!isset($validated['loai_nguoi_tao'])) {
            $validated['loai_nguoi_tao'] = 'giang_vien'; // Default value
        }

        // Process recurring event data
        if (isset($validated['is_recurring']) && $validated['is_recurring']) {
            $validated['recurring_pattern'] = $validated['recurring_pattern'] ?? 'weekly';
            $validated['recurring_interval'] = $validated['recurring_interval'] ?? 1;
        }

        return $validated;
    }
}