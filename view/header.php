<?php
if (isset($_GET['similar'])) {
	include_once "../inc/config.inc.php";

    require CONTROLLER . "/candidato.class.php";
    $candidato = new candidato();

	if ($res = $candidato->buscaSimiliar($_GET['similar'])) {
		foreach($res as $reg)
			$arr[] = $reg;

		print json_encode($arr);
	} else {
		print 0;
	}
		
	die;
}

if (isset($_GET['sound'])) {
	include_once "../inc/config.inc.php";

	if (isset($_GET['set'])) {
		$_SESSION["sound"] = $_GET['set'];
		print $_GET['set'];
	}
	if (isset($_GET['get']))
		print $_SESSION["sound"];
	
	die;
}

if (isset($_GET['dados'])) {
	include_once "../inc/config.inc.php";

    require CONTROLLER . "/candidato.class.php";
    $candidato = new candidato();

	if ($res = $candidato->listCandToJSON($_GET["q"])) {
		foreach($res as $reg)
			$arr[] = $reg;

		print json_encode($arr);
	} else {
		print 0;
	}

    die;
}

require CONTROLLER . "/estado.class.php";

?>
<script type="text/javascript" src="<?= JS ?>/j-ulrich-jquery-simulate/libs/jquery.simulate.js"></script>
<script type="text/javascript">$.simulate.ext_disableQuirkDetection = true;</script>
<script type="text/javascript" src="<?= JS ?>/j-ulrich-jquery-simulate/src/jquery.simulate.ext.js"></script>
<script type="text/javascript" src="<?= JS ?>/j-ulrich-jquery-simulate/src/jquery.simulate.key-sequence.js"></script>
<script type="text/javascript" src="<?= JS ?>/j-ulrich-jquery-simulate/libs/bililiteRange.js"></script>

<script type="text/javascript" src="<?= JS ?>/AutocompleteList/src/jquery.tokeninput.js"></script>
<link rel="stylesheet" href="<?= JS ?>/AutocompleteList/styles/token-input.css" type="text/css" />
<link rel="stylesheet" href="<?= JS ?>/AutocompleteList/styles/token-input-facebook.css" type="text/css" />

<script type="text/javascript">
    $(document).ready(function () {
        $("#to").tokenInput("<?= VIEW ?>/header.php?dados=1", {
            theme: "facebook",
            searchingText: "Procurando...",
            noResultsText: "Sem resultados para esse nome!",
            preventDuplicates: true,
            tokenLimit: 1,
        });
    });
</script>

<div class="header-topo">
	<div id="logoImage">
		<img src="<?= IMAGES ?>/logo.png"/>
	</div>
	<div id="logo">
		<h1 align="center" margin-bottom="0px">Quiosque IoT para Conscientização Eleitoral</h1>
	</div>

	<div id="pesquisa">
		<img id="logoPesquisa" src="<?= IMAGES ?>/politico2.png"/>
		<input type="text" name="to" id="to">
		<div id="info"></div>
		  <button id="start_button" onclick="startButton(event)">
		  <img id="start_img" src="<?=IMAGES?>mic.gif" alt="Start"></button>
		<img id="logoSound" src="<?= IMAGES ?>/sound.png"/><div id="sound"></div>
	</div>
</div>
<style>
#start_button {
    border: 0;
    background-color:transparent;
	padding: 0;
	float: left;
    cursor: none;
	border-color: white;
	outline: none;
}
</style>

<div class="header-menu">
   <div class="row">
		<div class="col-md-4">
			<div class="firstTop main-box mb-red clicked" id="topRendas.php?" id2="topRendas.php?">
				<a>
					<p class="top">TOP 10</p><p>Aumento de Renda</p>
					<h6>Candidatos</h6>
				</a>
			</div>
		</div>
		<div class="col-md-4">
			<div class="main-box mb-dull" id="topCandidatos.php?mode=receita" id2="candReceitas.php?">
				<a>
					<p class="top">TOP 10</p><p>Maiores Receitas</p>
					<h6>Candidatos</h6>
				</a>
			</div>
		</div>
		<div class="col-md-4">
			<div class="main-box mb-pink" id="topCandidatos.php?mode=despesa" id2="candDespesas.php?">
				<a>
					<p class="top">TOP 10</p><p>Maiores Despesas</p>
					<h6>Candidatos</h6>
				</a>
			</div>
		</div>
		<div class="col-md-4">
			<div class="main-box mb-red" id="topDoador.php?" id2="candDoador.php?">
				<a>
					<p class="top">TOP 10</p><p>Maiores Doadores</p>
					<h6><span>Candidatos</span><span class="ntop">/Partidos</span></h6>
				</a>
			</div>
		</div>
		<div class="ntop col-md-4">
			<div class="main-box mb-dull" id="topPartidos.php?mode=receita">
				<a>
					<p>TOP 10</p><p>Maiores Receitas</p>
					<h6>Partidos</h6>
				</a>
			</div>
		</div>
		<div class="ntop col-md-4">
			<div class="main-box mb-pink" id="topPartidos.php?mode=despesa">
				<a>
					<p>TOP 10</p><p>Maiores Despesas</p>
					<h6>Partidos</h6>
				</a>
			</div>
		</div>
	</div>
	<!-- /. ROW  estados -->
	<div class="row">
		<div class="col-md-12" id="state">
			<?php
			$st = new estado();
			if ($res = $st->getList(null, 'ORDER BY nome'))
				print "<a href=\"#\" class=\"estado\" id=\"BR\"><button type=\"button\" 
							class=\"btn btn-lg btn-primary\" id=\"button\">BR</button></a>";
							
				foreach ($res as $e) {
					print "<a href=\"#\" class=\"estado\" id=".$e['nome']."><button type=\"button\" 
							class=\"btn btn-lg btn-primary\" id=\"button\">".$e['nome']."</button></a>";
				}
			?>
		</div>
	</div>
</div>

				
<script type="text/javascript">
	$(document).ready(function(){
		var mode='';
		if (!mode) {
			mode = 'topRendas.php?';
			$('#reviews').load('<?=VIEW?>/'+mode);
			$('.delete').hide();

			if ('<?= $_SESSION["sound"] ?>' == 'OFF')
				$('#logoSound').attr("src", '<?= IMAGES ?>/nosound.png');

		}
		
		function label() {
			var politico = $('#to').val();

			if (politico == '') {
				$('.top').show();
				$('.ntop').show();
				$('.btn').show();
				$('.delete').hide();
				$(".main-box").attr("tabindex",-1).focus();
			} else {
				$('.top').hide();
				$('.ntop').hide();
				$('.btn').hide();
				$('.delete').show();
			}
			
			return politico;
		}
		
		function getLink(id) {
			$('#menu').html('');
			$('#reviews').load('<?=VIEW?>/'+id);
			mode = id;
		};
		
		$('.main-box').click(function(){
			var politico = label();
			if (politico)
				id = $(this).attr('id2') + 'politico='+politico;
			else
				id = this.id;

			getLink(id);
		});

		$('#to').change(function(){
			$('.firstTop').click();
		});
		
		$('.estado').click(function(){
			estado = this.id;
			$('#reviews').load('<?=VIEW?>/'+mode+'&estado='+estado);
		});
		
		function send(vl) {
			$.ajax({
			  url: '<?= VIEW ?>/header.php',
			  async:false,
			  data: {sound: 1, set: vl},
			  cache: false,
			});
			$('#loading').hide();
		}
		
		$('#logoSound').click(function(){

			if ( $(this).attr("src") == '<?= IMAGES ?>/nosound.png' ) {
				$('#logoSound').attr("src", '<?= IMAGES ?>/sound.png');
				send('ON');
			} else {
				$('#logoSound').attr("src", '<?= IMAGES ?>/nosound.png');
				send('OFF');
				$('audio').each(function(){
					this.pause(); // Stop playing
					this.currentTime = 0; // Reset time
				});
			}
		});
	});

var final_transcript = '';
var recognizing = false;
var ignore_onend;
var start_timestamp;
if (('webkitSpeechRecognition' in window)) {
  start_button.style.display = 'inline-block';
  var recognition = new webkitSpeechRecognition();
  recognition.continuous = true;
  recognition.interimResults = true;
  recognition.onstart = function() {
    recognizing = true;
    showInfo('info_speak_now');
    start_img.src = '<?=IMAGES?>/mic-animate.gif';
  };
  recognition.onerror = function(event) {
    if (event.error == 'no-speech') {
      start_img.src = '<?=IMAGES?>/mic.gif';
      showInfo('info_no_speech');
      ignore_onend = true;
    }
    if (event.error == 'audio-capture') {
      start_img.src = '<?=IMAGES?>/mic.gif';
      showInfo('info_no_microphone');
      ignore_onend = true;
    }
    if (event.error == 'not-allowed') {
      if (event.timeStamp - start_timestamp < 100) {
        showInfo('info_blocked');
      } else {
        showInfo('info_denied');
      }
      ignore_onend = true;
    }
  };
  recognition.onend = function() {
    recognizing = false;
    if (ignore_onend) {
      return;
    }
    start_img.src = '<?=IMAGES?>/mic.gif';
    if (!final_transcript) {
      showInfo('info_start');
      return;
    }
    showInfo('');
  };
  recognition.onresult = function(event) {
    var interim_transcript = '';
    for (var i = event.resultIndex; i < event.results.length; ++i) {
      if (event.results[i].isFinal) {
        final_transcript += event.results[i][0].transcript;
      } 
    }

    if (final_transcript || interim_transcript) {
		$.ajax({
			url: '<?= VIEW ?>/header.php',
			data: {similar: final_transcript},
			async:false,
			success: function(data) {
                $('#to').tokenInput('remove', {id: $("#to").val()});

				var obj = $.parseJSON( data );
				$(obj).each(function(i,val){
					$("#to").tokenInput("add", {id: val.cpf, name: val.nome})
				});
				$('.firstTop').click();
			}
		});
		
      startButton(event);
    }
  };
}
        
var two_line = /\n\n/g;
var one_line = /\n/g;
function linebreak(s) {
  return s.replace(two_line, '<p></p>').replace(one_line, '<br>');
}
var first_char = /\S/;
function capitalize(s) {
  return s.replace(first_char, function(m) { return m.toUpperCase(); });
}

function startButton(event) {

  $('audio').each(function(){
	this.pause(); // Stop playing
	this.currentTime = 0; // Reset time
  });
  if (recognizing) {
    recognition.stop();
    return;
  }
  final_transcript = '';
  recognition.lang = 'pt-BR';
  recognition.start();
  ignore_onend = false;
  start_img.src = '<?=IMAGES?>/mic-slash.gif';
  showInfo('info_allow');
  showButtons('none');
  start_timestamp = event.timeStamp;
}
function showInfo(s) {
  if (s) {
    for (var child = info.firstChild; child; child = child.nextSibling) {
      if (child.style) {
        child.style.display = child.id == s ? 'inline' : 'none';
      }
    }
    info.style.visibility = 'visible';
  } else {
    info.style.visibility = 'hidden';
  }
}

</script>
