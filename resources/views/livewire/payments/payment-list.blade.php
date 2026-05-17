<div>
    @if($flashMessage)
        <div class="mb-4 px-4 py-3 rounded text-sm {{ $flashType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
            {{ $flashMessage }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-4 mb-4 flex gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Filter by Status</label>
            <select wire:model.live="status" class="border rounded px-3 py-2 text-sm">
                <option value="">All</option>
                <option>Pending</option><option>Paid</option>
                <option>Partial</option><option>Refunded</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Passenger</th>
                    <th class="px-4 py-3 text-left">Route</th>
                    <th class="px-4 py-3 text-right">Amount Due</th>
                    <th class="px-4 py-3 text-right">Amount Paid</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3 text-left">Method</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400">{{ $payment->payment_id }}</td>
                        <td class="px-4 py-3 font-medium">{{ $payment->passenger->full_name }}</td>
                        <td class="px-4 py-3">
                            {{ $payment->booking->schedule->route->origin }}
                            → {{ $payment->booking->schedule->route->destination }}
                        </td>
                        <td class="px-4 py-3 text-right">৳{{ number_format($payment->amount_due, 2) }}</td>
                        <td class="px-4 py-3 text-right">৳{{ number_format($payment->amount_paid, 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $payment->balance_due > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                            ৳{{ number_format($payment->balance_due, 2) }}
                        </td>
                        <td class="px-4 py-3">{{ $payment->payment_method }}</td>
                        <td class="px-4 py-3">
                            @php $sc = ['Paid'=>'bg-green-100 text-green-700','Pending'=>'bg-yellow-100 text-yellow-700','Partial'=>'bg-orange-100 text-orange-700','Refunded'=>'bg-gray-100 text-gray-600']; @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $sc[$payment->payment_status] ?? '' }}">
                                {{ $payment->payment_status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            @if(in_array($payment->payment_status, ['Pending', 'Partial']))
                                <button wire:click="openPay({{ $payment->payment_id }})" class="text-xs text-green-600 hover:underline">Pay Full</button>
                                <button wire:click="openPay({{ $payment->payment_id }}, true)" class="text-xs text-blue-600 hover:underline">Partial</button>
                            @endif
                            @if($payment->payment_status === 'Paid' && $payment->booking->booking_status === 'Cancelled')
                                <button wire:click="refund({{ $payment->payment_id }})" class="text-xs text-orange-600 hover:underline"
                                    onclick="return confirm('Issue refund?')">Refund</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $payments->links() }}</div>
    </div>

    {{-- Pay Modal --}}
    @if($showPayModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm p-6 space-y-4">
                <h3 class="text-lg font-semibold">{{ $isPartial ? 'Record Partial Payment' : 'Confirm Full Payment' }}</h3>

                <div>
                    <label class="text-sm font-medium">Payment Method</label>
                    <select wire:model="paymentMethod" class="w-full border rounded px-3 py-2 text-sm mt-1">
                        <option>Cash</option><option>Card</option>
                        <option>Mobile Banking</option><option>Online</option>
                    </select>
                </div>

                @if($isPartial)
                    <div>
                        <label class="text-sm font-medium">Amount</label>
                        <input wire:model="partialAmount" type="number" step="0.01" min="0.01"
                            class="w-full border rounded px-3 py-2 text-sm mt-1">
                        @error('partialAmount') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="flex gap-3 pt-2">
                    <button wire:click="confirmPay" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">Confirm</button>
                    <button wire:click="$set('showPayModal', false)" class="border px-4 py-2 rounded text-sm hover:bg-gray-50">Cancel</button>
                </div>
            </div>
        </div>
    @endif
</div>
