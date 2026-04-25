<?php
/**
 * API: Get Events
 * GET /api/events.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Event.php';

try {
    $type = $_GET['type'] ?? 'all'; // all, upcoming, search
    $page = max(1, $_GET['page'] ?? 1);
    $limit = min(50, $_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    
    // Initialize classes
    $db = new Database($conn);
    $event = new Event($db);
    
    if ($type === 'upcoming') {
        // Get upcoming public events
        $events = $event->get_upcoming_events($offset, $limit);
        $total = $event->get_upcoming_event_count();
        
    } elseif ($type === 'search') {
        $search = sanitize($_GET['q'] ?? '');
        if (strlen($search) < 2) {
            json_response('error', 'Search query must be at least 2 characters', null, 400);
        }
        
        $events = $event->search_events($search, $offset, $limit);
        $total = $db->count('events'); // Simplified count
        
    } else {
        // Get all events
        $events = $event->get_all_events($offset, $limit);
        $total = $event->get_event_count();
    }
    
    $pagination = paginate($total, $limit, $page);
    
    json_response('success', 'Events retrieved', [
        'events' => $events,
        'pagination' => $pagination
    ]);
    
} catch (Exception $e) {
    error_log('Events API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>
