<form action="admin.php?page=minecraftadmin-whitelist" method="post">
<fieldset class="mca_adm">
	<legend>Whitelist</legend>
	<table class="form-table">
		<?php foreach ($list_users as $user) {  ?>
		<tr>
			<th><?php echo $user->user_login; ?></th>
			<td><input type="checkbox" name="whitelist[]" value="<?php echo $user->ID; ?>" <?php if (in_array(get_the_author_meta('mca_minecraft_login', $user->ID), $listWlUser)) { ?>checked="checked"<?php } ?>/></td>
		</tr>
		<?php } ?>
	</table>
	<p class="submit">
	<input type="submit" name="options_save" class="button-primary" value="Save changes" />
	</p>
</fieldset>
</form>