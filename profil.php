<?php
/**
 * Page de profil utilisateur - v2
 * Fichier: profil.php
 */

// Définir la constante de sécurité
define('PROTEC64_V2', true);

// Inclure les fichiers nécessaires
require_once 'shared/includes/config.php';
require_once 'shared/includes/db.php';
require_once 'shared/includes/auth.php';
require_once 'shared/includes/utils.php';

// Vérifier l'authentification
require_login();

// Variables pour la page
$page_title = 'Mon profil';
$current_section = 'profil';

// Récupérer l'utilisateur actuel avec toutes ses données
$current_user = get_current_user_data();
$user_id = get_current_user_id();

// CORRECTION : Vérifier si $current_user est bien un tableau
if (!is_array($current_user) || empty($current_user)) {
    error_log("ERREUR Profil: current_user invalide - " . print_r($current_user, true));
    
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
            'actif' => 1,
            'identifiant_eprotec' => '',
            'telephone' => '',
            'date_naissance' => '',
            'adresse' => '',
            'ville' => '',
            'code_postal' => '',
            'mot_de_passe' => ''
        ];
        
        // Logger le problème pour debug
        log_action("Utilisateur invalide dans profil - session corrompue ?", 'error');
    }
}

// Pour la navigation entre utilisateurs (admin/responsable)
$nav_user_id = $user_id;
$nav_user = $current_user;

if ((is_admin() || has_role('responsable')) && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $nav_user_id = (int)$_GET['user_id'];
    
    try {
        $db = db_connect();
        $nav_user_query = $db->prepare("
            SELECT u.*, a.nom as antenne_nom 
            FROM " . DB_PREFIX . "utilisateurs u 
            LEFT JOIN " . DB_PREFIX . "antennes a ON u.antenne_id = a.id 
            WHERE u.id = ?
        ");
        $nav_user_query->execute([$nav_user_id]);
        $nav_user_data = $nav_user_query->fetch(PDO::FETCH_ASSOC);
        
        if ($nav_user_data && is_array($nav_user_data)) {
            $nav_user = $nav_user_data;
            
            // S'assurer que toutes les clés nécessaires existent
            $default_values = [
                'id' => 0,
                'nom' => '',
                'prenom' => '',
                'email' => '',
                'identifiant_eprotec' => '',
                'telephone' => '',
                'date_naissance' => '',
                'adresse' => '',
                'ville' => '',
                'code_postal' => '',
                'role' => 'conducteur',
                'antenne_id' => null,
                'antenne_nom' => 'Aucune antenne',
                'actif' => 1,
                'mot_de_passe' => ''
            ];
            
            foreach ($default_values as $key => $default) {
                if (!isset($nav_user[$key])) {
                    $nav_user[$key] = $default;
                }
            }
        } else {
            redirect('profil.php');
        }
    } catch (Exception $e) {
        log_action("Erreur récupération utilisateur profil: " . $e->getMessage(), 'error');
        redirect('profil.php');
    }
}

// S'assurer que nav_user est toujours un tableau valide
if (!is_array($nav_user)) {
    $nav_user = $current_user;
}

// Traitement des formulaires
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'update_profile' && (is_admin() || is_admin_or_responsable() || $nav_user_id === $user_id)) {
                // Mise à jour des informations personnelles
                $nom = clean_name($_POST['nom'] ?? '');
                $prenom = clean_name($_POST['prenom'] ?? '');
                $email = validate_email($_POST['email'] ?? '');
                $identifiant_eprotec = trim($_POST['identifiant_eprotec'] ?? '');
                $telephone = trim($_POST['telephone'] ?? '');
                $date_naissance = $_POST['date_naissance'] ?? '';
                $adresse = trim($_POST['adresse'] ?? '');
                $ville = trim($_POST['ville'] ?? '');
                $code_postal = trim($_POST['code_postal'] ?? '');
                
                // Validation
                $errors = [];
                if (empty($nom)) $errors[] = "Le nom est obligatoire";
                if (empty($prenom)) $errors[] = "Le prénom est obligatoire";
                if (empty($email)) $errors[] = "L'email est obligatoire";
                
                // Vérifier unicité email et identifiant
                $db = db_connect();
                $email_check = $db->prepare("SELECT id FROM " . DB_PREFIX . "utilisateurs WHERE email = ? AND id != ?");
                $email_check->execute([$email, $nav_user_id]);
                if ($email_check->fetch()) {
                    $errors[] = "Cet email est déjà utilisé";
                }
                
                if (!empty($identifiant_eprotec)) {
                    $eprotec_check = $db->prepare("SELECT id FROM " . DB_PREFIX . "utilisateurs WHERE identifiant_eprotec = ? AND id != ?");
                    $eprotec_check->execute([$identifiant_eprotec, $nav_user_id]);
                    if ($eprotec_check->fetch()) {
                        $errors[] = "Cet identifiant eProtec est déjà utilisé";
                    }
                }
                
                if (empty($errors)) {
                    $update_data = [
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email,
                        'identifiant_eprotec' => $identifiant_eprotec,
                        'telephone' => $telephone,
                        'date_naissance' => $date_naissance ?: null,
                        'adresse' => $adresse,
                        'ville' => $ville,
                        'code_postal' => $code_postal
                    ];
                    
                    // Champs réservés aux admin/responsable
                    if (is_admin() || is_admin_or_responsable()) {
                        $update_data['role'] = $_POST['role'] ?? $nav_user['role'];
                        $update_data['antenne_id'] = (int)($_POST['antenne_id'] ?? $nav_user['antenne_id']);
                        $update_data['actif'] = isset($_POST['actif']) ? 1 : 0;
                    }
                    
                    $placeholders = implode(' = ?, ', array_keys($update_data)) . ' = ?';
                    $values = array_values($update_data);
                    $values[] = $nav_user_id;
                    
                    $update_query = $db->prepare("UPDATE " . DB_PREFIX . "utilisateurs SET $placeholders WHERE id = ?");
                    $update_query->execute($values);
                    
                    // Log de l'action
                    log_action("Modification profil utilisateur #$nav_user_id", 'info', [
                        'modified_user' => $nav_user['nom'] . ' ' . $nav_user['prenom'],
                        'modifier_user' => $current_user['nom'] . ' ' . $current_user['prenom']
                    ]);
                    
                    $message = 'Profil mis à jour avec succès !';
                    $message_type = 'success';
                    
                    // Recharger les données
                    $reload_query = $db->prepare("
                        SELECT u.*, a.nom as antenne_nom 
                        FROM " . DB_PREFIX . "utilisateurs u 
                        LEFT JOIN " . DB_PREFIX . "antennes a ON u.antenne_id = a.id 
                        WHERE u.id = ?
                    ");
                    $reload_query->execute([$nav_user_id]);
                    $reloaded = $reload_query->fetch(PDO::FETCH_ASSOC);
                    
                    if ($reloaded && is_array($reloaded)) {
                        $nav_user = $reloaded;
                    }
                } else {
                    $message = implode('<br>', $errors);
                    $message_type = 'danger';
                }
                
            } elseif ($_POST['action'] === 'change_password') {
                // Changement de mot de passe
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                $errors = [];
                
                // Pour les conducteurs : vérifier l'ancien mot de passe
                if (!is_admin() && !is_admin_or_responsable() && $nav_user_id === $user_id) {
                    if (empty($current_password)) {
                        $errors[] = "L'ancien mot de passe est obligatoire";
                    } elseif (!password_verify($current_password, $nav_user['mot_de_passe'])) {
                        $errors[] = "L'ancien mot de passe est incorrect";
                    }
                }
                
                if (empty($new_password)) {
                    $errors[] = "Le nouveau mot de passe est obligatoire";
                } elseif (strlen($new_password) < 6) {
                    $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
                } elseif ($new_password !== $confirm_password) {
                    $errors[] = "La confirmation du mot de passe ne correspond pas";
                }
                
                if (empty($errors)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $db = db_connect();
                    $pwd_query = $db->prepare("UPDATE " . DB_PREFIX . "utilisateurs SET mot_de_passe = ? WHERE id = ?");
                    $pwd_query->execute([$hashed_password, $nav_user_id]);
                    
                    log_action("Changement mot de passe utilisateur #$nav_user_id", 'info', [
                        'target_user' => $nav_user['nom'] . ' ' . $nav_user['prenom'],
                        'changed_by' => $current_user['nom'] . ' ' . $current_user['prenom']
                    ]);
                    
                    $message = 'Mot de passe modifié avec succès !';
                    $message_type = 'success';
                } else {
                    $message = implode('<br>', $errors);
                    $message_type = 'danger';
                }
            }
        }
    } catch (Exception $e) {
        log_action("Erreur lors de la modification du profil: " . $e->getMessage(), 'error');
        $message = 'Une erreur est survenue lors de la modification.';
        $message_type = 'danger';
    }
}

// Récupérer les antennes pour le formulaire
$antennes = [];
try {
    $db = db_connect();
    $antennes_query = $db->query("SELECT * FROM " . DB_PREFIX . "antennes ORDER BY nom");
    if ($antennes_query) {
        $antennes = $antennes_query->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    log_action("Erreur lors de la récupération des antennes: " . $e->getMessage(), 'error');
}

// Récupérer les statistiques de l'utilisateur
$stats = [
    'total_sorties' => 0,
    'sorties_terminees' => 0,
    'km_total' => 0,
    'km_moyen' => 0
];

try {
    $db = db_connect();
    // Statistiques des sorties
    $stats_query = $db->prepare("
        SELECT 
            COUNT(*) as total_sorties,
            COUNT(CASE WHEN statut = 'termine' THEN 1 END) as sorties_terminees,
            COALESCE(SUM(CASE 
                WHEN s.kilometrage_retour > s.kilometrage_depart 
                AND s.kilometrage_retour > 0 
                AND s.kilometrage_depart > 0 
                THEN s.kilometrage_retour - s.kilometrage_depart 
                ELSE 0 
            END), 0) as km_total
        FROM " . DB_PREFIX . "sorties s 
        WHERE s.conducteur_id = ?
    ");
    $stats_query->execute([$nav_user_id]);
    
    if ($stats_data = $stats_query->fetch(PDO::FETCH_ASSOC)) {
        $stats['total_sorties'] = (int)$stats_data['total_sorties'];
        $stats['sorties_terminees'] = (int)$stats_data['sorties_terminees'];
        $stats['km_total'] = (int)$stats_data['km_total'];
        
        if ($stats['sorties_terminees'] > 0) {
            $stats['km_moyen'] = round($stats['km_total'] / $stats['sorties_terminees'], 1);
        }
    }
} catch (Exception $e) {
    log_action("Erreur lors de la récupération des statistiques: " . $e->getMessage(), 'error');
}

// Récupérer les dernières sorties
$dernieres_sorties = [];
try {
    $db = db_connect();
    $sorties_query = $db->prepare("
        SELECT s.*, v.immatriculation, v.type
        FROM " . DB_PREFIX . "sorties s
        JOIN " . DB_PREFIX . "vehicules v ON s.vehicule_id = v.id
        WHERE s.conducteur_id = ?
        ORDER BY s.date_sortie DESC
        LIMIT 5
    ");
    $sorties_query->execute([$nav_user_id]);
    $dernieres_sorties = $sorties_query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    log_action("Erreur lors de la récupération des dernières sorties: " . $e->getMessage(), 'error');
}

// Récupérer la liste des utilisateurs pour navigation (admin/responsable)
$users_list = [];
if (is_admin() || is_admin_or_responsable()) {
    try {
        $db = db_connect();
        $users_query = $db->query("
            SELECT u.*, a.nom as antenne_nom 
            FROM " . DB_PREFIX . "utilisateurs u 
            LEFT JOIN " . DB_PREFIX . "antennes a ON u.antenne_id = a.id 
            WHERE u.actif = 1 
            ORDER BY u.nom, u.prenom
        ");
        if ($users_query) {
            $users_list = $users_query->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        log_action("Erreur lors de la récupération de la liste des utilisateurs: " . $e->getMessage(), 'error');
    }
}

// Rendre les variables globales pour les templates
$GLOBALS['current_user'] = $current_user;

// Inclure le header
include SHARED_PATH . '/templates/header.php';
?>

<body>
    <?php include SHARED_PATH . '/templates/navigation.php'; ?>
    
    <div class="d-flex">
        <?php include SHARED_PATH . '/templates/sidebar.php'; ?>
        
        <main class="flex-grow-1 p-4">
            <div class="container-fluid">
                
                <!-- En-tête de page -->
                <div class="row mb-4">
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h2 mb-1">
                                    <i class="bi bi-person-circle text-primary me-2"></i>
                                    <?php if ($nav_user_id !== $user_id): ?>
                                        Profil de <?php echo h($nav_user['prenom'] . ' ' . $nav_user['nom']); ?>
                                    <?php else: ?>
                                        Mon profil
                                    <?php endif; ?>
                                </h1>
                                <p class="text-muted mb-0">
                                    <?php echo h(ucfirst($nav_user['role'])); ?> - 
                                    <?php echo h($nav_user['antenne_nom'] ?? 'Aucune antenne'); ?>
                                </p>
                            </div>
                            
                            <!-- Navigation utilisateurs pour admin/responsable -->
                            <?php if (!empty($users_list) && count($users_list) > 1): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-people me-1"></i>
                                    Changer d'utilisateur
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($users_list as $user): ?>
                                    <li>
                                        <a class="dropdown-item <?php echo $user['id'] == $nav_user_id ? 'active' : ''; ?>" 
                                           href="profil.php?user_id=<?php echo $user['id']; ?>">
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <?php if (!$user['actif']): ?>
                                                        <i class="bi bi-person-slash text-muted"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-person-check text-success"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-medium"><?php echo h($user['prenom'] . ' ' . $user['nom']); ?></div>
                                                    <small class="text-muted"><?php echo h($user['role'] . ' - ' . ($user['antenne_nom'] ?? 'Sans antenne')); ?></small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Messages d'alerte -->
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo h($message_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Colonne gauche - Informations personnelles -->
                    <div class="col-lg-8">
                        
                        <!-- Formulaire informations personnelles -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-person-lines-fill me-2"></i>
                                    Informations personnelles
                                </h5>
                            </div>
                            <div class="card-body">
                                
                                <?php if (!is_admin() && !is_admin_or_responsable() && $nav_user_id === $user_id): ?>
                                <!-- Mode lecture seule pour conducteur -->
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Information :</strong> Vos données personnelles sont en lecture seule. 
                                    Contactez votre responsable d'antenne pour toute modification.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Nom</label>
                                        <p class="form-control-plaintext"><?php echo h($nav_user['nom']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Prénom</label>
                                        <p class="form-control-plaintext"><?php echo h($nav_user['prenom']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Email</label>
                                        <p class="form-control-plaintext"><?php echo h($nav_user['email']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Identifiant eProtec</label>
                                        <p class="form-control-plaintext"><?php echo h($nav_user['identifiant_eprotec'] ?: 'Non défini'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Téléphone</label>
                                        <p class="form-control-plaintext"><?php echo h($nav_user['telephone'] ?: 'Non défini'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-medium">Rôle</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge bg-<?php echo $nav_user['role'] === 'admin' ? 'danger' : ($nav_user['role'] === 'responsable' ? 'warning' : 'secondary'); ?>">
                                                <?php echo h(ucfirst($nav_user['role'])); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php else: ?>
                                <!-- Mode édition pour admin/responsable -->
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nom" name="nom" 
                                                   value="<?php echo h($nav_user['nom'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                                   value="<?php echo h($nav_user['prenom'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo h($nav_user['email'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="identifiant_eprotec" class="form-label">Identifiant eProtec</label>
                                            <input type="text" class="form-control" id="identifiant_eprotec" name="identifiant_eprotec" 
                                                   value="<?php echo h($nav_user['identifiant_eprotec'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="telephone" class="form-label">Téléphone</label>
                                            <input type="tel" class="form-control" id="telephone" name="telephone" 
                                                   value="<?php echo h($nav_user['telephone'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="date_naissance" class="form-label">Date de naissance</label>
                                            <input type="date" class="form-control" id="date_naissance" name="date_naissance" 
                                                   value="<?php echo h($nav_user['date_naissance'] ?? ''); ?>">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="adresse" class="form-label">Adresse</label>
                                            <input type="text" class="form-control" id="adresse" name="adresse" 
                                                   value="<?php echo h($nav_user['adresse'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="ville" class="form-label">Ville</label>
                                            <input type="text" class="form-control" id="ville" name="ville" 
                                                   value="<?php echo h($nav_user['ville'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="code_postal" class="form-label">Code postal</label>
                                            <input type="text" class="form-control" id="code_postal" name="code_postal" 
                                                   value="<?php echo h($nav_user['code_postal'] ?? ''); ?>" maxlength="5">
                                        </div>
                                        
                                        <!-- Champs admin/responsable -->
                                        <div class="col-md-4 mb-3">
                                            <label for="role" class="form-label">Rôle</label>
                                            <select class="form-select" id="role" name="role">
                                                <option value="conducteur" <?php echo (isset($nav_user['role']) && $nav_user['role'] === 'conducteur') ? 'selected' : ''; ?>>Conducteur</option>
                                                <option value="responsable" <?php echo (isset($nav_user['role']) && $nav_user['role'] === 'responsable') ? 'selected' : ''; ?>>Responsable</option>
                                                <?php if (is_admin()): ?>
                                                <option value="admin" <?php echo (isset($nav_user['role']) && $nav_user['role'] === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="antenne_id" class="form-label">Antenne</label>
                                            <select class="form-select" id="antenne_id" name="antenne_id">
                                                <option value="">Sélectionner une antenne</option>
                                                <?php foreach ($antennes as $antenne): ?>
                                                <option value="<?php echo $antenne['id']; ?>" 
                                                        <?php echo (isset($nav_user['antenne_id']) && $nav_user['antenne_id'] == $antenne['id']) ? 'selected' : ''; ?>>
                                                    <?php echo h($antenne['nom']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Statut</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="actif" name="actif" 
                                                       <?php echo (isset($nav_user['actif']) && $nav_user['actif']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="actif">
                                                    Compte actif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-1"></i>
                                            Enregistrer les modifications
                                        </button>
                                    </div>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Changement de mot de passe -->
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-key me-2"></i>
                                    Changement de mot de passe