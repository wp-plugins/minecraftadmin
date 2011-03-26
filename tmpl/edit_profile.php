<h3><?php _e('Minecraft profile information'); ?></h3>
<table class="form-table">
<tr>
<th><label for="minecraft_login"><?php _e("Minecraft Login"); ?></label></th>
<td>
<input type="text" name="minecraft_login" id="minecraft_login" value="<?php echo esc_attr(get_the_author_meta('mca_minecraft_login', $user->ID)); ?>" class="regular-text" /><br />
<span class="description"><?php _e("Please enter your minecraft login."); ?></span>
</td>
</table>