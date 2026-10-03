<?php

class Category extends Model
{
    public function allWithCounts(): array
    {
        return $this->all('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count FROM categories c ORDER BY c.name');
    }
}
