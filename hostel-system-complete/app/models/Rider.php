<?php
class Rider {
    private $conn;
    private $table = 'riders';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, license_plate, bike_type, phone, location) 
                  VALUES (:user_id, :license_plate, :bike_type, :phone, :location)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':license_plate', $data['license_plate']);
        $stmt->bindParam(':bike_type', $data['bike_type']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':location', $data['location']);
        
        return $stmt->execute();
    }

    public function getByUserId($userId) {
        $query = "SELECT r.*, u.name as rider_name, u.email 
                  FROM " . $this->table . " r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAvailable() {
        $query = "SELECT r.*, u.name as rider_name 
                  FROM " . $this->table . " r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.is_available = TRUE
                  ORDER BY r.rating DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = "SELECT r.*, u.name as rider_name, u.email 
                  FROM " . $this->table . " r
                  JOIN users u ON r.user_id = u.id
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($userId, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET license_plate = :license_plate, 
                      bike_type = :bike_type, 
                      phone = :phone, 
                      location = :location,
                      is_available = :is_available
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':license_plate', $data['license_plate']);
        $stmt->bindParam(':bike_type', $data['bike_type']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':is_available', $data['is_available'], PDO::PARAM_BOOL);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    public function setAvailability($userId, $available) {
        $query = "UPDATE " . $this->table . " SET is_available = :available WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':available', $available, PDO::PARAM_BOOL);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    public function incrementRides($userId) {
        $query = "UPDATE " . $this->table . " SET total_rides = total_rides + 1 WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
