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
/*
Plugin Name: MinecraftAdmin
Description: Manage a minecraft server with craftbukkit
Version: 0.4.3
Plugin URI: http://code.google.com/p/wp-minecraftadmin/
Author: Maximilien Bersoult
Author URI: http://code.google.com/p/wp-minecraftadmin/
*/


define('MCA_VERSION', '0.4.3');
define('MCA_LIB', dirname(__FILE__) . '/lib');
define('MCA_TMPL', dirname(__FILE__) . '/tmpl');

require_once MCA_LIB . '/MinecraftAdmin.php';
require_once MCA_LIB . '/MinecraftAdmin/Config.php';
require_once MCA_LIB . '/MinecraftAdmin/Widget.php';

$minecraftadmin = MinecraftAdmin::getInstance();
$minecraftadmin->run();
?>