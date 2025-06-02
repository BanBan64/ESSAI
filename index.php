<?php
/**
 * Page d'accueil Protection Civile 64 - v2
 * Tableau de bord principal avec statistiques et accès rapide
 * VERSION CORRIGÉE - index2.php avec sidebar fixée SEULEMENT
 */

// Définir la constante de sécurité
define('PROTEC64_V2', true);

// Inclure les fichiers de base
require_once 'shared/includes/config.php';
require_once 'shared/includes/db.php';
require_once 'shared/includes/auth.php';
require_once 'shared/includes/utils.php';

// Vérifier l'authentification
require_login();

// Variables pour la page
$page_title = 'Tableau de bord';
$current_section = 'dashboard';

// Récupérer l'utilisateur actuel avec toutes ses données
$current_user = get_current_user_data();

// CORRECTION : Vérifier si $current_user est bien un tableau
if (!is_array($current_user) || empty($current_user)) {
    error_log("ERREUR Dashboard: current_user invalide - " . print_r($current_user, true));
    
    // Essayer de récupérer depuis la session
    $current_user = get_current_user();
    
    if (!is_array($current_user) || empty($current_user)) {
        // Créer un utilisateur minimal pour éviter les erreurs fatales
        $current_user = [
            'id' => 0,
            'nom' => 'Utilisateur',
            'prenom' => 'Inconnu',
            'role' => 'guest',
            'antenne_id' => null,
            'antenne_nom' => 'Aucune antenne',
            'email' => '',
            'actif' => 1
        ];
        
        // Logger le problème pour debug
        log_action("Utilisateur invalide dans dashboard - session corrompue ?", 'error');
    }
}

// S'assurer que toutes les clés nécessaires existent
$required_keys = ['id', 'nom', 'prenom', 'role', 'antenne_id'];
foreach ($required_keys as $key) {
    if (!isset($current_user[$key])) {
        $current_user[$key] = '';
    }
}

// Récupérer les données complètes depuis la BDD si on a un ID valide
if ($current_user['id'] > 0) {
    try {
        $db = db_connect();
        $user_query = $db->prepare("
            SELECT u.*, a.nom as antenne_nom 
            FROM " . DB_PREFIX . "utilisateurs u 
            LEFT JOIN " . DB_PREFIX . "antennes a ON u.antenne_id = a.id 
            WHERE u.id = ?
        ");
        $user_query->execute([$current_user['id']]);
        $user_full = $user_query->fetch(PDO::FETCH_ASSOC);
        
        if ($user_full && is_array($user_full)) {
            // Fusionner les données de session avec les données BDD
            $current_user = array_merge($current_user, $user_full);
        }
    } catch (Exception $e) {
        log_action("Erreur récupération utilisateur complet: " . $e->getMessage(), 'error');
    }
}

// Initialiser les statistiques
$stats = [
    'vehicules_total' => 0,
    'vehicules_disponibles' => 0,
    'vehicules_maintenance' => 0,
    'sorties_today' => 0,
    'sorties_en_cours' => 0,
    'sorties_ce_mois' => 0,
    'utilisateurs_total' => 0,
    'utilisateurs_actifs' => 0,
    'utilisateurs_mon_antenne' => 0
];

// Récupérer les statistiques
try {
    $db = db_connect();
    
    // Statistiques véhicules
    $vehicules_stats = $db->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN statut = 'disponible' THEN 1 END) as disponibles,
            COUNT(CASE WHEN statut = 'en_maintenance' THEN 1 END) as maintenance
        FROM " . DB_PREFIX . "vehicules
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($vehicules_stats) {
        $stats['vehicules_total'] = (int)$vehicules_stats['total'];
        $stats['vehicules_disponibles'] = (int)$vehicules_stats['disponibles'];
        $stats['vehicules_maintenance'] = (int)$vehicules_stats['maintenance'];
    }
    
    // Statistiques sorties
    $sorties_stats = $db->query("
        SELECT 
            COUNT(CASE WHEN DATE(date_sortie) = CURDATE() THEN 1 END) as today,
            COUNT(CASE WHEN statut = 'en_cours' THEN 1 END) as en_cours,
            COUNT(CASE WHEN MONTH(date_sortie) = MONTH(CURDATE()) AND YEAR(date_sortie) = YEAR(CURDATE()) THEN 1 END) as ce_mois
        FROM " . DB_PREFIX . "sorties
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($sorties_stats) {
        $stats['sorties_today'] = (int)$sorties_stats['today'];
        $stats['sorties_en_cours'] = (int)$sorties_stats['en_cours'];
        $stats['sorties_ce_mois'] = (int)$sorties_stats['ce_mois'];
    }
    
    // Statistiques utilisateurs (si admin ou responsable)
    if (is_admin() || is_admin_or_responsable()) {
        $users_stats = $db->query("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN actif = 1 THEN 1 END) as actifs
            FROM " . DB_PREFIX . "utilisateurs
        ")->fetch(PDO::FETCH_ASSOC);
        
        if ($users_stats) {
            $stats['utilisateurs_total'] = (int)$users_stats['total'];
            $stats['utilisateurs_actifs'] = (int)$users_stats['actifs'];
        }
        
        // Utilisateurs de mon antenne
        if (isset($current_user['antenne_id']) && $current_user['antenne_id']) {
            $antenne_stats = $db->prepare("SELECT COUNT(*) as count FROM " . DB_PREFIX . "utilisateurs WHERE antenne_id = ? AND actif = 1");
            $antenne_stats->execute([$current_user['antenne_id']]);
            $antenne_result = $antenne_stats->fetch(PDO::FETCH_ASSOC);
            if ($antenne_result) {
                $stats['utilisateurs_mon_antenne'] = (int)$antenne_result['count'];
            }
        }
    }
    
} catch (Exception $e) {
    log_action("Erreur lors de la récupération des statistiques: " . $e->getMessage(), 'error');
}

// Récupérer les dernières sorties de l'utilisateur
$dernieres_sorties = [];
if ($current_user['id'] > 0) {
    try {
        $sorties_query = $db->prepare("
            SELECT s.*, v.identifiant as vehicule_nom, v.type_vehicule
            FROM " . DB_PREFIX . "sorties s
            JOIN " . DB_PREFIX . "vehicules v ON s.vehicule_id = v.id
            WHERE s.conducteur_id = ?
            ORDER BY s.date_sortie DESC
            LIMIT 5
        ");
        $sorties_query->execute([$current_user['id']]);
        $dernieres_sorties = $sorties_query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        log_action("Erreur lors de la récupération des dernières sorties: " . $e->getMessage(), 'error');
    }
}

// Alertes importantes
$alertes = [];

// Vérifier les véhicules en maintenance
if ($stats['vehicules_maintenance'] > 0) {
    $alertes[] = [
        'type' => 'warning',
        'message' => $stats['vehicules_maintenance'] . ' véhicule(s) en maintenance',
        'icon' => 'tools'
    ];
}

// Vérifier les sorties longues (plus de 24h)
try {
    $sorties_longues = $db->query("
        SELECT COUNT(*) as count 
        FROM " . DB_PREFIX . "sorties 
        WHERE statut = 'en_cours' 
        AND TIMESTAMPDIFF(HOUR, date_sortie, NOW()) > 24
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($sorties_longues && $sorties_longues['count'] > 0) {
        $alertes[] = [
            'type' => 'danger',
            'message' => $sorties_longues['count'] . ' sortie(s) de plus de 24h sans retour',
            'icon' => 'exclamation-triangle'
        ];
    }
} catch (Exception $e) {
    // Ignorer cette erreur, ce n'est pas critique
}

// Inclure le header
$GLOBALS['current_user'] = $current_user; // Rendre accessible aux templates
include SHARED_PATH . '/templates/header.php';
?>

<body>
    <?php include SHARED_PATH . '/templates/navigation.php'; ?>
    
    <div class="d-flex">
        <!-- SIDEBAR CORRIGÉE -->
        <nav class="sidebar bg-primary text-white d-none d-lg-block" style="width: 250px; min-height: calc(100vh - 76px);">
            <div class="p-3">
                <!-- Profil utilisateur -->
                <div class="d-flex align-items-center mb-4 p-3 bg-white bg-opacity-10 rounded">
                    <div class="me-3">
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px;">
                            <span class="fw-bold text-white">
                                <?= strtoupper(substr($current_user['prenom'] ?? 'U', 0, 1)) ?>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-white">
                            <?= h(($current_user['prenom'] ?? '') . ' ' . ($current_user['nom'] ?? '')) ?>
                        </div>
                        <small class="text-white-50">
                            <?= h($current_user['role'] ?? 'Utilisateur') ?>
                        </small>
                    </div>
                </div>
                
                <!-- Navigation -->
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item mb-1">
                        <a href="<?= BASE_URL ?>/index.php" 
                           class="nav-link text-white d-flex align-items-center active">
                            <i class="bi bi-speedometer2 me-2"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    
                    <?php if (is_admin() || is_admin_or_responsable()): ?>
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center" 
                           data-bs-toggle="collapse" 
                           href="#submenu-admin" 
                           role="button">
                            <i class="bi bi-gear me-2"></i>
                            <span class="flex-grow-1">Administration</span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        
                        <div class="collapse" id="submenu-admin">
                            <ul class="nav nav-pills flex-column ms-3 mt-2">
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>/admin/roles.php" 
                                       class="nav-link text-white-50 py-1">
                                        <i class="bi bi-shield-check me-2"></i>
                                        Gestion des rôles
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>/admin/utilisateurs.php" 
                                       class="nav-link text-white-50 py-1">
                                        <i class="bi bi-people me-2"></i>
                                        Utilisateurs
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Actions rapides -->
                <hr class="text-white-50">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/profil.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-person me-1"></i>
                        Mon profil
                    </a>
                    <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Déconnexion
                    </a>
                </div>
                
                <!-- Version -->
                <div class="mt-4 text-center">
                    <small class="text-white-50">
                        v2.0 • En ligne
                    </small>
                </div>
            </div>
        </nav>
        
        <!-- SIDEBAR MOBILE -->
        <nav class="sidebar-mobile bg-primary text-white d-lg-none position-fixed start-0 top-0 h-100" 
             style="width: 250px; z-index: 1050; transform: translateX(-100%); transition: transform 0.3s ease;">
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-white mb-0">Menu</h5>
                    <button class="btn btn-sm btn-outline-light" onclick="closeSidebar()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                
                <!-- Même contenu que la sidebar desktop -->
                <div class="d-flex align-items-center mb-4 p-3 bg-white bg-opacity-10 rounded">
                    <div class="me-3">
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px;">
                            <span class="fw-bold text-white">
                                <?= strtoupper(substr($current_user['prenom'] ?? 'U', 0, 1)) ?>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-white">
                            <?= h(($current_user['prenom'] ?? '') . ' ' . ($current_user['nom'] ?? '')) ?>
                        </div>
                        <small class="text-white-50">
                            <?= h($current_user['role'] ?? 'Utilisateur') ?>
                        </small>
                    </div>
                </div>
                
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item mb-1">
                        <a href="<?= BASE_URL ?>/index.php" 
                           class="nav-link text-white d-flex align-items-center active">
                            <i class="bi bi-speedometer2 me-2"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    
                    <?php if (is_admin() || is_admin_or_responsable()): ?>
                    <li class="nav-item mb-1">
                        <a href="<?= BASE_URL ?>/admin/roles.php" 
                           class="nav-link text-white d-flex align-items-center">
                            <i class="bi bi-shield-check me-2"></i>
                            <span>Gestion des rôles</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= BASE_URL ?>/admin/utilisateurs.php" 
                           class="nav-link text-white d-flex align-items-center">
                            <i class="bi bi-people me-2"></i>
                            <span>Utilisateurs</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <hr class="text-white-50">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/profil.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-person me-1"></i>
                        Mon profil
                    </a>
                    <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Déconnexion
                    </a>
                </div>
            </div>
        </nav>
        
        <main class="flex-grow-1 p-4" style="margin-left: 0;">
            <div class="container-fluid">
                
                <!-- En-tête avec salutation -->
                <div class="row mb-4">
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="mb-2 mb-md-0">
                                <h1 class="h2 mb-1">
                                    <i class="bi bi-speedometer2 text-primary me-2"></i>
                                    Bonjour <?php echo h($current_user['prenom'] ?? 'Utilisateur'); ?> !
                                </h1>
                                <p class="text-muted mb-0">
                                    <?php echo ucfirst($current_user['role'] ?? 'Utilisateur'); ?> - 
                                    <?php echo h($current_user['antenne_nom'] ?? 'Aucune antenne'); ?> • 
                                    <?php echo format_datetime(date('Y-m-d H:i:s')); ?>
                                </p>
                            </div>
                            
                            <!-- Actions rapides -->
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if (is_admin() || is_admin_or_responsable()): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/utilisateurs.php" class="btn btn-outline-primary">
                                    <i class="bi bi-person-plus me-1"></i>
                                    Nouvel utilisateur
                                </a>
                                <a href="<?php echo BASE_URL; ?>/admin/roles.php" class="btn btn-primary">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Gestion des rôles
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertes importantes -->
                <?php if (!empty($alertes)): ?>
                <div class="row mb-4">
                    <div class="col">
                        <?php foreach ($alertes as $alerte): ?>
                        <div class="alert alert-<?php echo $alerte['type']; ?> alert-dismissible fade show" role="alert">
                            <i class="bi bi-<?php echo $alerte['icon']; ?> me-2"></i>
                            <?php echo h($alerte['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Statistiques principales -->
                <div class="row mb-4 g-3">
                    <!-- Véhicules -->
                    <div class="col-md-4">
                        <div class="card border-0 bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h3 class="mb-0"><?php echo number_format($stats['vehicules_disponibles']); ?></h3>
                                        <p class="mb-0">Véhicules disponibles</p>
                                        <small class="opacity-75">Total: <?php echo $stats['vehicules_total']; ?> véhicules</small>
                                    </div>
                                    <div class="opacity-75 flex-shrink-0">
                                        <i class="bi bi-truck" style="font-size: 2.5rem;"></i>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <span class="btn btn-light btn-sm disabled">
                                        <i class="bi bi-clock me-1"></i>
                                        Module en développement
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sorties -->
                    <div class="col-md-4">
                        <div class="card border-0 bg-warning text-dark h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h3 class="mb-0"><?php echo number_format($stats['sorties_today']); ?></h3>
                                        <p class="mb-0">Sorties aujourd'hui</p>
                                        <small class="opacity-75">En cours: <?php echo $stats['sorties_en_cours']; ?></small>
                                    </div>
                                    <div class="opacity-75 flex-shrink-0">
                                        <i class="bi bi-calendar-check" style="font-size: 2.5rem;"></i>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <span class="btn btn-dark btn-sm disabled">
                                        <i class="bi bi-clock me-1"></i>
                                        Module en développement
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Utilisateurs (si admin/responsable) -->
                    <?php if (is_admin() || is_admin_or_responsable()): ?>
                    <div class="col-md-4">
                        <div class="card border-0 bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h3 class="mb-0"><?php echo number_format($stats['utilisateurs_actifs']); ?></h3>
                                        <p class="mb-0">Utilisateurs actifs</p>
                                        <small class="opacity-75">Mon antenne: <?php echo $stats['utilisateurs_mon_antenne']; ?></small>
                                    </div>
                                    <div class="opacity-75 flex-shrink-0">
                                        <i class="bi bi-people" style="font-size: 2.5rem;"></i>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="<?php echo BASE_URL; ?>/admin/utilisateurs.php" class="btn btn-light btn-sm">
                                        <i class="bi bi-arrow-right me-1"></i>
                                        Gérer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <!-- Mes dernières sorties -->
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Mes dernières sorties
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($dernieres_sorties)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Véhicule</th>
                                                <th>Date</th>
                                                <th>Destination</th>
                                                <th>Statut</th>
                                                <th>KM</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dernieres_sorties as $sortie): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary me-2"><?php echo h($sortie['type_vehicule'] ?? 'VPS'); ?></span>
                                                    <?php echo h($sortie['vehicule_nom'] ?? 'Véhicule'); ?>
                                                </td>
                                                <td>
                                                    <?php echo format_datetime($sortie['date_sortie']); ?>
                                                    <br><small class="text-muted"><?php echo time_ago($sortie['date_sortie']); ?></small>
                                                </td>
                                                <td><?php echo h($sortie['destination'] ?: 'Non précisée'); ?></td>
                                                <td>
                                                    <?php if ($sortie['statut'] === 'termine' || $sortie['date_retour']): ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>Terminée
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-clock me-1"></i>En cours
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($sortie['kilometrage_retour'] && $sortie['kilometrage_depart']): ?>
                                                        <?php echo number_format($sortie['kilometrage_retour'] - $sortie['kilometrage_depart']); ?> km
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <span class="btn btn-outline-primary disabled">
                                        <i class="bi bi-clock me-1"></i>
                                        Module sorties en développement
                                    </span>
                                </div>
                                
                                <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-car-front text-muted" style="font-size: 4rem;"></i>
                                    <h5 class="text-muted mt-3">Aucune sortie enregistrée</h5>
                                    <p class="text-muted">Les sorties apparaîtront ici quand le module véhicules sera développé.</p>
                                    <span class="btn btn-primary disabled">
                                        <i class="bi bi-clock me-1"></i>
                                        Module en développement
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Accès rapide aux modules -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-lightning me-2"></i>
                                    Accès rapide
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    
                                    <!-- Mon profil -->
                                    <a href="<?php echo BASE_URL; ?>/profil.php" class="btn btn-outline-primary text-start">
                                        <i class="bi bi-person me-2"></i>
                                        <strong>Mon profil</strong>
                                        <br><small class="text-muted">Informations personnelles</small>
                                    </a>
                                    
                                    <!-- Modules en développement -->
                                    <div class="btn btn-outline-secondary text-start disabled">
                                        <i class="bi bi-truck me-2"></i>
                                        <strong>Véhicules</strong>
                                        <br><small class="text-muted">Module en développement</small>
                                    </div>
                                    
                                    <div class="btn btn-outline-warning text-start disabled">
                                        <i class="bi bi-clipboard-check me-2"></i>
                                        <strong>Inventaires</strong>
                                        <br><small class="text-muted">Module en développement</small>
                                    </div>
                                    
                                    <div class="btn btn-outline-danger text-start disabled">
                                        <i class="bi bi-capsule me-2"></i>
                                        <strong>Pharmacie</strong>
                                        <br><small class="text-muted">Module en développement</small>
                                    </div>
                                    
                                    <div class="btn btn-outline-dark text-start disabled">
                                        <i class="bi bi-receipt me-2"></i>
                                        <strong>Notes de frais</strong>
                                        <br><small class="text-muted">Module en développement</small>
                                    </div>
                                    
                                </div>
                                
                                <!-- Administration (si autorisé) -->
                                <?php if (is_admin() || is_admin_or_responsable()): ?>
                                <hr>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo BASE_URL; ?>/admin/roles.php" class="btn btn-outline-success text-start">
                                        <i class="bi bi-shield-check me-2"></i>
                                        <strong>Gestion des rôles</strong>
                                        <br><small class="text-muted">Permissions et droits</small>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/utilisateurs.php" class="btn btn-outline-info text-start">
                                        <i class="bi bi-people me-2"></i>
                                        <strong>Utilisateurs</strong>
                                        <br><small class="text-muted">Gestion des comptes</small>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Overlay pour sidebar mobile -->
    <div class="sidebar-overlay d-lg-none" onclick="closeSidebar()" style="display: none;"></div>

    <?php include SHARED_PATH . '/templates/footer.php'; ?>

    <!-- CSS personnalisé de votre index2.php + corrections sidebar -->
    <style>
        /* Variables CSS */
        :root {
            --pc-blue: #004080;
            --pc-orange: #F08700;
        }
        
        /* Fixes pour éviter le scroll horizontal */
        body {
            overflow-x: hidden;
        }
        
        .container-fluid {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .row {
            margin-left: 0;
            margin-right: 0;
        }
        
        .col, .col-md-4, .col-lg-8, .col-lg-4 {
            padding-left: 12px;
            padding-right: 12px;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: box-shadow 0.15s ease-in-out;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }
        
        .bg-primary {
            background-color: var(--pc-blue) !important;
        }
        
        .bg-warning {
            background-color: var(--pc-orange) !important;
        }
        
        .text-primary {
            color: var(--pc-blue) !important;
        }
        
        .btn-primary {
            background-color: var(--pc-blue);
            border-color: var(--pc-blue);
        }
        
        .btn-primary:hover {
            background-color: #003366;
            border-color: #003366;
        }
        
        .btn-outline-primary {
            color: var(--pc-blue);
            border-color: var(--pc-blue);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--pc-blue);
            border-color: var(--pc-blue);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 64, 128, 0.05);
        }
        
        .badge {
            font-size: 0.75em;
        }
        
        /* SIDEBAR RESPONSIVE CORRECTIONS */
        
        /* Desktop - sidebar toujours visible à gauche */
        @media (min-width: 992px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 76px; /* Hauteur navbar */
                width: 250px;
                height: calc(100vh - 76px);
                background: linear-gradient(180deg, var(--pc-blue) 0%, #003366 100%);
                overflow-y: auto;
                z-index: 1000;
            }
            
            main.flex-grow-1 {
                margin-left: 250px !important;
            }
        }
        
        /* Mobile - sidebar cachée et overlay */
        @media (max-width: 991.98px) {
            main.flex-grow-1 {
                margin-left: 0 !important;
            }
            
            .sidebar-mobile.show {
                transform: translateX(0) !important;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
            }
            
            .sidebar-overlay.show {
                display: block !important;
            }
        }
        
        /* Responsive fixes */
        @media (max-width: 768px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .h3 {
                font-size: 1.5rem;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .btn {
                font-size: 0.875rem;
                white-space: nowrap;
            }
        }
        
        /* Animations */
        .card {
            animation: fadeInUp 0.5s ease-out;
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
        
        /* Fix pour les flex items */
        .flex-grow-1 {
            flex-grow: 1;
            min-width: 0; /* Permet le word-wrap */
        }
        
        .flex-shrink-0 {
            flex-shrink: 0;
        }
        
        /* Auto-refresh indicator */
        .auto-refresh {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 64, 128, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
        }
        
        .auto-refresh.show {
            opacity: 1;
        }
    </style>

    <!-- JavaScript personnalisé + sidebar mobile -->
    <script>
        // Fonction pour ouvrir sidebar mobile
        function showSidebar() {
            const sidebar = document.querySelector('.sidebar-mobile');
            const overlay = document.querySelector('.sidebar-overlay');
            if (sidebar && overlay) {
                sidebar.classList.add('show');
                overlay.style.display = 'block';
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }
        
        // Fonction pour fermer sidebar mobile
        function closeSidebar() {
            const sidebar = document.querySelector('.sidebar-mobile');
            const overlay = document.querySelector('.sidebar-overlay');
            if (sidebar && overlay) {
                sidebar.classList.remove('show');
                overlay.style.display = 'none';
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Fermer sidebar avec touche Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeSidebar();
                }
            });
            
            // Fermer sidebar au redimensionnement
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    closeSidebar();
                }
            });
            
            // Auto-refresh des statistiques toutes les 5 minutes
            setInterval(function() {
                if (!document.hidden) {
                    console.log('Auto-refresh stats');
                }
            }, 300000); // 5 minutes
            
            // Auto-hide alerts après 10 secondes
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    if (alert.parentElement) {
                        bsAlert.close();
                    }
                }, 10000);
            });
            
            // Smooth scroll pour les liens internes
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Protection contre les erreurs JS
            window.addEventListener('error', function(e) {
                console.warn('Erreur JS interceptée:', e.message);
            });
            
            console.log('Dashboard Protection Civile 64 v2 loaded - VERSION INDEX2 CORRIGÉE');
            console.log('User:', {
                id: <?php echo (int)($current_user['id'] ?? 0); ?>,
                role: '<?php echo h($current_user['role'] ?? 'guest'); ?>',
                antenne: '<?php echo h($current_user['antenne_nom'] ?? 'Aucune'); ?>'
            });
        });
    </script>
</body>
</html>