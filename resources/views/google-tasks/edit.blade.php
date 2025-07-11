<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Google Task') }} - {{ $googleTask->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form id="update-task-form" method="POST" action="{{ route('google-tasks.update', $googleTask) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Task Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Task Title</label>
                                    <input type="text" name="title" id="title" value="{{ old('title', $googleTask->title) }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="task_list_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Task List</label>
                                    <select name="task_list_id" id="task_list_id" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select a task list</option>
                                        @foreach($taskLists as $list)
                                            <option value="{{ $list->list_id }}" {{ old('task_list_id', $googleTask->list_id) == $list->list_id ? 'selected' : '' }}>
                                                {{ $list->list_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('task_list_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Task Details -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Task Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</label>
                                    <input type="datetime-local" name="due_date" id="due_date" value="{{ old('due_date', $googleTask->due_date ? $googleTask->due_date->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('due_date')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                                    <select name="priority" id="priority" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">No Priority</option>
                                        <option value="low" {{ old('priority', $googleTask->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', $googleTask->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority', $googleTask->priority) === 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                    @error('priority')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <select name="status" id="status" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="needsAction" {{ old('status', $googleTask->completed ? 'completed' : 'needsAction') === 'needsAction' ? 'selected' : '' }}>Needs Action</option>
                                        <option value="completed" {{ old('status', $googleTask->completed ? 'completed' : 'needsAction') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $googleTask->notes) }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add any additional notes or details about the task.</p>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Parent Task (if applicable) -->
                        @if($parentTasks->count() > 0)
                        <div>
                            <label for="parent_task_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent Task (Optional)</label>
                            <select name="parent_task_id" id="parent_task_id" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">No Parent Task</option>
                                @foreach($parentTasks as $parentTask)
                                    @if($parentTask->id !== $googleTask->id)
                                        <option value="{{ $parentTask->id }}" {{ old('parent_task_id', $googleTask->parent_task_id) == $parentTask->id ? 'selected' : '' }}>
                                            {{ $parentTask->title }} ({{ $parentTask->list_name ?? 'Unknown List' }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select a parent task if this is a subtask.</p>
                            @error('parent_task_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <!-- Sync Options -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Sync Options</h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="sync_to_google" id="sync_to_google" value="1" {{ old('sync_to_google', true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="sync_to_google" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                        Sync changes to Google Tasks
                                    </label>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">When enabled, changes will be automatically synced to your Google Tasks account.</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('google-tasks.show', $googleTask) }}" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors">
                                Update Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-save draft functionality
        let autoSaveTimer;
        const form = document.querySelector('form');

        function autoSave() {
            const currentFormData = new FormData(form);
            const data = {};
            currentFormData.forEach((value, key) => {
                // Skip the _method field as it's only for form submissions, not AJAX
                if (key !== '_method') {
                    data[key] = value;
                }
            });

            fetch('{{ route("google-tasks.auto-save", $googleTask) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Server returned non-JSON response');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.message) {
                    console.log('Auto-saved:', data.message);
                }
            })
            .catch(error => {
                console.error('Auto-save error:', error);
                // Don't show error to user for auto-save failures
            });
        }

        // Auto-save on form changes
        form.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSave, 2000); // Auto-save after 2 seconds of inactivity
        });

        // Clear auto-save timer on form submit
        form.addEventListener('submit', function() {
            clearTimeout(autoSaveTimer);
        });

        // Add event listener for create task form
        const updateTaskForm = document.getElementById('update-task-form');
        if (updateTaskForm) {
            updateTaskForm.addEventListener('submit', handleUpdateTaskSubmit);
        }

        // Update task submit
        function handleUpdateTaskSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Updating...';
            submitButton.disabled = true;
            
            fetch('{{ route("google-tasks.update", $googleTask) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                return response.json();
            })
            .then(data => {
                if (data.message) {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    successMessage.textContent = '✅ ' + data.message;
                    document.body.appendChild(successMessage);
                    
                    setTimeout(() => {
                        if (successMessage.parentNode) {
                            successMessage.parentNode.removeChild(successMessage);
                        }
                    }, 3000);
                    
                    // Redirect to tasks index page
                    window.location.href = '{{ route("google-tasks.index") }}';
                }
            })
            .catch(error => {
                console.error('Error updating task:', error);
                alert('Error updating task. Please try again.');
            })
            .finally(() => {
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
        }
    </script>
</x-app-layout> 