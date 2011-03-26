function mca_getStatus(){
	jQuery.ajax({
		type: 'POST',
		url: 'index.php',
		data: {
			mca_action: 'status'
		},
		dataType: 'json',
		success: function(data, textStatus, jqXHR) {
			jQuery('#mca_widget_status_img_manager').attr({src: data['manager']});
			jQuery('#mca_widget_status_img_server').attr({src: data['server']});
			jQuery('#mca_widget_max_players').text(data['max_players']);
			jQuery('#mca_widget_manager_version').text(data['manager_version']);
			window.setTimeout('mca_getStatus()', 120000);
		}
	});
}

jQuery(document).ready(function($){mca_getStatus();});