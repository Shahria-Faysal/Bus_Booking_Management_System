<div>
    <div class="bg-white rounded-lg shadow p-4 mb-4 flex gap-3 items-end">
        <flux:select wire:model.live="action" label="Filter by Action" class="w-48">
            <flux:select.option value="">All Actions</flux:select.option>
            @foreach($actions as $a)
                <flux:select.option value="{{ $a }}">{{ $a }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:card class="p-0 overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Action</flux:table.column>
                <flux:table.column>Table</flux:table.column>
                <flux:table.column>Record ID</flux:table.column>
                <flux:table.column>Description</flux:table.column>
                <flux:table.column>Timestamp</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($logs as $log)
                    <flux:table.row>
                        <flux:table.cell class="text-zinc-400">{{ $log->log_id }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm">{{ $log->action_type }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-zinc-500">{{ $log->table_name }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500">{{ $log->record_id ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">{{ $log->description }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500 text-sm">
                            {{ $log->logged_at->format('d M Y H:i') }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-400 py-10">
                            No audit logs found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
        <div class="px-4 py-3 border-t dark:border-zinc-700">
            {{ $logs->links() }}
        </div>
    </flux:card>
</div>
