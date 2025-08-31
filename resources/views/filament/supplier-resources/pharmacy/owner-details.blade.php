<div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-300">Name</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $owner->name }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-300">Email</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $owner->email }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-300">Phone</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $owner->phone ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-300">Pharmacy</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $owner->pharmacy->name ?? 'N/A' }}</p>
        </div>
    </div>
</div>