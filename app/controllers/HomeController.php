<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $products = new Product();
        $this->view('home/index', [
            'title'      => 'Home',
            'featured'   => $products->featured(4),
            'latest'     => $products->latest(8),
            'categories' => (new Category())->allWithCounts(),
        ]);
    }
}
