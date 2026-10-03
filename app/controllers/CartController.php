<?php

class CartController extends Controller
{
    public function index(): void
    {
        $lines = Cart::lines();
        $this->view('cart/index', [
            'title'  => 'Your Cart',
            'lines'  => $lines,
            'totals' => Cart::totals($lines),
        ]);
    }

    public function add(): void
    {
        $id = (int) ($_POST['product_id'] ?? 0);
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));
        $product = (new Product())->find($id);

        if (!$product) {
            flash('error', 'Product not found.');
            $this->redirect('/products');
        }
        $inCart = Cart::items()[$id] ?? 0;
        if ($inCart + $qty > $product['stock']) {
            flash('error', "Only {$product['stock']} of \"{$product['name']}\" in stock.");
            $this->back('/products');
        }
        Cart::add($id, $qty);
        flash('success', "Added \"{$product['name']}\" to your cart.");
        if (isset($_POST['buy_now'])) {
            $this->redirect('/checkout');
        }
        $this->back('/cart');
    }

    public function update(): void
    {
        foreach ((array) ($_POST['qty'] ?? []) as $id => $qty) {
            Cart::set((int) $id, (int) $qty);
        }
        flash('success', 'Cart updated.');
        $this->redirect('/cart');
    }

    public function remove(): void
    {
        Cart::set((int) ($_POST['product_id'] ?? 0), 0);
        flash('success', 'Item removed.');
        $this->redirect('/cart');
    }
}
