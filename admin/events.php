<?php
$page_title = "All Events - Admin Dashboard";
require_once '../includes/header.php';
require_once '../config/database.php';

// Require admin access
requireAdmin();

// Get sorting parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'event_date';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Validate sort column to prevent SQL injection
$allowed_sort_columns = ['title', 'event_date', 'venue', 'category', 'price', 'total_seats'];
if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'event_date';
}

// Validate order
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

try {
    // Fetch all events with booking counts
    $stmt = $pdo->prepare("
        SELECT e.*, 
               COUNT(DISTINCT b.id) as total_bookings,
               COALESCE(SUM(b.quantity), 0) as booked_seats
        FROM events e
        LEFT JOIN bookings b ON e.id = b.event_id
        GROUP BY e.id
        ORDER BY {$sort} {$order}
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching events";
    $events = [];
}

// Function to toggle sort order
function getSortLink($column, $currentSort, $currentOrder) {
    $newOrder = ($currentSort === $column && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    return "?sort={$column}&order={$newOrder}";
}

// Function to get sort icon
function getSortIcon($column, $currentSort, $currentOrder) {
    if ($currentSort !== $column) {
        return '<i class="bi bi-arrow-down-up text-muted"></i>';
    }
    return $currentOrder === 'ASC' 
        ? '<i class="bi bi-arrow-up text-primary"></i>'
        : '<i class="bi bi-arrow-down text-primary"></i>';
}
?>

<style>
    /* Header Section Styles */
    .page-header {
        background: linear-gradient(135deg, var(--primary-color), #224abe);
        padding: 2rem 0;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .page-header .container {
        position: relative;
    }

    .page-header h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 600;
    }

    .header-actions .btn {
        padding: 0.5rem 1.25rem;
        border-radius: 50rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .header-actions .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .btn-create {
        background: white;
        color: var(--primary-color);
        border: none;
    }

    .btn-create:hover {
        background: #f8f9fc;
        color: #224abe;
    }

    /* Table Styles */
    .table-container {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        padding: 1.5rem;
        margin-top: 2rem;
    }
    
    .table th {
        cursor: pointer;
        white-space: nowrap;
        padding: 1rem;
        background-color: #f8f9fc;
        border-bottom: 2px solid #e3e6f0;
    }
    
    .table th:hover {
        background-color: #eaecf4;
    }
    
    .event-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 0.5rem;
    }
    
    .actions-column {
        width: 150px;
    }
    
    .status-badge {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
        border-radius: 50rem;
    }

    .btn-group .btn {
        border-radius: 50%;
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-3px);
    }
</style>

<!-- Page Header -->
<div class="page-header animate__animated animate__fadeIn">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="animate__animated animate__fadeInLeft">All Events</h1>
            <div class="header-actions animate__animated animate__fadeInRight">
                <a href="add-event.php" class="btn btn-create">
                    <i class="bi bi-plus-circle me-2"></i> Create New Event
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>
                            <a href="<?php echo getSortLink('title', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Title <?php echo getSortIcon('title', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('event_date', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Date <?php echo getSortIcon('event_date', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('venue', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Venue <?php echo getSortIcon('venue', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('price', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Price <?php echo getSortIcon('price', $sort, $order); ?>
                            </a>
                        </th>
                        <th>Seats</th>
                        <th>Status</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <?php 
                        $available_seats = $event['total_seats'] - $event['booked_seats'];
                        $status_class = 'bg-success';
                        $status_text = 'Available';
                        
                        if ($available_seats <= 0) {
                            $status_class = 'bg-danger';
                            $status_text = 'Sold Out';
                        } elseif ($available_seats <= 10) {
                            $status_class = 'bg-warning';
                            $status_text = 'Limited';
                        }
                        ?>
                        <tr>
                            <td>
                                <?php if ($event['image_url']): ?>
                                    <img src="../<?php echo htmlspecialchars($event['image_url']); ?>" 
                                         alt="Event image" class="event-image">
                                <?php else: ?>
                                    <div class="event-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td>
                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                <br>
                                <small class="text-muted">
                                    <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                </small>
                            </td>
                            <td><?php echo htmlspecialchars($event['venue']); ?></td>
                            <td><?php echo number_format($event['price']); ?> FCFA</td>
                            <td>
                                <?php echo $available_seats; ?> / <?php echo $event['total_seats']; ?>
                                <br>
                                <small class="text-muted">
                                    <?php echo $event['total_bookings']; ?> bookings
                                </small>
                            </td>
                            <td>
                                <span class="badge <?php echo $status_class; ?> status-badge">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="view-event.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit-event.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-primary" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?php echo $event['id']; ?>)" 
                                            class="btn btn-sm btn-danger" 
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-calendar-x display-4 text-muted"></i>
                                <p class="mt-3">No events found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 