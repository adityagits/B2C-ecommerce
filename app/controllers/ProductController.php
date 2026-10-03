<?php

class ProductController extends Controller
{
    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $cat = (int) ($_GET['category'] ?? 0) ?: null;
        $sort = (string) ($_GET['sort'] ?? 'newest');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) config('per_page');

        $result = (new Product())->search($q, $cat, $sort, $page, $perPage);
        $this->view('products/index', [
            'title'      => 'Shop',
            'products'   => $result['items'],
            'total'      => $result['total'],
            'pages'      => max(1, (int) ceil($result['total'] / $perPage)),
            'page'       => $page,
            'q'          => $q,
            'cat'        => $cat,
            'sort'       => $sort,
            'categories' => (new Category())->allWithCounts(),
        ]);
    }

    public function show(string $id): void
    {
        $model = new Product();
        $product = $model->find((int) $id) ?? $this->notFound();
        $this->view('products/show', [
            'title'   => $product['name'],
            'product' => $product,
            'reviews' => $model->reviews((int) $id),
        ]);
    }

    public function review(string $id): void
    {
        $user = $this->requireLogin();
        $model = new Product();
        $model->find((int) $id) ?? $this->notFound();

        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = $this->input('comment');
        if ($rating < 1 || $rating > 5) {
            flash('error', 'Please choose a rating from 1 to 5.');
        } else {
            $model->addReview((int) $id, (int) $user['id'], $rating, mb_substr($comment, 0, 1000));
            flash('success', 'Thanks for your review!');
        }
        $this->redirect("/products/$id#reviews");
    }
}
