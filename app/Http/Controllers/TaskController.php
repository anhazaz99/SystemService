<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Task::with('files');
            
            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by priority
            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }
            
            // Filter by assignee
            if ($request->has('assignee_id')) {
                $query->where('assignee_id', $request->assignee_id);
            }
            
            // Search by title
            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }
            
            $tasks = $query->paginate($request->get('per_page', 10));
            
            return response()->json([
                'success' => true,
                'data' => $tasks->items(),
                'pagination' => [
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tasks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách công việc'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'assignee_id' => 'required|integer',
                'assignee_type' => 'required|in:giang_vien,sinh_vien',
                'creator_id' => 'required|integer',
                'creator_type' => 'required|in:giang_vien,sinh_vien',
                'files.*' => 'nullable|file|max:10240' // 10MB max
            ]);

            // Map API fields to database fields
            $taskData = [
                'tieu_de' => $validated['title'],
                'mo_ta' => $validated['description'],
                'nguoi_nhan_id' => $validated['assignee_id'],
                'loai_nguoi_nhan' => $validated['assignee_type'],
                'nguoi_tao_id' => $validated['creator_id'],
                'loai_nguoi_tao' => $validated['creator_type'],
                'ngay_tao' => now()
            ];

            $task = Task::create($taskData);

            // Handle file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('task-files', 'public');
                    TaskFile::create([
                        'task_id' => $task->id,
                        'file_path' => $path
                    ]);
                }
            }

            $task->load('files');

            return response()->json([
                'success' => true,
                'message' => 'Tạo công việc thành công',
                'data' => $task
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo công việc'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $task = Task::with('files')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy công việc'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $task = Task::findOrFail($id);
            
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'assignee_id' => 'sometimes|required|integer',
                'assignee_type' => 'sometimes|required|in:giang_vien,sinh_vien',
                'creator_id' => 'sometimes|required|integer',
                'creator_type' => 'sometimes|required|in:giang_vien,sinh_vien',
                'files.*' => 'nullable|file|max:10240'
            ]);

            // Map API fields to database fields
            $taskData = [];
            if (isset($validated['title'])) $taskData['tieu_de'] = $validated['title'];
            if (isset($validated['description'])) $taskData['mo_ta'] = $validated['description'];
            if (isset($validated['assignee_id'])) $taskData['nguoi_nhan_id'] = $validated['assignee_id'];
            if (isset($validated['assignee_type'])) $taskData['loai_nguoi_nhan'] = $validated['assignee_type'];
            if (isset($validated['creator_id'])) $taskData['nguoi_tao_id'] = $validated['creator_id'];
            if (isset($validated['creator_type'])) $taskData['loai_nguoi_tao'] = $validated['creator_type'];

            $task->update($taskData);

            // Handle new file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('task-files', 'public');
                    TaskFile::create([
                        'task_id' => $task->id,
                        'file_path' => $path
                    ]);
                }
            }

            $task->load('files');

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật công việc thành công',
                'data' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật công việc'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $task = Task::findOrFail($id);
            
            // Delete associated files
            foreach ($task->files as $file) {
                Storage::disk('public')->delete($file->file_path);
                $file->delete();
            }
            
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa công việc thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa công việc'
            ], 500);
        }
    }

    /**
     * Download task file
     */
    public function downloadFile($taskId, $fileId): JsonResponse
    {
        try {
            $file = TaskFile::where('task_id', $taskId)
                           ->where('id', $fileId)
                           ->firstOrFail();

            if (!Storage::disk('public')->exists($file->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File không tồn tại'
                ], 404);
            }

            return Storage::disk('public')->download($file->file_path, $file->file_name);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải file'
            ], 500);
        }
    }

    /**
     * Delete task file
     */
    public function deleteFile($taskId, $fileId): JsonResponse
    {
        try {
            $file = TaskFile::where('task_id', $taskId)
                           ->where('id', $fileId)
                           ->firstOrFail();

            Storage::disk('public')->delete($file->file_path);
            $file->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa file thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa file'
            ], 500);
        }
    }
}
