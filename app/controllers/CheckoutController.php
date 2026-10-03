<?php

class CheckoutController extends Controller
{
    private const PAYMENTS = ['cod' => 'Cash on delivery', 'card' => 'Credit card (demo)'];

    public function index(): void
    {
        $this->requireLogin();
        $lines = Cart::lines();
        if (!$lines) {
            flash('info', 'Your cart is empty.');
            $this->redirect('/products');
        }
        $this->view('checkout/index', [
            'title'    => 'Checkout',
            'lines'    => $lines,
            'totals'   => Cart::totals($lines),
            'payments' => self::PAYMENTS,
        ]);
    }

    public function place(): void
    {
        $user = $this->requireLogin();
        $lines = Cart::lines();
        if (!$lines) {
            $this->redirect('/cart');
        }

        $ship = [
            'name'    => $this->input('name'),
            'address' => $this->input('address'),
            'city'    => $this->input('city'),
            'zip'     => $this->input('zip'),
            'phone'   => $this->input('phone'),
        ];
        $payment = $this->input('payment');
        $errors = [];
        foreach (['name' => 'Full name', 'address' => 'Address', 'city' => 'City', 'zip' => 'ZIP code', 'phone' => 'Phone'] as $k => $label) {
            if ($ship[$k] === '') $errors[] = "$label is required.";
        }
        if (!isset(self::PAYMENTS[$payment])) $errors[] = 'Choose a payment method.';
        if ($errors) {
            $this->withOld(['name', 'address', 'city', 'zip', 'phone', 'payment']);
            foreach ($errors as $err) flash('error', $err);
            $this->redirect('/checkout');
        }

        try {
            $orderId = (new Order())->place((int) $user['id'], $lines, Cart::totals($lines), $ship, $payment);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            $this->redirect('/cart');
        }
        Cart::clear();
        flash('success', "Order #$orderId placed. Thank you for shopping with us!");
        $this->redirect("/orders/$orderId");
    }
}
