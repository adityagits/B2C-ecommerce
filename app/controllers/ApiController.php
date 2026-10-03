<?php

/** Read-only JSON API authenticated with an admin-issued key: Authorization: Bearer sk_... */
class ApiController extends Controller
{
    public function products(): void
    {
        $this->authenticate();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per = 20;
        $r = (new Product())->search(trim((string) ($_GET['q'] ?? '')), (int) ($_GET['category'] ?? 0) ?: null, 'newest', $page, $per);
        $this->json(['page' => $page, 'per_page' => $per, 'total' => $r['total'], 'data' => array_map([$this, 'productOut'], $r['items'])]);
    }

    public function product(string $id): void
    {
        $this->authenticate();
        $p = (new Product())->find((int) $id);
        $p ? $this->json(['data' => $this->productOut($p)]) : $this->json(['error' => 'Not found'], 404);
    }

    public function orders(): void
    {
        $this->authenticate();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per = 20;
        $all = (new Order())->adminList((string) ($_GET['status'] ?? ''));
        $rows = array_map(fn($o) => [
            'id' => (int) $o['id'], 'status' => $o['status'], 'total' => (float) $o['total'], 'customer' => $o['customer_name'],
            'invoice_number' => $o['invoice_number'], 'payment_status' => $o['payment_status'],
            'shipment_status' => $o['shipment_status'], 'created_at' => $o['created_at'],
        ], array_slice($all, ($page - 1) * $per, $per));
        $this->json(['page' => $page, 'per_page' => $per, 'total' => count($all), 'data' => $rows]);
    }

    public function order(string $id): void
    {
        $this->authenticate();
        $orders = new Order();
        $o = $orders->find((int) $id);
        if (!$o) {
            $this->json(['error' => 'Not found'], 404);
        }
        $pay = $orders->payment((int) $id);
        $ship = $orders->shipment((int) $id);
        $inv = $orders->invoice((int) $id);
        $this->json(['data' => [
            'id' => (int) $o['id'], 'status' => $o['status'], 'created_at' => $o['created_at'],
            'customer' => ['name' => $o['customer_name'], 'email' => $o['customer_email']],
            'totals' => ['subtotal' => (float) $o['subtotal'], 'shipping' => (float) $o['shipping'], 'tax' => (float) $o['tax'], 'total' => (float) $o['total']],
            'items' => array_map(fn($i) => ['name' => $i['name'], 'price' => (float) $i['price'], 'quantity' => (int) $i['quantity']], $orders->items((int) $id)),
            'invoice' => $inv ? ['number' => $inv['invoice_number'], 'status' => $inv['status'], 'issued_at' => $inv['issued_at']] : null,
            'payment' => $pay ? ['transaction_id' => $pay['transaction_id'], 'method' => $pay['method'], 'status' => $pay['status'], 'paid_at' => $pay['paid_at']] : null,
            'shipment' => $ship ? ['carrier' => $ship['carrier'], 'tracking_number' => $ship['tracking_number'], 'status' => $ship['status'],
                'estimated_delivery' => $ship['estimated_delivery'], 'shipped_at' => $ship['shipped_at'], 'delivered_at' => $ship['delivered_at']] : null,
        ]]);
    }

    private function productOut(array $p): array
    {
        return [
            'id' => (int) $p['id'], 'name' => $p['name'], 'description' => $p['description'], 'price' => (float) $p['price'],
            'stock' => (int) $p['stock'], 'category' => $p['category_name'] ?? null, 'image' => product_image($p['image']),
        ];
    }

    private function authenticate(): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(\S+)$/i', $header, $m) || !(new ApiKey())->authenticate($m[1])) {
            header('WWW-Authenticate: Bearer');
            $this->json(['error' => 'Invalid or missing API key'], 401);
        }
    }

    private function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
