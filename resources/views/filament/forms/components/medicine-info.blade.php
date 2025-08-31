@if($record && $record->medicine)
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titer</label>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded border border-gray-300 dark:border-gray-600">
                {{ $record->medicine->titer ?? 'N/A' }}
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded border border-gray-300 dark:border-gray-600">
                {{ $record->medicine->category->name ?? 'N/A' }}
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded border border-gray-300 dark:border-gray-600">
                {{ $record->medicine->company->name ?? 'N/A' }}
            </div>
        </div>
    </div>
@else
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 00 2 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1 md:flex md:justify-between">
                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                    No medicine selected. Please select a medicine to view details.
                </p>
            </div>
        </div>
    </div>
@endif