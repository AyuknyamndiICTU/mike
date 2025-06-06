<?php
$page_title = "Events - Event Booking System";
require_once 'includes/header.php';
require_once 'config/database.php';

try {
    // Get filters from URL
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
    $category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
    $date = isset($_GET['date']) ? sanitize($_GET['date']) : '';

    // Build the query with minimum ticket price
    $sql = "SELECT e.*,
                   COALESCE(MIN(tt.price), 0) as min_price,
                   COUNT(tt.id) as ticket_type_count
            FROM events e
            LEFT JOIN ticket_types tt ON e.id = tt.event_id
            WHERE e.status = 'active'";
    $params = [];

    if ($search) {
        $sql .= " AND (e.title LIKE :search OR e.description LIKE :search2 OR e.venue LIKE :search3)";
        $params[':search'] = "%$search%";
        $params[':search2'] = "%$search%";
        $params[':search3'] = "%$search%";
    }

    if ($category) {
        $sql .= " AND e.category = :category";
        $params[':category'] = $category;
    }

    if ($date) {
        $sql .= " AND DATE(e.event_date) = :date";
        $params[':date'] = $date;
    }

    $sql .= " GROUP BY e.id ORDER BY e.event_date ASC";

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Get categories for filter
    $categories_sql = "SELECT DISTINCT category FROM events WHERE category IS NOT NULL ORDER BY category";
    $categories_stmt = $pdo->query($categories_sql);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $error = "An error occurred while fetching events.";
}
?>

<style>
/* Events Page Styles */
.events-header {
    background: linear-gradient(135deg, var(--primary-color), #224abe);
    padding: 60px 0;
    margin-bottom: 60px;
    color: white;
    position: relative;
    overflow: hidden;
}

.events-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.1) 75%, transparent 75%, transparent);
    background-size: 50px 50px;
    opacity: 0.1;
    animation: slide 20s linear infinite;
}

.filter-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-top: -80px;
    position: relative;
    z-index: 2;
}

.filter-section input,
.filter-section select {
    height: 50px;
    border-radius: 10px;
    border: 2px solid #eef0f5;
    padding: 10px 20px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.filter-section input:focus,
.filter-section select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-color-rgb), 0.15);
}

.filter-section .btn-primary {
    height: 50px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.filter-section .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(var(--primary-color-rgb), 0.3);
}

.event-card {
    background: #fff;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    margin-bottom: 2rem;
}

.event-card:hover {
    transform: translateY(-5px);
}

.event-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.event-content {
    padding: 1.5rem;
}

.event-title {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #333;
}

.event-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.9rem;
}

.detail-item i {
    color: #0d6efd;
}

.event-description {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.event-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.price {
    font-size: 1.25rem;
    font-weight: bold;
    color: #0d6efd;
}

.no-events-message {
    background: rgba(var(--primary-color-rgb), 0.05);
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    margin: 40px 0;
}

.pagination {
    margin-top: 50px;
    margin-bottom: 50px;
}

.pagination .page-link {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    margin: 0 5px;
    border: none;
    color: #6c757d;
    background: #f8f9fc;
    transition: all 0.3s ease;
}

.pagination .page-link:hover,
.pagination .page-item.active .page-link {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(var(--primary-color-rgb), 0.2);
}

@keyframes slide {
    from { background-position: 0 0; }
    to { background-position: 1000px 0; }
}

@media (max-width: 768px) {
    .events-header {
        padding: 40px 0;
    }
    
    .filter-section {
        margin-top: -60px;
        padding: 20px;
    }
    
    .event-card {
        margin-bottom: 20px;
    }
}
</style>

<!-- Events Header -->
<section class="events-header">
    <div class="container">
        <h1 class="text-center mb-0">Discover Amazing Events</h1>
    </div>
</section>

<!-- Search and Filter Section -->
<div class="container">
    <div class="filter-section">
        <form action="" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search events..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        <?php while($cat = $categories_stmt->fetch()): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                    <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date" 
                           value="<?php echo htmlspecialchars($date); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-2"></i>Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Events Grid -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <?php if ($stmt->rowCount() === 0): ?>
            <div class="no-events-message">
                <i class="bi bi-calendar-x display-4 text-primary mb-4 d-block"></i>
                <h3 class="mb-3">No events found matching your criteria</h3>
                <p class="text-muted mb-4">Try adjusting your search parameters or browse all events</p>
                <a href="events.php" class="btn btn-primary">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Clear all filters
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4 mt-4">
                <?php while($event = $stmt->fetch()): ?>
                    <div class="col-md-4">
                        <div class="event-card">
                            <img src="<?php echo htmlspecialchars($event['image_url']); ?>" class="event-image" alt="<?php echo htmlspecialchars($event['title']); ?>">
                            <div class="event-content">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                
                                <div class="event-details">
                                    <div class="detail-item">
                                        <i class="bi bi-calendar"></i>
                                        <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                                    </div>
                                    <div class="detail-item">
                                        <i class="bi bi-clock"></i>
                                        <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                    </div>
                                    <div class="detail-item">
                                        <i class="bi bi-geo-alt"></i>
                                        <?php echo htmlspecialchars($event['venue']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <i class="bi bi-person"></i>
                                        <?php echo htmlspecialchars($event['organizer_name']); ?>
                                    </div>
                                </div>

                                <p class="event-description"><?php echo substr(htmlspecialchars($event['description']), 0, 150) . '...'; ?></p>
                                
                                <div class="event-footer">
                                    <div class="price">
                                        <?php if ($event['ticket_type_count'] > 0): ?>
                                            From <?php echo number_format($event['min_price'], 0); ?> FCFA
                                        <?php else: ?>
                                            <span class="text-muted">No tickets available</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Pagination -->
    <nav aria-label="Page navigation" class="mt-5">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
</div>

<?php require_once 'includes/footer.php'; ?> 