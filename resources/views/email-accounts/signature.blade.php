<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Signature for') }} {{ $emailAccount->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('email-accounts.signature.update', $emailAccount) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Signature Text -->
                        <div class="mb-6">
                            <x-input-label for="signature_text" :value="__('Signature Text')" class="dark:text-white" />
                            <textarea
                                id="signature_text"
                                name="signature_text"
                                rows="6"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter your email signature text here..."
                            >{{ old('signature_text', $emailAccount->signature_text) }}</textarea>
                            <x-input-error :messages="$errors->get('signature_text')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Use the rich text editor below to format your signature. Maximum 2000 characters.
                            </p>
                        </div>

                        <!-- Signature Image -->
                        <div class="mb-6">
                            <x-input-label for="signature_image" :value="__('Signature Image')" />
                            
                            @if($emailAccount->signature_image_path)
                                <div class="mt-2 mb-4">
                                    <p class="text-sm text-gray-600 mb-2">Current signature image:</p>
                                    <img src="{{ Storage::url($emailAccount->signature_image_path) }}" 
                                         alt="Current signature" 
                                         class="max-w-xs border rounded-lg shadow-sm dark:bg-gray-700 dark:text-white">
                                    <form method="POST" action="{{ route('email-accounts.signature.remove-image', $emailAccount) }}" class="mt-2">
                                        @csrf
                                        @method('DELETE')
                                        <x-danger-button type="submit" class="text-sm">
                                            {{ __('Remove Image') }}
                                        </x-danger-button>
                                    </form>
                                </div>
                            @endif

                            <input
                                type="file"
                                id="signature_image"
                                name="signature_image"
                                accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            />
                            <x-input-error :messages="$errors->get('signature_image')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Supported formats: JPEG, PNG, JPG, GIF. Maximum size: 2MB.
                            </p>
                        </div>

                        <!-- Preview Section -->
                        <div class="mb-6">
                            <x-input-label :value="__('Preview')" />
                            <div class="mt-2 p-4 border rounded-lg bg-gray-50 dark:bg-gray-700 min-h-[100px]">
                                <div id="signature-preview">
                                    @if($emailAccount->signature_text || $emailAccount->signature_image_path)
                                        @if($emailAccount->signature_text)
                                            <div class="mb-2">{!! $emailAccount->signature_text !!}</div>
                                        @endif
                                        @if($emailAccount->signature_image_path)
                                            <img src="{{ Storage::url($emailAccount->signature_image_path) }}" 
                                                 alt="Signature" 
                                                 class="max-w-xs">
                                        @endif
                                    @else
                                        <p class="text-gray-400 italic">No signature content to preview</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end mt-6 dark:text-white dark:bg-gray-800">
                            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Update Signature') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- TinyMCE CDN -->

    <script src="https://cdn.tiny.cloud/1/$_ENV['TINYMCE_API_KEY']/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#signature_text',
            height: 200,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
                skin: window.matchMedia("(prefers-color-scheme: dark)").matches
                    ? "oxide-dark"
                    : "oxide",
                content_css: window.matchMedia("(prefers-color-scheme: dark)").matches
                    ? "dark"
                    : "default",
            setup: function(editor) {
                // Update preview when content changes
                editor.on('input', function() {
                    updatePreview();
                });
                
                // Update preview when content is set
                editor.on('setContent', function() {
                    updatePreview();
                });
            }
        });

        // Function to update preview
        function updatePreview() {
            const preview = document.getElementById('signature-preview');
            const content = tinymce.get('signature_text').getContent();
            
            if (content) {
                preview.innerHTML = '<div class="mb-2">' + content + '</div>';
                
                // Add image if exists
                const currentImage = preview.querySelector('img');
                if (currentImage) {
                    preview.appendChild(currentImage.cloneNode(true));
                }
            } else {
                const currentImage = preview.querySelector('img');
                if (currentImage) {
                    preview.innerHTML = '';
                    preview.appendChild(currentImage);
                } else {
                    preview.innerHTML = '<p class="text-gray-400 italic">No signature content to preview</p>';
                }
            }
        }

        // Image preview
        document.getElementById('signature_image').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('signature-preview');
                    const textContent = preview.querySelector('div');
                    
                    preview.innerHTML = '';
                    if (textContent) {
                        preview.appendChild(textContent);
                    }
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'max-w-xs';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-app-layout> 