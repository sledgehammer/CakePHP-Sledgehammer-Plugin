<?php
$hideStatusbar = (Configure::read('debug') == 0);
if (isset($_GET['debug'])) {
	$hideStatusbar = !$_GET['debug'];
}
if ($hideStatusbar) {
	return;
}
echo $this->Html->css('/core/css/debug.css'); ?>
<div class="statusbar" id="statusbar">
	<div class="statusbar" id="statusbar">
	<a href="javascript:document.getElementById('statusbar').style.display='none';" title="Hide statusbar" style="float:right;margin-right: 4px; font: 14px sans-serif; text-decoration: none;">&#10062;</a>
	<?php SledgeHammer\statusbar(); ?>
</div>
