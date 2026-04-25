<?php
/**
 * Event Management Class
 * Handles event CRUD operations
 */

class Event {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create event
     */
    public function create($data) {
        try {
            $data['created_by'] = get_current_user_id();
            $data['created_at'] = date('Y-m-d H:i:s');
            
            $event_id = $this->db->insert('events', $data);
            
            log_activity('EVENT_CREATED', 'Event created: ' . $data['event_name']);
            
            return ['success' => true, 'message' => SUCCESS_EVENT_CREATED, 'event_id' => $event_id];
            
        } catch (Exception $e) {
            error_log('Event Create Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create event'];
        }
    }
    
    /**
     * Update event
     */
    public function update($event_id, $data) {
        try {
            $data['updated_by'] = get_current_user_id();
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $affected = $this->db->update('events', $data, ['event_id' => $event_id]);
            
            if ($affected > 0) {
                log_activity('EVENT_UPDATED', 'Event updated: ID ' . $event_id);
                return ['success' => true, 'message' => 'Event updated successfully'];
            } else {
                return ['success' => false, 'message' => 'No changes made'];
            }
            
        } catch (Exception $e) {
            error_log('Event Update Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update event'];
        }
    }
    
    /**
     * Delete event
     */
    public function delete($event_id) {
        try {
            $affected = $this->db->delete('events', ['event_id' => $event_id]);
            
            if ($affected > 0) {
                log_activity('EVENT_DELETED', 'Event deleted: ID ' . $event_id);
                return ['success' => true, 'message' => 'Event deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Event not found'];
            }
            
        } catch (Exception $e) {
            error_log('Event Delete Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete event'];
        }
    }
    
    /**
     * Get event by ID
     */
    public function get_event($event_id) {
        try {
            $sql = "SELECT e.*, i.url as image_url, i.alt_text FROM events e 
                    LEFT JOIN images i ON e.image_id = i.image_id 
                    WHERE e.event_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log('Get Event Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all events
     */
    public function get_all_events($offset = 0, $limit = 10) {
        try {
            $sql = "SELECT e.*, i.url as image_url, COUNT(r.registration_id) as total_registrations
                    FROM events e 
                    LEFT JOIN images i ON e.image_id = i.image_id
                    LEFT JOIN event_registrations r ON e.event_id = r.event_id
                    GROUP BY e.event_id
                    ORDER BY e.start_date_time DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
            
        } catch (Exception $e) {
            error_log('Get Events Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get upcoming events (for public)
     */
    public function get_upcoming_events($limit = 10) {
        try {
            $now = date('Y-m-d H:i:s');
            
            $sql = "SELECT e.event_id, e.event_name, e.description, e.start_date_time, 
                           e.end_date_time, e.address, i.url as image_url
                    FROM events e 
                    LEFT JOIN images i ON e.image_id = i.image_id
                    WHERE e.event_for = 'all' AND e.start_date_time > ?
                    ORDER BY e.start_date_time ASC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $now, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
            
        } catch (Exception $e) {
            error_log('Get Upcoming Events Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get event count
     */
    public function get_event_count() {
        try {
            return $this->db->count('events');
        } catch (Exception $e) {
            error_log('Event Count Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Search events
     */
    public function search_events($search_term, $offset = 0, $limit = 10) {
        try {
            $search_term = '%' . $search_term . '%';
            
            $sql = "SELECT e.*, i.url as image_url
                    FROM events e 
                    LEFT JOIN images i ON e.image_id = i.image_id
                    WHERE e.event_name LIKE ? OR e.description LIKE ? OR e.address LIKE ?
                    ORDER BY e.start_date_time DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('sssii', $search_term, $search_term, $search_term, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
            
        } catch (Exception $e) {
            error_log('Search Events Error: ' . $e->getMessage());
            return [];
        }
    }
}

?>
