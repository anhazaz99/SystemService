<?php

namespace Modules\Task\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class xử lý validation cho Task
 * 
 * Class này chứa tất cả logic validation cho các operations liên quan đến Task
 * Tuân thủ Clean Architecture: chỉ xử lý validation, không chứa business logic
 */
class TaskRequest extends FormRequest
{
    /**
     * Kiểm tra quyền truy cập của user
     * 
     * @return bool True nếu user có quyền thực hiện request này
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Định nghĩa các validation rules cho request
     * 
     * @return array Mảng chứa các validation rules
     */
    public function rules(): array
    {
        $rules = [];

        // Quy tắc cơ bản cho create/update tasks
        if ($this->isMethod('POST') || $this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'deadline' => 'nullable|date|after:now',
                'status' => 'nullable|in:pending,in_progress,completed,overdue',
                'priority' => 'nullable|in:low,medium,high',
                'receivers' => 'required|array|min:1',
                'receivers.*.receiver_id' => 'required|integer',
                'receivers.*.receiver_type' => 'required|in:student,lecturer,class,all_students',
                'creator_id' => 'required|integer',
                'creator_type' => 'required|in:lecturer,student',
            ];
        }

        // Quy tắc cho lấy tasks theo receiver
        if ($this->routeIs('tasks.by-receiver')) {
            $rules = [
                'receiver_id' => 'required|integer',
                'receiver_type' => 'required|in:student,lecturer,class,all_students'
            ];
        }

        // Quy tắc cho lấy tasks theo creator
        if ($this->routeIs('tasks.by-creator')) {
            $rules = [
                'creator_id' => 'required|integer',
                'creator_type' => 'required|in:lecturer,student'
            ];
        }

        return $rules;
    }

    /**
     * Định nghĩa các message validation tùy chỉnh
     * 
     * @return array Mảng chứa các custom messages
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.max' => 'Task title cannot exceed 255 characters.',
            'deadline.after' => 'Deadline must be in the future.',
            'status.in' => 'Status must be pending, in_progress, completed, or overdue.',
            'priority.in' => 'Priority must be low, medium, or high.',
            'receivers.required' => 'At least one receiver is required.',
            'receivers.array' => 'Receivers must be an array.',
            'receivers.min' => 'At least one receiver is required.',
            'receivers.*.receiver_id.required' => 'Receiver ID is required.',
            'receivers.*.receiver_id.integer' => 'Receiver ID must be an integer.',
            'receivers.*.receiver_type.required' => 'Receiver type is required.',
            'receivers.*.receiver_type.in' => 'Receiver type must be student, lecturer, class, or all_students.',
            'creator_id.required' => 'Creator ID is required.',
            'creator_id.integer' => 'Creator ID must be an integer.',
            'creator_type.required' => 'Creator type is required.',
            'creator_type.in' => 'Creator type must be either lecturer or student.',
        ];
    }

    /**
     * Lấy dữ liệu đã được validate với xử lý bổ sung (nếu có)
     * 
     * @param string|null $key Key cụ thể cần lấy
     * @param mixed $default Giá trị mặc định nếu không tìm thấy
     * @return array|mixed Dữ liệu đã được validate và xử lý
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Thiết lập giá trị mặc định nếu không có
        if (!isset($validated['creator_id'])) {
            $validated['creator_id'] = auth()->id();
        }

        if (!isset($validated['creator_type'])) {
            $validated['creator_type'] = 'lecturer'; // Default value
        }

        return $validated;
    }

    /**
     * Xử lý validation errors để trả về JSON response
     * 
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_ERROR'
            ], 422)
        );
    }
}
