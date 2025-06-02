<?php
/**
 * Template Header - Protection Civile 64 v2
 * VERSION CSS SÉPARÉS
 */

if (!defined('PROTEC64_V2')) {
    die('Accès direct non autorisé');
}

$page_title = $page_title ?? 'Protection Civile 64';
$current_section = $current_section ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Système de gestion Protection Civile des Pyrénées-Atlantiques">
    <meta name="keywords" content="Protection Civile, 64, Pyrénées-Atlantiques, gestion, véhicules">
    <meta name="author" content="Protection Civile 64">
    
    <title><?php echo h($page_title); ?> - <?php echo APP_NAME ?? 'Protection Civile 64'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS SÉPARÉS -->
    <link href="<?php echo ASSETS_URL; ?>/css/main.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/sidebar.css" rel="stylesheet">
    
    <!-- CSS spécifique à la page -->
    <?php if (isset($additional_css) && is_array($additional_css)): ?>
        <?php foreach ($additional_css as $css_file): ?>
            <link href="<?php echo ASSETS_URL; ?>/css/<?php echo $css_file; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>