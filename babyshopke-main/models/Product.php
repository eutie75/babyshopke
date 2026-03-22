<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class Product
{
    public static function getById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function getByIdForUpdate(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM products WHERE id = :id LIMIT 1 FOR UPDATE');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function getAll(): array
    {
        $db = getDB();
        return $db->query('SELECT * FROM products ORDER BY created_at DESC, id DESC')->fetchAll();
    }

    public static function getCategories(): array
    {
        $db = getDB();
        $rows = $db->query('SELECT DISTINCT category FROM products ORDER BY category ASC')->fetchAll();
        return array_map(static fn(array $row): string => $row['category'], $rows);
    }

    public static function getFiltered(?string $category, ?string $search, ?array $ageRange, int $limit = 36): array
    {
        $db = getDB();
        $sql = 'SELECT * FROM products WHERE 1=1';
        $params = [];

        if ($category !== null && $category !== '') {
            $sql .= ' AND category = :category';
            $params[':category'] = $category;
        }

        if ($search !== null && $search !== '') {
            $sql .= ' AND (name LIKE :search OR description LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if (is_array($ageRange) && isset($ageRange[0], $ageRange[1])) {
            $sql .= ' AND age_min_months <= :age_max AND age_max_months >= :age_min';
            $params[':age_min'] = (int)$ageRange[0];
            $params[':age_max'] = (int)$ageRange[1];
        }

        $sql .= ' ORDER BY created_at DESC, id DESC LIMIT :limit';
        $stmt = $db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function validate(array $data): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));
        $price = (float)($data['price'] ?? 0);
        $stock = (int)($data['stock'] ?? 0);
        $category = trim((string)($data['category'] ?? ''));
        $imageUrl = trim((string)($data['image_url'] ?? ''));
        $ageMin = (int)($data['age_min_months'] ?? 0);
        $ageMax = (int)($data['age_max_months'] ?? 48);

        $errors = [];

        if ($name === '' || strlen($name) > 200) {
            $errors[] = 'Product name is required and must be less than 200 characters.';
        }
        if ($price <= 0) {
            $errors[] = 'Price must be greater than 0.';
        }
        if ($stock < 0) {
            $errors[] = 'Stock cannot be negative.';
        }
        if ($category === '') {
            $errors[] = 'Category is required.';
        }
        if ($ageMin < 0 || $ageMax < 0 || $ageMin > $ageMax) {
            $errors[] = 'Age range is invalid.';
        }
        if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Image URL must be valid.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => [
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':stock' => $stock,
                ':category' => $category,
                ':image_url' => $imageUrl !== '' ? $imageUrl : 'https://placehold.co/600x600/FFF7F2/1F2933?text=Baby+Shop+KE',
                ':age_min_months' => $ageMin,
                ':age_max_months' => $ageMax,
            ],
        ];
    }

    public static function create(array $data): int
    {
        $db = getDB();
        $stmt = $db->prepare('
            INSERT INTO products (name, description, price, stock, category, image_url, age_min_months, age_max_months)
            VALUES (:name, :description, :price, :stock, :category, :image_url, :age_min_months, :age_max_months)
        ');
        $stmt->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = getDB();
        $stmt = $db->prepare('
            UPDATE products
            SET name = :name,
                description = :description,
                price = :price,
                stock = :stock,
                category = :category,
                image_url = :image_url,
                age_min_months = :age_min_months,
                age_max_months = :age_max_months
            WHERE id = :id
        ');

        $data[':id'] = $id;
        return $stmt->execute($data);
    }

    public static function delete(int $id): bool
    {
        $db = getDB();
        $stmt = $db->prepare('DELETE FROM products WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public static function adjustStock(int $id, int $qty): bool
    {
        $db = getDB();
        $stmt = $db->prepare('UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':qty', $qty, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public static function countAll(): int
    {
        $db = getDB();
        return (int)$db->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }

    public static function lowStockCount(int $threshold = 5): int
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE stock <= :threshold');
        $stmt->execute([':threshold' => $threshold]);
        return (int)$stmt->fetchColumn();
    }
}
