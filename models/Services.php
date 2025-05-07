<?php

class Services
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get all services offered by a provider, with global service info
     */
    public function getServicesByProvider($provider_id)
    {
        $services = [];
        $query = "SELECT ps.*, s.name, s.description, s.duration AS default_duration, s.price
                  FROM provider_services ps
                  JOIN services s ON ps.service_id = s.service_id
                  WHERE ps.provider_id = ?
                  ORDER BY s.name";
        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$provider_id]);
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $services;
    }

    /**
     * Add a service for a provider (link to global service)
     */
    public function addProviderService($provider_id, $service_id, $custom_duration = null, $custom_notes = null)
    {
        $query = "INSERT INTO provider_services (provider_id, service_id, custom_duration, custom_notes) VALUES (?, ?, ?, ?)";
        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iiis", $provider_id, $service_id, $custom_duration, $custom_notes);
            return $stmt->execute();
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$provider_id, $service_id, $custom_duration, $custom_notes]);
        }
        return false;
    }

    /**
     * Edit a provider's service customization
     */
    public function editProviderService($provider_service_id, $custom_duration, $custom_notes)
    {
        $query = "UPDATE provider_services SET custom_duration = ?, custom_notes = ? WHERE provider_service_id = ?";
        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isi", $custom_duration, $custom_notes, $provider_service_id);
            return $stmt->execute();
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$custom_duration, $custom_notes, $provider_service_id]);
        }
        return false;
    }

    /**
     * Delete a provider's service
     */
    public function deleteProviderService($provider_service_id)
    {
        $query = "DELETE FROM provider_services WHERE provider_service_id = ?";
        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $provider_service_id);
            return $stmt->execute();
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$provider_service_id]);
        }
        return false;
    }
}
?>