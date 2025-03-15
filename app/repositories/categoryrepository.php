<?php

namespace Repositories;

use PDO;
use PDOException;
use Exception;
use Repositories\Repository;
use Models\Category;

class CategoryRepository extends Repository
{
    function getAll($offset = NULL, $limit = NULL)
    {
        try {
            $query = "SELECT * FROM categories";
            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();

            $categories = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
             $catogory=new Category( $row['name']);
             $catogory->setId($row['id']);
             $categories[] = $catogory;
            }

            return $categories;

        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getOne($id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM categories WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $catogory=new Category( $row['name']);
                $catogory->setId($row['id']);
            return $catogory;
            }

        return null;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    public function insert($category)
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->bindValue(':name', $category->getName(), PDO::PARAM_STR);
    
            $stmt->execute();
    
            if ($stmt->rowCount() > 0) { 
                $category->setId($this->connection->lastInsertId());
            }
    
            return $category;
    
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return null;
        }
    }

    public function update($category, $id)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE categories SET name = :name WHERE id = :id");
    
            $stmt->bindValue(':name', $category->getName(), PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    
            $stmt->execute();
    
            // Check if an update actually occurred
            if ($stmt->rowCount() === 0) {
                throw new Exception("No record updated. Category with ID $id might not exist.");
            }
            
            $category->setId($id);
            return $category;
    
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return null;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    

    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return;
        } catch (PDOException $e) {
            echo $e;
        }
        return true;
    }
}
