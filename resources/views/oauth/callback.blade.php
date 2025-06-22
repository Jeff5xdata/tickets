<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Callback</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="text-center">
                @if($success)
                    <!-- Success Icon -->
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 mb-4">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Success!</h3>
                @else
                    <!-- Error Icon -->
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 mb-4">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Error</h3>
                @endif
                
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">{{ $message }}</p>
                
                <div class="space-y-3">
                    <button onclick="closeAndRedirect()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors">
                        Continue
                    </button>
                    
                    @if(!$success)
                        <button onclick="window.close()" class="w-full bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded transition-colors">
                            Close Window
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeAndRedirect() {
            // Close this popup window
            window.close();
            
            // Redirect the parent window
            if (window.opener && !window.opener.closed) {
                window.opener.location.href = '{{ $redirect_url }}';
            } else {
                // Fallback: redirect this window if opener is not available
                window.location.href = '{{ $redirect_url }}';
            }
        }

        // Auto-close and redirect after 3 seconds on success
        @if($success)
            setTimeout(function() {
                closeAndRedirect();
            }, 3000);
        @endif
    </script>
</body>
</html> 