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
                <div class="lg:col-span-3 space-y-6">
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                    <input wire:model="state" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500" />
                                    @error('state') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ZIP Code</label>
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
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Order Notes <span class="text-gray-400 text-sm font-normal">(optional)</span></h2>
                        <textarea wire:model="notes" rows="3" placeholder="Any special instructions..."
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                    </div>
                </div>
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
                            <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>${{ number_format($subtotal, 2) }}</span></div>
                            <div class="flex justify-between text-gray-600"><span>Tax (8%)</span><span>${{ number_format($tax, 2) }}</span></div>
                            <div class="flex justify-between text-gray-600"><span>Shipping</span><span class="text-green-600">Free</span></div>
                            <div class="border-t pt-2 flex justify-between font-bold text-gray-900 text-base"><span>Total</span><span>${{ number_format($total, 2) }}</span></div>
                        </div>
                        <button wire:click="placeOrder" wire:loading.attr="disabled"
                            class="mt-6 w-full bg-indigo-600 text-white py-3.5 rounded-xl font-bold text-base hover:bg-indigo-700 active:scale-95 transition-all disabled:opacity-60">
                            <span wire:loading.remove>Place Order — ${{ number_format($total, 2) }}</span>
                            <span wire:loading>Processing...</span>
                        </button>
                        <p class="mt-3 text-xs text-gray-400 text-center">By placing your order, you agree to our terms of service.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
