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

require_once MCA_LIB . '/MinecraftAdmin/Widget/PlayersOnline.php';
require_once MCA_LIB . '/MinecraftAdmin/Widget/Status.php';

/**
 * Class for manage widget
 * 
 * @author Maximilien Bersoult <leoncx@gmail.com>
 * @package wp-minecraftAdmin
 * @subpackage MinecraftAdmin
 * @license GPL v2
 * @copyright 2011
 */
class MinecraftAdmin_Widget
{
    static private $instance = null;
    
    /**
     * Constructor
     */
    private function __construct()
    {
    }
        
    /**
     * Run the widget loader
     */
    public function run()
    {
        $config = MinecraftAdmin_Config::getInstance();
        if ($config->getBoolean('mca.enable.bukkittcp')) {
            if (!is_admin()) {
                wp_register_script('mca-js-players-online', WP_PLUGIN_URL . '/minecraftadmin/js/players_online.js', array('jquery'), MCA_VERSION, true);
                wp_enqueue_script('mca-js-players-online');
            }
        }
        if ($config->getBoolean('mca.enable.bukkittcp') && $config->getBoolean('mca.enable.remotetoolkit')) {
            if (!is_admin()) {
                wp_register_script('mca-js-status', WP_PLUGIN_URL . '/minecraftadmin/js/status.js', array('jquery'), MCA_VERSION, true);
                wp_enqueue_script('mca-js-status');
            }
        }
    }

    /**
     * Handler for ajax action to players online widget
     */
    public function players_online_handler()
    {
        if (isset($_POST['mca_action']) && $_POST['mca_action'] == 'players_online') {
            $config = MinecraftAdmin_Config::getInstance();
            $bukkittcp = Bukkit_Tcp::getInstance($config->get('mca.bukkittcp.host'), $config->get('mca.bukkittcp.port'), $config->get('mca.bukkittcp.pass'));
            echo json_encode($bukkittcp->getListPlayers());
            exit();
        }
    }
    
    /**
     * Handler for ajax action to status widget
     */
    public function status_handler()
    {
        if (isset($_POST['mca_action']) && $_POST['mca_action'] == 'status') {
            $data = array();
            $config = MinecraftAdmin_Config::getInstance();
            $bukkittcp = Bukkit_Tcp::getInstance($config->get('mca.bukkittcp.host'), $config->get('mca.bukkittcp.port'), $config->get('mca.bukkittcp.pass'));
            $maxPlayers = $bukkittcp->maxPlayers();
            if ($maxPlayers == 0) {
                $data['server'] = plugins_url('img/offline.png', 'minecraftadmin/minecraftadmin.php');
                $data['max_players'] = "";
            } else {
                $data['server'] = plugins_url('img/online.png', 'minecraftadmin/minecraftadmin.php');
                $data['max_players'] = $maxPlayers;
            }
            $remotetoolkit = Bukkit_RemoteToolkit::getInstance($config->get('mca.remotetoolkit.host'), $config->get('mca.remotetoolkit.port'), $config->get('mca.remotetoolkit.user'), $config->get('mca.remotetoolkit.pass'));
            $manager_version = $remotetoolkit->getVersion();
            if ($manager_version === false || trim($manager_version) == '') {
                $data['manager'] = plugins_url('img/offline.png', 'minecraftadmin/minecraftadmin.php');
                $data['manager_version'] = "";
            } else {
                $data['manager'] = plugins_url('img/online.png', 'minecraftadmin/minecraftadmin.php');
                $data['manager_version'] = $manager_version;
            }
            echo json_encode($data);
            exit();
        }
    }
    
    /**
     * Get instance of Widget
     * 
     * @return MinecraftAdmin_Widget
     */
    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new MinecraftAdmin_Widget();
        }
        return self::$instance;
    }
}
?>