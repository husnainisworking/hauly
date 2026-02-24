<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

use Livewire\Attributes\Rule;
use Livewire\Component;

class Checkout extends Component
{
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

    public string $country = 'US';
    public string $notes = '';
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

    public function placeOrder(): void
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

        $order = DB::transaction(function () use ($items, $subtotal, $tax, $total, $cart) {

        $order = Order::create([
            'order_number'     => Order::generateOrderNumber(),
            'user_id'          => Auth::id(),
            'status'           => 'pending',
            'subtotal'         => $subtotal,
            'tax'              => $tax,
            'shipping'         => 0,
            'total'            => $total,
            'payment_status'   => 'pending',
            'shipping_address' => [
                'first_name' => $this->firstName,
                'last_name'  => $this->lastName,
                'address'    => $this->address,
                'city'       => $this->city,
                'state'      => $this->state,
                'zip'        => $this->zip,
                'country'    => $this->country,
                'phone'      => $this->phone,
            ],
            'notes' => $this->notes,
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id'      => $order->id,
                'product_id'    => $item->product_id,
                'product_name'  => $item->product->name,
                'product_sku'   => $item->product->sku,
                'product_price' => $item->product->price,
                'quantity'      => $item->quantity,
                'total'         => $item->quantity * $item->product->price,
            ]);

            if ($item->product->track_stock) {
                $item->product->decrement('stock', $item->quantity);
            }
        }

        $cart->clear();
        return $order;
        });

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

        return view('livewire.checkout', [
            'items'    => $items,
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'total'    => $subtotal + $tax,
        ])->layout('layouts.app');
    }
}
