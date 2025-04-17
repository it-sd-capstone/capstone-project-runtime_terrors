<?php
class Provider {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
      // Get available slots (excluding booked ones)
      public function getAvailableSlots() {
          try {
              // Check if using MySQLi or PDO
              if ($this->db instanceof mysqli) {
                  // MySQLi implementation
                  $query = "SELECT a.*, u.first_name, u.last_name 
                            FROM provider_availability a
                            JOIN users u ON a.provider_id = u.user_id
                            WHERE a.is_available = 1 AND a.available_date >= CURDATE()
                            ORDER BY a.available_date, a.start_time";
                  
                  $stmt = $this->db->prepare($query);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  
                  $slots = [];
                  while ($row = $result->fetch_assoc()) {
                      $slots[] = $row;
                  }
                  
                  return $slots;
                  
              } else if ($this->db instanceof PDO) {
                  // PDO implementation
                  $query = "SELECT a.*, u.first_name, u.last_name 
                            FROM provider_availability a
                            JOIN users u ON a.provider_id = u.user_id
                            WHERE a.is_available = 1 AND a.available_date >= CURDATE()
                            ORDER BY a.available_date, a.start_time";
                  
                  $stmt = $this->db->prepare($query);
                  $stmt->execute();
                  return $stmt->fetchAll(PDO::FETCH_ASSOC);
              } else {
                  throw new Exception("Unsupported database connection type");
              }
          } catch (Exception $e) {
              error_log("Error in getAvailableSlots: " . $e->getMessage());
              throw $e; // Re-throw to handle in controller
          }
      }

    // Get booked appointments for a provider
    public function getBookedAppointments($provider_id) {
        $stmt = $this->db->prepare("
            SELECT a.*, u.first_name AS patient_name
            FROM appointments a
            JOIN users u ON a.patient_id = u.user_id
            WHERE a.provider_id = ?
        ");
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add provider availability
    public function addAvailability($date, $start_time, $end_time, $provider_id) {
        $query = "INSERT INTO provider_availability (provider_id, available_date, start_time, end_time) 
              VALUES (:provider_id, :date, :start_time, :end_time)";
    
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':provider_id', $provider_id, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
    
        return $stmt->execute();
    }

    // Check if a time slot is already booked
    public function isSlotBooked($availability_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM appointments WHERE availability_id = ?
        ");
        $stmt->execute([$availability_id]);
        return $stmt->fetchColumn() > 0;
    }
    // Add this method to your Provider model
    public function getAvailability($provider_id) {
        try {
            // Check if using MySQLi or PDO
            if ($this->db instanceof mysqli) {
                // MySQLi implementation
                $query = "SELECT * FROM provider_availability 
                          WHERE provider_id = ? 
                          AND available_date >= CURDATE()
                          ORDER BY available_date, start_time";
                
                $stmt = $this->db->prepare($query);
                
                // Properly bind parameter for MySQLi
                $stmt->bind_param("i", $provider_id);
                $stmt->execute();
                
                // Get the result and fetch as array
                $result = $stmt->get_result();
                
                $availability = [];
                while ($row = $result->fetch_assoc()) {
                    $availability[] = $row;
                }
                
                return $availability;
                
            } elseif ($this->db instanceof PDO) {
                // PDO implementation
                $query = "SELECT * FROM provider_availability 
                         WHERE provider_id = :provider_id 
                         AND available_date >= CURDATE()
                         ORDER BY available_date, start_time";
                         
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':provider_id', $provider_id, PDO::PARAM_INT);
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                throw new Exception("Unsupported database connection type");
            }
        } catch (Exception $e) {
            error_log("Error in getAvailability: " . $e->getMessage());
            throw $e; // Re-throw to handle in controller
        }
    }
}
?>