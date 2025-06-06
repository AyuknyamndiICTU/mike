<?php
$page_title = "Manage Users - Admin Dashboard";
require_once '../includes/header.php';
require_once '../config/database.php';

// Require admin access
requireAdmin();

// Get sorting parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort column to prevent SQL injection
$allowed_sort_columns = ['username', 'email', 'role', 'created_at'];
if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'created_at';
}

// Validate order
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

try {
    // First, verify the users table structure
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Build the query based on existing columns
    $select_columns = ['id'];
    
    // Add optional columns if they exist
    if (in_array('username', $columns)) $select_columns[] = 'username';
    if (in_array('name', $columns)) $select_columns[] = 'name';
    if (in_array('email', $columns)) $select_columns[] = 'email';
    if (in_array('role', $columns)) $select_columns[] = 'role';
    if (in_array('status', $columns)) $select_columns[] = 'status';
    if (in_array('created_at', $columns)) $select_columns[] = 'created_at';
    
    // Construct the query
    $query = "
        SELECT u." . implode(', u.', $select_columns) . ",
               COUNT(b.id) as total_bookings
        FROM users u
        LEFT JOIN bookings b ON u.id = b.user_id
        GROUP BY u.id" . (count($select_columns) > 1 ? ", u." . implode(', u.', array_slice($select_columns, 1)) : "") . "
        ORDER BY u.{$sort} {$order}
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set default values for missing fields
    foreach ($users as &$user) {
        if (!isset($user['username']) && isset($user['name'])) {
            $user['username'] = $user['name'];
        }
        if (!isset($user['status'])) {
            $user['status'] = 'active';
        }
        if (!isset($user['role'])) {
            $user['role'] = 'user';
        }
    }
    unset($user); // Break the reference

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $users = [];
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    $_SESSION['error'] = "Error: " . $e->getMessage();
    $users = [];
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

<!-- Add error/success message display -->
<div class="container mt-3">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
</div>

<!-- Page Header -->
<div class="page-header animate__animated animate__fadeIn">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="animate__animated animate__fadeInLeft">Manage Users</h1>
        </div>
    </div>
</div>

<div class="container">
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>
                            <a href="<?php echo getSortLink('username', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Name <?php echo getSortIcon('username', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('email', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Email <?php echo getSortIcon('email', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo getSortLink('role', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Role <?php echo getSortIcon('role', $sort, $order); ?>
                            </a>
                        </th>
                        <th>Bookings</th>
                        <th>
                            <a href="<?php echo getSortLink('created_at', $sort, $order); ?>" class="text-dark text-decoration-none">
                                Joined <?php echo getSortIcon('created_at', $sort, $order); ?>
                            </a>
                        </th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username'] ?? $user['name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge <?php echo ($user['role'] ?? 'user') === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                        <?php echo ucfirst($user['role'] ?? 'user'); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['total_bookings']; ?> bookings</td>
                                <td><?php echo isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?></td>
                                <td>
                                    <span class="badge <?php echo ($user['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status'] ?? 'active'; ?>')" 
                                                class="btn btn-sm <?php echo ($user['status'] ?? 'active') === 'active' ? 'btn-warning' : 'btn-success'; ?>" 
                                                title="<?php echo ($user['status'] ?? 'active') === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="bi bi-<?php echo ($user['status'] ?? 'active') === 'active' ? 'pause' : 'play'; ?>"></i>
                                        </button>
                                        <?php if (($user['role'] ?? 'user') !== 'admin'): ?>
                                        <button onclick="confirmDelete(<?php echo $user['id']; ?>, 'Are you sure you want to delete this user?')" 
                                                class="btn btn-sm btn-danger" 
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-people display-4 text-muted"></i>
                                <p class="mt-3">No users found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const message = `Are you sure you want to ${currentStatus === 'active' ? 'deactivate' : 'activate'} this user?`;
    
    if (confirm(message)) {
        window.location.href = `toggle-user-status.php?id=${userId}&status=${newStatus}`;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 