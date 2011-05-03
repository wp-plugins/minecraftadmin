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

/**
 * Configuration class for MinecraftAdmin
 *
 * @author Maximilien Bersoult <leoncx@gmail.com>
 * @package wp-minecraftAdmin
 * @subpackage MinecraftAdmin
 * @copyright 2011
 * @license GPL v2
 */
class MinecraftAdmin_Config
{
    static private $instance = null;
    
    /**
     * Default values with options
     * 
     * @var array
     */
    private $keys = array(
        'mca.enable.remotetoolkit' => 0,
    	'mca.enable.bukkittcp' => 0,
       	'mca.enable.dynmap' => 0,
        'mca.enable.permissions' => 0, 
        'mca.enable.whitelist' => 0,
        'mca.bukkittcp.host' => 'localhost',
        'mca.bukkittcp.port' => 6790,
        'mca.bukkittcp.pass' => 'password',
    	'mca.remotetoolkit.host' => 'localhost',
        'mca.remotetoolkit.port' => 25561,
        'mca.remotetoolkit.user' => 'user',
        'mca.remotetoolkit.pass' => 'pass',
        'mca.permissions.path' => ''
    ); 
    
    /**
     * Constructor
     */
    private function __construct()
    {
        /**
         * Add action to init the administration
         */
        add_action('admin-init', array(
            $this,
            'registerSettings'
        ));   
    }
    
    /**
     * Register the settings for this module
     */
    public function registerSettings()
    {
        foreach ($this->keys as $key => $default) {
            register_setting('minecraft-admin-options', $key);
        }
    }

    /**
     * Initialize the settings in activation
     * 
	 * @return bool
     */
    public function initSettings()
    {
        foreach ($this->keys as $key => $default) {
            if (false === add_option($key, $default)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Delete the settings in deactivation
     * 
	 * @return bool
     */
    public function deleteSettings()
    {
        foreach ($this->keys as $key => $default) {
            if (false === delete_option($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get a value
     * 
     * @param string $key The key of options
     * @return mixed The value or null if not exists
     */
    public function get($key)
    {
        if (isset($this->keys[$key])) {
            return get_option($key);    
        }
        return null;
    }
    
    /**
     * Get a value to boolean
     * 
     * @param string $key The key of options
     * @return bool The value, false if not exists
     */
    public function getBoolean($key)
    {
        if (!isset($this->keys[$key])) {
            return false;
        }
        $val = get_option($key);
        if ($val == 1) {
            return true;
        }
        return false;
    }
    
    /**
     * Update a value
     * 
     * @param string $key The key of options
     * @param mixed $value The value
     */
    public function set($key, $value = null)
    {
        if (isset($this->keys[$key]) && isset($value) && !is_null($value)) {
            update_option($key, $value);
        }
    }
    
    /**
     * Get instance of MinecraftAdmin
     * 
     * @return MinecraftAdmin_Config
     */
    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new MinecraftAdmin_Config();
        }
        return self::$instance;
    }
}