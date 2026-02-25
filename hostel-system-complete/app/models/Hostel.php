<?php
class Hostel {
    private $conn;
    private $table_name = "hostels";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($name, $location, $latitude, $longitude, $description, $contact, $ownerId = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, location, latitude, longitude, description, contact, owner_id) 
                  VALUES (:name, :location, :latitude, :longitude, :description, :contact, :owner_id)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":latitude", $latitude);
        $stmt->bindParam(":longitude", $longitude);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":contact", $contact);
        $stmt->bindParam(":owner_id", $ownerId);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getAll() {
        $query = "SELECT h.*, u.name as owner_name 
                  FROM " . $this->table_name . " h
                  LEFT JOIN users u ON h.owner_id = u.id
                  ORDER BY h.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT h.*, u.name as owner_name 
                  FROM " . $this->table_name . " h
                  LEFT JOIN users u ON h.owner_id = u.id
                  WHERE h.id = :id";
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

    public function update($id, $name, $location, $latitude, $longitude, $description, $contact) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, location = :location, latitude = :latitude, 
                      longitude = :longitude, description = :description, contact = :contact
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":latitude", $latitude);
        $stmt->bindParam(":longitude", $longitude);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":contact", $contact);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function searchByName($name) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE LOWER(name) LIKE LOWER(:name) 
                  ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $search = "%" . $name . "%";
        $stmt->bindParam(":name", $search);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNearby($latitude, $longitude, $radius = 2) {
        $query = "SELECT *, 
                  (6371 * acos(cos(radians(:lat)) * cos(radians(latitude)) * 
                  cos(radians(longitude) - radians(:lng)) + sin(radians(:lat)) * 
                  sin(radians(latitude)))) AS distance 
                  FROM " . $this->table_name . " 
                  HAVING distance < :radius 
                  ORDER BY distance";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":lat", $latitude);
        $stmt->bindParam(":lng", $longitude);
        $stmt->bindParam(":radius", $radius);
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
