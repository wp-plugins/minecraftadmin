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
 * Widget for display user connected to Minecraft server
 *
 * @author Maximilien Bersoult <leoncx@gmail.com>
 * @package wp-minecraftAdmin
 * @subpackage MinecraftAdmin
 * @license GPL v2
 * @copyright 2011
 */
class MinecraftAdmin_Widget_PlayersOnline extends WP_Widget
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('mca-widget-players-online', __('Minecraft Players Online'), array(
             'classname' => 'MinecraftAdmin_Widget_PlayersOnline',
             'description' => 'Display the players connected to Minecraft Server'
         ));
    }
    
    /**
     * Update the widget
     * 
     * @param array $new_instance The new instance of widget
     * @param array $old_instance The old instance of widget
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
        
    }

    /**
     * Display the widget
     * 
     * @param array $args The arguments
     * @param array $instance The instance of widget
     */
    public function widget($args, $instance)
    {
        extract($args);
        $title = apply_filters('widget_title', empty( $instance['title']) ? __('Minecraft Players Online') : $instance['title'], $instance, $this->id_base);
        include MCA_TMPL . '/widget/players_online.php';
    }
    
    /**
     * The form for configure the widget
     * 
     * @param array $instance The instance of widget
     */
    public function form($instance)
    {
        if (isset($instance['title'])) {
            $title = esc_attr($instance['title']);
        }
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php 
    } 
    
    /**
     * Loader of widget for wordpress 
     */
    static public function load()
    {
        register_widget('MinecraftAdmin_Widget_PlayersOnline');
    }
}