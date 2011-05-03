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

require_once MCA_LIB . '/spyc.php';

/**
 * Class for manage permissions for Minecraft command
 * 
 * See the Permissions : http://forums.bukkit.org/threads/admn-dev-permissions-v2-5-4-phoenix-now-with-real-multiworld-permissions-556.5974/
 * 
 * @author Maximilien Bersoult <leoncx@gmail.com>
 * @package wp-minecraftAdmin
 * @subpackage Bukkit
 * @license GPL v2
 * @copyright 2011
 */
class Bukkit_Permissions
{
    private $path;
    private $worlds = array();
    private $worldperms = array();
    static private $instance = null;
    
    /**
     * Constructor
     * 
     * @param string $path The path with permissions files
     */
    private function __construct($path)
    {
        $this->path = realpath($path);
        $this->loadWorlds();
    }
    
    /**
     * Get the list of worlds
     * 
     * @return array
     */
    public function getWorldsList()
    {
        return $this->worlds;
    }
    
    /**
     * Load a world permissions
     * 
     * @param string $world The world name
     * @return bool
     */
    public function loadWorld($world)
    {
        $filename = $this->path . '/' . $world . '.yml';
        if (!file_exists($filename)) {
            return false;
        }
        $this->worldperms = Spyc::YAMLLoad($filename);
        return true;
    }
    
    
    /**
     * Get the list of groups
     *
     * @return array
     */
    public function getGroups()
    {
        if (!isset($this->worldperms['groups'])) {
            return array();
        }
        return array_keys($this->worldperms['groups']);
    }
    
    /**
     * Get the rights and inheritance for a group
     * 
     * @param string $group The group name
     * @return array
     */
    public function getGroupInfos($group)
    {
        $infos = array();
        $infos['permissions'] = array();
        $infos['inheritance'] = array();
        $infos['users'] = array();
        if (isset($this->worldperms['groups'][$group]) && isset($this->worldperms['groups'][$group]['permissions'])) {
            $infos['permissions'] = $this->worldperms['groups'][$group]['permissions'];
        }
        if (isset($this->worldperms['groups'][$group]) && isset($this->worldperms['groups'][$group]['inheritance'])) {
            $infos['inheritance'] = $this->worldperms['groups'][$group]['inheritance'];
        }
        $infos['users'] = $this->getUsers($group);
        return $infos;
    }
    
    /**
     * Save a group in permissions file
     * 
     * @param string $world The world
     * @param string $group The group
     * @param array $users The list of users for this group
     * @param array $inherite Inheritable group
     * @param array $rights Permissions
     * @return bool
     */
    public function saveGroup($world, $group, $users, $inherite, $rights)
    {
        if (!isset($this->worldperms['groups'][$group])) {
            $this->worldperms['groups'][$group] = array();
            $this->worldperms['groups'][$group]['default'] = false;
            $this->worldperms['groups'][$group]['info'] = array('prefix' => '', 'suffix' => '', 'build' => false);
        }
        if (is_array($rights) && count($rights) != 0) {
            $this->worldperms['groups'][$group]['permissions'] = $rights;
        } else {
            $this->worldperms['groups'][$group]['permissions'] = null;
        }
        if (is_array($inherite) && count($inherite) != 0) {
            $this->worldperms['groups'][$group]['inheritance'] = $inherite;
        } else {
            $this->worldperms['groups'][$group]['inheritance'] = null;
        }
        $this->setUsers($users, $group);
        
        $filename = $this->path . '/' . $world . '.yml';
        if (false === file_put_contents($filename, Spyc::YAMLDump($this->worldperms, 4))) {
            return false;
        }
        return true;
    }
    
    /**
     * Load world file
     */
    private function loadWorlds()
    {
        $listFiles = glob($this->path . '/*.yml');
        foreach ($listFiles as $file) {
            $this->worlds[] = basename(basename($file), '.yml');
        }        
    }
    
    /**
     * Get the list of users for a group
     *
     * @param string $group The group name
     * @return array
     */
    private function getUsers($group)
    {
        if (!isset($this->worldperms['users'])) {
            return array();
        }
        $users = array();
        foreach ($this->worldperms['users'] as $user => $userInfos) {
            if (isset($userInfos['group']) && $userInfos['group'] == $group) {
                $users[] = $user; 
            }
        }
        return $users;
    }
    
    /**
     * Set group to list of users
     * 
     * @param array $users The list of users
     * @param string $group The group name
     */
    private function setUsers($users, $group)
    {
        foreach ($this->worldperms['users'] as $user => $userInfos) {
            if (isset($userInfos['group']) && $userInfos['group'] == $group) {
                 $this->worldperms['users'][$user]['group'] = null;
            }
        }
        foreach ($users as $user) {
            if (isset($this->worldperms['users'][$user])) {
                $this->worldperms['users'][$user]['group'] = $group;
            } else {
                $this->worldperms['users'][$user] = array('group' => $group, 'permissions' => null, 'info' => array('prefix' => '', 'suffix' => ''));
            }
        }
    }
    
    /**
     * Get a instance of Bukkit_Permissions
     * 
     * @param string $path The path with permissions files
     * @return Bukkit_Permissions
     */
    static public function getInstance($path = '')
    {
        if (is_null(self::$instance) && (!isset($path) || trim($path) == '' || !is_dir($path))) {
            return null;
        }
        if (is_null(self::$instance)) {
            self::$instance = new Bukkit_Permissions($path);
        }
        return self::$instance;
    }
}
?>