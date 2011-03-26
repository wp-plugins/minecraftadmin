<form action="admin.php?page=minecraftadmin-bukkittcp" method="post">
<fieldset class="mca_adm">
	<legend>Bukkit Tcp</legend>
	<table class="form-table">
		<tr>
			<th>Host</th>
			<td><label><input class="enabled" type="text" name="mca.bukkittcp.host" value="<?php echo $config->get('mca.bukkittcp.host'); ?>" /></label><br/>
			<span class="description">The host where Bukkit TCP is running.</span>
		</tr>
		<tr>
			<th>Port</th>
			<td><label><input class="enabled" type="text" name="mca.bukkittcp.port" value="<?php echo $config->get('mca.bukkittcp.port'); ?>" size="5" /></label><br/>
			<span class="description">The port of Bukkit TCP.</span>
		</tr>
		<tr>
			<th>Password</th>
			<td><label><input class="enabled" type="password" name="mca.bukkittcp.pass" value="<?php echo $config->get('mca.bukkittcp.pass'); ?>" /></label><br/>
			<span class="description">The password for connect to Bukkit TCP.</span>
		</tr>
	</table>
	<p class="submit">
	<input type="submit" name="options_save" class="button-primary" value="Save changes" />
	</p>
</fieldset>
</form>