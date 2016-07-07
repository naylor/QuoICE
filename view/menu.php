<?php
include_once "../inc/config.inc.php";
include_once INC."/funcoes.inc.php";

$res = json_decode($_GET['menu'], TRUE );
?>
<div class="sidebar-collapse">
	<ul class="nav" id="main-menu">
	<?php
	if ($res) {
		$i=1;
		foreach ($res as $c=>$n) {
			print "<li data-target=\"#reviews\" data-slide-to='".($i-1)."'>";
			print "<a>$i - ".$n."</a>";
			print "</li>";
			$i++;
		}
	} else {
		print "Banco de dados ainda não está pronto...";
	}
	?>
	</ul>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$('.sidebar-collapse .nav > li > a').click(function(){
		$('.sidebar-collapse .nav > li > a').removeClass('clicked');
		$(this).addClass('clicked');
	});
});
</script>
