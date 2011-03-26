<?php
echo $before_widget;
if (isset($title)) {
    echo $before_title . $title . $after_title;
}
?>
<table class="mca_status">
<tr>
	<td>Manager Online</td>
	<td class="nd"><img id="mca_widget_status_img_manager" src="" width="12px" height="12px"/></td>
</tr>
<tr>
	<td>Manager version</td>
	<td class="nd"><span id="mca_widget_manager_version"></span></td>
</tr>
<tr>
	<td>Server Online</td>
	<td class="nd"><img id="mca_widget_status_img_server" src="" width="12px" height="12px"/></td>
</tr>
<tr>
	<td>Maximum players</td>
	<td class="nd"><span id="mca_widget_max_players"></span></td>
</tr>
</table>
<?php
echo $after_widget;
?>