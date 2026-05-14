<div>
    <form method="POST" action="{{ route('extensions.gateways.manual-payment.submit', $invoice) }}" class="space-y-4">
        @csrf

        <div>
            <h3 class="text-base font-semibold mb-2">Select payment method</h3>
            <div class="space-y-2">
                @if(filled($bkashNumber))
                    <label class="flex items-start gap-3 p-3 border border-neutral rounded-lg cursor-pointer">
                        <input type="radio" name="payment_method" value="bkash" class="mt-1" {{ old('payment_method', 'bkash') === 'bkash' ? 'checked' : '' }}>
                        <div>
                            <p class="font-medium">bKash</p>
                            <p class="text-sm text-base/60">Send money to: <span class="font-semibold">{{ $bkashNumber }}</span></p>
                        </div>
                    </label>
                @endif

                @if(filled($nagadNumber))
                    <label class="flex items-start gap-3 p-3 border border-neutral rounded-lg cursor-pointer">
                        <input type="radio" name="payment_method" value="nagad" class="mt-1" {{ old('payment_method', !filled($bkashNumber) ? 'nagad' : '') === 'nagad' ? 'checked' : '' }}>
                        <div>
                            <p class="font-medium">Nagad</p>
                            <p class="text-sm text-base/60">Send money to: <span class="font-semibold">{{ $nagadNumber }}</span></p>
                        </div>
                    </label>
                @endif
            </div>
            @error('payment_method')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sender_number" class="block text-sm font-medium mb-1">Sender number</label>
            <input id="sender_number" name="sender_number" type="text" value="{{ old('sender_number') }}"
                   class="w-full rounded-md border border-neutral bg-background-secondary px-3 py-2" required>
            @error('sender_number')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="transaction_id" class="block text-sm font-medium mb-1">Transaction ID</label>
            <input id="transaction_id" name="transaction_id" type="text" value="{{ old('transaction_id') }}"
                   class="w-full rounded-md border border-neutral bg-background-secondary px-3 py-2" required>
            @error('transaction_id')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:opacity-90">
            Submit payment details
        </button>
    </form>
</div>
