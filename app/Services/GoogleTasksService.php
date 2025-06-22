<?php

namespace App\Services;

use App\Models\GoogleTask;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google_Client;
use Google_Service_Tasks;

class GoogleTasksService
{
    protected Google_Client $client;
    protected ?Google_Service_Tasks $service = null;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(config('services.google-tasks.client_id'));
        $this->client->setClientSecret(config('services.google-tasks.client_secret'));
        $this->client->setRedirectUri(config('services.google-tasks.redirect'));
        $this->client->setScopes([
            'https://www.googleapis.com/auth/tasks',
            'https://www.googleapis.com/auth/tasks.readonly'
        ]);
    }

    public function createTask(array $taskData, User $user = null): ?GoogleTask
    {
        try {
            if (!$user) {
                $user = auth()->user();
            }

            if (!$user) {
                Log::error('No user available for creating Google task');
                return null;
            }

            // Get user's Google access token
            $emailAccount = $user->emailAccounts()
                ->where('provider', 'google-tasks')
                ->where('is_active', true)
                ->first();

            if (!$emailAccount || !$emailAccount->access_token) {
                Log::error('No active Google account found for user: ' . $user->id);
                return null;
            }

            // Refresh token if needed
            if ($emailAccount->needsTokenRefresh()) {
                $this->refreshToken($emailAccount);
            }

            $this->client->setAccessToken($emailAccount->access_token);
            $this->service = new Google_Service_Tasks($this->client);

            // Get default task list
            $taskList = $this->getDefaultTaskList();

            if (!$taskList) {
                Log::error('No task list found for user: ' . $user->id);
                return null;
            }

            // Create Google Task
            $googleTask = new \Google_Service_Tasks_Task();
            $googleTask->setTitle($taskData['title']);
            $googleTask->setNotes($taskData['notes'] ?? '');
            
            if (isset($taskData['due_date']) && $taskData['due_date']) {
                // Convert date to RFC 3339 format for Google Tasks API
                $dueDate = new \DateTime($taskData['due_date']);
                $googleTask->setDue($dueDate->format('c')); // RFC 3339 format
            }

            // Note: Google Tasks API doesn't support priority levels
            // Priority is only stored in our local database for internal organization

            $createdTask = $this->service->tasks->insert($taskList->getId(), $googleTask);

            // Save to database
            $task = GoogleTask::create([
                'user_id' => $user->id,
                'ticket_id' => $taskData['ticket_id'] ?? null,
                'parent_task_id' => $taskData['parent_task_id'] ?? null,
                'google_task_id' => $createdTask->getId(),
                'title' => $taskData['title'],
                'notes' => $taskData['notes'] ?? '',
                'internal_note' => $taskData['internal_note'] ?? '',
                'due_date' => $taskData['due_date'] ?? null,
                'completed' => $taskData['completed'] ?? false,
                'list_id' => $taskList->getId(),
                'list_name' => $taskList->getTitle(),
                'priority' => $taskData['priority'] ?? 'medium',
            ]);

            Log::info('Google task created successfully', [
                'task_id' => $task->id,
                'google_task_id' => $createdTask->getId(),
                'user_id' => $user->id
            ]);

            return $task;

        } catch (\Exception $e) {
            Log::error('Error creating Google task: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'task_data' => $taskData
            ]);
            return null;
        }
        
    }

    public function updateTask(GoogleTask $task, array $taskData, bool $syncToGoogle = true): bool
    {
        try {
            if ($syncToGoogle) {
                $emailAccount = $task->user->emailAccounts()
                    ->where('provider', 'google-tasks')
                    ->where('is_active', true)
                    ->first();

                if ($emailAccount && $emailAccount->access_token) {
                    if ($emailAccount->needsTokenRefresh()) {
                        $this->refreshToken($emailAccount);
                    }

                    $this->client->setAccessToken($emailAccount->access_token);
                    $this->service = new Google_Service_Tasks($this->client);

                    $googleTask = $this->service->tasks->get($task->list_id, $task->google_task_id);
                    
                    if (isset($taskData['title'])) {
                        $googleTask->setTitle($taskData['title']);
                    }
                    
                    if (isset($taskData['notes'])) {
                        $googleTask->setNotes($taskData['notes']);
                    }
                    
                    if (isset($taskData['due_date'])) {
                        // Convert date to RFC 3339 format for Google Tasks API
                        $dueDate = new \DateTime($taskData['due_date']);
                        $googleTask->setDue($dueDate->format('c')); // RFC 3339 format
                    }
                    
                    if (isset($taskData['completed'])) {
                        $googleTask->setStatus($taskData['completed'] ? 'completed' : 'needsAction');
                    }

                    $this->service->tasks->update($task->list_id, $task->google_task_id, $googleTask);
                }
            }

            // Update local database
            $task->update($taskData);

            return true;

        } catch (\Exception $e) {
            Log::error('Error updating Google task: ' . $e->getMessage(), [
                'task_id' => $task->id
            ]);
            return false;
        }
    }

    public function deleteTask(GoogleTask $task): bool
    {
        try {
            $emailAccount = $task->user->emailAccounts()
                ->where('provider', 'google-tasks')
                ->where('is_active', true)
                ->first();

            if ($emailAccount && $emailAccount->access_token) {
                if ($emailAccount->needsTokenRefresh()) {
                    $this->refreshToken($emailAccount);
                }

                $this->client->setAccessToken($emailAccount->access_token);
                $this->service = new Google_Service_Tasks($this->client);

                $this->service->tasks->delete($task->list_id, $task->google_task_id);
            }
            
            $task->delete();

            return true;

        } catch (\Exception $e) {
            Log::error('Error deleting Google task: ' . $e->getMessage(), [
                'task_id' => $task->id
            ]);
            return false;
        }
    }

    public function syncTasks(User $user): void
    {
        try {
            $emailAccount = $user->emailAccounts()
                ->where('provider', 'google-tasks')
                ->where('is_active', true)
                ->first();

            if (!$emailAccount || !$emailAccount->access_token) {
                Log::error('No active Google account found for user: ' . $user->id);
                return;
            }

            if ($emailAccount->needsTokenRefresh()) {
                $this->refreshToken($emailAccount);
            }

            $this->client->setAccessToken($emailAccount->access_token);
            $this->service = new Google_Service_Tasks($this->client);

            $taskLists = $this->service->tasklists->listTasklists();

            foreach ($taskLists->getItems() as $taskList) {
                $tasks = $this->service->tasks->listTasks($taskList->getId());
                
                foreach ($tasks->getItems() as $googleTask) {
                    $this->syncTask($user, $taskList, $googleTask);
                }
            }

            Log::info('Google tasks synced successfully for user: ' . $user->id);

        } catch (\Exception $e) {
            Log::error('Error syncing Google tasks: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);
        }
    }

    public function createTicketFromTask(GoogleTask $task): ?\App\Models\Ticket
    {
        try {
            $ticket = \App\Models\Ticket::create([
                'user_id' => $task->user_id,
                'subject' => 'Task: ' . $task->title,
                'body' => $task->notes ?? 'Task created from Google Tasks',
                'status' => 'open',
                'priority' => $task->priority,
                'google_task_id' => $task->id,
            ]);

            Log::info('Ticket created from Google task', [
                'task_id' => $task->id,
                'ticket_id' => $ticket->id
            ]);

            return $ticket;

        } catch (\Exception $e) {
            Log::error('Error creating ticket from Google task: ' . $e->getMessage(), [
                'task_id' => $task->id
            ]);
            return null;
        }
    }

    public function duplicateTask(GoogleTask $task): ?GoogleTask
    {
        try {
            $duplicatedTask = $task->replicate();
            $duplicatedTask->title = $task->title . ' (Copy)';
            $duplicatedTask->completed = false;
            $duplicatedTask->google_task_id = null; // Don't sync to Google initially
            $duplicatedTask->save();

            Log::info('Google task duplicated successfully', [
                'original_task_id' => $task->id,
                'duplicated_task_id' => $duplicatedTask->id
            ]);

            return $duplicatedTask;

        } catch (\Exception $e) {
            Log::error('Error duplicating Google task: ' . $e->getMessage(), [
                'task_id' => $task->id
            ]);
            return null;
        }
    }

    protected function syncTask(User $user, $taskList, $googleTask): void
    {
        try {
            $existingTask = GoogleTask::where('google_task_id', $googleTask->getId())
                ->where('user_id', $user->id)
                ->first();

            if ($existingTask) {
                // Update existing task
                $existingTask->update([
                    'title' => $googleTask->getTitle(),
                    'notes' => $googleTask->getNotes(),
                    'due_date' => $googleTask->getDue(),
                    'completed' => $googleTask->getStatus() === 'completed',
                    'list_id' => $taskList->getId(),
                    'list_name' => $taskList->getTitle(),
                ]);
            } else {
                // Create new task
                GoogleTask::create([
                    'user_id' => $user->id,
                    'google_task_id' => $googleTask->getId(),
                    'title' => $googleTask->getTitle(),
                    'notes' => $googleTask->getNotes(),
                    'due_date' => $googleTask->getDue(),
                    'completed' => $googleTask->getStatus() === 'completed',
                    'list_id' => $taskList->getId(),
                    'list_name' => $taskList->getTitle(),
                    'priority' => 'medium',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error syncing individual Google task: ' . $e->getMessage(), [
                'google_task_id' => $googleTask->getId(),
                'user_id' => $user->id
            ]);
        }
    }

    protected function getDefaultTaskList()
    {
        try {
            $taskLists = $this->service->tasklists->listTasklists();
            
            // Try to find "@default" list first
            foreach ($taskLists->getItems() as $taskList) {
                if ($taskList->getId() === '@default') {
                    return $taskList;
                }
            }
            
            // If no default list, return the first one
            return $taskLists->getItems()[0] ?? null;
            
        } catch (\Exception $e) {
            Log::error('Error getting default task list: ' . $e->getMessage());
            return null;
        }
    }

    protected function refreshToken($emailAccount): void
    {
        try {
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google-tasks.client_id'),
                'client_secret' => config('services.google-tasks.client_secret'),
                'refresh_token' => $emailAccount->refresh_token,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $emailAccount->update([
                    'access_token' => $data['access_token'],
                    'token_expires_at' => now()->addSeconds($data['expires_in']),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error refreshing Google token: ' . $e->getMessage());
        }
    }
} 