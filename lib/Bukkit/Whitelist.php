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
 * Class for manage whitelist users
 * 
 * See the Bukkit_Tcp : http://forums.bukkit.org/threads/admin-dev-tcp-interface-for-bukkit-v1-2-440.746/
 * 
 * Set the configuration with MySQL
 * 
 * @author Maximilien Bersoult <leoncx@gmail.com>
 * @package wp-minecraftAdmin
 * @subpackage Bukkit
 * @license GPL v2
 * @copyright 2011
 */
class Bukkit_Whitelist
{
    static private $instance = null;
    
    private $dbObj;
    private $dbClassType;
    private $queryList;
    private $queryGet;
    private $queryAdd;
    private $queryDel;
    
    /**
     * Constructor
     */
    private function __construct()
    {
        $this->queryList = "SELECT name FROM tbl_names";
        $this->queryGet = "SELECT name FROM tbl_names WHERE name = '<%USERNAME%>'";
        $this->queryAdd = "INSERT INTO tbl_names (name) VALUES ('<%USERNAME%>')";
        $this->queryDel = "DELETE FROM tbl_names WHERE name = '<%USERNAME%>'";
    }
    
    /**
     * Set the database object
     * 
     * @param mixed $dbObj The database object
     * @param string $dbClassType The type of object
     */
    public function setDatabase($dbObj, $dbClassType = 'PDO')
    {
        $this->dbObj = $dbObj;
        $this->dbClassType = $dbClassType;
    }
    
    /**
     * Set the query for get list of users
     * 
     * @param string $query The query
     */
    public function setQueryList($query)
    {
        $this->queryList = $query;
    }
    
    /**
     * Set the query for get a user
     * 
     * @param string $query The query
     */
    public function setQueryGet($query)
    {
        $this->queryGet = $query;
    }
    
    /**
     * Set the query for add a user
     * 
     * @param string $query The query
     */
    public function setQueryAdd($query)
    {
        $this->queryAdd = $query;
    }
    
    /**
     * Set the query for delete a user
     * 
     * @param string $query The query
     */
    public function setQueryDel($query)
    {
        $this->queryDel = $query;
    }
    
    /**
     * Get the list of users in whitelist
     * 
     * @return array
     */
    public function listUser()
    {
        $res = $this->execQuery($this->queryList, '', true);
        if (is_null($res)) {
            return array();
        }
        $users = array();
        foreach ($res as $user) {
            $users[] = $user[0];
        }
        return $users;
    }
    
    /**
     * Get a user from whitelist
     * 
     * @param string $user The user login
     * @return string Empty if not in whitelist
     */
    public function getUser($user)
    {
        $res = $this->execQuery($this->queryGet, $user, true);
        if (is_null($res)) {
            return '';
        }
        return $res[0][0];
    }
    
    /**
     * Add a user to the whitelist
     * 
     * @param string $user The user login
     * @return bool
     */
    public function addUser($user)
    {
        $res = $this->execQuery($this->queryAdd, $user, false);
        if (is_null($res)) {
            return false;
        }
        return true;
    }
    
    /**
     * Delete a user from whitelist
     * 
     * @param string $user The user login
     * @return bool
     */
    public function delUser($user)
    {
        $res = $this->execQuery($this->queryDel, $user, false);
        if (is_null($res)) {
            return false;
        }
        return true;
    }
    
    /**
     * Execute a query
     * 
     * @param string $query The query
     * @param string $user The username
     * @param bool $result If return lines
     * @return mixed Array if return lines, or bool
     */
    private function execQuery($query, $user, $result = false)
    {
        $query = str_replace('<%USERNAME%>', $user, $query);
        if ($this->dbClassType == 'PDO') {
            try {
                $res = $this->dbObj->query($query);
                if ($result) {
                    return $res->fetchAll(PDO::FETCH_NUM);
                } else {
                    return true;
                }
            } catch (PDOException $e) {
                return null;
            }
        } elseif ($this->dbClassType == 'wp') {
            if ($result) {
                $res = $this->dbObj->get_results($query, ARRAY_N);
                if ($res === false) {
                    return null;
                }
                return $res;
            } else {
                $res = $this->dbObj->query($query);
                if ($res === false) {
                    return null;
                }
                return true;
            }
        } else {
            return null;
        }
    }

    /**
     * Get a instance of Bukkit_Whitelist
     * 
     * @return Bukkit_Whitelist
     */
    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Bukkit_Whitelist();
        }
        return self::$instance;
    }
}