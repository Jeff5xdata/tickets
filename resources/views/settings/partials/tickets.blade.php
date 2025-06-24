<div class="space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Ticket Settings</h3>
        
        <!-- Default Ticket Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Default Ticket Settings</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="default_ticket_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Status</label>
                    <select name="default_ticket_status" id="default_ticket_status" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($ticketStatuses as $value => $label)
                            <option value="{{ $value }}" {{ $preferences->default_ticket_status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default status for new tickets</p>
                </div>
                <div>
                    <label for="default_ticket_priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Priority</label>
                    <select name="default_ticket_priority" id="default_ticket_priority" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($ticketPriorities as $value => $label)
                            <option value="{{ $value }}" {{ $preferences->default_ticket_priority === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default priority for new tickets</p>
                </div>
            </div>
        </div>

        <!-- Automation Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Automation Settings</h4>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="auto_create_tasks" id="auto_create_tasks" value="1" {{ $preferences->auto_create_tasks ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="auto_create_tasks" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Automatically Create Tasks from Tickets</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="auto_assign_tickets" id="auto_assign_tickets" value="1" {{ $preferences->auto_assign_tickets ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="auto_assign_tickets" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Automatically Assign Tickets to Me</label>
                </div>
            </div>
        </div>

        <!-- Display Settings -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">Display Settings</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="tickets_per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tickets per Page</label>
                    <input type="number" name="tickets_per_page" id="tickets_per_page" value="{{ $preferences->tickets_per_page }}" min="5" max="100" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Number of tickets to show per page</p>
                </div>
                <div>
                    <label for="ticket_display_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Display Format</label>
                    <select name="ticket_display_format" id="ticket_display_format" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($ticketDisplayFormats as $value => $label)
                            <option value="{{ $value }}" {{ $preferences->ticket_display_format === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default format for displaying ticket content</p>
                </div>
                <div>
                    <label for="ticket_sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Sort Order</label>
                    <select name="ticket_sort" id="ticket_sort" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($ticketSorts as $value => $label)
                            <option value="{{ $value }}" {{ $preferences->ticket_sort === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default sort order for tickets list</p>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="show_ticket_preview" id="show_ticket_preview" value="1" {{ $preferences->show_ticket_preview ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="show_ticket_preview" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Show Ticket Preview</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="enable_ticket_search" id="enable_ticket_search" value="1" {{ $preferences->enable_ticket_search ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="enable_ticket_search" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Enable Ticket Search</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="show_closed_tickets" id="show_closed_tickets" value="1" {{ $preferences->show_closed_tickets ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="show_closed_tickets" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Show Closed Tickets</label>
                </div>
            </div>
        </div>
    </div>
</div> 