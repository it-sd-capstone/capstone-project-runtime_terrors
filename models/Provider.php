<?php
class Provider {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addAvailability($date, $start, $end) {
        $stmt = $this->db->prepare("INSERT INTO provider_availability (provider_name, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");
        return $stmt->execute(["Dr. Smith", $date, $start, $end]); // Replace "Dr. Smith" dynamically
    }
}
?>