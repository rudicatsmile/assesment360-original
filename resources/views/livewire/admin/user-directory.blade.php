<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">Users</h1>
            <p class="text-sm text-zinc-500">Daftar pengguna aktif berdasarkan role.</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="startCreate">Tambah Pengguna</flux:button>
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 pt-10 pb-10"
            wire:click.self="cancelForm">
            <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl my-auto">
                <div class="flex items-center justify-between border-b border-zinc-100 bg-zinc-50 px-6 py-4 rounded-t-xl">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full {{ $editingUserId ? 'bg-blue-100 text-blue-600' : 'bg-emerald-100 text-emerald-600' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($editingUserId)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                @endif
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900">
                                {{ $editingUserId ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}
                            </h2>
                            <p class="text-xs text-zinc-500">
                                {{ $editingUserId ? 'Perbarui informasi pengguna yang sudah ada.' : 'Isi data lengkap untuk membuat pengguna baru.' }}
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
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                Nama Lengkap
                            </label>
                            <input type="text" wire:model.live.debounce.300ms="name"
                                class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all"
                                placeholder="Masukkan nama lengkap">
                            @error('name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-zinc-700">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                                Email
                            </label>
                            <input type="email" wire:model.live.debounce.300ms="email"
                                class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all"
                                placeholder="email@contoh.com">
                            @error('email') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-zinc-700">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                                Nomor Telepon
                            </label>
                            <input type="text" wire:model.live.debounce.300ms="phone_number"
                                class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all"
                                placeholder="08xxxxxxxxxx">
                            @error('phone_number') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-zinc-700">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                Password {{ $editingUserId ? '(opsional)' : '' }}
                            </label>
                            <input type="password" wire:model.live.debounce.300ms="password"
                                class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all"
                                placeholder="{{ $editingUserId ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}">
                            @error('password') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-zinc-700">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                Role
                            </label>
                            <select wire:model.live="role_id"
                                class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all bg-white">
                                <option value="">Pilih role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-zinc-700">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                </svg>
                                Department
                            </label>
                            <select wire:model.live="department_id"
                                class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all bg-white">
                                <option value="">Tanpa Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <flux:heading size="sm" class="mb-2">Department yang Bisa Dievaluasi</flux:heading>
                            <flux:text size="sm" class="mb-2 text-zinc-500">Pilih department yang bisa dievaluasi oleh user
                                ini. Jika tidak ada yang dipilih, user mengikuti flow normal.
                                @if($editingUserId)
                                    Tombol <span class="font-medium text-amber-600">Reset Sesi</span> di samping nama departemen akan mereset jawaban dan timer hanya untuk departemen tersebut.
                                @endif
                            </flux:text>
                            <div class="flex flex-col gap-1 max-h-48 overflow-y-auto border rounded-lg p-3">
                                @foreach($departments as $dept)
                                    <div class="flex items-center justify-between gap-2 rounded px-2 py-1 hover:bg-zinc-50">
                                        <label class="flex flex-1 items-center gap-2 text-sm cursor-pointer">
                                            <input type="checkbox" wire:model="selectedEvaluableDepartments" value="{{ $dept->id }}"
                                                class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                                            <span>{{ $dept->name }}</span>
                                        </label>
                                        @if($editingUserId && in_array((string) $dept->id, $selectedEvaluableDepartments) && in_array((int) $dept->id, $editingUserSubmittedDepartmentIds))
                                            <button type="button"
                                                wire:click="resetUserSessionByDepartment({{ $editingUserId }}, {{ $dept->id }})"
                                                wire:confirm="Reset sesi untuk departemen {{ $dept->name }}? Timer departemen ini akan direset dan jawaban yang sudah dikirim untuk departemen ini akan dikembalikan ke draft."
                                                class="shrink-0 text-xs text-amber-600 hover:text-amber-800 hover:underline">
                                                Reset Sesi
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-zinc-700">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Status Aktif
                            </label>
                            <select wire:model.live="is_active"
                                class="w-full rounded-lg border border-zinc-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all bg-white">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                            @error('is_active') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-zinc-700">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Batas Waktu Pengisian Kuisioner
                            </label>
                            <div class="flex flex-wrap items-center gap-3">
                                <div
                                    class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2.5">
                                    <input type="number" wire:model.live="time_limit_hours" min="0" max="168"
                                        placeholder="0"
                                        class="w-16 rounded-md border border-zinc-300 px-2 py-2 text-center text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <span class="text-sm text-zinc-600">Jam</span>
                                </div>
                                <div
                                    class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2.5">
                                    <input type="number" wire:model.live="time_limit_minutes" min="0" max="59"
                                        placeholder="0"
                                        class="w-16 rounded-md border border-zinc-300 px-2 py-2 text-center text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <span class="text-sm text-zinc-600">Menit</span>
                                </div>
                                @if($time_limit_hours || $time_limit_minutes)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        {{ ($time_limit_hours ?? 0) * 60 + ($time_limit_minutes ?? 0) }} menit total
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-400">Kosongkan jika tidak ada batas waktu</span>
                                @endif
                            </div>
                            <p class="text-xs text-zinc-400">Timer berlaku untuk satu sesi pengisian semua kuisioner.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 rounded-b-xl">
                    <flux:button variant="ghost" wire:click="cancelForm">Batal</flux:button>
                    <flux:button variant="primary" wire:click="saveUser">
                        {{ $editingUserId ? 'Simpan Perubahan' : 'Tambah Pengguna' }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-3 rounded-xl border border-zinc-200 bg-white p-3 shadow-sm md:grid-cols-3 lg:grid-cols-7">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama/email..."
            class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
        <select wire:model.live="roleFilter" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
            <option value="">Semua role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}">{{ $role->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
        </select>
        <select wire:model.live="departmentFilter" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
            <option value="">Semua department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
        </select>
        <input type="text" wire:model.live.debounce.300ms="phoneFilter" placeholder="Filter no. telepon..."
            class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
        <select wire:model.live="questionnaireFilter" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
            <option value="">Semua kuisioner</option>
            <option value="completed">Sudah menyelesaikan</option>
            <option value="in_progress">Belum / Sedang menyelesaikan</option>
            <option value="not_started">Belum menyelesaikan</option>
        </select>
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
                        <th class="px-4 py-3"><button type="button" wire:click="sortUsers('id')"
                                class="inline-flex items-center gap-1">ID <span
                                    class="text-[10px] text-zinc-500">{{ $sortBy === 'id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' }}</span></button>
                        </th>
                        <th class="px-4 py-3"><button type="button" wire:click="sortUsers('name')"
                                class="inline-flex items-center gap-1">Nama <span
                                    class="text-[10px] text-zinc-500">{{ $sortBy === 'name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' }}</span></button>
                        </th>
                        <th class="px-4 py-3">
                            <div class="inline-flex items-center gap-2">
                                <button type="button" wire:click="sortUsers('email')"
                                    class="inline-flex items-center gap-1">Kontak <span
                                        class="text-[10px] text-zinc-500">{{ $sortBy === 'email' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' }}</span></button>
                                <button type="button" wire:click="sortUsers('phone_number')"
                                    class="inline-flex items-center gap-1 text-zinc-500">No. HP <span
                                        class="text-[10px] text-zinc-500">{{ $sortBy === 'phone_number' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' }}</span></button>
                            </div>
                        </th>
                        <th class="px-4 py-3"><button type="button" wire:click="sortUsers('role')"
                                class="inline-flex items-center gap-1">Role <span
                                    class="text-[10px] text-zinc-500">{{ $sortBy === 'role' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' }}</span></button>
                        </th>
                        <th class="px-4 py-3"><button type="button" wire:click="sortUsers('department')"
                                class="inline-flex items-center gap-1">Department <span
                                    class="text-[10px] text-zinc-500">{{ $sortBy === 'department' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' }}</span></button>
                        </th>
                        <th class="px-4 py-3"><button type="button" wire:click="sortUsers('is_active')"
                                class="inline-flex items-center gap-1">Status <span
                                    class="text-[10px] text-zinc-500">{{ $sortBy === 'is_active' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' }}</span></button>
                        </th>
                        <th class="px-4 py-3">Batas Waktu</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3 text-zinc-500">{{ $user->id }}</td>
                            <td class="px-4 py-3 text-zinc-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-zinc-700">
                                <div class="flex min-w-[220px] flex-col">
                                    <span class="truncate">{{ $user->email }}</span>
                                    <span class="text-xs text-zinc-500">{{ $user->phone_number ?: '-' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-zinc-700">{{ $user->roleRef?->name ?: $user->role }}</td>
                            <td class="px-4 py-3 text-zinc-700">
                                {{ $user->departmentRef?->name ?: '-' }}
                                @if($user->evaluable_departments_count > 0)
                                    <flux:badge size="sm" color="indigo" class="ml-1">Multi-Dept</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-200 text-zinc-700' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($user->time_limit_minutes)
                                    <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700">
                                        {{ intdiv($user->time_limit_minutes, 60) }}j {{ $user->time_limit_minutes % 60 }}m
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-400">Tanpa batas</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    @if($user->filling_started_at || $user->responses()->where('status', 'submitted')->exists())
                                        <flux:button size="xs" variant="outline" wire:click="resetUserSession({{ $user->id }})"
                                            wire:confirm="Reset sesi pengisian {{ $user->name }}? Timer akan direset dan SEMUA jawaban yang sudah dikirim (seluruh departemen) akan dikembalikan ke draft. Untuk reset per-departemen, gunakan tombol Reset Sesi di popup Edit Pengguna."
                                            class="text-amber-700 ring-amber-300 hover:bg-amber-50">
                                            Reset Sesi
                                        </flux:button>
                                    @endif
                                    <flux:button size="xs" variant="outline" wire:click="startEdit({{ $user->id }})">Edit
                                    </flux:button>
                                    <flux:button size="xs" variant="danger"
                                        wire:click="confirmDeleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                        Hapus
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-zinc-500">Belum ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $users->links() }}

    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="cancelDeleteUser">
            <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
                <div class="px-6 py-5 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900">Hapus Pengguna</h3>
                    <p class="mt-2 text-sm text-zinc-500">
                        Apakah Anda yakin ingin menghapus pengguna
                        <span class="font-semibold text-zinc-700">"{{ $deletingUserName }}"</span>?
                        Data akan dihapus secara soft delete.
                    </p>
                </div>
                <div class="flex gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 rounded-b-xl">
                    <flux:button variant="ghost" class="flex-1" wire:click="cancelDeleteUser">Batal</flux:button>
                    <flux:button variant="danger" class="flex-1" wire:click="executeDeleteUser">Ya, Hapus</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>