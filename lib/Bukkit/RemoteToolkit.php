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
 * Class for manage send command to RemoteToolkit
 * 
 * See the RemoteToolkit : http://forums.bukkit.org/threads/admn-remotetoolkit-restarts-advanced-backup-preview-full-remote-console-access-r10-a8-2.674/
 * 
 * @author Maximilien Bersoult <leoncx@gmail.com>
 * @package wp-minecraftAdmin
 * @subpackage Bukkit
 * @license GPL v2
 * @copyright 2011
 */
class Bukkit_RemoteToolkit
{
    const TIMEOUT = 5; 
    private $host;
    private $port;
    private $user;
    private $pass;
    
    static private $instance = null;
    
    /**
     * Constructor
     * 
     * @param string $host The minecraft remotetoolkit host
     * @param int $port The port of remotetoolkit
     * @param string $user The username
     * @param string $pass The password
     */
    private function __construct($host, $port, $user, $pass)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
    }
    
    /**
     * Get the version of RemoteToolkit
     * 
     * @return string
     */
    public function getVersion()
    {
        $version = $this->sendCommand('version');
        $version = trim(str_ireplace(array('ALPHA', 'BETA'), '', $version));
        return $version;
    }
    
    /**
     * Send a command to RemoteToolkit
     * 
     * @param string $cmd The command to send
     * @return string The return of command or false in error
     */
    private function sendCommand($cmd)
    {
        $fp = fsockopen('udp://' . $this->host, $this->port, $errno, $errstr, self::TIMEOUT);
        if (false === $fp) {
            return false;
        } else {
            fwrite($fp, $cmd . ':' . $this->user . ':' . $this->pass);
            stream_set_timeout($fp, self::TIMEOUT);
            $ret = fread($fp, 2048);
            fclose($fp);            
        }
        return trim($ret);
    }

    /**
     * Get a instance of Bukkit_RemoteToolkit
     * 
     * @param string $host The minecraft remotetoolkit host
     * @param int $port The port of remotetoolkit
     * @param string $user The username
     * @param string $pass The password
     * @return Bukkit_RemoteToolkit
     */
    static public function getInstance($host = null, $port = null, $user = null, $pass = null)
    {
        if (is_null(self::$instance) && (is_null($host) || is_null($port) || is_null($user) || is_null($pass))) {
            return null;
        }
        if (is_null(self::$instance)) {
            self::$instance = new Bukkit_RemoteToolkit($host, $port, $user, $pass);
        }
        return self::$instance;
    }
}
?>