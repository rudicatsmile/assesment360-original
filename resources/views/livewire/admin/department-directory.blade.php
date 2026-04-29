<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">Departments</h1>
            <p class="text-sm text-zinc-500">Master data department untuk referensi users.</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="startCreate">Tambah Department</flux:button>
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 pt-10 pb-10"
            wire:click.self="cancelForm">
            <div class="w-full max-w-xl rounded-xl bg-white shadow-xl my-auto">
                <div class="flex items-center justify-between border-b border-zinc-100 bg-zinc-50 px-6 py-4 rounded-t-xl">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full {{ $editingId ? 'bg-blue-100 text-blue-600' : 'bg-emerald-100 text-emerald-600' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($editingId)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                @endif
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900">
                                {{ $editingId ? 'Edit Department' : 'Tambah Department Baru' }}
                            </h2>
                            <p class="text-xs text-zinc-500">
                                {{ $editingId ? 'Perbarui informasi department yang sudah ada.' : 'Isi data lengkap untuk membuat department baru.' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="cancelForm"
                        class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-200 hover:text-zinc-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-zinc-700">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                </svg>
                                Nama Department
                            </label>
                            <input type="text" wire:model.live.debounce.300ms="name"
                                class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all"
                                placeholder="Masukkan nama department">
                            @error('name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-zinc-700">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                                Urutan
                            </label>
                            <input type="number" min="0" wire:model.live="urut"
                                class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all"
                                placeholder="0">
                            @error('urut') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-zinc-700">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                                </svg>
                                Deskripsi
                            </label>
                            <textarea rows="3" wire:model.live.debounce.300ms="description"
                                class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all"
                                placeholder="Jelaskan fungsi department ini..."></textarea>
                            @error('description') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 rounded-b-xl">
                    <flux:button variant="ghost" wire:click="cancelForm">Batal</flux:button>
                    <flux:button variant="primary" wire:click="save">
                        {{ $editingId ? 'Simpan Perubahan' : 'Tambah Department' }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-3 rounded-xl border border-zinc-200 bg-white p-3 shadow-sm md:grid-cols-2">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama/deskripsi..."
            class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
        <select wire:model.live="perPage" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
            <option value="10">10 / halaman</option>
            <option value="20">20 / halaman</option>
            <option value="50">50 / halaman</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                    <tr>
                        <th class="px-4 py-3"><button type="button" wire:click="sortByColumn('id')"
                                class="inline-flex items-center gap-1">ID</button></th>
                        <th class="px-4 py-3"><button type="button" wire:click="sortByColumn('name')"
                                class="inline-flex items-center gap-1">Name</button></th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Users</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($departements as $department)
                        <tr>
                            <td class="px-4 py-3 text-zinc-500">{{ $department->id }}</td>
                            <td class="px-4 py-3 text-zinc-900">{{ $department->name }}</td>
                            <td class="px-4 py-3 text-zinc-700">{{ $department->description ?: '-' }}</td>
                            <td class="px-4 py-3 text-zinc-700">{{ $department->users_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <flux:button size="xs" variant="outline" wire:click="startEdit({{ $department->id }})">
                                        Edit</flux:button>
                                    <flux:button size="xs" variant="danger" wire:click="delete({{ $department->id }})"
                                        wire:confirm="Hapus department ini?">
                                        Hapus
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-zinc-500">Belum ada data department.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $departements->links() }}
</div>