function mcaLoadWorld(select, group) {
	if (select.options[select.selectedIndex].text != '') {
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'permissions-loadworld',
				world: select.options[select.selectedIndex].text
			},
			success: function(data){
				var select = jQuery('#mca-permissions-listgroups');
				select.find('option').remove();
				var select2 = jQuery('#mca-permissions-inherite');
				select2.find('option').remove();
				jQuery('<option/>').attr('value', '').text('').appendTo(select);
				jQuery.each(data, function(index, value){
					jQuery('<option/>').attr('value', value).text(value).appendTo(select);
					jQuery('<option/>').attr('value', value).text(value).appendTo(select2);
				});
				jQuery('<option/>').attr('value', 'newgroup').text('New ...').addClass('mca-option-new').appendTo(select);
				jQuery('#mca-permissions-groups').slideDown('15');
				if (group != '') {
					jQuery("#mca-permissions-listgroups").val(group);
					mcaLoadGroupPerms(document.getElementById('mca-permissions-listgroups'));
				}
			}
		});
		mcaReloadRights();
	} else {
		jQuery('#mca-permissions-groups').slideUp('15');
	}
}

function mcaReloadRights(callback) {
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'permissions-listrights'
		},
		success: function(data){
			var select = jQuery('#mca-permissions-listrights');
			select.find('option').remove();
			jQuery('<option/>').text('*').appendTo(select);
			jQuery.each(data, function(index, value){
				jQuery('<option/>').text(value).appendTo(select);
			});
			if (callback != undefined) {
				callback();
			}
		}
	});
}

function mcaLoadFile() {
	var fileUpload = document.getElementById('mca_permissions_commandfile');
	if (fileUpload.files != undefined && fileUpload.files.length != 0) {
		var files = fileUpload.files;
		jQuery('#mca_dialog_upload').dialog({
			closeOnEscape: false,
			draggable: false,
			resizable: false,
			title: 'Loading commands...',
			dialogClass: 'wp-dialog',
			close: function(event, ui) {
				jQuery('#mca_dialog_upload').empty();
			}
		});
		
		for (i = 0; i < files.length; i++) {
			var file = files[i];
			var name = '';
			if (file.name == undefined) {
				name = file.fileName;
			} else {
				name = file.name;
			}
			var size = 0;
			if (file.size == undefined) {
				size = file.fileSize;
			} else {
				size = file.size;
			}
			var hr1 = jQuery('<hr/>');
			jQuery('#mca_dialog_upload').append(hr1);
			var spanFileName = jQuery('<span/>').addClass('mca_filename').text(name);
			jQuery('#mca_dialog_upload').append(spanFileName);
			var progressDiv = jQuery('<div/>');
			jQuery('#mca_dialog_upload').append(progressDiv);
			progressDiv.progressbar({ value: 0 });
			progressDiv.addClass('mca-progressbar');
			
			var xhr = new XMLHttpRequest();
			xhr.upload.onprogress = function(e) {
				if (e.lengthComputable) {
					var pourcent = e.loaded * 100 / e.total;
					progressDiv.progressbar("value", pourcent);
				}
			};
			xhr.onreadystatechange = function() {
				if (xhr.readyState == 4) {
					var resp = jQuery.parseJSON(xhr.responseText);
					if (resp.success != undefined) {
						jQuery.ajax({
							url: ajaxurl,
							type: "POST",
							dataType: 'json',
							data: {
								action: 'permissions-loadfile',
								fname: resp.success
							},
							success: function(data){
								var msg = "";
								if (data.success) {
									msg = jQuery('<span/>').text('File loaded');
									mcaReloadRights();
								} else {
									msg = jQuery('<span/>').addClass('error').text(data.error);
								}
								jQuery('#mca_dialog_upload').append(msg);
							}
						});
					} else {
						var err = jQuery('<span/>').addClass('error').text(resp.error);
						jQuery('#mca_dialog_upload').append(err);
					}
				}
			};
			
			xhr.open('POST', ajaxurl + "?action=permissions-pushfile", true);
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	        xhr.setRequestHeader("X-File-Name", encodeURIComponent(name));
	        xhr.setRequestHeader("Content-Type", "application/octet-stream");
	        xhr.send(file);
		}
	}
}

function mcaLoadGroupPerms(select) {
	jQuery('#mca-permissions-listrights').find('option').each(function(key, data) { data.selected = false; });
	jQuery('#mca-permissions-inherite').find('option').each(function(key, data) { data.selected = false; });
	if (jQuery('select#mca-permissions-users').length != 0) {
		jQuery('#mca-permissions-users').find('option').each(function(key, data) { data.selected = false; });
	} else if (jQuery('textarea#mca-permissions-users').length != 0) {
		jQuery('#mca-permissions-users').text('');
	}
	
	selectWorld = document.getElementById("mca_worldlist");
	
	group = select.options[select.selectedIndex].value;
	if (group != '' && group != 'newgroup') {
		jQuery.ajax({
			url: ajaxurl,
			type: "POST",
			dataType: 'json',
			data: {
				action: 'permissions-groupinfos',
				group: group,
				world: selectWorld.options[selectWorld.selectedIndex].text
			},
			success: function(data) {
				/* Users */
				if (data.whitelist.enable) {
					userSelect = jQuery("#mca-permissions-users");
					jQuery.each(data.whitelist.users, function(index, value){
						option = jQuery('<option/>').attr('value', value).text(value);
						if (jQuery.inArray(value, data.users) != -1) {
							option.selected = true;
						}
						option.appendTo(userSelect);
					});
				} else {
					userText = jQuery("#mca-permissions-users");
					userText.text(data.users.join('\n'));
				}
				mcaReloadRights(function() {
					/* Permissions */
					jQuery.each(data.permissions, function(key, value) {
						jQuery('#mca-permissions-listrights').find('option').each(function(key1, data1) {
							if (value == data1.text) {
								data1.selected = true;
							}
						});
					});
				});
				/* Inheritance */
				jQuery.each(data.inheritance, function(key, value) {
					jQuery('#mca-permissions-inherite').find('option').each(function(key1, data1) {
						if (value == data1.text) {
							data1.selected = true;
						}
					});
				});
			}
		});
	}
}

function mcaSavePermissionGroup() {
	var group;
	var users = new Array();
	var inherite = new Array();
	var rights = new Array();
	
	/* Group name */
	if (jQuery('#mca-permissions-listgroups').val() == 'newgroup') {
		if (jQuery.trim(jQuery('#mca-permissions-newgroup').val()) == '') {
			jQuery('#mca_dialog_upload').dialog({
				closeOnEscape: false,
				draggable: false,
				resizable: false,
				title: 'Loading commands...',
				dialogClass: 'wp-dialog',
				close: function(event, ui) {
					jQuery('#mca_dialog_upload').empty();
				}
			});
			msg = jQuery('<span/>').addClass('error').text("The new group name is not defined");
			jQuery('#mca_dialog_upload').append(msg);
		} else {
			group = jQuery.trim(jQuery('#mca-permissions-newgroup').val());
		}
	} else if (jQuery('#mca-permissions-listgroups').val() != '') {
		group = jQuery('#mca-permissions-listgroups').val();
	}
	
	/* Inheritance */
	inherite = jQuery('#mca-permissions-inherite').val();
	
	/* Rights */
	rights = jQuery('#mca-permissions-listrights').val();
	
	/* Users */
	if (jQuery('select#mca-permissions-users').length != 0) {
		users = jQuery('#mca-permissions-users').val();
	} else if (jQuery('textarea#mca-permissions-users').length != 0) {
		users = jQuery('#mca-permissions-users').val().split("\n");
	}
	
	world = document.getElementById("mca_worldlist").options[selectWorld.selectedIndex].text;
	
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'permissions-savegroup',
			group: group,
			world: world,
			inherite: JSON.stringify(inherite),
			users: JSON.stringify(users),
			rights: JSON.stringify(rights)
		},
		success: function(data){
			jQuery('#mca_dialog_upload').dialog({
				closeOnEscape: false,
				draggable: false,
				resizable: false,
				title: 'Loading commands...',
				dialogClass: 'wp-dialog',
				close: function(event, ui) {
					jQuery('#mca_dialog_upload').empty();
				}
			});
			if (data.success) {
				mcaLoadWorld(document.getElementById("mca_worldlist"), group);
				jQuery('#mca-permissions-newgroup').val('');
				msg = jQuery('<span/>').text('Permissions saved');
			} else {
				msg = jQuery('<span/>').addClass('error').text("Can't write permissions file");
			}
			jQuery('#mca_dialog_upload').append(msg);
		}
	});
}