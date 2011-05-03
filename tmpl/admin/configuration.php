<form action="admin.php?page=minecraftadmin" method="post">
<fieldset class="mca_adm">
	<legend>Modules</legend>
	<table class="form-table">
		<tr>
			<th colspan="2">
			Enable/disable module to configure
			</th>
		</tr>
		<tr>
			<th>Remote Toolkit</th>
			<td><label><input class="enabled" type="checkbox" name="mca.enable.remotetoolkit" value="1"<?php checked($config->getBoolean('mca.enable.remotetoolkit'), true); ?> />&nbsp;<strong>Enable</strong></label><br/>
			<span class="description">The remote toolkit is for manage Minecraft server in UDP.</span>
		</tr>
		<tr>
			<th>TCP Bukkit</th>
			<td><label><input class="enabled" type="checkbox" name="mca.enable.bukkittcp" value="1"<?php checked($config->getBoolean('mca.enable.bukkittcp'), true); ?> />&nbsp;<strong>Enable</strong></label><br/>
			<span class="description">The TCP Bukkit is for get informations from Minecraft server in TCP.</span>
		</tr>
		<tr>
			<th>Whitelist</th>
			<td><label><input class="enabled" type="checkbox" name="mca.enable.whitelist" value="1"<?php checked($config->getBoolean('mca.enable.whitelist'), true); ?> />&nbsp;<strong>Enable</strong></label><br/>
			<span class="description">The whitelist of user for connect to server.</span>
		</tr>
		<tr>
			<th>Permissions</th>
			<td><label><input class="enabled" type="checkbox" name="mca.enable.permissions" value="1"<?php checked($config->getBoolean('mca.enable.permissions'), true); ?> />&nbsp;<strong>Enable</strong></label><br/>
			<span class="description">Manage the permissions for server.</span>
		</tr>
	</table>
	<p class="submit">
	<input type="submit" name="options_save" class="button-primary" value="Save changes" />
	</p>
</fieldset>
</form>