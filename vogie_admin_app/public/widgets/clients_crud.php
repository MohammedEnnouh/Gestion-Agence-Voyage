<?php

try {
  $tableExists = false;
  $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
  if ($stmt && $stmt->fetchColumn()) { $tableExists = true; }
} catch (Throwable $e) {
  $tableExists = false;
}

if (!$tableExists) {
  echo '<div class="alert alert-warning">La table <code>users</code> est introuvable. Veuillez exécuter <a href="/Vogie2/public/import_sql.php">import_sql.php</a> pour importer le dump SQL.</div>';
  return;
}

global $pdo;

if (($_POST['action'] ?? '') === 'create_client') {
  $full_name = trim($_POST['nom'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['telephone'] ?? '');
  if ($full_name !== '') {
    try {
      $pdo->prepare("INSERT INTO users(full_name,email,phone,password,role,is_active) VALUES(?,?,?,?, 'user', 1)")
          ->execute([$full_name,$email,$phone,'']);
    } catch (Throwable $e) {
      echo '<div class="alert alert-danger">Erreur lors de l\'ajout du client: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
  }
}

if (($_POST['action'] ?? '') === 'update_client') {
  $id = (int)($_POST['id'] ?? 0);
  $full_name = trim($_POST['nom'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['telephone'] ?? '');
  if ($id && $full_name !== '') {
    try {
      $pdo->prepare("UPDATE users SET full_name=?, email=?, phone=? WHERE id=? AND role='user'")
          ->execute([$full_name, $email, $phone, $id]);
    } catch (Throwable $e) {
      echo '<div class="alert alert-danger">Erreur lors de la modification: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
  }
}

if (($_POST['action'] ?? '') === 'delete_client') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) {
    try {
      $pdo->prepare("DELETE FROM users WHERE id=? AND role='user'")->execute([$id]);
    } catch (Throwable $e) {
      echo '<div class="alert alert-danger">Erreur lors de la suppression: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
  }
}

try {
  $clients = $pdo->query("SELECT * FROM users WHERE role='user' ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $clients = [];
  echo '<div class="alert alert-danger">Erreur lors du chargement des clients: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

function getAvatarColor($name) {
  $colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe'];
  return $colors[crc32($name) % count($colors)];
}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="mb-1">Clients Management</h2>
    <p class="text-muted mb-0">Manage your customer database</p>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary">
      <i class="fas fa-download"></i> Export
    </button>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
      <i class="fas fa-plus"></i> Add Client
    </button>
  </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-md-6">
    <div class="dashboard-card">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
          <div class="dashboard-icon bg-primary">
            <i class="fas fa-users"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number"><?= count($clients) ?></div>
          <div class="dashboard-label">Total Clients</div>
          <div class="dashboard-growth positive">
            <i class="fas fa-arrow-up"></i> +12% This month
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="dashboard-card">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
          <div class="dashboard-icon bg-success">
            <i class="fas fa-user-check"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number"><?= count(array_filter($clients, fn($c) => $c['is_active'])) ?></div>
          <div class="dashboard-label">Active Clients</div>
          <div class="dashboard-growth positive">
            <i class="fas fa-arrow-up"></i> +8% This month
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="dashboard-card">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
          <div class="dashboard-icon bg-info">
            <i class="fas fa-calendar-plus"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number">5</div>
          <div class="dashboard-label">New This Week</div>
          <div class="dashboard-growth positive">
            <i class="fas fa-arrow-up"></i> +25% vs last week
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="dashboard-card">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
          <div class="dashboard-icon bg-warning">
            <i class="fas fa-star"></i>
          </div>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="dashboard-number">4.8</div>
          <div class="dashboard-label">Avg. Rating</div>
          <div class="dashboard-growth positive">
            <i class="fas fa-arrow-up"></i> +0.2 This month
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Clients Table -->
<div class="dashboard-card">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="card-title mb-0">All Clients</h6>
    <div class="d-flex gap-2">
      <div class="input-group" style="width: 250px;">
        <input type="text" class="form-control" placeholder="Search clients...">
        <button class="btn btn-outline-secondary">
          <i class="fas fa-search"></i>
        </button>
      </div>
      <button class="btn btn-outline-secondary">
        <i class="fas fa-filter"></i>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Client</th>
          <th>Contact Info</th>
          <th>Status</th>
          <th>Joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$clients): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No clients found</td></tr>
        <?php else: foreach ($clients as $client): ?>
          <tr id="client-row-<?= (int)$client['id'] ?>">
            <td>
              <span class="fw-medium">
            </td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar-sm me-2" style="background: <?= getAvatarColor($client['full_name']) ?>">
                  <?= strtoupper(substr($client['full_name'], 0, 2)) ?>
                </div>
                <div>
                  <div class="fw-medium"><?= htmlspecialchars($client['full_name']) ?></div>
                  <small class="text-muted">Customer</small>
                </div>
              </div>
            </td>
            <td>
              <div>
                <div class="fw-medium"><?= htmlspecialchars($client['email']) ?></div>
                <small class="text-muted"><?= htmlspecialchars($client['phone']) ?></small>
              </div>
            </td>
            <td>
              <span class="badge <?= $client['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                <?= $client['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td>
              <span class="text-muted"><?= date('d/m/Y', strtotime($client['created_at'] ?? 'now')) ?></span>
            </td>
            <td>
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary" onclick="editClient(<?= (int)$client['id'] ?>, '<?= htmlspecialchars($client['full_name']) ?>', '<?= htmlspecialchars($client['email']) ?>', '<?= htmlspecialchars($client['phone']) ?>')">
                  <i class="fas fa-edit"></i>
                </button>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this client?');">
                  <input type="hidden" name="action" value="delete_client">
                  <input type="hidden" name="id" value="<?= (int)$client['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <input type="hidden" name="action" value="create_client">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input name="nom" class="form-control" placeholder="Enter full name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" placeholder="Enter email address">
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input name="telephone" class="form-control" placeholder="Enter phone number">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Client</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <input type="hidden" name="action" value="update_client">
          <input type="hidden" name="id" id="edit-client-id">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input name="nom" id="edit-client-name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" id="edit-client-email" type="email" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input name="telephone" id="edit-client-phone" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editClient(id, name, email, phone) {
  document.getElementById('edit-client-id').value = id;
  document.getElementById('edit-client-name').value = name;
  document.getElementById('edit-client-email').value = email;
  document.getElementById('edit-client-phone').value = phone;
  new bootstrap.Modal(document.getElementById('editClientModal')).show();
}
</script>