<?php
class Room {
    private $conn;
    private $table_name = "rooms";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($hostelId, $roomNumber, $roomType, $capacity, $price) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (hostel_id, room_number, room_type, capacity, price, availability) 
                  VALUES (:hostel_id, :room_number, :room_type, :capacity, :price, TRUE)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":hostel_id", $hostelId);
        $stmt->bindParam(":room_number", $roomNumber);
        $stmt->bindParam(":room_type", $roomType);
        $stmt->bindParam(":capacity", $capacity);
        $stmt->bindParam(":price", $price);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getAll() {
        $query = "SELECT r.*, h.name as hostel_name, h.location 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN hostels h ON r.hostel_id = h.id 
                  ORDER BY h.name, r.room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailable() {
        $query = "SELECT r.*, h.name as hostel_name, h.location 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN hostels h ON r.hostel_id = h.id 
                  WHERE r.availability = TRUE 
                  ORDER BY h.name, r.room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByHostel($hostel_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE hostel_id = :hostel_id 
                  ORDER BY room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":hostel_id", $hostel_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByOwner($ownerId) {
        $query = "SELECT r.*, h.name as hostel_name, h.location 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN hostels h ON r.hostel_id = h.id 
                  WHERE h.owner_id = :owner_id
                  ORDER BY h.name, r.room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":owner_id", $ownerId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT r.*, h.name as hostel_name, h.location, h.owner_id 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN hostels h ON r.hostel_id = h.id 
                  WHERE r.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $roomNumber, $roomType, $capacity, $price, $availability) {
        $query = "UPDATE " . $this->table_name . " 
                  SET room_number = :room_number, room_type = :room_type, 
                      capacity = :capacity, price = :price, availability = :availability
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":room_number", $roomNumber);
        $stmt->bindParam(":room_type", $roomType);
        $stmt->bindParam(":capacity", $capacity);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":availability", $availability, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function updateAvailability($id, $availability) {
        $query = "UPDATE " . $this->table_name . " 
                  SET availability = :availability 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":availability", $availability, PDO::PARAM_BOOL);
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
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " r 
                  JOIN hostels h ON r.hostel_id = h.id 
                  WHERE h.owner_id = :owner_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":owner_id", $ownerId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
