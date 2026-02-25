<?php
class Booking {
    private $conn;
    private $table_name = "bookings";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $room_id, $check_in, $check_out, $total_amount) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, room_id, booking_date, check_in, check_out, total_amount) 
                  VALUES (:user_id, :room_id, CURRENT_DATE, :check_in, :check_out, :total_amount)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":room_id", $room_id);
        $stmt->bindParam(":check_in", $check_in);
        $stmt->bindParam(":check_out", $check_out);
        $stmt->bindParam(":total_amount", $total_amount);
        
        return $stmt->execute();
    }

    public function getByUser($user_id) {
        $query = "SELECT b.*, r.room_number, r.room_type, h.name as hostel_name 
                  FROM " . $this->table_name . " b 
                  LEFT JOIN rooms r ON b.room_id = r.id 
                  LEFT JOIN hostels h ON r.hostel_id = h.id 
                  WHERE b.user_id = :user_id 
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = "SELECT b.*, u.name as user_name, r.room_number, h.name as hostel_name 
                  FROM " . $this->table_name . " b 
                  LEFT JOIN users u ON b.user_id = u.id 
                  LEFT JOIN rooms r ON b.room_id = r.id 
                  LEFT JOIN hostels h ON r.hostel_id = h.id 
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
