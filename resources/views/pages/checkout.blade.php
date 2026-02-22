<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Stripe\Stripe;
use Stripe\PaymentIntent;

new class extends Component {
    #[Rule('required|string|max:100')]
    public string $firstName = '';

    #[Rule('required|string|max:100')]
    public string $lastName = '';

    #[Rule('required|email|max:255')]
    public string $email = '';

    #[Rule('required|string|max:20')]
    public string $phone = '';

    #[Rule('required|string|max:255')]
    public string $address = '';

    #[Rule('required|string|max:100')]
    public string $city = '';

    #[Rule('required|string|max:100')]
    public string $state = '';

    #[Rule('required|string|max:20')]
    public string $zip = '';

    #[Rule('required|string|max:100')]
    public string $country = 'US';

    public string $notes = '';
    public string $clientSecret = '';
    public bool $processing = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->email = $user->email;
            $nameParts = explode(' ', $user->name, 2);
            $this->firstName = $nameParts[0] ?? '';
            $this->lastName = $nameParts[1] ?? '';
            $this->phone = $user->phone ?? '';
            if ($user->address) {
                $this->address = $user->address['address'] ?? '';
                $this->city = $user->address['city'] ?? '';
                $this->state = $user->address['state'] ?? '';
                $this->zip = $user->address['zip'] ?? '';
                $this->country = $user->address['country'] ?? 'US';
            }
        }
    }

    public function createPaymentIntent(): void
    {
        $cart = app(CartService::class);
        $total = $cart->getTotal();

        if ($total <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Your cart is empty.');
            return;
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $intent = PaymentIntent::create([
                'amount' => (int) round($total * 100),
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => ['user_id' => Auth::id() ?? 'guest'],
            ]);
            $this->clientSecret = $intent->client_secret;
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Payment setup failed. Please try again.');
        }
    }

    public function placeOrder(string $paymentIntentId = ''): void
    {
        $this->validate();
        $this->processing = true;

        $cart = app(CartService::class);
        $items = $cart->getItems();

        if ($items->isEmpty()) {
            $this->dispatch('notify', type: 'error', message: 'Your cart is empty.');
            $this->processing = false;
            return;
        }

        $subtotal = $cart->getTotal();
        $tax = round($subtotal * 0.08, 2);
        $total = $subtotal + $tax;

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => Auth::id(),
            'status' => 'pending',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => 0,
            'total' => $total,
            'payment_intent_id' => $paymentIntentId,
            'payment_status' => $paymentIntentId ? 'paid' : 'pending',
            'shipping_address' => [
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'zip' => $this->zip,
                'country' => $this->country,
                'phone' => $this->phone,
            ],
            'notes' => $this->notes,
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_sku' => $item->product->sku,
                'product_price' => $item->product->price,
                'quantity' => $item->quantity,
                'total' => $item->quantity * $item->product->price,
            ]);

            if ($item->product->track_stock) {
                $item->product->decrement('stock', $item->quantity);
            }
        }

        $cart->clear();
        $this->processing = false;
        $this->dispatch('cart-updated');
        $this->redirect(route('orders.confirmation', $order->order_number));
    }

    public function render()
    {
        $cart = app(CartService::class);
        $items = $cart->getItems();
        $subtotal = $cart->getTotal();
        $tax = round($subtotal * 0.08, 2);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ];
    }
};
?>

<div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

        @if($items->isEmpty())
            <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
                <p class="text-xl text-gray-500">Your cart is empty.</p>
                <a href="{{ route('shop') }}" wire:navigate class="mt-4 inline-block text-indigo-600 hover:underline">Browse Products</a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                {{-- Form --}}
                <div class="lg:col-span-3 space-y-6">
                    {{-- Contact Info --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Contact Information</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input wire:model="firstName" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500" />
                                @error('firstName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input wire:model="lastName" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500" />
                                @error('lastName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input wire:model="email" type="email" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500" />
                                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input wire:model="phone" type="tel" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500" />
                                @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Shipping Address --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Shipping Address</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                                <input wire:model="address" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500" />
                                @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                    <input wire:model="city" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500" />
                                    @error('city') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">State / Province</label>
                                    <input wire:model="state" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500" />
                                    @error('state') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ZIP / Postal Code</label>
                                    <input wire:model="zip" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500" />
                                    @error('zip') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                    <select wire:model="country" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="AU">Australia</option>
                                        <option value="NG">Nigeria</option>
                                        <option value="GH">Ghana</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Order Notes --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Order Notes (optional)</h2>
                        <textarea wire:model="notes" rows="3" placeholder="Any special instructions..."
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                    </div>

                    {{-- Payment --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Payment</h2>
                        <div id="payment-element" class="min-h-[100px]"></div>
                        <p class="mt-3 text-xs text-gray-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Secured by Stripe. Your payment info is encrypted.
                        </p>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>
                        <div class="space-y-3 mb-4">
                            @foreach($items as $item)
                                <div class="flex gap-3 items-center">
                                    <img src="{{ $item->product->primary_image }}" alt="{{ $item->product->name }}"
                                        class="w-12 h-12 object-cover rounded-lg flex-none" />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $item->product->name }}</p>
                                        <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                    </div>
                                    <span class="text-sm font-semibold">${{ number_format($item->subtotal, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t pt-4 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span><span>${{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax (8%)</span><span>${{ number_format($tax, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping</span><span class="text-green-600">Free</span>
                            </div>
                            <div class="border-t pt-2 flex justify-between font-bold text-gray-900 text-base">
                                <span>Total</span><span>${{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <button wire:click="placeOrder"
                            wire:loading.attr="disabled"
                            :disabled="processing"
                            class="mt-6 w-full bg-indigo-600 text-white py-3.5 rounded-xl font-bold text-base hover:bg-indigo-700 active:scale-95 transition-all disabled:opacity-60">
                            <span wire:loading.remove>Place Order — ${{ number_format($total, 2) }}</span>
                            <span wire:loading>Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
