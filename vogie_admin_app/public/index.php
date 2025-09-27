<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require __DIR__ . '/db.php';

$__bootstrap_done = false;

try {

  $pdo->exec("CREATE TABLE IF NOT EXISTS villes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $pdo->exec("CREATE TABLE IF NOT EXISTS trajets (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ville_depart_id INT UNSIGNED NOT NULL,
    ville_arrivee_id INT UNSIGNED NOT NULL,
    heure_depart TIME NOT NULL,
    heure_arrivee TIME NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

  $pdo->exec("CREATE TABLE IF NOT EXISTS horaires (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    trajet_id INT UNSIGNED NOT NULL,
    jour_semaine ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
    heure_depart TIME NOT NULL,
    heure_arrivee TIME NOT NULL,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    telephone VARCHAR(50) NULL,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $pdo->exec("CREATE TABLE IF NOT EXISTS paiements (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED NOT NULL,
    trajet_id INT UNSIGNED NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'en_attente',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_confirmation DATETIME NULL,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

  $villesCount = (int)$pdo->query('SELECT COUNT(*) FROM villes')->fetchColumn();
  $hasCities = (bool)($pdo->query("SHOW TABLES LIKE 'cities'")->fetchColumn());
  if ($villesCount === 0 && $hasCities) {
    $pdo->exec("INSERT IGNORE INTO villes(id, nom) SELECT id, name FROM cities");
  }

  $trajetsCount = (int)$pdo->query('SELECT COUNT(*) FROM trajets')->fetchColumn();
  $hasRoutes = (bool)($pdo->query("SHOW TABLES LIKE 'routes'")->fetchColumn());
  if ($trajetsCount === 0 && $hasRoutes) {
    $pdo->exec("INSERT IGNORE INTO trajets(id, ville_depart_id, ville_arrivee_id, heure_depart, heure_arrivee, prix)
                SELECT r.id, r.from_city_id, r.to_city_id, '08:00:00', '12:00:00', r.price_per_person FROM routes r");
  }
  $__bootstrap_done = true;
} catch (Throwable $e) {

}

try {
  $hasUsers = (bool)($pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn());
  if ($hasUsers) {
    $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    if ($adminCount === 0) {
      $email = 'admin@gestion-transport.local';
      $hash = password_hash('admin123', PASSWORD_BCRYPT);
      $stmt = $pdo->prepare("INSERT INTO users(full_name,email,phone,password,role,is_active) VALUES(?,?,?,?, 'admin', 1)");
      $stmt->execute(['Administrateur', $email, '', $hash]);
    }
  }
} catch (Throwable $e) {

}

$page = $_GET['page'] ?? 'dashboard';

function view($name, $vars = []) {
  global $pdo;
  extract($vars);
  include __DIR__ . '/partials/header.php';
  include __DIR__ . "/views/$name.php";
  include __DIR__ . '/partials/footer.php';
}

$viewsDir = __DIR__ . '/views';
if (!is_dir($viewsDir)) { mkdir($viewsDir, 0777, true); }

$defaults = [
  'dashboard' => '<h3>Tableau de bord</h3><?php include __DIR__ . "/../widgets/dashboard_kpis.php"; ?>',
  'villes' => '<h3>Villes du Maroc</h3><?php include __DIR__ . "/../widgets/villes_crud.php"; ?>',
  'trajets' => '<h3>Trajets</h3><?php include __DIR__ . "/../widgets/trajets_crud.php"; ?>',
  'clients' => '<h3>Clients</h3><?php include __DIR__ . "/../widgets/clients_crud.php"; ?>',
  'paiements' => '<h3>Paiements</h3><?php include __DIR__ . "/../widgets/paiements_crud.php"; ?>',
  'horaires' => '<h3>Horaires</h3><?php include __DIR__ . "/../widgets/horaires_crud.php"; ?>',
  'controle' => '<h3>Contrôle</h3><?php include __DIR__ . "/../widgets/controle_panel.php"; ?>',
];

foreach ($defaults as $k=>$html) {
  $f = "$viewsDir/$k.php";
  if (!file_exists($f)) file_put_contents($f, "<?php ?>\n" . $html);
}

$widgetsDir = __DIR__ . '/widgets';
if (!is_dir($widgetsDir)) { mkdir($widgetsDir, 0777, true); }

function ensure_widget($name, $content) {
  $file = __DIR__ . "/widgets/$name.php";
  if (!file_exists($file)) file_put_contents($file, $content);
}

ensure_widget('dashboard_kpis', <<<'PHP'
<?php
try {
  $villesCount = (int)$pdo->query('SELECT COUNT(*) FROM villes')->fetchColumn();
} catch (Throwable $e) { $villesCount = 0; }
try {
  $trajetsCount = (int)$pdo->query('SELECT COUNT(*) FROM trajets')->fetchColumn();
} catch (Throwable $e) { $trajetsCount = 0; }
try {
  $clientsCount = (int)$pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
} catch (Throwable $e) { $clientsCount = 0; }
try {
  $paiementsCount = (int)$pdo->query('SELECT COUNT(*) FROM paiements')->fetchColumn();
} catch (Throwable $e) { $paiementsCount = 0; }
try {
  $totalEncaisse = (float)$pdo->query("SELECT COALESCE(SUM(montant),0) FROM paiements WHERE statut='confirme'")->fetchColumn();
} catch (Throwable $e) { $totalEncaisse = 0; }
?>
<div class="row g-3">
  <div class="col-md-3"><div class="card card-kpi"><div class="card-body"><h6>Villes</h6><div class="fs-3"><?= $villesCount ?></div></div></div></div>
  <div class="col-md-3"><div class="card card-kpi"><div class="card-body"><h6>Trajets</h6><div class="fs-3"><?= $trajetsCount ?></div></div></div></div>
  <div class="col-md-3"><div class="card card-kpi"><div class="card-body"><h6>Clients</h6><div class="fs-3"><?= $clientsCount ?></div></div></div></div>
  <div class="col-md-3"><div class="card card-kpi"><div class="card-body"><h6>Total encaissé</h6><div class="fs-3"><span class="me-1">MAD</span><?= number_format($totalEncaisse, 2, ',', ' ') ?></div></div></div></div>
</div>
<?php
try {
  $rows = $pdo->query("SELECT p.id, c.nom as client, p.montant, p.statut, p.date_creation FROM paiements p JOIN clients c ON c.id=p.client_id ORDER BY p.date_creation DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $rows = []; }
?>
<div class="card mt-4">
  <div class="card-header">Derniers paiements</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead><tr><th>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="5" class="text-center text-muted">Aucun paiement</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['client']) ?></td>
            <td><?= number_format((float)$r['montant'], 2, ',', ' ') ?></td>
            <td><span class="badge <?= $r['statut']==='confirme'?'bg-success':'bg-warning text-dark' ?>"><?= htmlspecialchars($r['statut']) ?></span></td>
            <td><?= htmlspecialchars($r['date_creation']) ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
PHP);

ensure_widget('horaires_crud', <<<'PHP'
<?php

$trajets = [];
try {
  $trajets = $pdo->query('SELECT t.id, vd.nom as depart, va.nom as arrivee FROM trajets t JOIN villes vd ON vd.id=t.ville_depart_id JOIN villes va ON va.id=t.ville_arrivee_id ORDER BY t.id DESC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $trajets = []; }

if (($_POST['action'] ?? '') === 'create_horaire') {
  $trajet_id = (int)($_POST['trajet_id'] ?? 0);
  $jour = trim($_POST['jour_semaine'] ?? '');
  $hd = trim($_POST['heure_depart'] ?? '');
  $ha = trim($_POST['heure_arrivee'] ?? '');
  $actif = isset($_POST['actif']) ? 1 : 0;
  if ($trajet_id && $jour && $hd && $ha) {
    $stmt = $pdo->prepare('INSERT INTO horaires(trajet_id, jour_semaine, heure_depart, heure_arrivee, actif) VALUES(?,?,?,?,?)');
    $stmt->execute([$trajet_id, $jour, $hd, $ha, $actif]);
  }
}

if (($_POST['action'] ?? '') === 'update_horaire') {
  $id = (int)($_POST['id'] ?? 0);
  $trajet_id = (int)($_POST['trajet_id'] ?? 0);
  $jour = trim($_POST['jour_semaine'] ?? '');
  $hd = trim($_POST['heure_depart'] ?? '');
  $ha = trim($_POST['heure_arrivee'] ?? '');
  $actif = isset($_POST['actif']) ? 1 : 0;
  if ($id && $trajet_id && $jour && $hd && $ha) {
    $stmt = $pdo->prepare('UPDATE horaires SET trajet_id=?, jour_semaine=?, heure_depart=?, heure_arrivee=?, actif=? WHERE id=?');
    $stmt->execute([$trajet_id, $jour, $hd, $ha, $actif, $id]);
  }
}

if (($_POST['action'] ?? '') === 'delete_horaire') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) $pdo->prepare('DELETE FROM horaires WHERE id=?')->execute([$id]);
}

$rows = [];
try {
  $rows = $pdo->query("SELECT h.*,
                              CONCAT(vd.nom, ' → ', va.nom) AS trajet,
                              t.heure_depart AS t_hd,
                              t.heure_arrivee AS t_ha
                       FROM horaires h
                       JOIN trajets t ON t.id=h.trajet_id
                       JOIN villes vd ON vd.id=t.ville_depart_id
                       JOIN villes va ON va.id=t.ville_arrivee_id
                       ORDER BY FIELD(jour_semaine,'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'), h.heure_depart")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $rows = []; }
?>

<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="action" value="create_horaire">
  <div class="col-md-3">
    <select name="trajet_id" class="form-select" required>
      <option value="">Trajet</option>
      <?php foreach ($trajets as $t): ?>
        <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['depart']) ?> → <?= htmlspecialchars($t['arrivee']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <select name="jour_semaine" class="form-select" required>
      <?php foreach (['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'] as $j): ?>
        <option value="<?= $j ?>"><?= $j ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><input type="time" name="heure_depart" class="form-control" required></div>
  <div class="col-md-2"><input type="time" name="heure_arrivee" class="form-control" required></div>
  <div class="col-md-2 d-flex align-items-center gap-2">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="actif" id="actif_create" checked>
      <label class="form-check-label" for="actif_create">Actif</label>
    </div>
  </div>
  <div class="col-md-1"><button class="btn btn-primary w-100">Ajouter</button></div>
  </form>

<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead>
    <tr>
      <th>
      <th>Trajet</th>
      <th>Jour</th>
      <th>Départ</th>
      <th>Arrivée</th>
      <th>Heures du trajet</th>
      <th>Durée</th>
      <th>Actif</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars($r['trajet']) ?></td>
        <td><?= htmlspecialchars($r['jour_semaine']) ?></td>
        <td><?= htmlspecialchars($r['heure_depart']) ?></td>
        <td><?= htmlspecialchars($r['heure_arrivee']) ?></td>
        <td>
          <span class="text-muted">
            <?= htmlspecialchars($r['t_hd'] ?? '-') ?> → <?= htmlspecialchars($r['t_ha'] ?? '-') ?>
          </span>
        </td>
        <td>
          <?php

            try {
              $d1 = new DateTime($r['t_hd'] ?? $r['heure_depart'] ?? '');
              $d2 = new DateTime($r['t_ha'] ?? $r['heure_arrivee'] ?? '');
              if ($d2 < $d1) { $d2->modify('+1 day'); }
              $diff = $d1->diff($d2);
              $hours = (int)$diff->h + ($diff->d * 24);
              $mins = (int)$diff->i;
              echo sprintf('%02dh%02dm', $hours, $mins);
            } catch (Throwable $e) { echo '-'; }
          ?>
        </td>
        <td>
          <span class="badge <?= ((int)$r['actif']===1)?'bg-success':'bg-secondary' ?>"><?= ((int)$r['actif']===1)?'Oui':'Non' ?></span>
        </td>
        <td class="text-nowrap">
          <!-- Edit inline form -->
          <form method="post" class="d-inline-block">
            <input type="hidden" name="action" value="update_horaire">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <select name="trajet_id" class="form-select form-select-sm d-inline-block" style="width:180px">
              <?php foreach ($trajets as $t): $sel = ((int)$t['id'] === (int)$r['trajet_id'])?'selected':''; ?>
                <option <?= $sel ?> value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['depart']) ?> → <?= htmlspecialchars($t['arrivee']) ?></option>
              <?php endforeach; ?>
            </select>
            <select name="jour_semaine" class="form-select form-select-sm d-inline-block" style="width:130px">
              <?php foreach (['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'] as $j): $sel = ($j===$r['jour_semaine'])?'selected':''; ?>
                <option <?= $sel ?> value="<?= $j ?>"><?= $j ?></option>
              <?php endforeach; ?>
            </select>
            <input type="time" name="heure_depart" value="<?= htmlspecialchars($r['heure_depart']) ?>" class="form-control form-control-sm d-inline-block" style="width:120px">
            <input type="time" name="heure_arrivee" value="<?= htmlspecialchars($r['heure_arrivee']) ?>" class="form-control form-control-sm d-inline-block" style="width:120px">
            <div class="form-check d-inline-block ms-2">
              <input class="form-check-input" type="checkbox" name="actif" id="actif_<?= (int)$r['id'] ?>" <?= ((int)$r['actif']===1)?'checked':'' ?>>
              <label class="form-check-label" for="actif_<?= (int)$r['id'] ?>">Actif</label>
            </div>
            <button class="btn btn-sm btn-success ms-2">Enregistrer</button>
          </form>
          <!-- Delete -->
          <form method="post" class="d-inline" onsubmit="return confirm('Supprimer cet horaire ?');">
            <input type="hidden" name="action" value="delete_horaire">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
PHP);

ensure_widget('controle_panel', <<<'PHP'
<?php

if (($_POST['action'] ?? '') === 'cleanup_empty_clients') {
  $pdo->exec("DELETE FROM clients WHERE (email IS NULL OR email='') AND (telephone IS NULL OR telephone='')");
}
if (($_POST['action'] ?? '') === 'confirm_payment') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) $pdo->prepare("UPDATE paiements SET statut='confirme', date_confirmation=CURRENT_TIMESTAMP WHERE id=?")->execute([$id]);
}
if (($_POST['action'] ?? '') === 'delete_payment') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) $pdo->prepare('DELETE FROM paiements WHERE id=?')->execute([$id]);
}

$recent = [];
try {
  $recent = $pdo->query("SELECT p.id, c.nom as client, p.montant, p.statut, p.date_creation
                         FROM paiements p JOIN clients c ON c.id=p.client_id
                         ORDER BY p.date_creation DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $recent = []; }
?>

<div class="row g-3">
  <div class="col-md-4">
    <div class="dashboard-card">
      <h6 class="card-title mb-3"><i class="fas fa-clock me-2"></i>Accès rapide</h6>
      <div class="d-grid gap-2">
        <a class="btn btn-outline-secondary" href="/Vogie2/public/index.php?page=trajets"><i class="fas fa-route me-2"></i>Gérer les trajets</a>
        <a class="btn btn-outline-secondary" href="/Vogie2/public/index.php?page=paiements"><i class="fas fa-receipt me-2"></i>Gérer les paiements</a>
      </div>
    </div>
    <div class="dashboard-card mt-3">
      <h6 class="card-title mb-3"><i class="fas fa-broom me-2"></i>Maintenance</h6>
      <form method="post">
        <input type="hidden" name="action" value="cleanup_empty_clients">
        <button class="btn btn-outline-danger"><i class="fas fa-user-slash me-2"></i>Supprimer clients vides</button>
      </form>
    </div>
  </div>
  <div class="col-md-8">
    <div class="dashboard-card">
      <h6 class="card-title mb-3">Paiements récents</h6>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead><tr><th>
          <tbody>
            <?php if (!$recent): ?>
              <tr><td colspan="6" class="text-center text-muted">Aucun paiement</td></tr>
            <?php else: foreach ($recent as $r): ?>
              <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= htmlspecialchars($r['client']) ?></td>
                <td>MAD <?= number_format((float)$r['montant'], 2, ',', ' ') ?></td>
                <td><span class="badge <?= $r['statut']==='confirme'?'bg-success':'bg-warning text-dark' ?>"><?= htmlspecialchars($r['statut']) ?></span></td>
                <td><?= htmlspecialchars($r['date_creation']) ?></td>
                <td class="text-nowrap">
                  <?php if ($r['statut']!=='confirme'): ?>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="confirm_payment">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-success">Confirmer</button>
                  </form>
                  <?php endif; ?>
                  <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce paiement ?');">
                    <input type="hidden" name="action" value="delete_payment">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
PHP);

ensure_widget('villes_crud', <<<'PHP'
<?php

if (($_POST['action'] ?? '') === 'create_ville') {
  $nom = trim($_POST['nom'] ?? '');
  if ($nom !== '') {
    $stmt = $pdo->prepare('INSERT IGNORE INTO villes(nom) VALUES(:nom)');
    $stmt->execute([':nom'=>$nom]);
  }
}

if (($_POST['action'] ?? '') === 'delete_ville') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) $pdo->prepare('DELETE FROM villes WHERE id=?')->execute([$id]);
}
$villes = $pdo->query('SELECT * FROM villes ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="action" value="create_ville">
  <div class="col-auto"><input name="nom" class="form-control" placeholder="Ajouter une ville"></div>
  <div class="col-auto"><button class="btn btn-primary">Ajouter</button></div>
</form>
<div class="table-responsive">
<table class="table table-sm">
  <thead><tr><th>
  <tbody>
    <?php foreach ($villes as $v): ?>
      <tr>
        <td><?= (int)$v['id'] ?></td>
        <td><?= htmlspecialchars($v['nom']) ?></td>
        <td>
          <form method="post" class="d-inline" onsubmit="return confirm('Supprimer cette ville ?');">
            <input type="hidden" name="action" value="delete_ville">
            <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
PHP);

ensure_widget('trajets_crud', <<<'PHP'
<?php
$villes = $pdo->query('SELECT * FROM villes ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);

if (($_POST['action'] ?? '') === 'create_trajet') {
  $vd = (int)($_POST['ville_depart_id'] ?? 0);
  $va = (int)($_POST['ville_arrivee_id'] ?? 0);
  $hd = trim($_POST['heure_depart'] ?? '');
  $ha = trim($_POST['heure_arrivee'] ?? '');
  $prix = (float)($_POST['prix'] ?? 0);
  if ($vd && $va && $hd && $ha && $prix>0) {
    $stmt = $pdo->prepare('INSERT INTO trajets(ville_depart_id,ville_arrivee_id,heure_depart,heure_arrivee,prix) VALUES(?,?,?,?,?)');
    $stmt->execute([$vd,$va,$hd,$ha,$prix]);
  }
}

if (($_POST['action'] ?? '') === 'delete_trajet') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) $pdo->prepare('DELETE FROM trajets WHERE id=?')->execute([$id]);
}
$trajets = $pdo->query('SELECT t.*, vd.nom as depart, va.nom as arrivee FROM trajets t JOIN villes vd ON vd.id=t.ville_depart_id JOIN villes va ON va.id=t.ville_arrivee_id ORDER BY t.id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="action" value="create_trajet">
  <div class="col-md-3">
    <select name="ville_depart_id" class="form-select" required>
      <option value="">Ville de départ</option>
      <?php foreach ($villes as $v): ?><option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['nom']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <select name="ville_arrivee_id" class="form-select" required>
      <option value="">Ville d'arrivée</option>
      <?php foreach ($villes as $v): ?><option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['nom']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><input name="heure_depart" type="time" class="form-control" required></div>
  <div class="col-md-2"><input name="heure_arrivee" type="time" class="form-control" required></div>
  <div class="col-md-1"><input name="prix" type="number" min="1" step="0.5" class="form-control" placeholder="MAD" required></div>
  <div class="col-md-1"><button class="btn btn-primary w-100">Ajouter</button></div>
</form>
<div class="table-responsive">
<table class="table table-sm">
  <thead><tr><th>
  <tbody>
    <?php foreach ($trajets as $t): ?>
      <tr>
        <td><?= (int)$t['id'] ?></td>
        <td><?= htmlspecialchars($t['depart']) ?></td>
        <td><?= htmlspecialchars($t['arrivee']) ?></td>
        <td><?= htmlspecialchars($t['heure_depart']) ?></td>
        <td><?= htmlspecialchars($t['heure_arrivee']) ?></td>
        <td><?= number_format((float)$t['prix'], 2, ',', ' ') ?></td>
        <td>
          <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce trajet ?');">
            <input type="hidden" name="action" value="delete_trajet">
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
PHP);

ensure_widget('clients_crud', <<<'PHP'
<?php

if (($_POST['action'] ?? '') === 'create_client') {
  $nom = trim($_POST['nom'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $telephone = trim($_POST['telephone'] ?? '');
  if ($nom) {
    $pdo->prepare('INSERT INTO clients(nom,email,telephone) VALUES(?,?,?)')->execute([$nom,$email,$telephone]);
  }
}

if (($_POST['action'] ?? '') === 'delete_client') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) $pdo->prepare('DELETE FROM clients WHERE id=?')->execute([$id]);
}
$clients = $pdo->query('SELECT * FROM clients ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="action" value="create_client">
  <div class="col-md-3"><input name="nom" class="form-control" placeholder="Nom" required></div>
  <div class="col-md-3"><input name="email" type="email" class="form-control" placeholder="Email"></div>
  <div class="col-md-3"><input name="telephone" class="form-control" placeholder="Téléphone"></div>
  <div class="col-md-2"><button class="btn btn-primary">Ajouter</button></div>
</form>
<div class="table-responsive">
<table class="table table-sm">
  <thead><tr><th>
  <tbody>
    <?php foreach ($clients as $c): ?>
      <tr>
        <td><?= (int)$c['id'] ?></td>
        <td><?= htmlspecialchars($c['nom']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td><?= htmlspecialchars($c['telephone']) ?></td>
        <td>
          <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce client ?');">
            <input type="hidden" name="action" value="delete_client">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
PHP);

ensure_widget('paiements_crud', <<<'PHP'
<?php
$clients = $pdo->query('SELECT id, nom FROM clients ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
$trajets = $pdo->query('SELECT t.id, vd.nom as depart, va.nom as arrivee FROM trajets t JOIN villes vd ON vd.id=t.ville_depart_id JOIN villes va ON va.id=t.ville_arrivee_id ORDER BY t.id DESC')->fetchAll(PDO::FETCH_ASSOC);

if (($_POST['action'] ?? '') === 'create_paiement') {
  $client_id = (int)($_POST['client_id'] ?? 0);
  $trajet_id = (int)($_POST['trajet_id'] ?? 0);
  $montant = (float)($_POST['montant'] ?? 0);
  if ($client_id && $trajet_id && $montant>0) {
    $pdo->prepare('INSERT INTO paiements(client_id,trajet_id,montant,statut) VALUES(?,?,?,\'en_attente\')')->execute([$client_id,$trajet_id,$montant]);
  }
}

if (($_POST['action'] ?? '') === 'confirm_paiement') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) $pdo->prepare("UPDATE paiements SET statut='confirme', date_confirmation=CURRENT_TIMESTAMP WHERE id=?")->execute([$id]);
}

if (($_POST['action'] ?? '') === 'delete_paiement') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) $pdo->prepare('DELETE FROM paiements WHERE id=?')->execute([$id]);
}
$rows = $pdo->query("SELECT p.*, c.nom as client, vd.nom as depart, va.nom as arrivee FROM paiements p JOIN clients c ON c.id=p.client_id JOIN trajets t ON t.id=p.trajet_id JOIN villes vd ON vd.id=t.ville_depart_id JOIN villes va ON va.id=t.ville_arrivee_id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="action" value="create_paiement">
  <div class="col-md-3">
    <select name="client_id" class="form-select" required>
      <option value="">Client</option>
      <?php foreach ($clients as $c): ?><option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-4">
    <select name="trajet_id" class="form-select" required>
      <option value="">Trajet</option>
      <?php foreach ($trajets as $t): ?><option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['depart']) ?> → <?= htmlspecialchars($t['arrivee']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><input name="montant" type="number" step="0.5" min="1" class="form-control" placeholder="Montant (MAD)" required></div>
  <div class="col-md-2"><button class="btn btn-primary">Ajouter</button></div>
</form>
<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead><tr><th>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars($r['client']) ?></td>
        <td><?= htmlspecialchars($r['depart']) ?> → <?= htmlspecialchars($r['arrivee']) ?></td>
        <td><?= number_format((float)$r['montant'], 2, ',', ' ') ?></td>
        <td><span class="badge <?= $r['statut']==='confirme'?'bg-success':'bg-warning text-dark' ?>"><?= htmlspecialchars($r['statut']) ?></span></td>
        <td><?= htmlspecialchars($r['date_creation']) ?></td>
        <td><?= htmlspecialchars($r['date_confirmation'] ?? '') ?></td>
        <td class="text-nowrap">
          <?php if ($r['statut'] !== 'confirme'): ?>
          <form method="post" class="d-inline">
            <input type="hidden" name="action" value="confirm_paiement">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-success">Confirmer</button>
          </form>
          <?php endif; ?>
          <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce paiement ?');">
            <input type="hidden" name="action" value="delete_paiement">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
PHP);

$allowed = ['dashboard','villes','trajets','clients','paiements','horaires','controle'];
if (!in_array($page, $allowed, true)) $page = 'dashboard';
view($page);