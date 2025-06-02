<?php
/**
 * GESTIONPC64 V2 - AJAX - Récupération des permissions d'un rôle
 * Fichier: admin/ajax/get_role_permissions.php
 */

define('PROTEC64_V2', true);

require_once '../../shared/includes/config.php';
require_once '../../shared/includes/db.php';
require_once '../../shared/includes/auth.php';
require_once '../../shared/includes/PermissionManager.php';
require_once '../../shared/includes/AuthMiddleware.php';

// Vérifier l'authentification et les permissions
if (!is_logged_in() || !checkPermission('roles', 'read', false)) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$role_id = (int)($_POST['role_id'] ?? 0);

if ($role_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de rôle invalide']);
    exit;
}

try {
    $permissionManager = new PermissionManager(db_connect());
    $permissions = $permissionManager->getRolePermissions($role_id);
    
    // Retourner seulement les IDs des permissions
    $permission_ids = array_column($permissions, 'id');
    
    header('Content-Type: application/json');
    echo json_encode($permission_ids);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>