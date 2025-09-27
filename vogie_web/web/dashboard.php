<?php
require_once 'config/config.php';
require_once 'includes/AuthController.php';

$auth = new AuthController($conn, $config);

$auth->requireAuth();

$user = $auth->getCurrentUser();

$upcomingBookings = [];
$stmt = $conn->prepare("
    SELECT b.*, d.name as destination_name, d.image_url, d.location
    FROM bookings b
    JOIN destinations d ON b.destination_id = d.id
    WHERE b.user_id = ? AND b.booking_date >= CURDATE()
    ORDER BY b.booking_date ASC
    LIMIT 3
");

$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $upcomingBookings[] = $row;
}
$stmt->close();

$wishlistCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $wishlistCount = $row['count'];
}
$stmt->close();

$totalBookings = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $totalBookings = $row['count'];
}
$stmt->close();

$recentActivities = [];
$stmt = $conn->prepare("
    (
        SELECT 'booking' as type, b.id, b.booking_date as date, d.name as title, 'You booked a trip to ' as message, b.status
        FROM bookings b
        JOIN destinations d ON b.destination_id = d.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
        LIMIT 5
    )
    UNION ALL
    (
        SELECT 'review' as type, r.id, r.created_at as date, d.name as title, CONCAT('You reviewed ', d.name) as message, 'completed' as status
        FROM reviews r
        JOIN destinations d ON r.destination_id = d.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
        LIMIT 5
    )
    ORDER BY date DESC
    LIMIT 5
");

$stmt->bind_param('ii', $user['id'], $user['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['time_ago'] = timeAgo($row['date']);
    $row['icon'] = $row['type'] === 'booking' ? 'fa-suitcase' : 'fa-star';
    $row['color'] = $row['type'] === 'booking' ? 'primary' : 'warning';
    $recentActivities[] = $row;
}
$stmt->close();

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $time = time() - $time;
    $time = ($time < 1) ? 1 : $time;
    $tokens = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    ];

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
    }
}

$pageTitle = 'Dashboard - ' . $config['app_name'];
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="avatar-xxl mb-3">
                        <div class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?php
                                $initials = '';
                                $nameParts = explode(' ', $user['full_name']);
                                foreach ($nameParts as $part) {
                                    $initials .= strtoupper(substr($part, 0, 1));
                                    if (strlen($initials) >= 2) break;
                                }
                                echo $initials;
                            ?>
                        </div>
                    </div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>

                    <div class="d-grid gap-2">
                        <a href="/book-now.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i> New Booking
                        </a>
                    </div>

                    <hr>

                    <div class="list-group list-group-flush">
                        <a href="/dashboard.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="/profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i> My Profile
                        </a>
                        <a href="/my-bookings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-suitcase me-2"></i> My Bookings
                            <span class="badge bg-primary rounded-pill float-end"><?php echo $totalBookings; ?></span>
                        </a>
                        <a href="/wishlist.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart me-2"></i> Wishlist
                            <span class="badge bg-danger rounded-pill float-end"><?php echo $wishlistCount; ?></span>
                        </a>
                        <a href="/reviews.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-star me-2"></i> My Reviews
                        </a>
                        <a href="/settings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                        <a href="/logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="card-title mb-3">Quick Stats</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Bookings:</span>
                        <span class="fw-bold"><?php echo $totalBookings; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Upcoming Trips:</span>
                        <span class="fw-bold"><?php echo count($upcomingBookings); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Wishlist Items:</span>
                        <span class="fw-bold"><?php echo $wishlistCount; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Welcome Banner -->
            <div class="card bg-primary text-white mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row align-items-center">
                        <div class="mb-3 mb-md-0 me-md-4">
                            <h2 class="mb-1">Welcome back, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>! 👋</h2>
                            <p class="mb-0 opacity-75">Here's what's happening with your bookings and trips.</p>
                        </div>
                        <div class="ms-md-auto">
                            <a href="/book-now.php" class="btn btn-light text-primary">
                                <i class="fas fa-plus-circle me-2"></i> Book a New Trip
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                                    <i class="fas fa-suitcase text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Upcoming Trips</h6>
                                    <h3 class="mb-0"><?php echo count($upcomingBookings); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pt-0">
                            <a href="/my-bookings.php?filter=upcoming" class="btn btn-link p-0">View all <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Completed Trips</h6>
                                    <h3 class="mb-0"><?php echo max(0, $totalBookings - count($upcomingBookings)); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pt-0">
                            <a href="/my-bookings.php?filter=completed" class="btn btn-link p-0">View all <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 p-3 rounded me-3">
                                    <i class="fas fa-heart text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Wishlist</h6>
                                    <h3 class="mb-0"><?php echo $wishlistCount; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pt-0">
                            <a href="/wishlist.php" class="btn btn-link p-0">View all <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Trips -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upcoming Trips</h5>
                    <a href="/my-bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($upcomingBookings) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Destination</th>
                                        <th>Date</th>
                                        <th>Travelers</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingBookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking['destination_name']); ?>" class="rounded me-3" width="60">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($booking['destination_name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($booking['location']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                            <td><?php echo $booking['travelers_count']; ?> <?php echo $booking['travelers_count'] > 1 ? 'People' : 'Person'; ?></td>
                                            <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                            <td>
                                                <?php
                                                    $statusClass = '';
                                                    switch (strtolower($booking['status'])) {
                                                        case 'confirmed':
                                                            $statusClass = 'success';
                                                            break;
                                                        case 'pending':
                                                            $statusClass = 'warning';
                                                            break;
                                                        case 'cancelled':
                                                            $statusClass = 'danger';
                                                            break;
                                                        default:
                                                            $statusClass = 'secondary';
                                                    }
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?> bg-opacity-10 text-<?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="bookingActions<?php echo $booking['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bookingActions<?php echo $booking['id']; ?>">
                                                        <li><a class="dropdown-item" href="/booking-details.php?id=<?php echo $booking['id']; ?>"><i class="far fa-eye me-2"></i>View Details</a></li>
                                                        <li><a class="dropdown-item" href="/booking-invoice.php?id=<?php echo $booking['id']; ?>"><i class="fas fa-file-invoice me-2"></i>View Invoice</a></li>
                                                        <?php if (strtolower($booking['status']) === 'confirmed'): ?>
                                                            <li><a class="dropdown-item text-warning" href="#" data-bs-toggle="modal" data-bs-target="#cancelBookingModal" data-booking-id="<?php echo $booking['id']; ?>"><i class="fas fa-times-circle me-2"></i>Cancel Booking</a></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-5">
                            <div class="mb-3">
                                <i class="fas fa-suitcase fa-3x text-muted"></i>
                            </div>
                            <h5>No Upcoming Trips</h5>
                            <p class="text-muted">You don't have any upcoming trips. Start planning your next adventure!</p>
                            <a href="/destinations.php" class="btn btn-primary">
                                <i class="fas fa-map-marked-alt me-2"></i> Explore Destinations
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recentActivities) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentActivities as $activity): ?>
                                <div class="list-group-item border-0 py-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="bg-<?php echo $activity['color']; ?>-subtle text-<?php echo $activity['color']; ?> rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas <?php echo $activity['icon']; ?> fa-fw"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><?php echo $activity['message']; ?></h6>
                                                <small class="text-muted"><?php echo $activity['time_ago']; ?></small>
                                            </div>
                                            <p class="mb-0 text-muted"><?php echo $activity['title']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer bg-white border-top-0 text-center">
                            <a href="/activity.php" class="btn btn-link">View All Activity</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-5">
                            <div class="mb-3">
                                <i class="fas fa-stream fa-3x text-muted"></i>
                            </div>
                            <h5>No Recent Activity</h5>
                            <p class="text-muted mb-0">Your recent activities will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-labelledby="cancelBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelBookingModalLabel">Cancel Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this booking? This action cannot be undone.</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="refundPolicyCheck">
                    <label class="form-check-label small" for="refundPolicyCheck">
                        I understand that cancellations may be subject to our <a href="/refund-policy.php" target="_blank">refund policy</a>.
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBooking">Cancel Booking</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const cancelBookingModal = document.getElementById('cancelBookingModal');
    if (cancelBookingModal) {
        let bookingId = null;

        cancelBookingModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            bookingId = button.getAttribute('data-booking-id');
        });

        document.getElementById('confirmCancelBooking').addEventListener('click', function() {
            const refundPolicyCheck = document.getElementById('refundPolicyCheck');

            if (!refundPolicyCheck.checked) {
                alert('Please confirm that you understand our refund policy.');
                return;
            }

            console.log('Cancelling booking:', bookingId);

            alert('Your booking has been cancelled successfully.');

            const modal = bootstrap.Modal.getInstance(cancelBookingModal);
            modal.hide();

            window.location.reload();
        });
    }

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php include 'includes/footer.php'; ?>