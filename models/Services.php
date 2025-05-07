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
     * Add icon and other metadata to service records
     * 
     * @param array $service Service data row
     * @return array Enhanced service data
     */
    private function enrichServiceData($service) {
        // Add appropriate Font Awesome icon based on service name
        $service['icon'] = $this->getServiceIcon($service['name']);
        
        // Format duration as hours and minutes if needed
        if (isset($service['duration']) && $service['duration'] >= 60) {
            $hours = floor($service['duration'] / 60);
            $minutes = $service['duration'] % 60;
            
            if ($minutes > 0) {
                $service['duration_formatted'] = "{$hours} hour" . ($hours > 1 ? 's' : '') .
                                             " {$minutes} minute" . ($minutes > 1 ? 's' : '');
            } else {
                $service['duration_formatted'] = "{$hours} hour" . ($hours > 1 ? 's' : '');
            }
        } else {
            $service['duration_formatted'] = $service['duration'] . " minute" .
                                          ($service['duration'] != 1 ? 's' : '');
        }
        
        return $service;
    }
    
    /**
     * Get appropriate icon for a service based on its name
     * 
     * @param string $serviceName Name of the service
     * @return string FontAwesome icon name
     */
    private function getServiceIcon($serviceName) {
        $name = strtolower($serviceName);
        
        if (strpos($name, 'check') !== false || strpos($name, 'exam') !== false) {
            return 'stethoscope';
        } elseif (strpos($name, 'therapy') !== false || strpos($name, 'counseling') !== false) {
            return 'brain';
        } elseif (strpos($name, 'cardiac') !== false || strpos($name, 'heart') !== false) {
            return 'heartbeat';
        } elseif (strpos($name, 'dental') !== false || strpos($name, 'teeth') !== false) {
            return 'tooth';
        } elseif (strpos($name, 'eye') !== false || strpos($name, 'vision') !== false) {
            return 'eye';
        } elseif (strpos($name, 'physical') !== false || strpos($name, 'rehab') !== false) {
            return 'dumbbell';
        } elseif (strpos($name, 'vaccine') !== false || strpos($name, 'immun') !== false) {
            return 'syringe';
        } elseif (strpos($name, 'lab') !== false || strpos($name, 'test') !== false) {
            return 'vial';
        } elseif (strpos($name, 'pediatric') !== false || strpos($name, 'child') !== false) {
            return 'child';
        } else {
            // Default icon
            return 'user-md';
        }
    }
    
   /**
     * Create a new service
     *
     * @param array $serviceData Service data to insert
     * @return int|bool New service ID or false on failure
     */
    public function createService($serviceData) {
        try {
            // Validate required fields
            if (empty($serviceData['name'])) {
                error_log("Missing required fields for service creation");
                return false;
            }
                
            // Get provider ID from session
            $providerId = $_SESSION['user_id'] ?? 0;
            
            if (empty($providerId)) {
                error_log("No provider ID found in session");
                return false;
            }
                
            // Set defaults for optional fields
            $duration = $serviceData['duration'] ?? 30;
            $price = $serviceData['price'] ?? 0;
            $isActive = $serviceData['is_active'] ?? 1;
                
            if ($this->db instanceof mysqli) {
                // Begin transaction
                $this->db->begin_transaction();
                
                try {
                    // Insert into services table
                    $sql = "INSERT INTO services (name, description, price, duration) VALUES (?, ?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bind_param("ssdi", 
                        $serviceData['name'], 
                        $serviceData['description'], 
                        $price, 
                        $duration
                    );
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Error inserting service: " . $stmt->error);
                    }
                    
                    $serviceId = $this->db->insert_id;
                    
                    // Associate service with provider
                    $sql2 = "INSERT INTO provider_services (provider_id, service_id) VALUES (?, ?)";
                    $stmt2 = $this->db->prepare($sql2);
                    $stmt2->bind_param("ii", $providerId, $serviceId);
                    
                    if (!$stmt2->execute()) {
                        throw new Exception("Error associating service with provider: " . $stmt2->error);
                    }
                    
                    // Commit transaction
                    $this->db->commit();
                    return $serviceId;
                    
                } catch (Exception $e) {
                    // Rollback on error
                    $this->db->rollback();
                    error_log("Transaction failed: " . $e->getMessage());
                    return false;
                }
                
            } elseif ($this->db instanceof PDO) {
                // Begin transaction
                $this->db->beginTransaction();
                
                try {
                    // Insert into services table
                    $sql = "INSERT INTO services (name, description, price, duration) VALUES (:name, :description, :price, :duration)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(':name', $serviceData['name']);
                    $stmt->bindParam(':description', $serviceData['description']);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':duration', $duration);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Error inserting service: " . implode(", ", $stmt->errorInfo()));
                    }
                    
                    $serviceId = $this->db->lastInsertId();
                    
                    // Associate service with provider
                    $sql2 = "INSERT INTO provider_services (provider_id, service_id) VALUES (:provider_id, :service_id)";
                    $stmt2 = $this->db->prepare($sql2);
                    $stmt2->bindParam(':provider_id', $providerId);
                    $stmt2->bindParam(':service_id', $serviceId);
                    
                    if (!$stmt2->execute()) {
                        throw new Exception("Error associating service with provider: " . implode(", ", $stmt2->errorInfo()));
                    }
                    
                    // Commit transaction
                    $this->db->commit();
                    return $serviceId;
                    
                } catch (Exception $e) {
                    // Rollback on error
                    $this->db->rollback();
                    error_log("Transaction failed: " . $e->getMessage());
                    return false;
                }
            }
        } catch (Exception $e) {
            error_log("Exception in createService: " . $e->getMessage());
        }
            
        return false;
    }

    /**
     * Get the total count of services
     * @param bool $activeOnly Whether to count only active services
     * @return int Total number of services
     */
    public function getTotalCount($activeOnly = false) {
        try {
            $query = "SELECT COUNT(*) as count FROM services";
            if ($activeOnly) {
                $query .= " WHERE is_active = 1";
            }
            
            $stmt = $this->db->query($query);
            if ($stmt) {
                                $result = $stmt->fetch_assoc();
                return $result['count'];
            }
        } catch (Exception $e) {
            error_log("Error in getTotalCount: " . $e->getMessage());
        }
        return 0;
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