<form action="admin.php?page=minecraftadmin-remotetoolkit" method="post">
<fieldset class="mca_adm">
	<legend>Remote Toolkit</legend>
	<table class="form-table">
		<tr>
			<th>Host</th>
			<td><label><input class="enabled" type="text" name="mca.remotetoolkit.host" value="<?php echo $config->get('mca.remotetoolkit.host'); ?>" /></label><br/>
			<span class="description">The host where Remote Toolkit is running.</span>
		</tr>
		<tr>
			<th>Port</th>
			<td><label><input class="enabled" type="text" name="mca.remotetoolkit.port" value="<?php echo $config->get('mca.remotetoolkit.port'); ?>" size="5" /></label><br/>
			<span class="description">The port of Remote Toolkit.</span>
		</tr>
		<tr>
			<th>User</th>
			<td><label><input class="enabled" type="text" name="mca.remotetoolkit.user" value="<?php echo $config->get('mca.remotetoolkit.user'); ?>" /></label><br/>
			<span class="description">The username of Remote Toolkit.</span>
		</tr>
		<tr>
			<th>Password</th>
			<td><label><input class="enabled" type="password" name="mca.remotetoolkit.pass" value="<?php echo $config->get('mca.remotetoolkit.pass'); ?>" /></label><br/>
			<span class="description">The password for connect to Remote Toolkit.</span>
		</tr>
	</table>
	<p class="submit">
	<input type="submit" name="options_save" class="button-primary" value="Save changes" />
	</p>
</fieldset>
</form>