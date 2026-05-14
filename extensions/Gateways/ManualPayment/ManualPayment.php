<?php

namespace Paymenter\Extensions\Gateways\ManualPayment;

use App\Attributes\ExtensionMeta;
use App\Classes\Extension\Gateway;
use App\Helpers\ExtensionHelper;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

#[ExtensionMeta(
    name: 'Manual Payment Gateway',
    description: 'Collect manual payment details for bKash and Nagad transfers.',
    version: '1.0.0',
    author: 'Paymenter',
    url: 'https://paymenter.org',
    icon: 'data:image/svg+xml;base64,PHN2ZyBmaWxsPSJub25lIiB2aWV3Qm94PSIwIDAgMjQgMjQiIHN0cm9rZT0iIzEwYjk4MSIgc3Ryb2tlLXdpZHRoPSIxLjUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBkPSJNMTIuMDEyIDYuNzQxYy0xLjM5MSAwLTIuNzQxLjIxMy0zLjk5Ny42MTh2Mi45OWg4VjcuMzU5Yy0xLjI1Ni0uNDA1LTIuNjA2LS42MTgtNC4wMDMtLjYxOFpNMTYgMTIuMzVoLTguMDAydjMuNjVjMS4yNTYuNDA1IDIuNjA2LjYxOCA0LjAwMy42MTggMS4zOTYgMCAyLjc0Ni0uMjEzIDQtLjYxOHYtMy42NVpNMy43NSAxMS4yNWEuNzUuNzUgMCAwIDEgLjc1LS43NWgxNWEuNzUuNzUgMCAwIDEgMCAxLjVoLTE1YS43NS43NSAwIDAgMS0uNzUtLjc1WiIgLz48L3N2Zz4='
)]
class ManualPayment extends Gateway
{
    public function boot()
    {
        require __DIR__ . '/routes.php';
        View::addNamespace('gateways.manual-payment', __DIR__ . '/resources/views');
    }

    public function getConfig($values = [])
    {
        return [
            [
                'name' => 'bkash_number',
                'label' => 'bKash Number',
                'type' => 'text',
                'placeholder' => '01XXXXXXXXX',
                'description' => 'Number shown on checkout for bKash payments.',
                'required' => false,
            ],
            [
                'name' => 'nagad_number',
                'label' => 'Nagad Number',
                'type' => 'text',
                'placeholder' => '01XXXXXXXXX',
                'description' => 'Number shown on checkout for Nagad payments.',
                'required' => false,
            ],
        ];
    }

    public function canUseGateway($total, $currency, $type, $items): bool
    {
        return filled($this->config('bkash_number')) || filled($this->config('nagad_number'));
    }

    public function pay(Invoice $invoice, $total)
    {
        $availableMethods = collect([
            'bkash' => $this->config('bkash_number'),
            'nagad' => $this->config('nagad_number'),
        ])->filter(fn ($number) => filled($number))->keys()->values();

        return view('gateways.manual-payment::pay', [
            'invoice' => $invoice,
            'bkashNumber' => $this->config('bkash_number'),
            'nagadNumber' => $this->config('nagad_number'),
            'defaultMethod' => $availableMethods->first(),
        ]);
    }

    public function submit(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'pending') {
            return redirect()->route('invoices.show', $invoice)->with('notification', [
                'type' => 'error',
                'message' => 'This invoice cannot be paid.',
            ]);
        }

        $enabledMethods = collect([
            'bkash' => $this->config('bkash_number'),
            'nagad' => $this->config('nagad_number'),
        ])->filter(fn ($number) => filled($number))->keys()->values()->all();

        $validated = $request->validate([
            'payment_method' => ['required', 'string', Rule::in($enabledMethods)],
            'sender_number' => ['required', 'string', 'max:30'],
            'transaction_id' => ['required', 'string', 'max:100'],
        ]);

        $reference = sprintf(
            'TrxID: %s | Method: %s | Sender: %s',
            $validated['transaction_id'],
            strtoupper($validated['payment_method']),
            $validated['sender_number']
        );

        ExtensionHelper::addProcessingPayment(
            $invoice->id,
            class_basename(static::class),
            $invoice->remaining,
            null,
            $reference,
        );

        return redirect()->route('invoices.show', $invoice, ['checkPayment' => true])->with('notification', [
            'type' => 'success',
            'message' => 'Payment details submitted successfully. We will verify your transaction shortly.',
        ]);
    }
}
