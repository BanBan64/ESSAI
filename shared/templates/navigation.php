<?php
/**
 * Template Navigation - Protection Civile 64 v2
 * VERSION CORRIGÉE avec bouton sidebar
 */

if (!defined('PROTEC64_V2')) {
    die('Accès direct non autorisé');
}

$safe_user = $GLOBALS['current_user'] ?? null;
if (!is_array($safe_user)) {
    $safe_user = [
        'id' => 0,
        'nom' => 'Utilisateur',
        'prenom' => 'Inconnu',
        'role' => 'guest',
        'antenne_nom' => 'Aucune antenne'
    ];
}

$current_section = $current_section ?? '';
?>

<!-- Navigation principale - CORRIGÉE avec bouton sidebar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <!-- Logo et titre -->
        <a class="navbar-brand d-flex align-items-center text-white" href="<?php echo BASE_URL; ?>/index.php">
            <img src="<?php echo ASSETS_URL; ?>/images/logo-protection-civile.png" 
                 alt="Logo" 
                 width="40" 
                 height="40" 
                 class="me-2">
            <span class="fw-bold">Protection Civile 64</span>
        </a>

        <!-- Bouton toggle pour sidebar mobile - CORRIGÉ -->
        <button class="navbar-toggler d-lg-none" 
                type="button" 
                onclick="showSidebar()" 
                aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Bouton toggle pour navbar mobile -->
        <button class="navbar-toggler d-lg-none ms-2" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" 
                aria-controls="navbarNav" 
                aria-expanded="false" 
                aria-label="Toggle navigation">
            <i class="bi bi-three-dots-vertical"></i>
        </button>

        <!-- Menu principal -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_section === 'dashboard' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>/index.php">
                        <i class="bi bi-speedometer2 me-1"></i>
                        Accueil
                    </a>
                </li>
                
                <!-- Administration (si autorisé) -->
                <?php if (is_admin() || is_admin_or_responsable()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_section === 'admin' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>/admin/roles.php">
                        <i class="bi bi-gear me-1"></i>
                        Administration
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Menu utilisateur -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" 
                       href="#" 
                       id="navbarUserDropdown" 
                       role="button" 
                       data-bs-toggle="dropdown" 
                       aria-expanded="false">
                        <i class="bi bi-person-circle me-2"></i>
                        <span class="d-none d-md-inline">
                            <?php echo h($safe_user['prenom'] ?? 'Utilisateur'); ?> 
                            <?php echo h($safe_user['nom'] ?? ''); ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <div class="dropdown-header">
                                <div class="fw-bold">
                                    <?php echo h($safe_user['prenom'] ?? 'Utilisateur'); ?> 
                                    <?php echo h($safe_user['nom'] ?? ''); ?>
                                </div>
                                <div class="small text-muted">
                                    <?php echo h(ucfirst($safe_user['role'] ?? 'guest')); ?> - 
                                    <?php echo h($safe_user['antenne_nom'] ?? 'Aucune antenne'); ?>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>/profil.php">
                                <i class="bi bi-person me-2"></i>
                                Mon profil
                            </a>
                        </li>
                        <?php if (is_admin() || is_admin_or_responsable()): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/utilisateurs.php">
                                <i class="bi bi-people me-2"></i>
                                Gestion utilisateurs
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/roles.php">
                                <i class="bi bi-shield-check me-2"></i>
                                Gestion des rôles
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Déconnexion
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>