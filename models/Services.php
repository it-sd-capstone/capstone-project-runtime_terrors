<?php
/**
 * Service Model
 * 
 * Handles business logic related to medical services
 */
class Service {
    private $db;
    
    /**
     * Constructor - initialize with database connection
     * 
     * @param mysqli $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all active services
     * 
     * @return array List of services
     */
    public function getAllServices() {
        $services = [];
        
        try {
            $query = "SELECT * FROM services WHERE is_active = 1 ORDER BY name";
            
            if ($this->db instanceof mysqli) {
                $result = $this->db->query($query);
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $services[] = $this->enrichServiceData($row);
                    }
                }
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->query($query);
                $services = array_map([$this, 'enrichServiceData'], $stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        } catch (Exception $e) {
            error_log("Error in getAllServices: " . $e->getMessage());
        }
        
        return $services;
    }
    
    /**
     * Get featured services for homepage display
     * 
     * @param int $limit Number of services to return
     * @return array List of featured services
     */
    public function getFeaturedServices($limit = 3) {
        $services = [];
        
        try {
            $query = "SELECT * FROM services WHERE is_active = 1 ORDER BY service_id LIMIT ?";
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $limit);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $services[] = $this->enrichServiceData($row);
                    }
                }
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $limit, PDO::PARAM_INT);
                $stmt->execute();
                
                $services = array_map([$this, 'enrichServiceData'], $stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        } catch (Exception $e) {
            error_log("Error in getFeaturedServices: " . $e->getMessage());
        }
        
        return $services;
    }
    
    /**
     * Get a single service by ID
     * 
     * @param int $serviceId Service ID
     * @return array|null Service data or null if not found
     */
    public function getServiceById($serviceId) {
        try {
            $query = "SELECT * FROM services WHERE service_id = ?";
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $serviceId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $row = $result->fetch_assoc()) {
                    return $this->enrichServiceData($row);
                }
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $serviceId, PDO::PARAM_INT);
                $stmt->execute();
                
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    return $this->enrichServiceData($row);
                }
            }
        } catch (Exception $e) {
            error_log("Error in getServiceById: " . $e->getMessage());
        }
        
        return null;
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
            $query = "INSERT INTO services (name, description, duration) 
                     VALUES (?, ?, ?)";
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ssi", 
                    $serviceData['name'],
                    $serviceData['description'],
                    $serviceData['duration']
                );
                
                if ($stmt->execute()) {
                    return $this->db->insert_id;
                }
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $serviceData['name'], PDO::PARAM_STR);
                $stmt->bindParam(2, $serviceData['description'], PDO::PARAM_STR);
                $stmt->bindParam(3, $serviceData['duration'], PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    return $this->db->lastInsertId();
                }
            }
        } catch (Exception $e) {
            error_log("Error in createService: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Update an existing service
     * 
     * @param int $serviceId Service ID to update
     * @param array $serviceData Updated service data
     * @return bool Success flag
     */
    public function updateService($serviceId, $serviceData) {
        try {
            $query = "UPDATE services 
                     SET name = ?, description = ?, duration = ?, is_active = ? 
                     WHERE service_id = ?";
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ssiii", 
                    $serviceData['name'],
                    $serviceData['description'],
                    $serviceData['duration'],
                    $serviceData['is_active'],
                    $serviceId
                );
                
                return $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $serviceData['name'], PDO::PARAM_STR);
                $stmt->bindParam(2, $serviceData['description'], PDO::PARAM_STR);
                $stmt->bindParam(3, $serviceData['duration'], PDO::PARAM_INT);
                $stmt->bindParam(4, $serviceData['is_active'], PDO::PARAM_INT);
                $stmt->bindParam(5, $serviceId, PDO::PARAM_INT);
                
                return $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Error in updateService: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Delete a service (or deactivate it)
     * 
     * @param int $serviceId Service ID to delete
     * @param bool $permanent Whether to permanently delete or just deactivate
     * @return bool Success flag
     */
    public function deleteService($serviceId, $permanent = false) {
        try {
            if ($permanent) {
                $query = "DELETE FROM services WHERE service_id = ?";
            } else {
                $query = "UPDATE services SET is_active = 0 WHERE service_id = ?";
            }
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $serviceId);
                return $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $serviceId, PDO::PARAM_INT);
                return $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Error in deleteService: " . $e->getMessage());
        }
        
        return false;
    }
}