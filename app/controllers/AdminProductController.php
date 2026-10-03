<?php

class AdminProductController extends Controller
{
    private const MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    public function index(): void
    {
        $this->requireAdmin();
        $this->view('admin/products/index', [
            'title'    => 'Products',
            'products' => (new Product())->adminList(),
        ], 'layout/admin');
    }

    public function create(): void
    {
        $this->requireAdmin();
        $this->form(null);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $data = $this->collect(null);
        if ($data === null) {
            $this->redirect('/admin/products/create');
        }
        (new Product())->create($data);
        flash('success', 'Product created.');
        $this->redirect('/admin/products');
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();
        $product = (new Product())->find((int) $id) ?? $this->notFound();
        $this->form($product);
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $model = new Product();
        $product = $model->find((int) $id) ?? $this->notFound();
        $data = $this->collect($product);
        if ($data === null) {
            $this->redirect("/admin/products/$id/edit");
        }
        $model->update((int) $id, $data);
        flash('success', 'Product updated.');
        $this->redirect('/admin/products');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $model = new Product();
        $product = $model->find((int) $id) ?? $this->notFound();
        $model->delete((int) $id);
        if ($product['image']) {
            @unlink(ROOT . '/public/uploads/' . basename($product['image']));
        }
        flash('success', 'Product deleted.');
        $this->redirect('/admin/products');
    }

    private function form(?array $product): void
    {
        $this->view('admin/products/form', [
            'title'      => $product ? 'Edit product' : 'New product',
            'product'    => $product,
            'categories' => (new Category())->allWithCounts(),
        ], 'layout/admin');
    }

    /** Validate input and handle upload; returns null (after flashing errors) on failure. */
    private function collect(?array $existing): ?array
    {
        $d = [
            'name'        => $this->input('name'),
            'description' => $this->input('description'),
            'category_id' => (int) $this->input('category_id') ?: null,
            'price'       => $this->input('price'),
            'stock'       => $this->input('stock', '0'),
            'featured'    => isset($_POST['featured']) ? 1 : 0,
            'image'       => $existing['image'] ?? null,
        ];
        $errors = [];
        if ($d['name'] === '') $errors[] = 'Name is required.';
        if (!is_numeric($d['price']) || $d['price'] < 0) $errors[] = 'Price must be a positive number.';
        if (!ctype_digit($d['stock'])) $errors[] = 'Stock must be a whole number.';

        $newImage = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];
            $mime = $file['error'] === UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : null;
            if (!isset(self::MIME[$mime]) || $file['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Image must be a JPG, PNG, WebP or GIF under 2 MB.';
            } else {
                $newImage = bin2hex(random_bytes(8)) . '.' . self::MIME[$mime];
            }
        }

        if ($errors) {
            $this->withOld(['name', 'description', 'category_id', 'price', 'stock', 'featured']);
            foreach ($errors as $err) flash('error', $err);
            return null;
        }
        if ($newImage) {
            move_uploaded_file($_FILES['image']['tmp_name'], ROOT . '/public/uploads/' . $newImage);
            if ($existing && $existing['image']) {
                @unlink(ROOT . '/public/uploads/' . basename($existing['image']));
            }
            $d['image'] = $newImage;
        }
        $d['price'] = round((float) $d['price'], 2);
        $d['stock'] = (int) $d['stock'];
        return $d;
    }
}
