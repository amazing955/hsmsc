<?php
class Product {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($name, $description, $price, $stock, $image = null, $ownerId = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description, price, stock, image, owner_id) 
                  VALUES (:name, :description, :price, :stock, :image, :owner_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":stock", $stock);
        $stmt->bindParam(":image", $image);
        $stmt->bindParam(":owner_id", $ownerId);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getAll() {
        $query = "SELECT p.*, u.name as owner_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN users u ON p.owner_id = u.id
                  WHERE p.stock > 0 
                  ORDER BY p.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByOwner($ownerId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE owner_id = :owner_id ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":owner_id", $ownerId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $name, $description, $price, $stock, $image = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, description = :description, price = :price, 
                      stock = :stock, image = :image
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":stock", $stock);
        $stmt->bindParam(":image", $image);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function updateStock($id, $quantity) {
        $query = "UPDATE " . $this->table_name . " SET stock = stock - :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function countByOwner($ownerId) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE owner_id = :owner_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":owner_id", $ownerId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
