@props(['title', 'projects', 'type'])

<div id="modal-{{ $type }}" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex justify-between items-center p-5 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ $title }}</h3>
            <button onclick="closeModal('{{ $type }}')" class="text-gray-400 hover:text-red-500 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-5 overflow-y-auto flex-1">
            @if($projects->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="py-3 px-4">Perusahaan</th>
                                @if($type === 'offers' || $type === 'rejected') <th class="py-3 px-4">Alamat</th> @endif
                                @if($type === 'offers') <th class="py-3 px-4">Tanggal</th> @endif
                                @if($type === 'progress_offers') <th class="py-3 px-4">Status</th> @endif
                                @if($type === 'rejected') <th class="py-3 px-4">Alasan</th> @endif
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 dark:text-gray-300">
                            @foreach($projects as $project)
                            <tr class="border-b border-gray-100 dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="py-3 px-4 font-medium">{{ $project->client_name ?? '-' }}</td>
                                @if($type === 'offers' || $type === 'rejected') <td class="py-3 px-4">{{ $project->address ?? '-' }}</td> @endif
                                @if($type === 'offers') <td class="py-3 px-4">{{ $project->created_at?->format('d/m/Y') ?? '-' }}</td> @endif
                                @if($type === 'progress_offers') <td class="py-3 px-4"><span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded text-xs font-semibold">{{ $project->status_text ?? 'Follow Up' }}</span></td> @endif
                                @if($type === 'rejected') <td class="py-3 px-4 text-red-500 dark:text-red-400">{{ $project->rejection_reason ?? '-' }}</td> @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-10 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <p>Tidak ada data.</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-right">
            <button onclick="closeModal('{{ $type }}')" class="px-5 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg font-medium transition">
                Tutup
            </button>
        </div>
    </div>
</div>