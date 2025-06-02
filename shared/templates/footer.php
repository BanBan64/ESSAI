<?php
/**
 * Template Footer - Protection Civile 64 v2
 * VERSION CSS SÉPARÉS - Design identique à index2.php
 */

// Vérifier que la constante de sécurité est définie
if (!defined('PROTEC64_V2')) {
    die('Accès direct non autorisé');
}

// Récupérer l'utilisateur actuel de manière sécurisée
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

// Variables d'environnement
$is_debug = defined('DEBUG') && DEBUG === true;
$app_version = defined('APP_VERSION') ? APP_VERSION : 'v2.0';
?>

<!-- Footer - EXACTEMENT comme index2.php mais sans CSS intégré -->
<footer class="bg-primary text-white border-top mt-5">
    <div class="container-fluid">
        <div class="row py-4">
            
            <!-- Informations système -->
            <div class="col-md-4">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?php echo ASSETS_URL; ?>/images/logo-protection-civile.png" 
                         alt="Logo Protection Civile 64" 
                         width="40" 
                         height="40" 
                         class="me-3 footer-logo">
                    <h6 class="text-white fw-bold mb-0">
                        Protection Civile 64
                    </h6>
                </div>
                <p class="text-light small mb-2">
                    Système de gestion unifié pour les bénévoles de la Protection Civile 
                    des Pyrénées-Atlantiques.
                </p>
                <div class="text-light small">
                    <i class="bi bi-geo-alt me-1"></i>
                    Pyrénées-Atlantiques (64)
                </div>
            </div>
            
            <!-- Navigation rapide - SIMPLIFIÉE (sans modules supprimés) -->
            <div class="col-md-4">
                <h6 class="text-white fw-bold mb-3">
                    <i class="bi bi-list-ul me-2"></i>
                    Navigation
                </h6>
                <ul class="list-unstyled small">
                    <li class="mb-1">
                        <a href="<?php echo BASE_URL; ?>/index.php" class="text-decoration-none text-light">
                            <i class="bi bi-house me-1"></i> Accueil
                        </a>
                    </li>
                    <?php if (is_admin() || is_admin_or_responsable()): ?>
                    <li class="mb-1">
                        <a href="<?php echo BASE_URL; ?>/admin/roles.php" class="text-decoration-none text-light">
                            <i class="bi bi-gear me-1"></i> Administration
                        </a>
                    </li>
                    <?php endif; ?>
                    <!-- NOTE: Modules futurs seront ajoutés ici -->
                </ul>
            </div>
            
            <!-- Informations utilisateur - EXACTEMENT comme index2.php -->
            <div class="col-md-4">
                <h6 class="text-white fw-bold mb-3">
                    <i class="bi bi-person-circle me-2"></i>
                    Informations utilisateur
                </h6>
                <div class="small text-light">
                    <div class="mb-1">
                        <i class="bi bi-person me-1"></i>
                        <strong>
                            <?php echo h($safe_user['prenom'] ?? 'Utilisateur'); ?> 
                            <?php echo h($safe_user['nom'] ?? ''); ?>
                        </strong>
                    </div>
                    <div class="mb-1">
                        <i class="bi bi-shield me-1"></i>
                        <?php echo h(ucfirst($safe_user['role'] ?? 'guest')); ?>
                        <?php if (!empty($safe_user['antenne_nom']) && $safe_user['antenne_nom'] !== 'Aucune antenne'): ?>
                         - <?php echo h($safe_user['antenne_nom']); ?>
                        <?php endif; ?>
                    </div>
                    <div class="mb-1">
                        <i class="bi bi-clock me-1"></i>
                        <?php echo date('d/m/Y H:i:s'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ligne de copyright - EXACTEMENT comme index2.php -->
        <div class="row border-top py-3">
            <div class="col-md-8">
                <div class="text-light small">
                    © <?php echo date('Y'); ?> Protection Civile des Pyrénées-Atlantiques. 
                    Tous droits réservés.
                    <?php if ($is_debug): ?>
                    <span class="badge bg-warning text-dark ms-2">
                        <i class="bi bi-bug me-1"></i>MODE DEBUG
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="text-light small">
                    <i class="bi bi-gear me-1"></i>
                    Système <?php echo h($app_version); ?>
                </div>
            </div>
        </div>
        
        <?php if ($is_debug): ?>
        <!-- Debug exactement comme index2.php -->
        <div class="row border-top py-2 bg-warning bg-opacity-10">
            <div class="col">
                <details class="small">
                    <summary class="text-warning fw-bold cursor-pointer">
                        <i class="bi bi-bug me-1"></i>
                        Informations de debug
                    </summary>
                    <div class="mt-2 text-muted">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Utilisateur:</strong><br>
                                ID: <?php echo (int)($safe_user['id'] ?? 0); ?><br>
                                Rôle: <?php echo h($safe_user['role'] ?? 'N/A'); ?><br>
                                Antenne: <?php echo h($safe_user['antenne_nom'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Système:</strong><br>
                                PHP: <?php echo PHP_VERSION; ?><br>
                                Mémoire: <?php echo ini_get('memory_limit'); ?><br>
                                Temps: <?php echo number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2); ?>ms
                            </div>
                        </div>
                    </div>
                </details>
            </div>
        </div>
        <?php endif; ?>
    </div>
</footer>

<!-- JavaScript footer - EXACTEMENT comme index2.php -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des liens du footer
    const footerLinks = document.querySelectorAll('footer a');
    footerLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.paddingLeft = '5px';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.paddingLeft = '0';
        });
    });
    
    // Debug console log (uniquement en mode debug)
    <?php if ($is_debug): ?>
    console.log('Footer chargé - Mode DEBUG actif');
    console.log('Utilisateur:', {
        id: <?php echo (int)($safe_user['id'] ?? 0); ?>,
        role: '<?php echo h($safe_user['role'] ?? 'N/A'); ?>',
        antenne: '<?php echo h($safe_user['antenne_nom'] ?? 'N/A'); ?>'
    });
    <?php endif; ?>
});
</script>