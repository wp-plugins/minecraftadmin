<?php
/*  Copyright 2011 Maximilien Bersoult  (email : leoncx@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once MCA_LIB . '/Bukkit/Whitelist.php';
require_once MCA_LIB . '/Bukkit/RemoteToolkit.php';
require_once MCA_LIB . '/Bukkit/Tcp.php';

/**
 * Class for MinecraftAdmin 
 * 
 * @author Maximilien Bersoult <leoncx@gmail.com>
 * @package wp-minecraftAdmin
 * @subpackage MinecraftAdmin
 * @copyright 2011
 * @license GPL v2
 */
class MinecraftAdmin
{
    static private $instance = null;
    
    private $page = 'minecraftadmin';

    /**
     * Constructor
     */
    private function __construct()
    {
        MinecraftAdmin_Config::getInstance();
    }

    /**
     * Run the module
     */
    public function run()
    {
        global $wpdb;
        
        /* Activate hook */
        register_activation_hook('minecraftadmin/minecraftadmin.php', array(
            $this,
            'activate'
        ));
        
        /* Deactivate hook */
        register_deactivation_hook('minecraftadmin/minecraftadmin.php', array(
            $this,
            'deactivate'
        ));
        
        /* Add the admin menu */
        add_action('admin_menu', array(
            $this,
            'admin_menu'
        ));
        
        /* Add CSS */
        if (is_admin()) {
            wp_register_style('mca_admin_css', WP_PLUGIN_URL . '/minecraftadmin/css/admin.css');
            wp_enqueue_style('mca_admin_css');
        } else {
            wp_register_style('mca_css', WP_PLUGIN_URL . '/minecraftadmin/css/mca.css');
            wp_enqueue_style('mca_css');
        }
        
        $config = MinecraftAdmin_Config::getInstance();
        
        /* Add information for users */
        if ($config->getBoolean('mca.enable.whitelist')) {
            $wl_mod = Bukkit_Whitelist::getInstance();
            $wl_mod->setDatabase($wpdb, 'wp');
            $wl_mod->setQueryList("SELECT username FROM " . $wpdb->prefix . "mca_whitelist");
            $wl_mod->setQueryGet("SELECT name FROM " . $wpdb->prefix . "mca_whitelist WHERE username = '<%USERNAME%>'");
            $wl_mod->setQueryAdd("INSERT INTO " . $wpdb->prefix . "mca_whitelist (username) VALUES ('<%USERNAME%>')");
            $wl_mod->setQueryDel("DELETE FROM " . $wpdb->prefix . "mca_whitelist WHERE username = '<%USERNAME%>'");
            
            add_action('show_user_profile', array($this, 'edit_profile'));
            add_action('edit_user_profile', array($this, 'edit_profile'));
            add_action('personal_options_update', array($this, 'save_profile'));
            add_action('edit_user_profile_update', array($this, 'save_profile'));
        }
        
        $widget = MinecraftAdmin_Widget::getInstance();
        /* Widget */
        add_action('init', array($widget, 'run'));
        if ($config->getBoolean('mca.enable.bukkittcp')) {
            add_action('widgets_init', array('MinecraftAdmin_Widget_PlayersOnline', 'load'));
            add_action('init', array($widget, 'players_online_handler'));
        }
        if ($config->getBoolean('mca.enable.remotetoolkit') && $config->getBoolean('mca.enable.bukkittcp')) {
            add_action('widgets_init', array('MinecraftAdmin_Widget_Status', 'load'));
            add_action('init', array($widget, 'status_handler'));
        }       
    }
    
    /**
     * Load the menu for administration 
     */
    public function admin_menu()
    {
        $pages = array();
        $config = MinecraftAdmin_Config::getInstance();
        add_menu_page('Minecraft', 'Minecraft', 'manage_options', 'minecraftadmin', array($this, 'admin_config_page'));
        $pages[] = add_submenu_page('minecraftadmin', 'Configuration | Minecraft', 'Configuration', 'manage_options', 'minecraftadmin', array($this, 'admin_config_page'));
        if ($config->getBoolean('mca.enable.remotetoolkit')) {
            $pages[] = add_submenu_page('minecraftadmin', 'RemoteToolkit | Minecraft', 'RemoteToolkit', 'manage_options', 'minecraftadmin-remotetoolkit', array($this, 'admin_config_page'));
        }
        if ($config->getBoolean('mca.enable.bukkittcp')) {
            $pages[] = add_submenu_page('minecraftadmin', 'Bukkit TCP | Minecraft', 'Bukkit TCP', 'manage_options', 'minecraftadmin-bukkittcp', array($this, 'admin_config_page'));
        }
        if ($config->getBoolean('mca.enable.whitelist')) {
            $pages[] = add_submenu_page('minecraftadmin', 'Whitelist | Minecraft', 'Whitelist', 'manage_options', 'minecraftadmin-whitelist', array($this, 'admin_config_page'));
        }
        if (current_user_can('manage_options')) {
            foreach ($pages as $page) {
                add_action('load-' . $page, array($this, 'load_config_page'));
            }
        }
    }
    
	/**
     * Load information for general configuration
     */
    public function load_config_page()
    {
        if (isset($_REQUEST['page'])) {
            
            $this->page = $_REQUEST['page'];
        }
        $config = MinecraftAdmin_Config::getInstance();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            switch ($this->page) {
                case 'minecraftadmin':
                    $config->set('mca.enable.remotetoolkit', isset($_POST['mca_enable_remotetoolkit']) ? 1 : 0);
                    $config->set('mca.enable.bukkittcp', isset($_POST['mca_enable_bukkittcp']) ? 1 : 0);
                    $config->set('mca.enable.whitelist', isset($_POST['mca_enable_whitelist']) ? 1 : 0);
                    break;
                case 'minecraftadmin-bukkittcp':
                    $config->set('mca.bukkittcp.host', $_POST['mca_bukkittcp_host']);
                    $config->set('mca.bukkittcp.port', $_POST['mca_bukkittcp_port']);
                    $config->set('mca.bukkittcp.pass', $_POST['mca_bukkittcp_pass']);
                    break;
                case 'minecraftadmin-remotetoolkit':
                    $config->set('mca.remotetoolkit.host', $_POST['mca_remotetoolkit_host']);
                    $config->set('mca.remotetoolkit.port', $_POST['mca_remotetoolkit_port']);
                    $config->set('mca.remotetoolkit.user', $_POST['mca_remotetoolkit_user']);
                    $config->set('mca.remotetoolkit.pass', $_POST['mca_remotetoolkit_pass']);
                    break;
                case 'minecraftadmin-whitelist':
                    $wl_mod = Bukkit_Whitelist::getInstance();
                    $listWlUser = $wl_mod->listUser();
                    $addWlUser = array();
                    foreach ($_POST['whitelist'] as $wluser) {
                        $mc_login = get_the_author_meta('mca_minecraft_login', $wluser);
                        if (!in_array($mc_login, $listWlUser)) {
                            $wl_mod->addUser($mc_login);
                        }
                        $addWlUser[] = $mc_login;
                    }
                    foreach ($listWlUser as $user) {
                        if (!in_array($user, $addWlUser)) {
                            $wl_mod->delUser($user);
                        }
                    }
                    break;
            }
        }        
    }
    
    /**
     * Display the page for general configuration
     */
    public function admin_config_page()
    {
        $config = MinecraftAdmin_Config::getInstance();
        switch ($this->page) {
            case 'minecraftadmin':
                include MCA_TMPL . '/admin/configuration.php';
                break;
            case 'minecraftadmin-bukkittcp':
                include MCA_TMPL . '/admin/config.bukkit.tcp.php';
                break;
            case 'minecraftadmin-remotetoolkit':
                include MCA_TMPL . '/admin/config.bukkit.remotetoolkit.php';
                break;
            case 'minecraftadmin-whitelist':
                $wpquery = new WP_User_query(array(
                        'fields' => array('ID', 'user_login'),
                        'meta_key' => 'mca_minecraft_login',
                        'meta_value' => '',
                        'meta_compare' => '!='
                    ));
                $list_users = $wpquery->get_results();
                $wl_mod = Bukkit_Whitelist::getInstance();
                $listWlUser = $wl_mod->listUser();
                include MCA_TMPL . '/admin/config.bukkit.whitelist.php';
                break;
        }
    }
    
    /**
     * Add form to the edit profile
     * 
     * @param WP_User $user The user
     */    
    public function edit_profile($user)
    {
        include MCA_TMPL . '/edit_profile.php';
    }
    
    /**
     * Save the extended information
     * 
     * @param int $user_id The user id
     */
    public function save_profile($user_id)
    {
        if (false === current_user_can('edit_user', $user_id)) {
            return false;
        }
        update_usermeta($user_id, 'mca_minecraft_login', $_POST['minecraft_login'] );
        return true;
    }
    
    /**
     * Activate the module
     */
    public function activate()
    {
        global $wpdb;
        $config = MinecraftAdmin_Config::getInstance();
        $config->initSettings();
        
        $tbl_name = $wpdb->prefix . "mca_whitelist";
        
        $sql = "CREATE TABLE " . $tbl_name . " (
        	username VARCHAR(255) NOT NULL,
        	PRIMARY KEY (username));";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        if($wpdb->get_var("show tables like '$tbl_name'") != $table_name) {
            dbDelta($sql);
            add_option('mca.dbversion', MCA_VERSION);
        } else {
            $version = get_option('mca.dbversion');
            if ($version != MCA_VERSION) {
                dbDelta($sql);
                update_option('mca.dbversion', MCA_VERSION);
            }
        }
    }
    
    /**
     * Deactivate the module
     */
    public function deactivate()
    {
        $config = MinecraftAdmin_Config::getInstance();
        $config->deleteSettings();

        delete_option('mca.dbversion');
    }

    /**
     * Get instance of MinecraftAdmin
     * 
     * @return MinecraftAdmin
     */
    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new MinecraftAdmin();
        }
        return self::$instance;
    }
}
?>