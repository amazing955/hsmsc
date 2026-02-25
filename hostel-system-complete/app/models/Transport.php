<?php
class Transport {
    private $conn;
    private $table_name = "transport";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $pickup, $destination, $cost) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, pickup_location, destination, cost, status) 
                  VALUES (:user_id, :pickup, :destination, :cost, 'pending')";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":pickup", $pickup);
        $stmt->bindParam(":destination", $destination);
        $stmt->bindParam(":cost", $cost);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getByUser($user_id) {
        $query = "SELECT t.*, 
                  COALESCE(u_rider.name, t.rider_name) as rider_name, 
                  u_customer.name as user_name 
                  FROM " . $this->table_name . " t 
                  LEFT JOIN users u_customer ON t.user_id = u_customer.id
                  LEFT JOIN riders r ON t.rider_id = r.id
                  LEFT JOIN users u_rider ON r.user_id = u_rider.id
                  WHERE t.user_id = :user_id
                  ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = "SELECT t.*, 
                  COALESCE(u_rider.name, t.rider_name) as rider_name,
                  u_customer.name as user_name
                  FROM " . $this->table_name . " t 
                  LEFT JOIN users u_customer ON t.user_id = u_customer.id
                  LEFT JOIN riders r ON t.rider_id = r.id
                  LEFT JOIN users u_rider ON r.user_id = u_rider.id
                  ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPending() {
        $query = "SELECT t.*, u.name as user_name, u.phone as user_phone
                  FROM " . $this->table_name . " t 
                  JOIN users u ON t.user_id = u.id 
                  WHERE t.status = 'pending' AND t.rider_id IS NULL
                  ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignToRider($transport_id, $rider_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET rider_id = :rider_id, status = 'assigned' 
                  WHERE id = :id AND status = 'pending'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rider_id", $rider_id);
        $stmt->bindParam(":id", $transport_id);
        
        return $stmt->execute();
    }

    public function completeRide($transport_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'completed' 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $transport_id);
        
        return $stmt->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function countPending() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'pending' AND rider_id IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
