<?php

namespace App\Http\Controllers;

use App\Models\GoogleTask;
use App\Services\GoogleTasksService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GoogleTaskController extends Controller
{
    use AuthorizesRequests;

    protected GoogleTasksService $googleTasksService;

    public function __construct(GoogleTasksService $googleTasksService)
    {
        $this->googleTasksService = $googleTasksService;
    }

    public function index(Request $request): View
    {
        $query = auth()->user()->googleTasks();

        // Filter by completion status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('completed', $request->status === 'completed');
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by list
        if ($request->has('list_id') && $request->list_id !== 'all') {
            $query->where('list_id', $request->list_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $googleTasks = $query->orderBy('due_date', 'desc')->paginate(20);
        $taskLists = auth()->user()->googleTasks()
            ->select('list_id', 'list_name')
            ->whereNotNull('list_id')
            ->distinct()
            ->get();

        return view('google-tasks.index', compact('googleTasks', 'taskLists'));
    }

    public function create(): View
    {
        $taskLists = auth()->user()->googleTasks()
            ->select('list_id', 'list_name')
            ->whereNotNull('list_id')
            ->distinct()
            ->get();
        
        $parentTasks = auth()->user()->googleTasks()
            ->orderBy('title')
            ->get();

        return view('google-tasks.create', compact('taskLists', 'parentTasks'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:needsAction,completed',
            'task_list_id' => 'required|string',
            'parent_task_id' => 'nullable|exists:google_tasks,id',
        ]);

        // Convert status to completed boolean
        if (isset($validated['status'])) {
            $validated['completed'] = $validated['status'] === 'completed';
            unset($validated['status']);
        }

        // Convert task_list_id to list_id
        if (isset($validated['task_list_id'])) {
            $validated['list_id'] = $validated['task_list_id'];
            unset($validated['task_list_id']);
        }

        try {
            $task = $this->googleTasksService->createTask($validated);

            if ($task) {
                return response()->json([
                    'message' => 'Task created successfully',
                    'task' => $task
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to create task. Please check your Google account connection.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating task: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(GoogleTask $googleTask): View
    {
        $this->authorize('view', $googleTask);
        
        return view('google-tasks.show', compact('googleTask'));
    }

    public function edit(GoogleTask $googleTask): View
    {
        $this->authorize('update', $googleTask);
        
        $taskLists = auth()->user()->googleTasks()
            ->select('list_id', 'list_name')
            ->whereNotNull('list_id')
            ->distinct()
            ->get();
        
        $parentTasks = auth()->user()->googleTasks()
            ->where('id', '!=', $googleTask->id)
            ->orderBy('title')
            ->get();

        return view('google-tasks.edit', compact('googleTask', 'taskLists', 'parentTasks'));
    }

    public function update(Request $request, GoogleTask $googleTask): JsonResponse
    {
        $this->authorize('update', $googleTask);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'sometimes|in:needsAction,completed',
            'task_list_id' => 'sometimes|required|string',
            'parent_task_id' => 'nullable|exists:google_tasks,id',
            'sync_to_google' => 'sometimes|boolean',
        ]);

        // Convert status to completed boolean
        if (isset($validated['status'])) {
            $validated['completed'] = $validated['status'] === 'completed';
            unset($validated['status']);
        }

        // Convert task_list_id to list_id
        if (isset($validated['task_list_id'])) {
            $validated['list_id'] = $validated['task_list_id'];
            unset($validated['task_list_id']);
        }

        try {
            $success = $this->googleTasksService->updateTask($googleTask, $validated);

            if ($success) {
                return response()->json([
                    'message' => 'Task updated successfully',
                    'task' => $googleTask->fresh()
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to update task. Please check your Google account connection.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating task: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(GoogleTask $googleTask): JsonResponse
    {
        $this->authorize('delete', $googleTask);

        try {
            $success = $this->googleTasksService->deleteTask($googleTask);

            if ($success) {
                return response()->json([
                    'message' => 'Task deleted successfully'
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to delete task. Please check your Google account connection.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting task: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sync(): JsonResponse
    {
        try {
            $this->googleTasksService->syncTasks(auth()->user());

            return response()->json([
                'message' => 'Tasks synchronized successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error synchronizing tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Request $request, GoogleTask $googleTask): JsonResponse
    {
        $this->authorize('update', $googleTask);

        $validated = $request->validate([
            'completed' => 'required|boolean'
        ]);

        try {
            $success = $this->googleTasksService->updateTask($googleTask, [
                'completed' => $validated['completed']
            ]);

            if ($success) {
                return response()->json([
                    'message' => 'Task status updated successfully',
                    'task' => $googleTask->fresh()
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to update task status. Please check your Google account connection.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating task status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createTicket(Request $request, GoogleTask $googleTask): JsonResponse
    {
        $this->authorize('update', $googleTask);

        try {
            $ticket = $this->googleTasksService->createTicketFromTask($googleTask);

            if ($ticket) {
                return response()->json([
                    'message' => 'Ticket created successfully from task',
                    'ticket_id' => $ticket->id
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to create ticket from task.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function duplicate(Request $request, GoogleTask $googleTask): JsonResponse
    {
        $this->authorize('update', $googleTask);

        try {
            $duplicatedTask = $this->googleTasksService->duplicateTask($googleTask);

            if ($duplicatedTask) {
                return response()->json([
                    'message' => 'Task duplicated successfully',
                    'task_id' => $duplicatedTask->id
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to duplicate task.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error duplicating task: ' . $e->getMessage()
            ], 500);
        }
    }

    public function autoSave(Request $request, GoogleTask $googleTask): JsonResponse
    {
        $this->authorize('update', $googleTask);

        try {
            // More lenient validation for auto-save
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'notes' => 'nullable|string',
                'due_date' => 'nullable|string', // Allow any string for due_date in auto-save
                'priority' => 'nullable|in:low,medium,high',
                'status' => 'sometimes|in:needsAction,completed',
                'task_list_id' => 'sometimes|string',
                'parent_task_id' => 'nullable|exists:google_tasks,id',
                'sync_to_google' => 'sometimes|boolean',
            ]);

            // Only update if we have actual data to update
            if (empty($validated)) {
                return response()->json([
                    'message' => 'No changes to save'
                ]);
            }

            // Convert status to completed boolean
            if (isset($validated['status'])) {
                $validated['completed'] = $validated['status'] === 'completed';
                unset($validated['status']);
            }

            // Convert task_list_id to list_id
            if (isset($validated['task_list_id'])) {
                $validated['list_id'] = $validated['task_list_id'];
                unset($validated['task_list_id']);
            }

            // Handle due_date conversion
            if (isset($validated['due_date']) && $validated['due_date']) {
                try {
                    $dueDate = new \DateTime($validated['due_date']);
                    $validated['due_date'] = $dueDate->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    // If date parsing fails, remove it from validation
                    unset($validated['due_date']);
                }
            }

            // Remove sync_to_google from validation data as it's not a database field
            unset($validated['sync_to_google']);

            $success = $this->googleTasksService->updateTask($googleTask, $validated, false); // Don't sync to Google for auto-save

            if ($success) {
                return response()->json([
                    'message' => 'Draft saved successfully'
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to save draft.'
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error: ' . implode(', ', $e->errors()),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error saving draft: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:google_tasks,id',
            'status' => 'sometimes|in:needsAction,completed',
            'priority' => 'sometimes|in:low,medium,high',
        ]);

        $tasks = auth()->user()->googleTasks()->whereIn('id', $validated['task_ids'])->get();
        $updatedCount = 0;

        foreach ($tasks as $task) {
            $updateData = [];
            if (isset($validated['status'])) {
                $updateData['completed'] = $validated['status'] === 'completed';
            }
            if (isset($validated['priority'])) {
                $updateData['priority'] = $validated['priority'];
            }
            
            if (!empty($updateData)) {
                $success = $this->googleTasksService->updateTask($task, $updateData);
                if ($success) {
                    $updatedCount++;
                }
            }
        }

        return response()->json([
            'message' => $updatedCount . ' tasks updated successfully'
        ]);
    }

    public function getLists(): JsonResponse
    {
        try {
            $taskLists = auth()->user()->googleTasks()
                ->select('list_id', 'list_name')
                ->whereNotNull('list_id')
                ->distinct()
                ->get();

            return response()->json([
                'lists' => $taskLists
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching task lists: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createTaskFromTicket(\App\Models\Ticket $ticket, Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $ticket);

        // Debug: Log request details
        \Illuminate\Support\Facades\Log::info('Create task request details', [
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson(),
            'accept_header' => $request->header('Accept'),
            'content_type' => $request->header('Content-Type'),
            'user_agent' => $request->header('User-Agent')
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'internal_note' => 'nullable|string', // For internal use only
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high,urgent', // For internal use only
        ]);

        try {
            // Check if user has a Google Tasks account connected
            $googleAccount = auth()->user()->emailAccounts()
                ->where('provider', 'google-tasks')
                ->where('is_active', true)
                ->first();

            if (!$googleAccount) {
                \Illuminate\Support\Facades\Log::warning('User attempted to create Google task without connected account', [
                    'user_id' => auth()->id(),
                    'ticket_id' => $ticket->id
                ]);
                return redirect()->route('google-tasks.index')
                    ->with('error', 'No Google Tasks account connected. Please connect your Google account first.');
            }

            $taskData = array_merge($validated, [
                'ticket_id' => $ticket->id,
            ]);

            \Illuminate\Support\Facades\Log::info('Creating Google task', [
                'user_id' => auth()->id(),
                'ticket_id' => $ticket->id,
                'task_data' => $taskData
            ]);

            $task = $this->googleTasksService->createTask($taskData);

            if ($task) {
                \Illuminate\Support\Facades\Log::info('Google task created successfully, redirecting to task list', [
                    'task_id' => $task->id,
                    'user_id' => auth()->id()
                ]);
                
                // Check if this is an AJAX request or expects JSON
                if ($request->ajax() || $request->expectsJson() || $request->header('Accept') === 'application/json') {
                    return response()->json([
                        'message' => 'Task created successfully',
                        'task' => $task
                    ]);
                }
                
                // Force redirect for regular form submissions
                return redirect()->route('google-tasks.show', $task)
                    ->with('success', 'Task created successfully');
            } else {
                \Illuminate\Support\Facades\Log::error('Failed to create Google task - service returned null', [
                    'user_id' => auth()->id(),
                    'ticket_id' => $ticket->id,
                    'task_data' => $taskData
                ]);
                return redirect()->route('google-tasks.index')
                    ->with('error', 'Failed to create task. Please check your Google account connection.');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating Google task', [
                'user_id' => auth()->id(),
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('google-tasks.index')
                ->with('error', 'Error creating task: ' . $e->getMessage());
        }
    }
}
