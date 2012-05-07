<?php
$hideStatusbar = (Configure::read('debug') == 0);
if (isset($_GET['debug'])) {
	$hideStatusbar = !$_GET['debug'];
}
if ($hideStatusbar) {
	return;
}
echo $this->Html->css('/core/css/debug.css');
$placeholder = isset($placeholder) ? $placeholder : true;
if ($placeholder) {
	echo "<div class=\"statusbar-placeholder\"></div>";
}
?>
<div class="statusbar">
	<?php SledgeHammer\statusbar(); ?>
	<a href="#" onclick="this.parentNode.style.display='none';<?php if ($placeholder) { echo "this.parentNode.parentNode.getElementsByClassName('statusbar-placeholder')[0].style.display='none';"; } ?>return false;" title="Hide statusbar" class="statusbar-close">&times;</a>
</div>