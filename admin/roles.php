<?php
/**
 * Page de gestion des rôles et permissions Protection Civile 64 - v2
 * Interface d'administration des rôles utilisateurs
 */

// Définir la constante de sécurité
define('PROTEC64_V2', true);

// Inclure les fichiers de base EXACTEMENT comme index.php
require_once '../shared/includes/config.php';
require_once '../shared/includes/db.php';
require_once '../shared/includes/auth.php';
require_once '../shared/includes/utils.php';
require_once '../shared/includes/PermissionManager.php';
require_once '../shared/includes/AuthMiddleware.php';

// Vérifier l'authentification EXACTEMENT comme index.php
require_login();

// Vérifier les permissions spécifiques
requireAuth('roles', 'read');

// Variables pour la page EXACTEMENT comme index.php
$page_title = 'Gestion des Rôles et Permissions';
$current_section = 'admin';

// Récupérer l'utilisateur actuel EXACTEMENT comme index.php
$current_user = get_current_user_data();

// CORRECTION : Vérifier si $current_user est bien un tableau (copié de index.php)
if (!is_array($current_user) || empty($current_user)) {
    error_log("ERREUR Roles: current_user invalide - " . print_r($current_user, true));
    
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
        log_action("Utilisateur invalide dans roles - session corrompue ?", 'error');
    }
}

// S'assurer que toutes les clés nécessaires existent
$required_keys = ['id', 'nom', 'prenom', 'role', 'antenne_id'];
foreach ($required_keys as $key) {
    if (!isset($current_user[$key])) {
        $current_user[$key] = '';
    }
}

// Initialiser le gestionnaire de permissions
$permissionManager = new PermissionManager(db_connect());

// Traitement des actions POST
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create_role':
                if (!checkPermission('roles', 'write', false)) {
                    throw new Exception('Permissions insuffisantes pour créer un rôle');
                }
                
                $nom = trim($_POST['nom'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $couleur = $_POST['couleur'] ?? '#6c757d';
                
                if (empty($nom)) {
                    throw new Exception('Le nom du rôle est obligatoire');
                }
                
                $role_id = $permissionManager->createRole($nom, $description, $couleur);
                if ($role_id) {
                    $message = "Rôle '$nom' créé avec succès";
                    $messageType = 'success';
                } else {
                    throw new Exception('Erreur lors de la création du rôle');
                }
                break;
                
            case 'update_permissions':
                if (!checkPermission('roles', 'write', false)) {
                    throw new Exception('Permissions insuffisantes pour modifier les permissions');
                }
                
                $role_id = (int)($_POST['role_id'] ?? 0);
                $permissions = $_POST['permissions'] ?? [];
                
                if ($role_id <= 0) {
                    throw new Exception('Rôle invalide');
                }
                
                $result = $permissionManager->assignPermissions($role_id, $permissions);
                if ($result) {
                    $message = "Permissions mises à jour avec succès";
                    $messageType = 'success';
                } else {
                    throw new Exception('Erreur lors de la mise à jour des permissions');
                }
                break;
                
            case 'delete_role':
                if (!checkPermission('roles', 'write', false)) {
                    throw new Exception('Permissions insuffisantes pour supprimer un rôle');
                }
                
                $role_id = (int)($_POST['role_id'] ?? 0);
                $result = $permissionManager->deleteRole($role_id);
                
                if ($result) {
                    $message = "Rôle supprimé avec succès";
                    $messageType = 'success';
                } else {
                    $message = "Impossible de supprimer ce rôle (rôle système ou utilisateurs assignés)";
                    $messageType = 'warning';
                }
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Récupérer les données
try {
    $roles = $permissionManager->getAllRoles(true);
    $permissions_grouped = $permissionManager->getAllPermissions();
} catch (Exception $e) {
    log_action("Erreur lors de la récupération des rôles: " . $e->getMessage(), 'error');
    $roles = [];
    $permissions_grouped = [];
}

// Inclure le header EXACTEMENT comme index.php
$GLOBALS['current_user'] = $current_user; // Rendre accessible aux templates
include SHARED_PATH . '/templates/header.php';
?>

<body>
    <?php include SHARED_PATH . '/templates/navigation.php'; ?>
    
    <div class="d-flex">
        <?php include SHARED_PATH . '/templates/sidebar.php'; ?>
        
        <main class="flex-grow-1 p-4">
            <div class="container-fluid">
                
                <!-- En-tête avec titre -->
                <div class="row mb-4">
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="mb-2 mb-md-0">
                                <h1 class="h2 mb-1">
                                    <i class="bi bi-people-fill text-primary me-2"></i>
                                    Gestion des Rôles et Permissions
                                </h1>
                                <p class="text-muted mb-0">
                                    Gérez les rôles utilisateurs et leurs permissions • 
                                    <?php echo format_datetime(date('Y-m-d H:i:s')); ?>
                                </p>
                            </div>
                            
                            <!-- Actions rapides -->
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if (checkPermission('roles', 'write', false)): ?>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Créer un rôle
                                </button>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Retour dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages d'alerte -->
                <?php if ($message): ?>
                <div class="row mb-4">
                    <div class="col">
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?> me-2"></i>
                            <?php echo h($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Grille des rôles -->
                <div class="row g-3">
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: <?php echo h($role['couleur']); ?>; color: white;">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-people" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo h($role['nom']); ?></h6>
                                            <?php if ($role['systeme']): ?>
                                            <small class="badge bg-warning text-dark">Système</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (checkPermission('roles', 'write', false)): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="editRolePermissions(<?php echo $role['id']; ?>, '<?php echo h($role['nom']); ?>')">
                                                    <i class="bi bi-key me-2"></i>Modifier permissions
                                                </a>
                                            </li>
                                            <?php if (!$role['systeme']): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="deleteRole(<?php echo $role['id']; ?>, '<?php echo h($role['nom']); ?>')">
                                                    <i class="bi bi-trash me-2"></i>Supprimer
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body">
                                    <p class="text-muted small mb-3"><?php echo h($role['description']); ?></p>
                                    
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <div class="h4 mb-0 text-primary"><?php echo number_format($role['nb_utilisateurs']); ?></div>
                                                <small class="text-muted">Utilisateur<?php echo $role['nb_utilisateurs'] > 1 ? 's' : ''; ?></small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="h4 mb-0 text-success" id="permissions-count-<?php echo $role['id']; ?>">
                                                <?php
                                                try {
                                                    $role_permissions = $permissionManager->getRolePermissions($role['id']);
                                                    echo count($role_permissions);
                                                } catch (Exception $e) {
                                                    echo '0';
                                                }
                                                ?>
                                            </div>
                                            <small class="text-muted">Permission<?php echo (count($role_permissions ?? []) > 1) ? 's' : ''; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                            <h5 class="text-muted mt-3">Aucun rôle trouvé</h5>
                            <p class="text-muted">Les rôles système n'ont pas été créés correctement.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Créer un rôle -->
    <div class="modal fade" id="createRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Créer un nouveau rôle
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_role">
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom du rôle *</label>
                            <input type="text" class="form-control" id="nom" name="nom" required 
                                   placeholder="Ex: Responsable Habillement">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Description du rôle et de ses responsabilités"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="couleur" class="form-label">Couleur</label>
                            <div class="d-flex align-items-center">
                                <input type="color" class="form-control form-control-color me-3" 
                                       id="couleur" name="couleur" value="#6f42c1">
                                <small class="text-muted">Couleur d'affichage du rôle</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Créer le rôle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modifier les permissions -->
    <div class="modal fade" id="editPermissionsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-key me-2"></i>Modifier les permissions : <span id="editRoleName"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" id="editPermissionsForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_permissions">
                        <input type="hidden" name="role_id" id="editRoleId">
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6>Sélectionnez les permissions pour ce rôle :</h6>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-success me-2" onclick="selectAllPermissions()">
                                            <i class="bi bi-check-all me-1"></i>Tout sélectionner
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="unselectAllPermissions()">
                                            <i class="bi bi-x me-1"></i>Tout désélectionner
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row" id="permissionsContainer">
                            <?php if (!empty($permissions_grouped)): ?>
                                <?php foreach ($permissions_grouped as $groupe => $permissions): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header py-2 bg-light">
                                            <h6 class="mb-0">
                                                <i class="bi bi-folder me-2"></i><?php echo h($groupe); ?>
                                            </h6>
                                        </div>
                                        <div class="card-body py-2">
                                            <?php foreach ($permissions as $permission): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input permission-checkbox" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="<?php echo $permission['id']; ?>" 
                                                       id="perm_<?php echo $permission['id']; ?>">
                                                <label class="form-check-label" for="perm_<?php echo $permission['id']; ?>">
                                                    <strong><?php echo h($permission['nom_affichage']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo h($permission['description']); ?>
                                                        <code class="ms-1">(<?php echo h($permission['module'] . '.' . $permission['action']); ?>)</code>
                                                    </small>
                                                </label>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Sauvegarder les permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Supprimer un rôle -->
    <div class="modal fade" id="deleteRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>Supprimer le rôle
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" id="deleteRoleForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_role">
                        <input type="hidden" name="role_id" id="deleteRoleId">
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Attention !</strong> Cette action est irréversible.
                        </div>
                        
                        <p>Êtes-vous sûr de vouloir supprimer le rôle <strong id="deleteRoleName"></strong> ?</p>
                        <p class="text-muted">
                            <small>Les utilisateurs assignés à ce rôle seront automatiquement réassignés au rôle "utilisateur".</small>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Supprimer définitivement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include SHARED_PATH . '/templates/footer.php'; ?>

    <!-- CSS personnalisé EXACTEMENT comme index.php -->
    <style>
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
        
        .col, .col-md-6, .col-lg-4 {
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
        
        /* Variables CSS */
        :root {
            --pc-blue: #004080;
            --pc-orange: #F08700;
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
    </style>

    <!-- JavaScript -->
    <script>
        // Récupérer les permissions d'un rôle via AJAX
        async function getRolePermissions(roleId) {
            try {
                const response = await fetch('ajax/get_role_permissions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `role_id=${roleId}`
                });
                return await response.json();
            } catch (error) {
                console.error('Erreur lors de la récupération des permissions:', error);
                return [];
            }
        }
        
        // Modifier les permissions d'un rôle
        async function editRolePermissions(roleId, roleName) {
            document.getElementById('editRoleId').value = roleId;
            document.getElementById('editRoleName').textContent = roleName;
            
            // Décocher toutes les cases
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
            
            // Récupérer et cocher les permissions actuelles
            const rolePermissions = await getRolePermissions(roleId);
            rolePermissions.forEach(permId => {
                const checkbox = document.getElementById(`perm_${permId}`);
                if (checkbox) checkbox.checked = true;
            });
            
            // Afficher le modal
            new bootstrap.Modal(document.getElementById('editPermissionsModal')).show();
        }
        
        // Supprimer un rôle
        function deleteRole(roleId, roleName) {
            document.getElementById('deleteRoleId').value = roleId;
            document.getElementById('deleteRoleName').textContent = roleName;
            new bootstrap.Modal(document.getElementById('deleteRoleModal')).show();
        }
        
        // Sélectionner toutes les permissions
        function selectAllPermissions() {
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
        }
        
        // Désélectionner toutes les permissions
        function unselectAllPermissions() {
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
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
            
            console.log('Page Rôles Protection Civile 64 v2 loaded');
        });
    </script>
</body>
</html>