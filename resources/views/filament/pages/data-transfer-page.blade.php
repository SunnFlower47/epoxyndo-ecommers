<x-filament-panels::page>
    <div x-data="{
        showLottie: false,
        lottieType: 'loading',
        lottieSrc() {
            if (this.lottieType === 'success') {
                return '{{ asset('assets/icon/lottie/success.lottie') }}';
            }
            if (this.lottieType === 'warning') {
                return '{{ asset('assets/icon/lottie/warning.lottie') }}';
            }
            return '{{ asset('assets/icon/lottie/Loading animation blue.lottie') }}';
        }
    }"
    @show-lottie.window="
        lottieType = $event.detail.name;
        showLottie = true;
        if (lottieType === 'success' || lottieType === 'warning') {
            setTimeout(() => { showLottie = false; }, 3000);
        }
    "
    @hide-lottie.window="showLottie = false;"
    >

        <!-- Lottie Overlay for Loading -->
        <div wire:loading wire:target="executeExport, processPreview, executeImport" class="fixed inset-0 z-[99] flex items-center justify-center bg-black/50 backdrop-blur-sm" x-transition>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-2xl flex flex-col items-center">
                <dotlottie-player src="{{ asset('assets/icon/lottie/Loading animation blue.lottie') }}" background="transparent" speed="1" style="width: 200px; height: 200px;" loop autoplay></dotlottie-player>
                <h3 class="mt-4 text-xl font-bold text-gray-800 dark:text-gray-200">Memproses...</h3>
            </div>
        </div>

        <!-- Lottie Overlay for Success/Warning -->
        <div x-show="showLottie" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;" x-transition>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-2xl flex flex-col items-center">
                <dotlottie-player :src="lottieSrc()" background="transparent" speed="1" style="width: 200px; height: 200px;" loop autoplay></dotlottie-player>
                <h3 class="mt-4 text-xl font-bold text-gray-800 dark:text-gray-200" x-text="lottieType === 'success' ? 'Berhasil!' : 'Perhatian'"></h3>
            </div>
        </div>

        <x-filament::tabs label="Data Transfer Tabs">
            <x-filament::tabs.item
                :active="$activeTab === 'export'"
                wire:click="setTab('export')"
                icon="heroicon-m-arrow-up-tray"
            >
                Export
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'import'"
                wire:click="setTab('import')"
                icon="heroicon-m-arrow-down-tray"
            >
                Import
            </x-filament::tabs.item>
        </x-filament::tabs>

        <div class="mt-6">
            @if($activeTab === 'export')
                <x-filament::section>
                    <x-slot name="heading">
                        Export Data ke Excel
                    </x-slot>
                    <x-slot name="description">
                        Unduh data dari sistem ke dalam format Excel.
                    </x-slot>

                    <div class="flex flex-col gap-4">
                        <label class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Pilih Resource</label>
                        <select wire:model="exportResource" class="block w-full rounded-lg border-none bg-white py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:focus:ring-primary-500">
                            <option value="products">Produk</option>
                            <option value="orders" disabled>Pesanan (Belum tersedia)</option>
                            <option value="users" disabled>Pengguna (Belum tersedia)</option>
                        </select>

                        <div class="mt-4">
                            <x-filament::button wire:click="executeExport" icon="heroicon-m-arrow-down-tray" size="lg">
                                Download Excel
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>
            @endif

            @if($activeTab === 'import')
                <x-filament::section>
                    <x-slot name="heading">
                        Import Data dari Excel
                    </x-slot>
                    <x-slot name="description">
                        Unggah file Excel untuk memasukkan data ke sistem. Pastikan format kolom sesuai.
                    </x-slot>

                    <div class="flex flex-col gap-6">
                        <div>
                            <label class="text-sm font-medium leading-6 text-gray-950 dark:text-white block mb-2">Pilih Resource</label>
                            <select wire:model="importResource" class="block w-full rounded-lg border-none bg-white py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:focus:ring-primary-500 max-w-md">
                                <option value="products">Produk</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium leading-6 text-gray-950 dark:text-white block mb-2">Unggah File Excel (.xlsx)</label>
                            <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900 dark:file:text-primary-400">
                        </div>

                        <div class="flex gap-4">
                            <x-filament::button wire:click="processPreview" color="info" icon="heroicon-m-eye" size="md">
                                Preview Validasi
                            </x-filament::button>
                        </div>

                        @if($isPreviewing)
                            <div class="mt-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-lg font-bold mb-4">Hasil Validasi Preview</h4>
                                <div class="grid grid-cols-2 gap-4 max-w-sm">
                                    <div class="p-4 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                        <div class="text-sm font-medium">Baris Valid</div>
                                        <div class="text-2xl font-bold">{{ $previewValidCount }}</div>
                                    </div>
                                    <div class="p-4 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                        <div class="text-sm font-medium">Baris Tidak Valid</div>
                                        <div class="text-2xl font-bold">{{ $previewInvalidCount }}</div>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <x-filament::button wire:click="executeImport" color="success" icon="heroicon-m-play" size="lg">
                                        Eksekusi Import
                                    </x-filament::button>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-filament::section>
            @endif
        </div>
    </div>
</x-filament-panels::page>
