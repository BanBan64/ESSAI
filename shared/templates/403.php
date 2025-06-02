<?php
/**
 * GESTIONPC64 V2 - Page 403 Accès refusé
 * Fichier: shared/templates/403.php
 * 
 * Template pour les erreurs de permissions
 */

defined('PROTEC64_V2') or exit('Accès refusé');

// S'assurer que les constantes sont chargées
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../includes/config.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé - GestionPC64</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --pc-blue: #004080;
            --pc-orange: #F08700;
        }
        
        body {
            background: linear-gradient(135deg, var(--pc-blue) 0%, #0056b3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
            position: relative;
            overflow: hidden;
        }
        
        .error-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--pc-orange), #ff9500);
        }
        
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: var(--pc-blue);
            line-height: 1;
            margin-bottom: 1rem;
        }
        
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .error-message {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .btn-home {
            background: var(--pc-blue);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-home:hover {
            background: var(--pc-orange);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(240, 135, 0, 0.3);
        }
        
        .contact-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
            color: #888;
        }
        
        .contact-info i {
            color: var(--pc-orange);
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <div class="error-code">403</div>
        
        <h1 class="error-title">Accès refusé</h1>
        
        <p class="error-message">
            Vous n'avez pas les permissions nécessaires pour accéder à cette ressource.<br>
            Cette action nécessite des droits spécifiques qui ne vous ont pas été attribués.
        </p>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary btn-home">
                <i class="fas fa-home me-2"></i>
                Retour au tableau de bord
            </a>
            
            <?php if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && 
                      strpos($_SERVER['HTTP_REFERER'], BASE_URL) !== false): ?>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Page précédente
            </a>
            <?php endif; ?>
        </div>
        
        <div class="contact-info">
            <div class="mb-2">
                <i class="fas fa-info-circle"></i>
                Si vous pensez qu'il s'agit d'une erreur, contactez votre administrateur.
            </div>
            
            <?php if (isset($current_user) && $current_user): ?>
            <div class="text-muted small">
                Utilisateur connecté : <strong><?= htmlspecialchars(($current_user['prenom'] ?? '') . ' ' . ($current_user['nom'] ?? '')) ?></strong><br>
                Rôle : <strong><?= htmlspecialchars($current_user['role'] ?? 'Non défini') ?></strong>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php
    // Logger la tentative d'accès non autorisé
    if (function_exists('logUserAction')) {
        $requested_url = $_SERVER['REQUEST_URI'] ?? 'URL inconnue';
        logUserAction(
            'Tentative d\'accès non autorisé',
            'security',
            "URL demandée: $requested_url"
        );
    }
    ?>
</body>
</html>