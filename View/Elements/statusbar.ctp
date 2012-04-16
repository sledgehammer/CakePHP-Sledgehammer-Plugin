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
	<?php SledgeHammer\statusbar(); ?>
	<a href="javascript:document.getElementById('statusbar').style.display='none';" title="Hide statusbar" class="statusbar-close">&times;</a>
</div>
