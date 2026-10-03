<?php

/** Session-backed cart: [product_id => quantity]. */
class Cart
{
    public static function items(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    public static function count(): int
    {
        return array_sum(self::items());
    }

    public static function set(int $productId, int $qty): void
    {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $qty;
        }
    }

    public static function add(int $productId, int $qty): void
    {
        self::set($productId, (self::items()[$productId] ?? 0) + $qty);
    }

    public static function clear(): void
    {
        unset($_SESSION['cart']);
    }

    /** Cart lines joined with current product data, quantities clamped to stock. */
    public static function lines(): array
    {
        $items = self::items();
        if (!$items) {
            return [];
        }
        $products = (new Product())->findMany(array_keys($items));
        $lines = [];
        foreach ($products as $p) {
            $qty = min((int) $items[$p['id']], (int) $p['stock']);
            if ($qty < 1) {
                self::set((int) $p['id'], 0);
                continue;
            }
            self::set((int) $p['id'], $qty);
            $lines[] = ['product' => $p, 'qty' => $qty, 'subtotal' => $qty * (float) $p['price']];
        }
        foreach (array_diff(array_keys($items), array_column($products, 'id')) as $gone) {
            self::set((int) $gone, 0);
        }
        return $lines;
    }

    public static function totals(array $lines): array
    {
        $subtotal = array_sum(array_column($lines, 'subtotal'));
        $shipping = ($subtotal == 0 || $subtotal >= config('free_shipping_over')) ? 0.0 : (float) config('shipping_flat');
        $tax = round($subtotal * config('tax_rate'), 2);
        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax'      => $tax,
            'total'    => $subtotal + $shipping + $tax,
        ];
    }
}
