<?php
/**
 * GESTIONPC64 V2 - Gestionnaire de Permissions
 * Fichier: shared/includes/PermissionManager.php
 * 
 * Classe principale pour gérer les permissions utilisateurs
 */

defined('PROTEC64_V2') or exit('Accès refusé');

// Configuration préfixe BDD v2
define('VEH_PREFIX', 'veh_');

class PermissionManager {
    private $pdo;
    private $cache = [];
    private $cache_timeout = 300; // 5 minutes
    
    public function __construct($database) {
        $this->pdo = $database;
    }
    
    /**
     * Vérifier si un utilisateur a une permission spécifique
     * @param int $user_id ID de l'utilisateur
     * @param string $module Module (vehicules, inventaire, etc.)
     * @param string $action Action (read, write, delete)
     * @return bool
     */
    public function hasPermission($user_id, $module, $action) {
        try {
            $cache_key = "perm_{$user_id}_{$module}_{$action}";
            
            // Vérifier le cache
            if (isset($this->cache[$cache_key]) && 
                (time() - $this->cache[$cache_key]['time']) < $this->cache_timeout) {
                return $this->cache[$cache_key]['result'];
            }
            
            $sql = "SELECT COUNT(*) as count FROM " . VEH_PREFIX . "utilisateurs u 
                    JOIN " . VEH_PREFIX . "roles r ON u.role_id = r.id
                    JOIN " . VEH_PREFIX . "role_permissions rp ON r.id = rp.role_id
                    JOIN " . VEH_PREFIX . "permissions p ON rp.permission_id = p.id
                    WHERE u.id = ? AND p.module = ? AND p.action = ? 
                    AND u.actif = 1 AND r.actif = 1 AND p.actif = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id, $module, $action]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $has_permission = ($result['count'] > 0);
            
            // Mettre en cache
            $this->cache[$cache_key] = [
                'result' => $has_permission,
                'time' => time()
            ];
            
            return $has_permission;
            
        } catch (PDOException $e) {
            error_log("Erreur PermissionManager::hasPermission: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer toutes les permissions d'un utilisateur
     * @param int $user_id ID de l'utilisateur
     * @return array
     */
    public function getUserPermissions($user_id) {
        try {
            $sql = "SELECT p.module, p.action, p.nom_affichage, p.description, p.groupe
                    FROM " . VEH_PREFIX . "utilisateurs u 
                    JOIN " . VEH_PREFIX . "roles r ON u.role_id = r.id
                    JOIN " . VEH_PREFIX . "role_permissions rp ON r.id = rp.role_id
                    JOIN " . VEH_PREFIX . "permissions p ON rp.permission_id = p.id
                    WHERE u.id = ? AND u.actif = 1 AND r.actif = 1 AND p.actif = 1
                    ORDER BY p.groupe, p.ordre";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur PermissionManager::getUserPermissions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les permissions d'un rôle
     * @param int $role_id ID du rôle
     * @return array
     */
    public function getRolePermissions($role_id) {
        try {
            $sql = "SELECT p.* FROM " . VEH_PREFIX . "permissions p
                    JOIN " . VEH_PREFIX . "role_permissions rp ON p.id = rp.permission_id
                    WHERE rp.role_id = ? AND p.actif = 1
                    ORDER BY p.groupe, p.ordre";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$role_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur PermissionManager::getRolePermissions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Assigner des permissions à un rôle
     * @param int $role_id ID du rôle
     * @param array $permission_ids Liste des IDs de permissions
     * @return bool
     */
    public function assignPermissions($role_id, $permission_ids) {
        try {
            $this->pdo->beginTransaction();
            
            // Supprimer les anciennes permissions
            $sql_delete = "DELETE FROM " . VEH_PREFIX . "role_permissions WHERE role_id = ?";
            $stmt_delete = $this->pdo->prepare($sql_delete);
            $stmt_delete->execute([$role_id]);
            
            // Ajouter les nouvelles permissions
            if (!empty($permission_ids)) {
                $sql_insert = "INSERT INTO " . VEH_PREFIX . "role_permissions (role_id, permission_id) VALUES (?, ?)";
                $stmt_insert = $this->pdo->prepare($sql_insert);
                
                foreach ($permission_ids as $permission_id) {
                    $stmt_insert->execute([$role_id, $permission_id]);
                }
            }
            
            $this->pdo->commit();
            $this->clearCache();
            
            return true;
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur PermissionManager::assignPermissions: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Créer un nouveau rôle
     * @param string $nom Nom du rôle
     * @param string $description Description
     * @param string $couleur Couleur hexadécimale
     * @return int|false ID du rôle créé ou false
     */
    public function createRole($nom, $description = '', $couleur = '#6c757d') {
        try {
            $sql = "INSERT INTO " . VEH_PREFIX . "roles (nom, description, couleur, systeme) 
                    VALUES (?, ?, ?, 0)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nom, $description, $couleur]);
            
            return $this->pdo->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Erreur PermissionManager::createRole: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Modifier un rôle existant
     * @param int $role_id ID du rôle
     * @param string $nom Nouveau nom
     * @param string $description Nouvelle description
     * @param string $couleur Nouvelle couleur
     * @return bool
     */
    public function updateRole($role_id, $nom, $description = '', $couleur = '#6c757d') {
        try {
            $sql = "UPDATE " . VEH_PREFIX . "roles 
                    SET nom = ?, description = ?, couleur = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ? AND systeme = 0";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$nom, $description, $couleur, $role_id]);
            
            $this->clearCache();
            return $result;
            
        } catch (PDOException $e) {
            error_log("Erreur PermissionManager::updateRole: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un rôle (seulement les rôles non-système)
     * @param int $role_id ID du rôle
     * @return bool
     */
    public function deleteRole($role_id) {
        try {
            // Vérifier que ce n'est pas un rôle système
            $check_sql = "SELECT systeme FROM " . VEH_PREFIX . "roles WHERE id = ?";
            $check_stmt = $this->pdo->prepare($check_sql);
            $check_stmt->execute([$role_id]);
            $role = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$role || $role['systeme'] == 1) {
                return false; // Impossible de supprimer un rôle système
            }
            
            $this->pdo->beginTransaction();
            
            // Réassigner les utilisateurs au rôle conducteur par défaut
            $default_role_sql = "SELECT id FROM " . VEH_PREFIX . "roles WHERE nom = 'conducteur' LIMIT 1";
            $default_role_stmt = $this->pdo->query($default_role_sql);
            $default_role = $default_role_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($default_role) {
                $update_users_sql = "UPDATE " . VEH_PREFIX . "utilisateurs SET role_id = ? WHERE role_id = ?";
                $update_users_stmt = $this->pdo->prepare($update_users_sql);
                $update_users_stmt->execute([$default_role['id'], $role_id]);
            }
            
            // Supprimer le rôle (les permissions sont supprimées automatiquement via CASCADE)
            $delete_sql = "DELETE FROM " . VEH_PREFIX . "roles WHERE id = ?";
            $delete_stmt = $this->pdo->prepare($delete_sql);
            $delete_stmt->execute([$role_id]);
            
            $this->pdo->commit();
            $this->clearCache();
            
            return true;
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur PermissionManager::deleteRole: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer tous les rôles
     * @param bool $include_system Inclure les rôles système
     * @return array
     */
    public function getAllRoles($include_system = true) {
        try {
            $sql = "SELECT r.*, COUNT(u.id) as nb_utilisateurs
                    FROM " . VEH_PREFIX . "roles r
                    LEFT JOIN " . VEH_PREFIX . "utilisateurs u ON r.id = u.role_id AND u.actif = 1
                    WHERE r.actif = 1";
            
            if (!$include_system) {
                $sql .= " AND r.systeme = 0";
            }
            
            $sql .= " GROUP BY r.id ORDER BY r.systeme DESC, r.nom";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur PermissionManager::getAllRoles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer toutes les permissions disponibles
     * @return array Permissions groupées
     */
    public function getAllPermissions() {
        try {
            $sql = "SELECT * FROM " . VEH_PREFIX . "permissions 
                    WHERE actif = 1 
                    ORDER BY groupe, ordre, nom_affichage";
            
            $stmt = $this->pdo->query($sql);
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Grouper par catégorie
            $grouped = [];
            foreach ($permissions as $perm) {
                $groupe = $perm['groupe'] ?: 'Autres';
                $grouped[$groupe][] = $perm;
            }
            
            return $grouped;
            
        } catch (PDOException $e) {
            error_log("Erreur PermissionManager::getAllPermissions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Vider le cache des permissions
     */
    public function clearCache() {
        $this->cache = [];
    }
    
    /**
     * Vérifier si un utilisateur peut accéder à un module
     * @param int $user_id ID utilisateur
     * @param string $module Nom du module
     * @return bool
     */
    public function canAccessModule($user_id, $module) {
        return $this->hasPermission($user_id, $module, 'read');
    }
    
    /**
     * Récupérer les informations complètes d'un utilisateur avec son rôle
     * @param int $user_id ID utilisateur
     * @return array|null
     */
    public function getUserWithRole($user_id) {
        try {
            $sql = "SELECT u.*, r.nom as role_nom, r.description as role_description, 
                           r.couleur as role_couleur
                    FROM " . VEH_PREFIX . "utilisateurs u
                    LEFT JOIN " . VEH_PREFIX . "roles r ON u.role_id = r.id
                    WHERE u.id = ? AND u.actif = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur PermissionManager::getUserWithRole: " . $e->getMessage());
            return null;
        }
    }
}
?>