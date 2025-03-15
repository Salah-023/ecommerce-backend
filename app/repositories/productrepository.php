<?php

namespace Repositories;

use Models\Category;
use Models\Product;
use Exception;
use PDO;
use PDOException;
use Repositories\Repository;

class ProductRepository extends Repository
{
    public function getAll(?int $categoryId = null, ?int $offset = null, ?int $limit = null): array
    {
        try {
            $query = "SELECT products.*, categories.name as category_name 
                      FROM products 
                      INNER JOIN categories ON products.category_id = categories.id";

            $params = [];

            //Add filtering by category if provided
            if ($categoryId !== null) {
                $query .= " WHERE products.category_id = :category_id";
                $params[':category_id'] = $categoryId;
            }

            //Apply pagination (LIMIT & OFFSET)
            if ($limit !== null && $offset !== null) {
                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $limit;
                $params[':offset'] = $offset;
            }

            $stmt = $this->connection->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }

            $stmt->execute();
            $products = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = $this->rowToProduct($row);
            }

            return $products;
        } catch (PDOException $e) {
            error_log("Error fetching products: " . $e->getMessage());
            return [];
        }
    }


    function getOne($id)
    {
        try {
            $query = "SELECT products.*, categories.name as category_name FROM products INNER JOIN categories ON products.category_id = categories.id WHERE products.id = :id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();

            if (!$row) {
                error_log("Product with ID $id not found.");
                return null;
            }

            $product = $this->rowToProduct($row);

            return $product;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function rowToProduct($row)
    {
        $category = new Category($row['category_name']);
        $category->setId($row['category_id']);

        $product = new Product($row['name'], $row['description'], $row['price'], $row['stock'], $row['image_url'], $category, $row['created_at']);
        $product->setId($row['id']);
        return $product;
    }

    public function insert($product)
    {
        try {
            $stmt = $this->connection->prepare("
                INSERT INTO products (name, price, description, stock, image_url, category_id) 
                VALUES (:name, :price, :description, :stock, :image, :category_id)
            ");

            $stmt->bindValue(':name', $product->getName(), PDO::PARAM_STR);
            $stmt->bindValue(':price', $product->getPrice(), PDO::PARAM_STR);
            $stmt->bindValue(':description', $product->getDescription(), PDO::PARAM_STR);
            $stmt->bindValue(':image', $product->getImageUrl(), PDO::PARAM_STR);
            $stmt->bindValue(':stock', $product->getStock(), PDO::PARAM_INT);
            $stmt->bindValue(':category_id', $product->getCategory()->getId(), PDO::PARAM_INT);

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $product->setId($this->connection->lastInsertId());
                return $this->getOne($product->getId());
            } else {
                throw new Exception("Product insertion failed.");
            }

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage()); // Log error
            return null;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }

    public function update($product, $id)
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE products 
                SET name = :name, price = :price, description = :description, stock = :stock,image_url = :image_url, category_id = :category_id
                WHERE id = :id
            ");

            $stmt->bindValue(':name', $product->getName(), PDO::PARAM_STR);
            $stmt->bindValue(':price', $product->getPrice(), PDO::PARAM_STR);
            $stmt->bindValue(':description', $product->getDescription(), PDO::PARAM_STR);
            $stmt->bindValue(':image_url', $product->getImageUrl(), PDO::PARAM_STR);
            $stmt->bindValue(':category_id', $product->getCategory()->getId(), PDO::PARAM_INT);
            $stmt->bindValue(':stock', $product->getStock(), PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new Exception("No record updated. Product with ID $id might not exist.");
            }

            return $this->getOne($id);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage()); // Log error
            return null;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }

    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM products WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return;
        } catch (PDOException $e) {
            echo $e;
        }
        return true;
    }

}
