<?php
/**
 * Barre latérale Protection Civile 64 - v2
 * VERSION COMPATIBLE avec structure d-flex de index2.php
 */

if (!defined('PROTEC64_V2')) {
    die('Accès interdit');
}

$current_user = $GLOBALS['current_user'] ?? get_current_user_data();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$request_uri = $_SERVER['REQUEST_URI'] ?? '';

// Menu simplifié selon votre structure actuelle (sans modules supprimés)
$menu_items = [
    [
        'title' => 'Tableau de bord',
        'url' => BASE_URL . '/index.php',
        'icon' => 'speedometer2',
        'active' => ($current_page === 'index'),
        'description' => 'Vue d\'ensemble'
    ]
];

// Ajouter admin si autorisé
if (is_admin() || is_admin_or_responsable()) {
    $menu_items[] = [
        'title' => 'Administration',
        'icon' => 'gear',
        'active' => (strpos($request_uri, '/admin') !== false),
        'description' => 'Gestion système',
        'submenu' => [
            [
                'title' => 'Gestion des rôles',
                'url' => BASE_URL . '/admin/roles.php',
                'icon' => 'shield-check'
            ],
            [
                'title' => 'Utilisateurs',
                'url' => BASE_URL . '/admin/utilisateurs.php',
                'icon' => 'people'
            ]
        ]
    ];
}
?>

<!-- Sidebar compatible avec votre structure d-flex exacte -->
<nav class="sidebar bg-primary text-white" style="width: 250px; min-height: calc(100vh - 76px);">
    <div class="p-3">
        <!-- Profil utilisateur - Style index2.php -->
        <?php if ($current_user): ?>
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
        <?php endif; ?>
        
        <!-- Navigation - Style index2.php -->
        <ul class="nav nav-pills flex-column">
            <?php foreach ($menu_items as $item): ?>
            <li class="nav-item mb-1">
                <?php if (isset($item['submenu'])): ?>
                <!-- Item avec sous-menu -->
                <a class="nav-link text-white d-flex align-items-center <?= $item['active'] ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" 
                   href="#submenu-<?= md5($item['title']) ?>" 
                   role="button">
                    <i class="bi bi-<?= $item['icon'] ?> me-2"></i>
                    <span class="flex-grow-1"><?= h($item['title']) ?></span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                
                <div class="collapse <?= $item['active'] ? 'show' : '' ?>" id="submenu-<?= md5($item['title']) ?>">
                    <ul class="nav nav-pills flex-column ms-3 mt-2">
                        <?php foreach ($item['submenu'] as $subitem): ?>
                        <li class="nav-item">
                            <a href="<?= $subitem['url'] ?>" 
                               class="nav-link text-white-50 py-1">
                                <i class="bi bi-<?= $subitem['icon'] ?> me-2"></i>
                                <?= h($subitem['title']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <?php else: ?>
                <!-- Item simple -->
                <a href="<?= $item['url'] ?>" 
                   class="nav-link text-white d-flex align-items-center <?= $item['active'] ? 'active' : '' ?>">
                    <i class="bi bi-<?= $item['icon'] ?> me-2"></i>
                    <span><?= h($item['title']) ?></span>
                </a>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <!-- Actions rapides - Style index2.php -->
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
                v<?= APP_VERSION ?? '2.0' ?> • En ligne
            </small>
        </div>
    </div>
</nav>