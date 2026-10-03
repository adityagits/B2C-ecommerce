<?php

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAdmin();
        $orders = new Order();
        $products = new Product();
        $this->view('admin/dashboard', [
            'title'     => 'Dashboard',
            'stats'     => $orders->stats() + ['products' => $products->count(), 'customers' => (new User())->count()],
            'recent'    => $orders->recent(),
            'lowStock'  => $products->lowStock(),
        ], 'layout/admin');
    }
}
