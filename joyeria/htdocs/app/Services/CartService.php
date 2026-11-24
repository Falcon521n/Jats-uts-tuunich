<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CartModel;
use App\Models\ProductModel;
use DomainException;

final class CartService
{
    public function __construct(private CartModel $carts, private ProductModel $products)
    {
    }

    public function add(int $cartId, int $productId, int $quantity): void
    {
        $product = $this->products->findActive($productId);
        if (!$product) {
            throw new DomainException('El producto ya no está disponible.');
        }
        $currentItems = $this->carts->items($cartId);
        $newQuantity = (int) ($currentItems[$productId] ?? 0) + $quantity;
        if ($quantity < 1 || $newQuantity > (int) $product['stock']) {
            throw new DomainException('No hay existencia suficiente para esa cantidad.');
        }
        $this->carts->add($cartId, $productId, $quantity);
    }

    public function update(int $cartId, array $quantities): void
    {
        $currentItems = $this->carts->items($cartId);
        $updates = [];
        foreach ($currentItems as $productId => $currentQuantity) {
            if (!array_key_exists($productId, $quantities)) {
                continue;
            }
            $quantity = filter_var($quantities[$productId], FILTER_VALIDATE_INT);
            $product = $this->products->findActive((int) $productId);
            if ($quantity === false || $quantity < 1 || !$product || $quantity > (int) $product['stock']) {
                throw new DomainException('Revisa las cantidades y existencias disponibles.');
            }
            $updates[(int) $productId] = (int) $quantity;
        }
        foreach ($updates as $productId => $quantity) {
            $this->carts->updateQuantity($cartId, $productId, $quantity);
        }
    }

    public function remove(int $cartId, int $productId): void
    {
        $this->carts->remove($cartId, $productId);
    }

    public function lines(int $cartId): array
    {
        $items = $this->carts->items($cartId);
        $products = $this->products->cartProducts(array_map('intval', array_keys($items)));
        $lines = [];
        foreach ($products as $product) {
            $product['quantity'] = (int) ($items[$product['id']] ?? 0);
            $product['subtotal'] = (float) $product['price'] * $product['quantity'];
            $lines[] = $product;
        }

        return $lines;
    }
}
