<div class="carousel-inner">
	<?php
	
	if ($res) {		
		$i=0;
		$m='';
		foreach ($res as $r) {
			$class = ($i == 0) ? 'active':'';
			print "<div class=\"item $class\" id=\"$i\">";
			print $r['layout'];
			if ($_SESSION["sound"] == 'ON')
				print "<audio class=\"player_audio\" style=\"display:none;\" id=\"sound_$i\" src=\"".VIEW."/file.php?fala=".$r['fala']."&tempo=$i\" preload=\"auto\" controls ></audio>";
				$m[$r['codigo']] = urlencode($r['nome']);
			print "</div>";
			$i++;
		}
		
		if (!isset($menu))
			$menu = $m;
			
		?>
</div>

<script type="text/javascript">
	$(document).ready(function () { 
		$('#menu').load('<?=VIEW?>/menu.php?menu=<?=json_encode($menu)?>');
	});
</script>

<?php
	} else {
		print "Nenhum registro encontrado para essa busca...";
	}
?>

<!-- BOOTSTRAP SCRIPTS -->
<script src="<?= JS ?>/bootstrap.js"></script>
	
<script type="text/javascript">
	$(document).ready(function(){
		$('#reviews').carousel({
			interval: 15000, pause: 'hover' //TIME IN MILLI SECONDS
		});
	});
	
	$('#reviews').bind('slid.bs.carousel', function (e) {
		$('audio').each(function(){
			this.pause(); // Stop playing
			this.currentTime = 0; // Reset time
		});

		fala();
		$('#loading').hide();
	});
	
	var first=0;
	if (first == 0)
		fala();
	first=1;
	
	function fala() {
		$.ajax({
			url: '<?= VIEW ?>/header.php',
			data: {sound: 1, get: 1},
			async:false,
			success: function(data) {
				if (data == 'ON')
					$('#sound_'+ $('.active').attr('id') )[0].play();
			}
		});
	}
</script>
