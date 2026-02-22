<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function __construct(public ?Request $request = null) {

    }

    protected function getIdentifier(): array
    {
        if (Auth::check()) {
            return ['user_id' => Auth::id()];
        }

        $sessionId = $this->request
            ? $this->request->session()->getId()
            : Session::getId();

        return ['session_id' => $sessionId];
    }

    public function getItems()
    {
        return CartItem::where($this->getIdentifier())
            ->with('product')
            ->get();
    }

    public function getCount(): int
    {
        return CartItem::where($this->getIdentifier())->sum('quantity');
    }

    public function getTotal(): float
    {
        return $this->getItems()->sum(fn($item) => $item->quantity * $item->product->price);
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $identifier = $this->getIdentifier();
        $item = CartItem::where($identifier)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $newQty = $item->quantity + $quantity;
            if ($product->track_stock && $newQty > $product->stock) {
                $newQty = $product->stock;
            }
            $item->update(['quantity' => $newQty]);
        } else {
            CartItem::create(array_merge($identifier, [
                'product_id' => $product->id,
                'quantity' => min($quantity, $product->track_stock ? $product->stock : 999),
            ]));
        }
    }

    public function update(int $cartItemId, int $quantity): void
    {
        $item = CartItem::where($this->getIdentifier())->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $item->delete();
            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    public function remove(int $cartItemId): void
    {
        CartItem::where($this->getIdentifier())->where('id', $cartItemId)->delete();
    }

    public function clear(): void
    {
        CartItem::where($this->getIdentifier())->delete();
    }

    public function mergeSessionCart(): void
    {
        if (!Auth::check()) return;

        $sessionId = $this->request
            ? $this->request->session()->getId()
            : Session::getId();

        $sessionItems = CartItem::where('session_id', $sessionId)->get();

        foreach ($sessionItems as $item) {
            $existing = CartItem::where('user_id', Auth::id())
                ->where('product_id', $item->product_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
                $item->delete();
            } else {
                $item->update(['user_id' => Auth::id(), 'session_id' => null]);
            }
        }
    }
}
