<?php
/**
 * GESTIONPC64 V2 - Middleware d'authentification
 * Fichier: shared/includes/AuthMiddleware.php
 * 
 * Middleware pour contrôler l'accès aux modules selon les permissions
 */

defined('PROTEC64_V2') or exit('Accès refusé');

// Configuration préfixe BDD v2 (éviter redéfinition)
if (!defined('VEH_PREFIX')) {
    define('VEH_PREFIX', 'veh_');
}

// Inclure le gestionnaire de permissions
require_once __DIR__ . '/PermissionManager.php';

// Instance globale du gestionnaire de permissions
global $permissionManager, $pdo;
if (!isset($permissionManager)) {
    // S'assurer que la connexion BDD est disponible
    if (!isset($pdo)) {
        $pdo = db_connect();
    }
    $permissionManager = new PermissionManager($pdo);
}

/**
 * Vérifier si l'utilisateur a une permission spécifique
 * @param string $module Module à vérifier
 * @param string $action Action à vérifier (read, write, delete)
 * @param bool $redirect_on_fail Rediriger vers 403 si échec
 * @return bool
 */
function checkPermission($module, $action, $redirect_on_fail = true) {
    global $current_user, $permissionManager;
    
    // Vérifier si l'utilisateur est connecté
    if (!isset($current_user) || !$current_user) {
        $current_user = get_current_user_data(); // Utiliser get_current_user_data() au lieu de get_current_user()
        if (!$current_user) {
            if ($redirect_on_fail) {
                header('Location: ' . BASE_URL . '/login.php');
                exit;
            }
            return false;
        }
    }
    
    // Vérifier la permission
    $has_permission = $permissionManager->hasPermission($current_user['id'], $module, $action);
    
    if (!$has_permission && $redirect_on_fail) {
        // Rediriger vers la page 403
        header('HTTP/1.1 403 Forbidden');
        include __DIR__ . '/../templates/403.php';
        exit;
    }
    
    return $has_permission;
}

/**
 * Vérifier l'accès en lecture à un module
 * @param string $module Module à vérifier
 * @param bool $redirect_on_fail Rediriger si échec
 * @return bool
 */
function requireRead($module, $redirect_on_fail = true) {
    return checkPermission($module, 'read', $redirect_on_fail);
}

/**
 * Vérifier l'accès en écriture à un module
 * @param string $module Module à vérifier
 * @param bool $redirect_on_fail Rediriger si échec
 * @return bool
 */
function requireWrite($module, $redirect_on_fail = true) {
    return checkPermission($module, 'write', $redirect_on_fail);
}

/**
 * Vérifier l'accès en suppression à un module
 * @param string $module Module à vérifier
 * @param bool $redirect_on_fail Rediriger si échec
 * @return bool
 */
function requireDelete($module, $redirect_on_fail = true) {
    return checkPermission($module, 'delete', $redirect_on_fail);
}

/**
 * Vérifier si l'utilisateur peut accéder à un module (lecture minimum)
 * @param string $module Module à vérifier
 * @param bool $redirect_on_fail Rediriger si échec
 * @return bool
 */
function canAccessModule($module, $redirect_on_fail = true) {
    global $permissionManager, $current_user;
    
    if (!isset($current_user) || !$current_user) {
        $current_user = get_current_user_data(); // Utiliser get_current_user_data()
        if (!$current_user) {
            if ($redirect_on_fail) {
                header('Location: ' . BASE_URL . '/login.php');
                exit;
            }
            return false;
        }
    }
    
    $can_access = $permissionManager->canAccessModule($current_user['id'], $module);
    
    if (!$can_access && $redirect_on_fail) {
        header('HTTP/1.1 403 Forbidden');
        include __DIR__ . '/../templates/403.php';
        exit;
    }
    
    return $can_access;
}

/**
 * Générer les attributs HTML pour désactiver un élément selon les permissions
 * @param string $module Module
 * @param string $action Action requise
 * @return string Attributs HTML
 */
function getPermissionAttributes($module, $action) {
    global $permissionManager, $current_user;
    
    if (!$current_user || !$permissionManager->hasPermission($current_user['id'], $module, $action)) {
        return 'disabled title="Vous n\'avez pas les droits pour cette action"';
    }
    
    return '';
}

/**
 * Afficher un bouton seulement si l'utilisateur a les permissions
 * @param string $module Module
 * @param string $action Action requise
 * @param string $html Code HTML du bouton
 * @param string $fallback HTML alternatif si pas de permission
 */
function showIfPermission($module, $action, $html, $fallback = '') {
    global $permissionManager, $current_user;
    
    if ($current_user && $permissionManager->hasPermission($current_user['id'], $module, $action)) {
        echo $html;
    } else {
        echo $fallback;
    }
}

/**
 * Récupérer le menu de navigation filtré selon les permissions
 * @return array Menu filtré
 */
function getFilteredNavigation() {
    global $permissionManager, $current_user;
    
    if (!$current_user) {
        return [];
    }
    
    $navigation = [
        [
            'name' => 'Tableau de bord',
            'url' => BASE_URL . '/index.php',
            'icon' => 'fas fa-tachometer-alt',
            'module' => 'dashboard',
            'action' => 'read'
        ],
        [
            'name' => 'Véhicules',
            'url' => BASE_URL . '/vehicules/',
            'icon' => 'fas fa-truck',
            'module' => 'vehicules',
            'action' => 'read'
        ],
        [
            'name' => 'Sorties',
            'url' => BASE_URL . '/sorties/',
            'icon' => 'fas fa-calendar-alt',
            'module' => 'sorties',
            'action' => 'read'
        ],
        [
            'name' => 'Inventaires',
            'icon' => 'fas fa-boxes',
            'module' => 'inventaire_general',
            'action' => 'read',
            'submenu' => [
                [
                    'name' => 'Inventaire général',
                    'url' => BASE_URL . '/inventaires/',
                    'module' => 'inventaire_general',
                    'action' => 'read'
                ],
                [
                    'name' => 'Habillement',
                    'url' => BASE_URL . '/inventaires/habillement.php',
                    'module' => 'inventaire_habillement',
                    'action' => 'read'
                ]
            ]
        ],
        [
            'name' => 'Pharmacie',
            'url' => BASE_URL . '/pharmacie/',
            'icon' => 'fas fa-pills',
            'module' => 'pharmacie',
            'action' => 'read'
        ],
        [
            'name' => 'Notes de frais',
            'url' => BASE_URL . '/notes-frais/',
            'icon' => 'fas fa-receipt',
            'module' => 'notes_frais',
            'action' => 'read'
        ],
        [
            'name' => 'Administration',
            'icon' => 'fas fa-cogs',
            'module' => 'utilisateurs',
            'action' => 'read',
            'submenu' => [
                [
                    'name' => 'Utilisateurs',
                    'url' => BASE_URL . '/admin/utilisateurs.php',
                    'module' => 'utilisateurs',
                    'action' => 'read'
                ],
                [
                    'name' => 'Antennes',
                    'url' => BASE_URL . '/admin/antennes.php',
                    'module' => 'antennes',
                    'action' => 'read'
                ],
                [
                    'name' => 'Rôles & Permissions',
                    'url' => BASE_URL . '/admin/roles.php',
                    'module' => 'roles',
                    'action' => 'read'
                ]
            ]
        ]
    ];
    
    // Filtrer la navigation selon les permissions
    $filtered_nav = [];
    
    foreach ($navigation as $item) {
        if ($permissionManager->hasPermission($current_user['id'], $item['module'], $item['action'])) {
            // Filtrer les sous-menus si présents
            if (isset($item['submenu'])) {
                $filtered_submenu = [];
                foreach ($item['submenu'] as $subitem) {
                    if ($permissionManager->hasPermission($current_user['id'], $subitem['module'], $subitem['action'])) {
                        $filtered_submenu[] = $subitem;
                    }
                }
                // Ne garder l'item principal que s'il y a des sous-menus accessibles
                if (!empty($filtered_submenu)) {
                    $item['submenu'] = $filtered_submenu;
                    $filtered_nav[] = $item;
                }
            } else {
                $filtered_nav[] = $item;
            }
        }
    }
    
    return $filtered_nav;
}

/**
 * Vérifier si l'utilisateur est administrateur (rôle système admin)
 * @return bool
 */
function isAdmin() {
    global $current_user, $pdo;
    
    if (!$current_user) {
        return false;
    }
    
    try {
        $sql = "SELECT r.nom FROM " . VEH_PREFIX . "utilisateurs u 
                JOIN " . VEH_PREFIX . "roles r ON u.role_id = r.id 
                WHERE u.id = ? AND r.nom = 'admin' AND r.systeme = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$current_user['id']]);
        
        return $stmt->rowCount() > 0;
        
    } catch (PDOException $e) {
        error_log("Erreur isAdmin: " . $e->getMessage());
        return false;
    }
}

/**
 * Middleware global à inclure en début de chaque page protégée
 * @param string $required_module Module requis pour accéder à la page
 * @param string $required_action Action requise (read par défaut)
 */
function requireAuth($required_module = null, $required_action = 'read') {
    global $current_user;
    
    // Vérifier si l'utilisateur est connecté
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    
    // Initialiser $current_user si pas fait
    if (!$current_user) {
        $current_user = get_current_user_data(); // Utiliser get_current_user_data()
    }
    
    // Vérifier les permissions si un module est spécifié
    if ($required_module) {
        checkPermission($required_module, $required_action, true);
    }
}

/**
 * Enregistrer une action utilisateur avec les permissions
 * @param string $action Description de l'action
 * @param string $module Module concerné
 * @param string $details Détails supplémentaires
 */
function logUserAction($action, $module = '', $details = '') {
    global $current_user, $pdo;
    
    if (!$current_user) {
        return;
    }
    
    try {
        // Vérifier si la table logs existe
        $table_exists = $pdo->query("SHOW TABLES LIKE '" . VEH_PREFIX . "logs'")->rowCount() > 0;
        
        if ($table_exists) {
            $sql = "INSERT INTO " . VEH_PREFIX . "logs (user_id, action, module, details, ip_address, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $current_user['id'],
                $action,
                $module,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Erreur logUserAction: " . $e->getMessage());
    }
}

// Fonctions utilitaires pour les templates

/**
 * Classe CSS pour styliser selon le niveau de permission
 * @param string $module Module
 * @param string $action Action
 * @return string Classes CSS
 */
function getPermissionClass($module, $action) {
    global $permissionManager, $current_user;
    
    if (!$current_user) {
        return 'text-muted';
    }
    
    if ($permissionManager->hasPermission($current_user['id'], $module, 'write')) {
        return 'text-success';
    } elseif ($permissionManager->hasPermission($current_user['id'], $module, 'read')) {
        return 'text-info';
    } else {
        return 'text-muted';
    }
}

/**
 * Icône selon le niveau de permission
 * @param string $module Module
 * @param string $action Action
 * @return string Icône FontAwesome
 */
function getPermissionIcon($module, $action) {
    global $permissionManager, $current_user;
    
    if (!$current_user) {
        return 'fas fa-lock';
    }
    
    if ($permissionManager->hasPermission($current_user['id'], $module, 'delete')) {
        return 'fas fa-user-shield';
    } elseif ($permissionManager->hasPermission($current_user['id'], $module, 'write')) {
        return 'fas fa-edit';
    } elseif ($permissionManager->hasPermission($current_user['id'], $module, 'read')) {
        return 'fas fa-eye';
    } else {
        return 'fas fa-ban';
    }
}
?>