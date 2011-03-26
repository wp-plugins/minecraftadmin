function mca_getOnlinePlayers() {
	jQuery.ajax({
		type: 'POST',
		url: 'index.php',
		data: {
			mca_action: 'players_online'
		},
		dataType: 'json',
		success: function(data, textStatus, jqXHR) {
			jQuery('#mca_players_online > li').remove();
			jQuery.each(data, function(index, value){
				var el = jQuery('<li/>').addClass('mca_player').text(value);
				jQuery('#mca_players_online').append(el);
			});
			window.setTimeout('mca_getOnlinePlayers()', 60000);
		}
	});
}

jQuery(document).ready(function($){mca_getOnlinePlayers();});