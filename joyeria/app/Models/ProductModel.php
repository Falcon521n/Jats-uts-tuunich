<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ProductModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function catalogOptions(): array
    {
        return [
            'types' => $this->pdo->query(
                'SELECT DISTINCT piece_type FROM products WHERE is_active = 1 ORDER BY piece_type'
            )->fetchAll(PDO::FETCH_COLUMN),
            'materials' => $this->pdo->query(
                'SELECT DISTINCT material FROM products WHERE is_active = 1 ORDER BY material'
            )->fetchAll(PDO::FETCH_COLUMN),
        ];
    }

    public function countCatalog(array $filters): int
    {
        [$where, $parameters] = $this->catalogWhere($filters);
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE ' . $where
        );
        $statement->execute($parameters);

        return (int) $statement->fetchColumn();
    }

    public function catalogPage(array $filters, int $limit, int $offset): array
    {
        [$where, $parameters] = $this->catalogWhere($filters);
        $statement = $this->pdo->prepare(
            'SELECT p.id, p.name, p.description, c.name AS category, p.piece_type, p.material,
                    p.price, p.stock, p.artisan_name, p.image_url
             FROM products p JOIN categories c ON c.id = p.category_id
             WHERE ' . $where . ' ORDER BY p.created_at DESC LIMIT ' . max(1, min($limit, 100)) .
             ' OFFSET ' . max(0, $offset)
        );
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function findActive(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT p.id, p.name, p.description, p.category_id, c.name AS category,
                    p.piece_type, p.material, p.price, p.stock, p.artisan_name, p.image_url
             FROM products p JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id AND p.is_active = 1'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function findForAdministration(int $id, bool $lock = false): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id' . ($lock ? ' FOR UPDATE' : '')
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function findAvailableForUpdate(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, price, stock FROM products WHERE id = :id AND is_active = 1 FOR UPDATE'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function cartProducts(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $statement = $this->pdo->prepare(
            'SELECT id, name, price, stock, image_url, is_active FROM products
             WHERE id IN (' . $placeholders . ')'
        );
        $statement->execute($productIds);

        return $statement->fetchAll();
    }

    public function activeForSale(): array
    {
        return $this->pdo->query(
            'SELECT id, name, price, stock FROM products WHERE is_active = 1 AND stock > 0 ORDER BY name'
        )->fetchAll();
    }

    public function allForAdministration(int $limit = 200): array
    {
        return $this->pdo->query(
            'SELECT p.id, p.name, c.name AS category, p.piece_type, p.material,
                    p.price, p.stock, p.is_active
             FROM products p JOIN categories c ON c.id = p.category_id
             ORDER BY p.created_at DESC LIMIT ' . max(1, min($limit, 1000))
        )->fetchAll();
    }

    public function insert(array $product): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO products
             (category_id, name, description, piece_type, material, price, stock, artisan_name, image_url)
             VALUES
             (:category_id, :name, :description, :piece_type, :material, :price, :stock, :artisan_name, :image_url)'
        );
        $statement->execute($product);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $product): void
    {
        $product['id'] = $id;
        $statement = $this->pdo->prepare(
            'UPDATE products SET category_id = :category_id, name = :name, description = :description,
             piece_type = :piece_type, material = :material, price = :price, stock = :stock,
             artisan_name = :artisan_name, image_url = :image_url WHERE id = :id'
        );
        $statement->execute($product);
    }

    public function decrementStock(int $id, int $quantity): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE products SET stock = stock - :quantity
             WHERE id = :id AND stock >= :required_quantity'
        );
        $statement->execute([
            'quantity' => $quantity,
            'id' => $id,
            'required_quantity' => $quantity,
        ]);

        return $statement->rowCount() === 1;
    }

    public function toggleActive(int $id): void
    {
        $statement = $this->pdo->prepare('UPDATE products SET is_active = NOT is_active WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function catalogWhere(array $filters): array
    {
        $where = ['p.is_active = 1'];
        $parameters = [];
        if (($filters['category'] ?? '') !== '') {
            $where[] = 'c.name = :category';
            $parameters['category'] = $filters['category'];
        }
        if (($filters['type'] ?? '') !== '') {
            $where[] = 'p.piece_type = :piece_type';
            $parameters['piece_type'] = $filters['type'];
        }
        if (($filters['search'] ?? '') !== '') {
            $where[] = '(p.name LIKE :search_name OR p.description LIKE :search_description)';
            $parameters['search_name'] = '%' . $filters['search'] . '%';
            $parameters['search_description'] = '%' . $filters['search'] . '%';
        }
        if (($filters['materials'] ?? []) !== []) {
            $placeholders = [];
            foreach ($filters['materials'] as $index => $material) {
                $key = 'material_' . $index;
                $placeholders[] = ':' . $key;
                $parameters[$key] = $material;
            }
            $where[] = 'p.material IN (' . implode(', ', $placeholders) . ')';
        }

        return [implode(' AND ', $where), $parameters];
    }
}
