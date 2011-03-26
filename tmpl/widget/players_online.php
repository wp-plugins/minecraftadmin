<?php
echo $before_widget;
if (isset($title)) {
    echo $before_title . $title . $after_title;
}
?>
<ul id="mca_players_online">
</ul>
<?php
echo $after_widget;
?>