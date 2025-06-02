<?php
/**
 * Page de connexion Protection Civile 64 - v2
 * Style identique à l'ancien login.php - VERSION CORRIGÉE
 */

// Définir la constante de sécurité
define('PROTEC64_V2', true);

// Inclure les fichiers de base
require_once 'shared/includes/config.php';
require_once 'shared/includes/db.php';
require_once 'shared/includes/auth.php';
require_once 'shared/includes/utils.php';

// Rediriger si déjà connecté
if (is_logged_in()) {
    redirect(BASE_URL . '/index.php');
}

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['user_login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login)) {
        $error = 'Le champ identifiant/email est obligatoire.';
    } elseif (empty($password)) {
        $error = 'Le champ mot de passe est obligatoire.';
    } else {
        try {
            $db = db_connect();
            
            // Chercher l'utilisateur par email OU identifiant (comme votre ancien système)
            $query = $db->prepare("
                SELECT u.*, a.nom as antenne_nom 
                FROM " . DB_PREFIX . "utilisateurs u 
                LEFT JOIN " . DB_PREFIX . "antennes a ON u.antenne_id = a.id 
                WHERE (u.email = ? OR u.identifiant = ?) AND u.actif = 1
            ");
            $query->execute([$login, $login]);
            $user = $query->fetch();
            
            if ($user && password_verify($password, $user['mot_de_passe'])) {
                // Connexion réussie
                login_user($user);
                
                // Redirection vers la page demandée ou l'accueil
                $redirect_url = $_GET['redirect'] ?? BASE_URL . '/index.php';
                redirect($redirect_url);
            } else {
                $error = 'Identifiant/Email ou mot de passe incorrect.';
            }
            
        } catch (Exception $e) {
            // Log détaillé pour debug
            error_log("Erreur connexion v2: " . $e->getMessage());
            $error = 'Erreur de connexion : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Protection Civile 64</title>
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <link rel="shortcut icon" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(rgba(0, 64, 128, 0.8), rgba(240, 135, 0, 0.4)), 
                        url('<?php echo ASSETS_URL; ?>/images/fond_vps.jpg') center center/cover no-repeat fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }
        
        .login-box {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 45px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.6s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .logo img {
            width: 85px;
            height: 85px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0, 64, 128, 0.3);
        }
        
        .logo h1 {
            color: #004080;
            margin: 0 0 8px 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .logo p {
            color: #F08700;
            margin: 0;
            font-size: 15px;
            font-weight: 500;
            opacity: 0.9;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #004080;
            font-size: 14px;
        }
        
        input[type="text"], 
        input[type="email"], 
        input[type="password"] {
            width: 100%;
            padding: 15px 18px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        input:focus {
            border-color: #004080;
            outline: none;
            transform: translateY(-1px);
            box-shadow: 0 0 0 3px rgba(0, 64, 128, 0.1);
        }
        
        input::placeholder {
            color: #a0a6ac;
            font-size: 15px;
        }
        
        .btn {
            background: linear-gradient(135deg, #F08700 0%, #ff9500 100%);
            color: white;
            padding: 16px 20px;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(240, 135, 0, 0.4);
            letter-spacing: 0.3px;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #e07600 0%, #F08700 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(240, 135, 0, 0.5);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .error {
            background: linear-gradient(135deg, #ffe6e6 0%, #ffecec 100%);
            color: #c53030;
            padding: 15px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #e53e3e;
            font-size: 14px;
            font-weight: 500;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .info {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: #718096;
            line-height: 1.5;
        }
        
        .info a {
            color: #F08700;
            text-decoration: none;
            font-weight: 500;
        }
        
        .info a:hover {
            text-decoration: underline;
        }
        
        /* Responsive Design */
        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .login-box {
                padding: 35px 25px;
                border-radius: 15px;
            }
            
            .logo h1 {
                font-size: 22px;
            }
            
            .logo img {
                width: 70px;
                height: 70px;
            }
            
            input[type="text"], 
            input[type="email"], 
            input[type="password"] {
                padding: 12px 15px;
                font-size: 16px; /* Évite le zoom sur iOS */
            }
            
            .btn {
                padding: 14px 18px;
                font-size: 16px;
            }
        }
        
        /* Amélioration de l'accessibilité */
        @media (prefers-reduced-motion: reduce) {
            .login-box,
            .btn,
            input {
                animation: none;
                transition: none;
            }
        }
        
        /* États de focus pour l'accessibilité */
        .btn:focus-visible {
            outline: 2px solid #F08700;
            outline-offset: 2px;
        }
        
        /* Indicateur de chargement */
        .btn.loading {
            position: relative;
            color: transparent;
        }
        
        .btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <img src="<?php echo ASSETS_URL; ?>/images/logo-protection-civile.png" 
                     alt="Logo Protection Civile 64">
                <h1>Protection Civile</h1>
                <p>Pyrénées-Atlantiques (64)</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error">
                    <i class="error-icon">⚠️</i> 
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" id="loginForm">
                <div class="form-group">
                    <label for="user_login">Identifiant ou Email</label>
                    <input type="text" 
                           id="user_login" 
                           name="user_login" 
                           value="<?php echo htmlspecialchars($_POST['user_login'] ?? ''); ?>"
                           placeholder="Identifiant ou Email"
                           autocomplete="username"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Votre mot de passe"
                           autocomplete="current-password"
                           required>
                </div>
                
                <button type="submit" class="btn" id="loginBtn">
                    🔐 Se connecter
                </button>
            </form>
            
            <div class="info">
                Première connexion ? Contactez votre administrateur.<br>
                <strong>Assistance :</strong> <a href="mailto:contact@protectioncivile64.org">contact@protectioncivile64.org</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('loginBtn');
            
            // Animation de chargement lors de la soumission
            form.addEventListener('submit', function() {
                btn.classList.add('loading');
                btn.disabled = true;
                
                // Restaurer le bouton après 10 secondes au cas où
                setTimeout(() => {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }, 10000);
            });
            
            // Auto-focus sur le premier champ vide
            const userLoginInput = document.getElementById('user_login');
            const passwordInput = document.getElementById('password');
            
            if (!userLoginInput.value) {
                userLoginInput.focus();
            } else {
                passwordInput.focus();
            }
            
            // Amélioration UX : Enter sur le champ username passe au password
            userLoginInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && this.value.trim()) {
                    e.preventDefault();
                    passwordInput.focus();
                }
            });
            
            // Validation côté client basique
            function validateForm() {
                const login = userLoginInput.value.trim();
                const password = passwordInput.value;
                
                if (!login) {
                    showError('Veuillez saisir votre identifiant ou email');
                    userLoginInput.focus();
                    return false;
                }
                
                if (!password) {
                    showError('Veuillez saisir votre mot de passe');
                    passwordInput.focus();
                    return false;
                }
                
                if (password.length < 3) {
                    showError('Le mot de passe semble trop court');
                    passwordInput.focus();
                    return false;
                }
                
                return true;
            }
            
            function showError(message) {
                // Supprimer l'ancienne erreur s'il y en a une
                const oldError = document.querySelector('.error.client-error');
                if (oldError) {
                    oldError.remove();
                }
                
                // Créer la nouvelle erreur
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error client-error';
                errorDiv.innerHTML = '<i class="error-icon">⚠️</i> ' + message;
                
                // L'insérer avant le formulaire
                form.parentNode.insertBefore(errorDiv, form);
                
                // La supprimer après 5 secondes
                setTimeout(() => {
                    if (errorDiv.parentNode) {
                        errorDiv.remove();
                    }
                }, 5000);
            }
            
            // Validation avant soumission
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            });
            
            console.log('Login Protection Civile 64 v2 - Page chargée');
        });
    </script>
</body>
</html>