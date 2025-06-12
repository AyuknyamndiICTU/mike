<?php
$page_title = "Home - Event Booking System";
require_once 'includes/header.php';
require_once 'config/database.php';

try {
    // Get featured events with minimum ticket price
    $featured_sql = "SELECT e.*,
                            COALESCE(MIN(tt.price), 0) as min_price,
                            COUNT(tt.id) as ticket_type_count
                     FROM events e
                     LEFT JOIN ticket_types tt ON e.id = tt.event_id
                     WHERE e.status = 'active'
                     GROUP BY e.id
                     ORDER BY e.created_at DESC
                     LIMIT 6";
    $featured_stmt = $pdo->query($featured_sql);

    // Get total number of events for pagination
    $count_sql = "SELECT COUNT(*) as total FROM events WHERE status = 'active'";
    $total_events = $pdo->query($count_sql)->fetch()['total'];
    
    // Calculate total pages
    $items_per_page = 6;
    $total_pages = ceil($total_events / $items_per_page);
    
    // Get current page
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    // Get event categories for filter
    $categories_sql = "SELECT DISTINCT category FROM events WHERE category IS NOT NULL ORDER BY category";
    $categories_stmt = $pdo->query($categories_sql);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $error = "An error occurred while fetching events.";
}
?>

<style>
/* Animated Background */
body {
    background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    min-height: 100vh;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Hero Section Styles */
.hero-section {
    background: linear-gradient(135deg, var(--primary-color), #224abe);
    padding: 80px 0;
    color: white;
    text-align: center;
    margin-bottom: 60px;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
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

@keyframes slide {
    from { background-position: 0 0; }
    to { background-position: 1000px 0; }
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

@keyframes headerShine {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(100%) rotate(45deg); }
}

.hero-section h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    animation: fadeInDown 1s ease;
    background: linear-gradient(135deg, var(--success-color) 0%, var(--accent-color) 50%, var(--primary-color) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    background-size: 200% 100%;
    animation: fadeInDown 1s ease, gradientShift 4s ease-in-out infinite 1s;
    position: relative;
    display: inline-block;
    overflow: hidden;
}

.hero-section h1::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.15), transparent);
    transform: rotate(45deg);
    animation: headerShine 4s infinite 1s;
    pointer-events: none;
}

.hero-section p {
    font-size: 1.2rem;
    margin-bottom: 30px;
    animation: fadeInUp 1s ease;
}

.search-form {
    background: rgba(255, 255, 255, 0.1);
    padding: 30px;
    border-radius: 15px;
    backdrop-filter: blur(10px);
    animation: fadeIn 1s ease;
}

/* Featured Events Styles */
.featured-events {
    padding: 60px 0;
    background: #f8f9fc;
}

.featured-events h2 {
    text-align: center;
    margin-bottom: 40px;
    position: relative;
    padding-bottom: 15px;
    background: linear-gradient(135deg, var(--success-color) 0%, var(--accent-color) 50%, var(--primary-color) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    background-size: 200% 100%;
    animation: gradientShift 4s ease-in-out infinite;
    display: inline-block;
    width: 100%;
    overflow: hidden;
}

.featured-events h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: var(--primary-color);
}

.card {
    border: none;
    transition: all 0.3s ease;
    animation: fadeIn 0.5s ease;
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.card-img-top {
    height: 200px;
    object-fit: cover;
}

/* Section Spacing and Common Styles */
section {
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}

section h2 {
    text-align: center;
    margin-bottom: 60px;
    font-size: 2.5rem;
    font-weight: 700;
    position: relative;
    padding-bottom: 20px;
}

section h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: var(--primary-color);
    border-radius: 2px;
}

/* Event Categories Styles */
.event-categories {
    background: linear-gradient(to bottom, #f8f9fc, #ffffff);
    margin-top: 80px;
}

.category-card {
    text-align: center;
    padding: 40px 25px;
    border-radius: 20px;
    background: white;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 0;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--primary-color);
    opacity: 0;
    transition: all 0.4s ease;
    z-index: 0;
}

.category-card:hover::before {
    opacity: 1;
}

.category-card > * {
    position: relative;
    z-index: 1;
}

.category-card:hover {
    transform: translateY(-15px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.category-card i {
    font-size: 3.5rem;
    margin-bottom: 25px;
    color: var(--primary-color);
    transition: all 0.4s ease;
    display: inline-block;
}

.category-card:hover i {
    color: white;
    transform: scale(1.1) rotate(5deg);
}

.category-card h5 {
    font-size: 1.25rem;
    margin-bottom: 0;
    font-weight: 600;
    transition: all 0.4s ease;
}

.category-card a {
    color: inherit;
    text-decoration: none;
    transition: all 0.4s ease;
}

.category-card:hover a,
.category-card:hover h5 {
    color: white;
}

/* How It Works Section */
.how-it-works {
    background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
    padding-top: 120px;
    padding-bottom: 120px;
}

.step {
    padding: 40px 30px;
    border-radius: 20px;
    background: white;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    height: 100%;
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    position: relative;
    overflow: hidden;
}

.step::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--primary-color);
    opacity: 0;
    transition: all 0.4s ease;
    z-index: 0;
}

.step:hover::before {
    opacity: 1;
}

.step > * {
    position: relative;
    z-index: 1;
}

.step:hover {
    transform: translateY(-15px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.step i {
    font-size: 3.5rem;
    color: var(--primary-color);
    margin-bottom: 25px;
    transition: all 0.4s ease;
    display: inline-block;
}

.step:hover i {
    color: white;
    transform: scale(1.1) rotate(5deg);
}

.step h4 {
    margin-bottom: 20px;
    font-weight: 600;
    transition: all 0.4s ease;
    font-size: 1.5rem;
}

.step p {
    color: #6c757d;
    transition: all 0.4s ease;
    margin-bottom: 0;
    line-height: 1.6;
}

.step:hover h4,
.step:hover p {
    color: white;
}

/* Pagination Styles */
.pagination {
    margin-top: 40px;
    justify-content: center;
}

.pagination .page-link {
    padding: 10px 20px;
    margin: 0 5px;
    border-radius: 8px;
    border: none;
    color: var(--primary-color);
    background-color: #f8f9fc;
    transition: all 0.3s ease;
}

.pagination .page-link:hover,
.pagination .page-item.active .page-link {
    background-color: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Footer Section */
.footer {
    background: #f8f9fc;
    padding: 80px 0 40px;
    margin-top: 80px;
}

.footer h3 {
    font-size: 1.5rem;
    margin-bottom: 25px;
    font-weight: 600;
}

.footer p {
    color: #6c757d;
    line-height: 1.6;
}

.footer-links {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 15px;
}

.footer-links a {
    color: #6c757d;
    text-decoration: none;
    transition: all 0.3s ease;
}

.footer-links a:hover {
    color: var(--primary-color);
    padding-left: 5px;
}

.social-links {
    margin-top: 20px;
}

.social-links a {
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    border-radius: 50%;
    background: #f1f3f8;
    color: var(--primary-color);
    margin-right: 10px;
    transition: all 0.3s ease;
}

.social-links a:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-3px);
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Styles */
@media (max-width: 768px) {
    .hero-section h1 {
        font-size: 2.5rem;
    }
    
    .search-form {
        padding: 20px;
    }
    
    .category-card {
        margin-bottom: 20px;
    }
}

/* Filter Section Styles */
.filter-section {
    background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.2));
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 20px;
    margin: 40px 0;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    animation: slideDown 0.6s ease-out;
}

.filter-section .row {
    align-items: center;
}

.filter-section input,
.filter-section select,
.filter-section .date-picker {
    height: 50px;
    border-radius: 12px;
    border: 2px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.9);
    padding: 10px 20px;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.filter-section input:focus,
.filter-section select:focus,
.filter-section .date-picker:focus {
    border-color: var(--primary-color);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.filter-section .btn-filter {
    height: 50px;
    border-radius: 12px;
    padding: 0 30px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    background: var(--primary-color);
    border: none;
    color: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.filter-section .btn-filter:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(var(--primary-color-rgb), 0.3);
}

/* Events Section Styles */
.events-section {
    padding: 60px 0;
    background: linear-gradient(to bottom, #ffffff, #f8f9fc);
}

.no-events-message {
    background: rgba(var(--primary-color-rgb), 0.05);
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    margin: 40px 0;
    animation: fadeInUp 0.5s ease;
}

.no-events-message p {
    color: var(--primary-color);
    font-size: 1.2rem;
    margin-bottom: 20px;
}

.clear-filters-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.clear-filters-link:hover {
    transform: translateX(5px);
}

.clear-filters-link i {
    transition: all 0.3s ease;
}

.clear-filters-link:hover i {
    transform: rotate(90deg);
}

/* Pagination Styles */
.pagination-container {
    margin-top: 50px;
    padding: 20px 0;
}

.pagination {
    gap: 10px;
}

.pagination .page-item .page-link {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-weight: 500;
    font-size: 1rem;
    color: #6c757d;
    background: white;
    border: 2px solid #eef0f5;
    transition: all 0.3s ease;
}

.pagination .page-item.active .page-link,
.pagination .page-item .page-link:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(var(--primary-color-rgb), 0.2);
}

/* Enhanced Footer Styles */
.footer {
    background: linear-gradient(135deg, #f8f9fc, #ffffff);
    padding: 100px 0 40px;
    position: relative;
    overflow: hidden;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(to right, var(--primary-color), #224abe, var(--primary-color));
}

.footer-content {
    position: relative;
    z-index: 1;
}

.footer-brand {
    margin-bottom: 30px;
}

.footer-brand h3 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.footer-brand p {
    font-size: 1.1rem;
    color: #6c757d;
    line-height: 1.6;
}

.footer-links h4 {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 25px;
    position: relative;
    padding-bottom: 15px;
}

.footer-links h4::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--primary-color);
    border-radius: 2px;
}

.footer-links ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links ul li {
    margin-bottom: 15px;
}

.footer-links ul li a {
    color: #6c757d;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.footer-links ul li a:hover {
    color: var(--primary-color);
    transform: translateX(8px);
}

.footer-links ul li a i {
    font-size: 0.8rem;
    transition: all 0.3s ease;
}

.footer-social {
    margin-top: 30px;
}

.footer-social h4 {
    margin-bottom: 20px;
}

.social-links {
    display: flex;
    gap: 15px;
}

.social-links a {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: white;
    color: var(--primary-color);
    font-size: 1.2rem;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.social-links a:hover {
    transform: translateY(-5px) rotate(8deg);
    background: var(--primary-color);
    color: white;
    box-shadow: 0 8px 20px rgba(var(--primary-color-rgb), 0.2);
}

.footer-bottom {
    margin-top: 60px;
    padding-top: 30px;
    border-top: 1px solid #eef0f5;
    text-align: center;
}

.footer-bottom p {
    color: #6c757d;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Enhanced Button Styles */
.btn-outline-primary {
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    font-weight: 600;
    padding: 8px 20px;
    border-radius: 25px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    letter-spacing: 0.3px;
}

.btn-outline-primary:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(var(--primary-color-rgb), 0.3);
    border-color: var(--primary-color);
}

.btn-outline-primary i {
    transition: all 0.3s ease;
}

.btn-outline-primary:hover i {
    transform: translateX(3px);
}

.view-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, var(--primary-color), #224abe);
    color: white;
    text-decoration: none;
    padding: 15px 35px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    letter-spacing: 0.5px;
    margin-top: 40px;
    transition: all 0.4s ease;
    box-shadow: 0 10px 30px rgba(var(--primary-color-rgb), 0.2);
    position: relative;
    overflow: hidden;
}

.view-all-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: all 0.6s ease;
}

.view-all-btn:hover::before {
    left: 100%;
}

.view-all-btn:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 15px 40px rgba(var(--primary-color-rgb), 0.4);
    color: white;
    text-decoration: none;
}

.view-all-btn i {
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.view-all-btn:hover i {
    transform: rotate(90deg) scale(1.1);
}

/* Card hover effects for event cards */
.card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.card-img-top {
    height: 200px;
    object-fit: cover;
    transition: all 0.3s ease;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}

.card-body {
    padding: 1.5rem;
}

.card-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.card-text {
    line-height: 1.6;
    margin-bottom: 1rem;
}

.card-text.text-muted {
    font-size: 0.9rem;
    color: #666 !important;
}

.card-text.text-muted i {
    color: var(--primary-color);
    margin-right: 5px;
}

/* Price Display Styling */
.price-display {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.price-display .h5 {
    font-weight: 700;
    margin: 0;
    background: linear-gradient(135deg, var(--primary-color), #224abe);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.price-display .text-muted {
    font-style: italic;
    font-size: 0.9rem;
}

/* Enhanced responsive design */
@media (max-width: 576px) {
    .btn-outline-primary {
        padding: 6px 15px;
        font-size: 0.8rem;
    }

    .view-all-btn {
        padding: 12px 25px;
        font-size: 1rem;
    }

    .card-body {
        padding: 1rem;
    }

    .price-display .h5 {
        font-size: 1rem;
    }
}
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="animate__animated animate__fadeInDown">Find Your Next Event</h1>
        <p class="animate__animated animate__fadeInUp">Discover amazing events happening around you</p>
        
        <form id="search-form" class="search-form animate__animated animate__fadeIn" action="events.php" method="GET">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Search events...">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        <?php while($category = $categories_stmt->fetch()): ?>
                            <option value="<?php echo htmlspecialchars($category['category']); ?>">
                                <?php echo htmlspecialchars($category['category']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Featured Events Section -->
<section class="featured-events">
    <div class="container">
        <h2 class="animate__animated animate__fadeInDown">Featured Events</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <?php if ($featured_stmt->rowCount() === 0): ?>
                <div class="alert alert-info">No featured events available at the moment.</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php 
                    $delay = 0;
                    while($event = $featured_stmt->fetch()): 
                    ?>
                        <div class="col-md-4">
                            <div class="card h-100 animate__animated animate__fadeIn" style="animation-delay: <?php echo $delay; ?>s">
                                <?php if (!empty($event['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($event['image_url']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <p class="card-text text-muted">
                                        <i class="bi bi-calendar"></i> 
                                        <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                                        <br>
                                        <i class="bi bi-geo-alt"></i> 
                                        <?php echo htmlspecialchars($event['venue']); ?>
                                    </p>
                                    <p class="card-text">
                                        <?php echo substr(htmlspecialchars($event['description']), 0, 100) . '...'; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="price-display">
                                            <?php if ($event['ticket_type_count'] > 0): ?>
                                                <span class="h5 mb-0 text-primary">From <?php echo number_format($event['min_price'], 0); ?> FCFA</span>
                                            <?php else: ?>
                                                <span class="h6 mb-0 text-muted">No tickets available</span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="event.php?id=<?php echo $event['id']; ?>"
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-arrow-right me-1"></i>View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                    $delay += 0.2;
                    endwhile; 
                    ?>
                </div>
                
                <div class="text-center">
                    <a href="events.php" class="view-all-btn animate__animated animate__fadeInUp">
                        <i class="bi bi-grid me-1"></i>View All Events
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Event Categories Section -->
<section class="event-categories">
    <div class="container">
        <h2 class="animate__animated animate__fadeInDown">Event Categories</h2>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="category-card animate__animated animate__fadeIn" style="animation-delay: 0.1s">
                    <i class="bi bi-music-note-beamed"></i>
                    <a href="events.php?category=Music">
                        <h5>Music</h5>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="category-card animate__animated animate__fadeIn" style="animation-delay: 0.2s">
                    <i class="bi bi-ticket-perforated"></i>
                    <a href="events.php?category=Theater">
                        <h5>Theater</h5>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="category-card animate__animated animate__fadeIn" style="animation-delay: 0.3s">
                    <i class="bi bi-person-workspace"></i>
                    <a href="events.php?category=Business">
                        <h5>Business</h5>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="category-card animate__animated animate__fadeIn" style="animation-delay: 0.4s">
                    <i class="bi bi-controller"></i>
                    <a href="events.php?category=Sports">
                        <h5>Sports</h5>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works">
    <div class="container">
        <h2 class="animate__animated animate__fadeInDown">How It Works</h2>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="step animate__animated animate__fadeIn" style="animation-delay: 0.1s">
                    <i class="bi bi-search"></i>
                    <h4>Find Events</h4>
                    <p>Search and discover events that match your interests</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="step animate__animated animate__fadeIn" style="animation-delay: 0.2s">
                    <i class="bi bi-ticket-detailed"></i>
                    <h4>Book Tickets</h4>
                    <p>Select your tickets and complete the booking process</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="step animate__animated animate__fadeIn" style="animation-delay: 0.3s">
                    <i class="bi bi-emoji-smile"></i>
                    <h4>Enjoy the Event</h4>
                    <p>Get your e-tickets and enjoy the experience</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="filter-section">
    <div class="container">
        <form action="events.php" method="GET" class="row g-3">
            <div class="col-lg-4 col-md-6">
                <input type="text" class="form-control" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php while($category = $categories_stmt->fetch()): ?>
                        <option value="<?php echo htmlspecialchars($category['category']); ?>" 
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $category['category']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['category']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <input type="date" class="form-control date-picker" name="date" value="<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>">
            </div>
            <div class="col-lg-2 col-md-6">
                <button type="submit" class="btn btn-filter w-100">
                    <i class="bi bi-search me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Events Section -->
<section class="events-section">
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <?php if ($featured_stmt->rowCount() === 0): ?>
                <div class="no-events-message">
                    <p>No events found matching your criteria.</p>
                    <a href="events.php" class="clear-filters-link">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Clear all filters
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Pagination -->
        <div class="pagination-container">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>" aria-label="Previous">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>" aria-label="Next">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 