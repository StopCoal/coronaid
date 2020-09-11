<?php
require_once("config.php");
date_default_timezone_set ( "Europe/Berlin" );

$timeSwitch=strtotime("2020-09-24 12:00:00");


wp_enqueue_style( 'bootstrap_form_css', get_home_url(null,BOOTSTRAP_CSS) );
wp_add_inline_style( 'bootstrap_form_css', <<<CSS
	.info_panel.infect_result.infected { border: solid 2px red;}
	.info_panel.infect_result.not_infected { border: solid 2px green;}
	.info_panel.infect_result.ast_infected { border: solid 2px #ff7800;}
	.info_row > div { display: inline-block; }
	.info_panel { max-width: 30rem; margin-left: auto; margin-right: auto; }
	.info_panel.infect_result { max-width: 39rem;  }
	.label_col { min-width: 4rem;  }
	.value_col { min-width: 6rem; text-align: right; font-weight: bold;}
	img.captcha_refresh_image {width: 22px; height: 22px; max-width: unset; }
	.captcha_input { margin-top: 0.5rem; margin-bottom: 0.5rem; }
	.show_corona_id_form, .show_corona_id, .show_corona_checkform, .show_corona_checkresult { display: none; margin-bottom: 3rem;}

        .corona_form_border {
			padding: 1.5rem;
			border: 1rem solid transparent;
			background: linear-gradient(white, white)
						padding-box,
						repeating-linear-gradient(-45deg,
						#f69ec4 0, #f69ec4 12.5%,
						transparent 0, transparent 25%,
						#7eb4e2 0, #7eb4e2 37.5%,
						transparent 0, transparent 50%)
						0 / 5em 5em;
		}
    
CSS
);

$a = shortcode_atts( array('lang' => 'de',), $attrs );

switch(@$_REQUEST['form_action'])
{
    case "get_new_corona_id":
		outputCoronaId($a['lang']);
        break;
    case "check_corona_id":
		outputInfection($a['lang']);
        break;
    default:
		if( time() > $timeSwitch)
			outputCheckForm($a['lang']);
		else
			outputGetIdForm($a['lang']);
		break;
}

function outputCoronaId($lang) {
wp_enqueue_script( 'bootstrap_form_js', get_home_url(null,BOOTSTRAP_JS), array("jquery"),'',true );	

wp_enqueue_script( 'polyfills.umd',JSPDF_POLY_JS, array("bootstrap_form_js"),'',true );
wp_enqueue_script( 'jspdf',JSPDF_JS, array("polyfills.umd"),'',true );
wp_enqueue_script( 'jspdf.plugin.autotable',JSPDF_AUTOTABLE_JS, array("jspdf"),'',true );

wp_add_inline_style( 'bootstrap_form_css', <<<CSS
div.show_corona_id { display: inherit; }
button.image_only.btn {
	
width: 8rem;
height: 2.5rem;
background-size: contain;
background-repeat: no-repeat;
background-position: left;
background-origin: inherit;
padding-left: 2rem;
font-size: 1.0rem;
font-weight: normal;
margin-right: 0.3rem;
padding-top: 0.1rem;
float:right;
}


button.list_print.btn { background-image: url('drucken.png');margin-left: 1.5rem !important;}
button.gen_pdf.btn { background-image: url('pdf.png');}
button.gen_pdf.btn .caret { margin-left: 3px; }

div.print_only { display: none; }	
	
@media print {
	/*
	*:not(.print_only) { visibility: hidden; margin: 0, padding: 0; max-height: 0px;}
	div.print_only { display: block; visibility: visible; }
	.print_only * {  visibility: visible;}
	*/
	body > :not(.print_only) { display: none;}
	div.print_only {display: block; height: 100vh;}
	
	div.print_only .for_aktion { margin-top: 30vh;}
	div.print_only .for_home { margin-top: 40vh;}
	
	div.print_only div.print_trenner { border-bottom: dashed lightgray 1mm; margin-top: 30vh;}
}

CSS
);


	$lang_ar= array(
		"de" => array(
			0 => "Für die Aktion",
			1 => "Zum aufbewahren",
			2 => "persönliche ID/PIN",
			3 => "Drucken",
		),
		"en" => array(
			0 => "For the action",
			1 => "To keep",
			2 => "personal ID/PIN",
			3 => "Print",
		),
	);
	$l=$lang_ar[$lang];


	wp_add_inline_script( 'jspdf.plugin.autotable',<<<JS

(function($){
	
	$(document).ready(function()
	{
		$("#pdf_a4").click(function(e) {
			e.preventDefault();
			genPDFSinglePage(
				$(".value_col.corona_id").text(),			
				$(".value_col.corona_pin").text(),"a4");			
		});
		$("#pdf_a5").click(function(e) {
			e.preventDefault();
			genPDFSinglePage(
				$(".value_col.corona_id").text(),			
				$(".value_col.corona_pin").text(),"a5");			
		});
		
	});

function genPDFSinglePage(corona_id,corona_pin,papersize) {		
	var doc = new jsPDF({unit: "mm", format: papersize});
	let pgHeight=doc.internal.pageSize.getHeight();
	let pgWidth=doc.internal.pageSize.getWidth();
	
	doc.text("{$l[0]}:",pgWidth/2,15, { align: "center" });
	
	doc.autoTable({
		startY: pgHeight / 4 - 5,
		margin:  {top: 0, right: 0, bottom: 0, left: pgWidth/2 - 15} ,
		tableWidth: 30,
		theme: 'grid',
		headStyles: { fillColor: "#f3f4f4", textColor: 20},
		styles: { halign: "center"},
		head: [['CoronaID']],
		body: [[corona_id]],
	});
	
	doc.setLineDash([5, 5], 0);
	doc.setLineWidth(1.1);
	doc.line(0,pgHeight / 2,pgWidth,pgHeight / 2);
	doc.setLineDash();

	doc.text("{$l[1]}:",pgWidth/2,pgHeight / 2 + 15, { align: "center" });

	doc.autoTable({
		startY: (3 * (pgHeight / 4)) - 5,
		margin: {top: 0, right: 0, bottom: 0, left: pgWidth/2 - 22} ,
		tableWidth: 44,
		theme: 'grid',
		headStyles: { fillColor: "#f3f4f4", textColor: 20},
		styles: { halign: "center"},
		showFoot: "never",
		head: [['CoronaID','PIN']],
		body: [[corona_id,corona_pin]],
	});
		

	doc.save("CoroaIds.pdf");
}
	
})(jQuery);

JS
);


    require_once(SECURIMAGE_PHP);
	if(!wp_verify_nonce(@$_REQUEST['_wpnonce'],'corona_id_form'))
	{
		echo("<div class='alert alert-danger' role='alert'>Keine Berechtigung um diese Funktion zu nutzen</div>");
		return;
	}
    

	if (Securimage::checkByCaptchaId($_REQUEST['captchaId'], $_REQUEST['captcha_code']) == false) {
	  echo "<div class='alert alert-danger' role='alert'>Das Captcha wurde nicht korrekt gelöst, bitte noch einmal versuchen <a href='javascript:history.go(-1)'>zurück</a></div> ";
	  return;
	}  
      
	$pdo = new \PDO('mysql:host=localhost;dbname=' . DB_NAME , DB_USER, DB_PASSWORD);
	try {                             
		$pdo ->beginTransaction();
		$sql = 'SELECT * FROM ' . CORONAID_TABLE . '  WHERE state = 0 ORDER BY random LIMIT 1 FOR UPDATE;';
		$statement = $pdo->prepare($sql);
		$statement->execute();
		$row=$statement->fetch(PDO::FETCH_ASSOC);
		if($row === false) throw new \Exception("Could not fetch next corona_id");
		$sql = 'UPDATE ' . CORONAID_TABLE . ' SET state = 1  WHERE id=:id;';
		$statement = $pdo->prepare($sql);
		if(!$statement->execute(array("id" => $row['id'], )))
			throw new \Exception("Error updating status of corona_id");
		$pdo->commit();
	} catch (Exception $e) {
		$pdo ->rollBack();
		error_log($e->getMessage());
		echo "<h3>Fehler beim ermitteln der ID, bitte später noch einmal versuchen</h3>";
		return;
	}
	

	add_action('wp_footer', function() use($row,$l) {
?>
<div class="print_only" >
	<div class="panel panel-default info_panel  for_aktion">
	  <div class="panel-heading "><?php echo $l[0]; ?></div>
	  <div class="panel-body ">
		  <div class="info_row ">
			<div class="label_col ">ID:</div>
			<div class="value_col "><?php echo $row['corona_key']; ?></div>
		  </div>
	  </div>
	</div>
	<div class="print_trenner">&nbsp;</div>
	<div class="panel panel-default info_panel  for_home">
	  <div class="panel-heading "><?php echo $l[1]; ?></div>
	  <div class="panel-body ">
		  <div class="info_row ">
			<div class="label_col ">ID:</div>
			<div class="value_col "><?php echo $row['corona_key']; ?></div>
		  </div>
		  <div class="info_row ">
			<div class="label_col ">PIN:</div>
			<div class="value_col "><?php echo $row['pin']; ?></div>
		  </div>
	  </div>
	</div>
</div>

<?php		
	});
?>
<div class="panel panel-default info_panel">
  <div class="panel-heading"><?php echo $l[2]; ?></div>
  <div class="panel-body">
	  <div class="info_row">
		<div class="label_col">ID:</div>
		<div class="value_col corona_id"><?php echo $row['corona_key']; ?></div>
	  </div>
	  <div class="info_row">
		<div class="label_col">PIN:</div>
		<div class="value_col corona_pin"><?php echo $row['pin']; ?></div>
	  </div>
  </div>
   <div class="panel-footer">
		<div class="row">
			<div class="btn-group col-xs-12 button_bottom_row"  role="group">			
					<button type="button"  class="btn btn-default image_only list_print" onclick="javascript:window.print();"  ><?php echo $l[3]; ?></button>
					<div class="dropdown">
						<button type="button"  class="btn btn-default image_only gen_pdf dropdown-toggle" data-toggle="dropdown"  >
								PDF
								<span class="caret"></span>
						</button>
						  <ul class="dropdown-menu">
							<li><a href="#" id="pdf_a4" >A4</a></li>
							<li><a href="#" id="pdf_a5">A5</a></li>
						  </ul>
					</div>				
			</div>
		</div> <!-- row -->
    </div>
</div>

<?php
	
}

function outputInfection($lang) {
	global $wpdb;
	wp_add_inline_style( 'bootstrap_form_css', <<<CSS
		div.show_corona_checkresult { display: inherit; }
CSS
);

	if(!wp_verify_nonce(@$_REQUEST['_wpnonce'],'corona_result_form'))
	{
		echo("<h3>Keine Berechtigung um diese Funktion zu nutzen</h3>");
		return;
	}

	$infCls=" not_infected ";
	
	$sql="SELECT DATE_FORMAT(first,'%%d.%%m.%%Y') as first, DATE_FORMAT(last,'%%d.%%m.%%Y') as last FROM " . 
				CORONA_AST_TABLE . " WHERE corona_key = '%s';";
	$prep=$wpdb->prepare($sql,$_REQUEST['corona_id']);
	$rowAst = $wpdb->get_row(  $prep );
	$isAstInfected=!(empty($rowAst->first) && empty($rowAst->last));
	if($isAstInfected) $infCls=" ast_infected ";
	
	$isInfected=!(empty($row->first) && empty($row->last));
	$sql="SELECT DATE_FORMAT(first,'%%d.%%m.%%Y') as first, DATE_FORMAT(last,'%%d.%%m.%%Y') as last FROM " . 
				CORONA_CONTACTS_TABLE . " WHERE corona_key = '%s';";
	$prep=$wpdb->prepare($sql,$_REQUEST['corona_id']);
	$row = $wpdb->get_row(  $prep );
	$isInfected=!(empty($row->first) && empty($row->last));
	if($isInfected) $infCls=" infected ";
	
	
	$lang_ar= array(
		"de" => array(
			0 => "Infektionsrisiko zu dieser ID",
			1 => "Bisher wurde kein Infektionsrisiko zu dieser ID gemeldet",
			2 => "Möglicher Infektionskontakt in Kleingruppe(n)",
			3 => "Infizierte Person(en) in der Anlaufstelle",
		),
		"en" => array(
			0 => "Risk of infection for this ID",
			1 => "So far no risk of infection has been reported for this ID",
			2 => "Possible infection contact in working group(s)",
			3 => "Infected person(s) in the Basestation",
		),
	);
	$l=$lang_ar[$lang];
	
	
?>	
<div class="panel panel-default info_panel infect_result <?php echo $infCls; ?>">
  <div class="panel-heading"><?php echo $l[0]; ?></div>
  <div class="panel-body">
	  <div class="info_row">
<?php	
	if(!$isInfected && !$isAstInfected) {
		echo $l[1];
	} else {
		if($isInfected) {
			echo "<p><strong>{$l[2]}</strong></p><p>{$row->first} - {$row->last}</p>";
		}
		if($isAstInfected) {
			echo "<p><strong>{$l[3]}</strong></p><p>{$rowAst->first} - {$rowAst->last}</p>";
		}
	}
		
?>
	  </div>
  </div>
</div>

<?php
	
}

function outputCheckForm($lang) {
	wp_add_inline_style( 'bootstrap_form_css', <<<CSS
		div.show_corona_checkform { display: inherit; }
CSS
);

	$lang_ar= array(
		"de" => array(
			0 => "Corona ID überprüfen",
			1 => "prüfen",
			2 => "Zu wenige oder nicht erlaubte Zeichen eingegeben",
		),
		"en" => array(
			0 => "Check Corona ID",
			1 => "check",
			2 => "Too few or not allowed characters entered",
		),
	);
	$l=$lang_ar[$lang];


?>
<div class="panel panel-default info_panel">
  <div class="panel-heading"><?php echo $l[0]; ?></div>
  <div class="panel-body">
	  <div class="info_row">
		<form id="check_corona_form" action="#corona_form" method="POST">
			<?php wp_nonce_field('corona_result_form'); ?>
					  <div class="form-group">
						  <label for="corona_id">Corona ID</label>
						  <div class="input-group">
							  <span class="input-group-btn">
								<button type="submit" name="form_action" value="check_corona_id" class="btn btn-default"><?php echo $l[1]; ?></button>
							  </span>
							  <input type="text" class="form-control" id="corona_id" name="corona_id" placeholder="ID"
								  pattern="[ABCDEFGHIKLMNOPSTUWXYZabcdefghiklmnopstuwxyz]*" minlength="5" maxlength="5" required="required"
								  style="text-transform: uppercase; "
								  oninvalid="this.setCustomValidity('<?php echo $l[2]; ?>')"
								   oninput="this.setCustomValidity('')" >
						  </div>
					 </div>			
		</form>
	</div>
  </div>
</div>
	
<?php
	
}

function outputGetIdForm($lang) {
	wp_enqueue_style('securimage_css',SECURIMAGE_CSS);
	wp_enqueue_script( 'securimage_js',SECURIMAGE_JS, array("jquery"),'',true );
	wp_add_inline_style( 'securimage_css', <<<CSS
		div.show_corona_id_form { display: inherit; }
CSS
);

	$lang_ar= array(
		"de" => array(
			0 => "Corona ID erzeugen",
			1 => "Captcha Lösung",
			2 => "Neues Captcha laden",
			3 => "Corona ID abrufen",
			4 => "Captcha Auflösung nicht korrekt",
		),
		"en" => array(
			0 => "Create Corona ID",
			1 => "Captcha resolved",
			2 => "Reload Captcha",
			3 => "Get Corona ID",
			4 => "Captcha resolution incorrect",
		),
	);
	$l=$lang_ar[$lang];



	wp_add_inline_script( 'securimage_js',<<<JS

(function($){
	
	$(document).ready(function()
	{
		$("#corona_id_form").submit(function(e) {			
			let captchaId=$(this).find("input[name=captchaId]");
			let captcha_code=$(this).find("input[name=captcha_code]");
			if(captcha_code.length > 0) e.preventDefault();
			checkCoronaCaptcha(e.currentTarget,captcha_code.val(),captchaId.val());
		});
		
		$("#bt_refresh_captcha").click(refreshCaptcha);
	});

function checkCoronaCaptcha(form,captcha_code,captchaId) {
	$.post({
		url: SECURIMAGE_AJAX,
		data: {
			captcha_code: captcha_code,
			captchaId: captchaId
		},
		success: function(data,  textStatus,  jqXHR ) {
			if(!data.state)  form.submit();
			
			if(data.state == 1) {
				alert("{$l[4]}");
				return;
			}
			form.submit();
			
		}
	})
	.fail(function(xhr, status, error) {
		form.submit();
	});;					
}

function refreshCaptcha() {
	$.post({
		url: SECURIMAGE_AJAX,
		data: {
			refresh: 1
		},
		success: function(data,  textStatus,  jqXHR ) {
			if(!data.state || data.state != 0)  return;
			
            let src = SECURIMAGE_PHP . '?captchaId=' + data.captchaId+ '&rand=' + Math.random();
            $('#corona_id_captcha').attr('src', src); // replace image with new captcha
            $('#captchaId').attr('value', data.captchaId); // update hidden form field
			
		}
	});					
}

	
})(jQuery);

JS
);
	
    require_once(SECURIMAGE_PHP);
	$captchaId = Securimage::getCaptchaId(true);
	
?>
<div class="panel panel-default info_panel">
  <div class="panel-heading"><?php echo $l[0]; ?></div>
  <div class="panel-body">

	  <div class="info_row">
		<form id="corona_id_form" action="#corona_form" method="POST">
			<?php wp_nonce_field('corona_id_form'); ?>

			<input type="hidden" id="captchaId" name="captchaId" value="<?php echo $captchaId ?>" />
			<img id="corona_id_captcha" src="<?php echo SECURIMAGE_BASE; ?>/captcha_display.php?captchaId=<?php echo $captchaId ?>" alt="captcha image" />			
			<div class="input-group input-group-sm captcha_input">
				<input type="text" class="form-control" name="captcha_code" maxlength="6" placeholder="<?php echo $l[1]; ?>" required="required" />
				<span class="input-group-btn">
					<div class="btn btn-default" id="bt_refresh_captcha" type="button" title="<?php echo $l[2]; ?>">
						<img class="captcha_refresh_image" src="<?php echo SECURIMAGE_BASE; ?>/images/refresh.png" alt="Refresh Image" onclick="this.blur()" >
					</div>
				</span>
			</div>
			<input type="hidden" id="form_action" name="form_action" value="get_new_corona_id" />
			<button type="submit" id="form_action" class="btn btn-default"><?php echo $l[3]; ?></button>
		</form>
	</div>
  </div>
</div>
<?php
	
}
