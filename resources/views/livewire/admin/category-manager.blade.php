<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
        <button wire:click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-indigo-700 transition-colors">+ Add Category</button>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">Name</th>
                    <th class="px-5 py-3 text-left">Slug</th>
                    <th class="px-5 py-3 text-center">Products</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($categories as $category)
                <tr class="hover:bg-gray-50 transition-colors" wire:key="cat-{{ $category->id }}">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                    <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $category->slug }}</td>
                    <td class="px-5 py-3 text-center text-gray-700">{{ $category->products_count }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $category->is_active ? 'Active' : 'Hidden' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button wire:click="openEdit({{ $category->id }})" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Edit</button>
                            <button wire:click="delete({{ $category->id }})" wire:confirm="Delete this category?" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-lg font-bold">{{ $editing ? 'Edit Category' : 'Add Category' }}</h2>
                <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input wire:model.live="name" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500" />
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                    <input wire:model="slug" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 font-mono text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <input wire:model="sortOrder" type="number" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="mt-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model="isActive" type="checkbox" class="rounded" />
                            <span class="text-sm font-medium text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t flex gap-3 justify-end">
                <button wire:click="$set('showModal', false)" class="px-5 py-2 border border-gray-300 rounded-xl font-medium hover:bg-gray-50">Cancel</button>
                <button wire:click="save" class="px-5 py-2 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700">{{ $editing ? 'Update' : 'Create' }}</button>
            </div>
        </div>
    </div>
    @endif
</div>
