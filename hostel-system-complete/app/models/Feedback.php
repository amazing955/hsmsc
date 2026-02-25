<?php
class Feedback {
    private $conn;
    private $table_name = "feedback";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $subject, $message, $type = 'feedback') {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, subject, message, type) 
                  VALUES (:user_id, :subject, :message, :type)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":subject", $subject);
        $stmt->bindParam(":message", $message);
        $stmt->bindParam(":type", $type);
        
        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT f.*, u.name as user_name, u.email 
                  FROM " . $this->table_name . " f 
                  LEFT JOIN users u ON f.user_id = u.id 
                  ORDER BY f.created_at DESC";
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
