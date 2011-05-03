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
require_once MCA_LIB . '/Bukkit/Permissions.php';

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
        
        $version = get_option('mca.dbversion');
        if ($version != MCA_VERSION) {
            $this->update();
        }
        
        /* Add the admin menu */
        add_action('admin_menu', array(
            $this,
            'admin_menu'
        ));
        
        /* Add CSS */
        if (is_admin()) {
            wp_register_style('mca_admin_css', WP_PLUGIN_URL . '/minecraftadmin/css/admin.css');
            wp_enqueue_style('mca_admin_css');
            wp_register_script('jquery-ui-progressbar', WP_PLUGIN_URL . '/minecraftadmin/js/jquery.ui.progressbar.min.js', array('jquery', 'jquery-ui-core'), '1.8.11', true);
            wp_register_script('mca-js-adm-permissions', WP_PLUGIN_URL . '/minecraftadmin/js/config.permissions.js', array('jquery', 'jquery-ui-dialog', 'jquery-ui-progressbar'), MCA_VERSION, true);
            wp_enqueue_script('mca-js-adm-permissions');
            wp_enqueue_style('wp-jquery-ui-dialog');
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
            $wl_mod->setQueryGet("SELECT username FROM " . $wpdb->prefix . "mca_whitelist WHERE username = '<%USERNAME%>'");
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
        /* Ajax */
        if ($config->getBoolean('mca.enable.permissions')) {
            add_action('wp_ajax_permissions-pushfile', array($this, 'ajax'));
            add_action('wp_ajax_permissions-loadfile', array($this, 'ajax'));
            add_action('wp_ajax_permissions-loadworld', array($this, 'ajax'));
            add_action('wp_ajax_permissions-listrights', array($this, 'ajax'));
            add_action('wp_ajax_permissions-groupinfos', array($this, 'ajax'));
            add_action('wp_ajax_permissions-savegroup', array($this, 'ajax'));
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
        if ($config->getBoolean('mca.enable.permissions')) {
            $pages[] = add_submenu_page('minecraftadmin', 'Permissions | Minecraft', 'Permissions', 'manage_options', 'minecraftadmin-permissions', array($this, 'admin_config_page'));
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
                    $config->set('mca.enable.permissions', isset($_POST['mca_enable_permissions']) ? 1 : 0);
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
                case 'minecraftadmin-permissions':
                    $config->set('mca.permissions.path', $_POST['mca_permissions_path']);
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
            case 'minecraftadmin-permissions':
                if (!is_null($config->get('mca.permissions.path')) && trim($config->get('mca.permissions.path')) != '') {
                    $perms = Bukkit_Permissions::getInstance($config->get('mca.permissions.path'));
                    $worldList = $perms->getWorldsList();
                }
                include MCA_TMPL . '/admin/config.bukkit.permissions.php';
                break;
        }
    }
    
    /**
     * Ajax
     */
    public function ajax() {
        global $wpdb;
        switch ($_REQUEST['action']) {
            case 'permissions-pushfile':
                $input = fopen('php://input', 'r');
                $tmpfile = tmpfile();
                $realSize = stream_copy_to_stream($input, $tmpfile);
                fclose($input);
                
                if (!isset($_SERVER['CONTENT_LENGTH'])) {
                    echo json_encode(array('error' => "Can't get the size of file"));
                } elseif ($realSize != $_SERVER['CONTENT_LENGTH']) {
                    echo json_encode(array('error' => "Error during upload file"));
                } else {
                    fseek($tmpfile, 0);
                    $tmpfilename = tempnam(sys_get_temp_dir(), 'wpmca_');
                    file_put_contents($tmpfilename, fread($tmpfile, $realSize));
                    echo json_encode(array('success' => $tmpfilename));
                }
                exit();
                break;
            case 'permissions-loadfile':
                if ($this->loadFile($_POST['fname'])) {
                    echo json_encode(array('success' => true));
                } else {
                    echo json_encode(array('success' => false, 'error' => "Error loading file"));
                }
                exit();
                break;
            case 'permissions-loadworld':
                $config = MinecraftAdmin_Config::getInstance();
                $permissions = Bukkit_Permissions::getInstance($config->get('mca.permissions.path'));
                $permissions->loadWorld($_POST['world']);
                echo json_encode($permissions->getGroups());
                exit();
                break;
            case 'permissions-listrights':
                $query = "SELECT perm_name FROM " . $wpdb->prefix . "mca_permissions ORDER BY perm_name";
                $res = $wpdb->get_results($query, ARRAY_N);
                $list = array();
                if ($res !== false) {
                    foreach ($res as $right) {
                        $list[] = $right[0];
                    }
                }
                echo json_encode($list);
                exit();
                break;
            case 'permissions-groupinfos':
                $config = MinecraftAdmin_Config::getInstance();
                $permissions = Bukkit_Permissions::getInstance($config->get('mca.permissions.path'));
                $permissions->loadWorld($_POST['world']);
                $infos = $permissions->getGroupInfos($_POST['group']);
                /*
                 * Insert unknown permissions
                 */
                $query = "SELECT perm_name FROM " . $wpdb->prefix . "mca_permissions ORDER BY perm_name";
                $res = $wpdb->get_results($query, ARRAY_N);
                $listPerm = array();
                if ($res !== false) {
                    foreach ($res as $right) {
                        $listPerm[] = $right[0];
                    }
                }
                $query = "INSERT INTO " . $wpdb->prefix . "mca_permissions (perm_name, perm_desc) VALUES (%s, '')";
                foreach ($infos['permissions'] as $perm) {
                    if (!in_array($perm, $listPerm)) {
                        $tmpQuery = $wpdb->prepare($query, $perm);
                        $wpdb->query($tmpQuery);
                    }
                }
                if ($config->getBoolean('mca.enable.whitelist')) {
                    $infos['whitelist']['enable'] = true;
                    $whitelist = Bukkit_Whitelist::getInstance();
                    $infos['whitelist']['users'] = $whitelist->listUser();
                } else {
                    $infos['whitelist']['enable'] = false;
                }
                echo json_encode($infos);
                exit();
                break;
            case 'permissions-savegroup':
                $config = MinecraftAdmin_Config::getInstance();
                $permissions = Bukkit_Permissions::getInstance($config->get('mca.permissions.path'));
                $permissions->loadWorld($_POST['world']);
                if (false === $permissions->saveGroup($_POST['world'], $_POST['group'], json_decode(str_replace('\\', '', $_POST['users'])), json_decode(str_replace('\\', '', $_POST['inherite'])), json_decode(str_replace('\\', '', $_POST['rights'])))) {
                    echo json_encode(array('success' => false));
                } else {
                    echo json_encode(array('success' => true));
                }
                exit();
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

        /* Table for whitelist */
        $tbl_name_wl = $wpdb->prefix . "mca_whitelist";
        $sqlWl = "CREATE TABLE " . $tbl_name_wl . " (
        	username VARCHAR(255) NOT NULL,
        	PRIMARY KEY (username));";
        
        /* Table for permissions */
        $tbl_name_perm = $wpdb->prefix . "mca_permissions";
        $sqlPerm = "CREATE TABLE " . $tbl_name_perm . " (
        	perm_name VARCHAR(255) NOT NULL,
        	perm_desc TINYTEXT,
        	PRIMARY KEY (perm_name));";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $version = get_option('mca.dbversion');
        
        if($wpdb->get_var("show tables like '$tbl_name_wl'") != $tbl_name_wl) {
            dbDelta($sqlWl);
        }
        if($wpdb->get_var("show tables like '$tbl_name_perm'") != $tbl_name_perm) {
            dbDelta($sqlPerm);
        }
        add_option('mca.dbversion', MCA_VERSION);
    }
    
    /**
     * Deactivate the module
     */
    public function deactivate()
    {
        global $wpdb;
        $config = MinecraftAdmin_Config::getInstance();
        $config->deleteSettings();
        
        $tbl_name_wl = $wpdb->prefix . "mca_whitelist";
        $tbl_name_perm = $wpdb->prefix . "mca_permissions";
        
        $sql = array();
        if($wpdb->get_var("show tables like '$tbl_name_wl'") != $tbl_name_wl) {
            $sql[] = "DROP TABLE " . $tbl_name_wl;
        }
        if($wpdb->get_var("show tables like '$tbl_name_perm'") != $tbl_name_perm) {
            $sql[] = "DROP TABLE " . $tbl_name_perm;
        }
        
        foreach ($sql as $query) {
            $wpdb->query($query);
        }
        
        delete_option('mca.dbversion');
    }
    
    /**
     * Load a file with permissions informations
     * 
     * @param string $file The file path
     * @return bool
     */
    public function loadFile($file)
    {
        global $wpdb;
        if (false === file_exists($file)) {
            return false;
        }
        $fd = fopen($file, 'r');
        $query = "INSERT INTO " . $wpdb->prefix . "mca_permissions (perm_name, perm_desc) VALUES (%s, %s)";
        $ok = true;
        $tmpPerms = array();
        while ($line = fgets($fd)) {
            $info = explode(';', trim($line), 2);
            if (count($info) == 2) {
                $tmpPerms[] = $info[0];
                $tmpQuery = $wpdb->prepare($query, $info[0], $info[1]);
                $ret = $wpdb->query($tmpQuery);
                if ($ret === false) {
                    $ok = false;
                }                
            }
        }
        fclose($fd);
    	/*
         * Insert wildcard
         */
        $wc = array();
        foreach ($tmpPerms as $perm) {
            $tmpinfo = $perm;
            while (($pos = strrpos($tmpinfo, '.')) !== false) {
                $wc[] = substr($tmpinfo, 0, $pos + 1) . '*';
                $tmpinfo = substr($tmpinfo, 0, $pos);
            }
        }
        $wc = array_unique($wc);
        foreach ($wc as $perm) {
            $tmpQuery = $wpdb->prepare($query, $perm, "General rights for " . preg_replace('/\.\*$/', '', $perm));
            $ret = $wpdb->query($tmpQuery);
        }
        return $ok;
    }
    
    /**
     * Update the plugins
     */
    private function update()
    {
        global $wpdb;
        $config = MinecraftAdmin_Config::getInstance();
        $config->initSettings();

        /* Table for whitelist */
        $tbl_name_wl = $wpdb->prefix . "mca_whitelist";
        $sqlWl = "CREATE TABLE " . $tbl_name_wl . " (
        	username VARCHAR(255) NOT NULL,
        	PRIMARY KEY (username));";
        
        /* Table for permissions */
        $tbl_name_perm = $wpdb->prefix . "mca_permissions";
        $sqlPerm = "CREATE TABLE " . $tbl_name_perm . " (
        	perm_name VARCHAR(255) NOT NULL,
        	perm_desc TINYTEXT,
        	PRIMARY KEY (perm_name));";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        dbDelta($sqlWl);
        dbDelta($sqlPerm);
        update_option('mca.dbversion', MCA_VERSION);
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