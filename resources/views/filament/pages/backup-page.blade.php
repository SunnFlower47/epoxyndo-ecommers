<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Daftar Backup Database
        </x-slot>
        <x-slot name="description">
            Berikut adalah daftar file backup database yang tersedia. Klik tombol "Buat Backup Baru" di atas untuk mem-backup database saat ini secara manual.
        </x-slot>

        @if(count($backups) > 0)
            <div class="overflow-x-auto ring-1 ring-gray-950/5 dark:ring-white/10 rounded-xl">
                <table class="w-full text-left divide-y divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-sm font-medium text-gray-950 dark:text-white">Nama File</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-950 dark:text-white">Ukuran</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-950 dark:text-white">Tanggal Dibuat</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-950 dark:text-white text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5 whitespace-nowrap bg-white dark:bg-gray-900">
                        @foreach($backups as $backup)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $backup['name'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $backup['size'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $backup['date'] }}</td>
                                <td class="px-4 py-3 text-sm text-right flex justify-end gap-2">
                                    <x-filament::button wire:click="downloadBackup('{{ $backup['path'] }}')" color="success" size="sm" icon="heroicon-m-arrow-down-tray">
                                        Download
                                    </x-filament::button>
                                    
                                    <x-filament::button wire:click="deleteBackup('{{ $backup['path'] }}')" color="danger" size="sm" icon="heroicon-m-trash" onclick="confirm('Apakah Anda yakin ingin menghapus file backup ini?') || event.stopImmediatePropagation()">
                                        Hapus
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                Belum ada file backup database yang tersedia.
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
