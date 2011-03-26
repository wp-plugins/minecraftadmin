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
 * Class for manage send command to Bukkit_Tcp
 * 
 * See the Bukkit_Tcp : http://forums.bukkit.org/threads/admin-dev-tcp-interface-for-bukkit-v1-2-440.746/
 * 
 * The option send-all-json must be enable
 * 
 * @author Maximilien Bersoult <leoncx@gmail.com>
 * @package wp-minecraftAdmin
 * @subpackage Bukkit
 * @license GPL v2
 * @copyright 2011
 */
class Bukkit_Tcp
{
    const TIMEOUT = 5;
    private $host;
    private $port;
    private $pass;
    
    static private $instance = null;
    
	/**
     * Constructor
     * 
     * @param string $host The minecraft bukkit_tcp host
     * @param int $port The port of bukkit_tcp
     * @param string $pass The password
     */
    private function __construct($host, $port, $pass)
    {
        $this->host = $host;
        $this->port = $port;
        $this->pass = $pass;
    }
    
    /**
     * Get the list of connected players
     * 
	 * @return array The list of connected players
     */
    public function getListPlayers()
    {
        $ret = $this->sendCommand("getplayers");
        if ($ret === false) {
            return array();
        }
        $players = json_decode($ret, true);
        return $players['players'];
    }
    
    public function maxPlayers()
    {
        $ret = $this->sendCommand('maxplayers');
        if ($ret === false) {
            return 0;
        }
        $ret = json_decode($ret, true);
        return $ret['message'];
    }
    
    /**
     * Send a command to Bukkit_Tcp
     * 
     * @param string $cmd The command to send
     * @return string The return of command or false in error
     */
    private function sendCommand($cmd)
    {
        $fp = stream_socket_client($this->host . ':' . $this->port, $errno, $errstr, self::TIMEOUT);
        if (false === $fp || $errno != 0) {
            return false;
        } else {
            /* Authentificate */
            fwrite($fp, 'pass ' . $this->pass . "\n");
            stream_set_timeout($fp, self::TIMEOUT);
            $buffer = "";
            $read =array($fp);
            $write =array();
            $except =array();
            while (stream_select($read, $write, $except, 1, 500)) {
                $buffer .= fread($fp, 2048);
            }
            $ret = json_decode(trim($buffer), true);
            if ($ret['ok'] === false) {
                fclose($fp);
                return false;
            }
            /* Send command */
            fwrite($fp, $cmd . "\n");
            stream_set_timeout($fp, self::TIMEOUT);
            $buffer = "";
            $read =array($fp);
            $write =array();
            $except =array();
            while (stream_select($read, $write, $except, 1, 500)) {
                $buffer .= fread($fp, 2048);
            }
            $lines = explode("\n", $buffer);
            $tmp = json_decode(trim($lines[0]), true);
            $ret = trim($lines[0]);
            if (isset($tmp['ok']) && $tmp['ok'] == false) {
                return false;
            } 
            /* Close */
            /*fwrite($fp, "quit\n");
            stream_set_timeout($fp, self::TIMEOUT);
            $buffer = "";
            $read =array($fp);
            $write =array();
            $except =array();
            while (stream_select($read, $write, $except, 1, 1000)) {
                $buffer .= fread($fp, 2048);
            }*/
            fclose($fp);            
        }
        return $ret;
    }
    
    /**
     * Get instance of Bukkit_Tcp
     * 
     * @param string $host The minecraft bukkit_tcp host
     * @param int $port The port of bukkit_tcp
     * @param string $pass The password
     * @return Bukkit_Tcp
     */
    static public function getInstance($host = null, $port = null, $pass = null)
    {
        if (is_null(self::$instance) && (is_null($host) || is_null($port) || is_null($pass))) {
            return null;
        }
        if (is_null(self::$instance)) {
            self::$instance = new Bukkit_Tcp($host, $port, $pass);
        }
        return self::$instance;
    }
}
?>