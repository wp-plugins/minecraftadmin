<form action="admin.php?page=minecraftadmin-permissions" method="post">
<fieldset class="mca_adm">
	<legend>Permissions</legend>
	<table class="form-table">
		<tr>
			<th>Path to configuration files</th>
			<td><label><input class="enabled" type="text" name="mca.permissions.path" value="<?php echo $config->get('mca.permissions.path'); ?>" /></label><br/>
			<span class="description">The path to the configuration files for plugins Permissions.</span>
		</tr>
	</table>
	<p class="submit">
	<input type="submit" name="options_save" class="button-primary" value="Save changes" />
	</p>
</fieldset>
</form>
<?php if (!is_null($config->get('mca.permissions.path')) && trim($config->get('mca.permissions.path')) != '') { ?>
<form method="post" enctype="multipart/form-data">
<fieldset class="mca_adm">
	<legend>Permissions load commands</legend>
	<table class="form-table">
		<tr>
			<th>File with commands</th>
			<td><label><input class="enabled" type="file" id="mca_permissions_commandfile" /></label><br/>
			<span class="description">The file with list of commands for plugins Permissions.<br/>
			The file format is : command_name;command description
			</span>
		</tr>
	</table>
	<p class="submit">
	<input type="button" name="loading_file" class="button-primary" value="Loading" onclick="mcaLoadFile();" />
	</p>
</fieldset>
</form>
<fieldset class="mca_adm">
	<legend>Permissions world configuration</legend>
	<table class="form-table">
		<tr>
			<th>World : </th>
			<td><select id="mca_worldlist" onchange="mcaLoadWorld(this, '');">
			<option></option><?php foreach ($worldList as $world) { ?>
			<option><?php echo $world; ?></option>
			<?php } ?></select><br/>
			</td>
		</tr>
		<tr id="mca-permissions-groups">
			<th>Permissions groups<br/>
			<select id="mca-permissions-listgroups" onchange="mcaLoadGroupPerms(this)"></select><br/>
			<input type="text" id="mca-permissions-newgroup" />
			</th>
			<td>
			<table class="form-table">
			<tr>
				<th>User :</th>
				<td>
				<?php if ($config->getBoolean('mca.enable.whitelist')) {?>
				<select id="mca-permissions-users" multiple="multiple" class="mca-multi"></select>
				<?php } else {?>
				<textarea id="mca-permissions-users" class="mca-multi"></textarea>
				<?php } ?>
				</td>
				<th>Inheritance :</th>
				<td><select id="mca-permissions-inherite" multiple="multiple" class="mca-multi"></select></td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>&nbsp;</td>
				<th>Rights :</th>
				<td><select multiple="multiple" id="mca-permissions-listrights" class="mca-multi"></select></td>
			</tr>
			</table>
			<p class="submit"><input type="button" name="save_rights" class="button-primary" value="Save" onclick="mcaSavePermissionGroup()" /></p>
			</td>
		</tr>
	</table>
</fieldset>
<div id="mca_dialog_upload"></div>
<?php } ?>