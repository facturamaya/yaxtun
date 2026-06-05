<?php
/**
 * NexoERP Premium — Sistema Empresarial Inteligente
 * Compatible: WordPress 5.8+, PHP 7.4+, Hostinger
 * 
 * Plugin Name: NexoERP Premium
 * Plugin URI:  https://nexoerp.dev
 * Description: ERP empresarial completo con IA, auditoría, exportación y permisos granulares
 * Version:     2.0.0-premium
 * Author:      Yaxtun Soluciones Digitales
 * Author URI:  https://facturamaya.com.mx
 * License:     GPLv2 or later
 * Text Domain: nexoerp-premium
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.8
 */

if (!defined('ABSPATH')) exit;

/* ═══════════════════════════════════════════════════
   CORE CONSTANTS & LOADER
   ═══════════════════════════════════════════════════ */
define('NXERP_PREMIUM_VERSION', '2.0.0-premium');
define('NXERP_PREMIUM_DIR', plugin_dir_path(__FILE__));
define('NXERP_PREMIUM_URL', plugin_dir_url(__FILE__));
define('NXERP_PREMIUM_DB_VER', 20);
define('NXERP_PREMIUM_PREFIX', 'nxerp_');

/* Load classes */
require_once NXERP_PREMIUM_DIR . 'includes/class-installer.php';
require_once NXERP_PREMIUM_DIR . 'includes/class-permissions.php';
require_once NXERP_PREMIUM_DIR . 'includes/class-audit.php';
require_once NXERP_PREMIUM_DIR . 'includes/class-ai.php';
require_once NXERP_PREMIUM_DIR . 'includes/class-export.php';
require_once NXERP_PREMIUM_DIR . 'includes/class-validator.php';
require_once NXERP_PREMIUM_DIR . 'includes/class-api.php';

/* ═══════════════════════════════════════════════════
   INITIALIZATION
   ═══════════════════════════════════════════════════ */
register_activation_hook(__FILE__, array('NXERP_Installer', 'activate'));
register_deactivation_hook(__FILE__, array('NXERP_Installer', 'deactivate'));
register_uninstall_hook(__FILE__, array('NXERP_Installer', 'uninstall'));

add_action('init', function() {
    /* Session */
    if (!session_id() && !headers_sent()) {
        session_start(['cookie_httponly' => true, 'cookie_secure' => is_ssl(), 'cookie_samesite' => 'Lax']);
    }
    
    /* Check DB version */
    if (get_option('nxerp_db_version') !== NXERP_PREMIUM_DB_VER) {
        NXERP_Installer::activate();
    }
});

/* Shortcode */
add_shortcode('nexoerp', function($atts) {
    if (!is_user_logged_in()) {
        return '<div style="padding:40px;text-align:center"><h2>Inicia sesion</h2><p><a href="' . esc_url(wp_login_url(get_permalink())) . '">Login</a></p></div>';
    }
    ob_start();
    include NXERP_PREMIUM_DIR . 'templates/app.php';
    return ob_get_clean();
});

/* Admin menu */
add_action('admin_menu', function() {
    add_options_page('NexoERP', 'NexoERP', 'manage_options', 'nexoerp', function() {
        echo '<div class="wrap"><h1>NexoERP Premium</h1><p>V' . NXERP_PREMIUM_VERSION . '</p></div>';
    });
});

/* AJAX router */
add_action('wp_ajax_nxerp_api', array('NXERP_API', 'router'));
add_action('wp_ajax_nopriv_nxerp_api', array('NXERP_API', 'router'));
