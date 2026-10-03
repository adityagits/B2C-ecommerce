<?php

class Product extends Model
{
    public const SORTS = [
        'newest'     => 'p.created_at DESC, p.id DESC',
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'name'       => 'p.name ASC',
    ];

    public function find(int $id): ?array
    {
        return $this->one(
            'SELECT p.*, c.name AS category_name,
                    (SELECT ROUND(AVG(rating),1) FROM reviews r WHERE r.product_id = p.id) AS avg_rating,
                    (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) AS review_count
             FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ?',
            [$id]
        );
    }

    public function findMany(array $ids): array
    {
        $ids = array_map('intval', $ids);
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return $this->all("SELECT * FROM products WHERE id IN ($in)", $ids);
    }

    public function featured(int $limit = 4): array
    {
        return $this->all("SELECT * FROM products WHERE featured = 1 AND stock > 0 ORDER BY created_at DESC LIMIT $limit");
    }

    public function latest(int $limit = 4): array
    {
        return $this->all("SELECT * FROM products ORDER BY created_at DESC, id DESC LIMIT $limit");
    }

    /** @return array{items: array, total: int} */
    public function search(string $q, ?int $categoryId, string $sort, int $page, int $perPage): array
    {
        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
            $params[] = $params[] = '%' . addcslashes($q, '%_\\') . '%';
        }
        if ($categoryId) {
            $where[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }
        $whereSql = implode(' AND ', $where);
        $order = self::SORTS[$sort] ?? self::SORTS['newest'];
        $offset = max(0, ($page - 1) * $perPage);

        $total = (int) $this->value("SELECT COUNT(*) FROM products p WHERE $whereSql", $params);
        $items = $this->all(
            "SELECT p.*, c.name AS category_name FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE $whereSql ORDER BY $order LIMIT $perPage OFFSET $offset",
            $params
        );
        return ['items' => $items, 'total' => $total];
    }

    public function create(array $d): int
    {
        $this->run(
            'INSERT INTO products (category_id, name, description, price, stock, image, featured) VALUES (?,?,?,?,?,?,?)',
            [$d['category_id'], $d['name'], $d['description'], $d['price'], $d['stock'], $d['image'], $d['featured']]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $this->run(
            'UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image=?, featured=? WHERE id=?',
            [$d['category_id'], $d['name'], $d['description'], $d['price'], $d['stock'], $d['image'], $d['featured'], $id]
        );
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM products WHERE id = ?', [$id]);
    }

    public function adminList(): array
    {
        return $this->all('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC');
    }

    public function lowStock(int $threshold = 5): array
    {
        return $this->all('SELECT id, name, stock FROM products WHERE stock <= ? ORDER BY stock ASC LIMIT 5', [$threshold]);
    }

    public function count(): int
    {
        return (int) $this->value('SELECT COUNT(*) FROM products');
    }

    public function reviews(int $productId): array
    {
        return $this->all(
            'SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC',
            [$productId]
        );
    }

    public function addReview(int $productId, int $userId, int $rating, string $comment): void
    {
        $this->run(
            'INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = NOW()',
            [$productId, $userId, $rating, $comment]
        );
    }
}
