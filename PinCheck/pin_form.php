<?php

?>
<div class="panel panel-default form_panel_outer">
  <div class="panel-heading">
    <h3 class="panel-title">Corona ID und PIN prüfen</h3>
  </div>
  <div class="panel-body">
	<ul class="list-group">
		<li class="list-group-item disabled">
		PIN/ID Prüfung
		</li>
		<li class="list-group-item">
			<form method="post" id="frm_data" class="form-horizontal">
			<div class="row">
				<div class="form-group col-sm-6">
					<label for="form[corona_id]" class="col-sm-2 control-label">ID:</label>
					<div class="col-sm-10">
						<input  name="form[corona_id]" aria-describedby="help_form[corona_id]"
						pattern="[ABCDEFGHIKLMNOPSTUWXYZabcdefghiklmnopstuwxyz]*" minlength="5" maxlength="5" required="required" 
						type="text" class="form-control upper" id="form_corona_id" placeholder="ID" value="<?php echo @$_REQUEST['form']['corona_id']; ?>" >
						<span id="help_form[corona_id]" class="help-block">5 Buchstaben ohne (V,Q,R,J)</span>
					</div>
				</div>
				<div class="form-group col-sm-6">
					<label for="form[pin]" class="col-sm-2 control-label">PIN:</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="form[pin]"  aria-describedby="help_form[pin]"
						pattern="[0123456789]*" minlength="4" maxlength="4" required="required" 
						id="form_pin" placeholder="PIN"  value="<?php echo @$_REQUEST['form']['pin']; ?>">
						<span id="help_form[pin]" class="help-block">4 Ziffern</span>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-6 col-sm-offset-1">
					<button type="submit" name="action" value="check_pin" class="btn btn-default">Prüfen</button>
				</div>
			</div>
			</form>
		</li>
		<li class="list-group-item disabled">
		Suche
		</li>
		<li class="list-group-item">
			<div class="row">
				<div class="col-md-2 col-sm-offset-2 search_char_buttons"  >
				  <button data-target=".input_char.char1" id="char1" name="char1" type="button" data-toggle="button" class="btn btn-default btn-xs" autocomplete="off" >?</button>
				  <button data-target=".input_char.char2" id="char2" name="char2" type="button" data-toggle="button" class="btn btn-default btn-xs" autocomplete="off">?</button>
				  <button data-target=".input_char.char3" id="char3" name="char3" type="button" data-toggle="button" class="btn btn-default btn-xs" autocomplete="off" >?</button>
				  <button data-target=".input_char.char4" id="char4" name="char4" type="button" data-toggle="button" class="btn btn-default btn-xs" autocomplete="off" >?</button>
				  <button  data-target=".input_char.char5"id="char5" name="char5" type="button" data-toggle="button" class="btn btn-default btn-xs" autocomplete="off" >?</button>
				</div>
			</div>
			<div class="row">
			<?php ob_start(); ?>
				<div class="col-md-6 col-sm-offset-2 search_id_input"  >
				  <!--<input type="text" id="search_id_text" pattern="[ABCDEFGHIKLMNOPSTUWXYZabcdefghiklmnopstuwxyz]*" minlength="5" maxlength="5" class="form-control upper" placeholder="ID">-->
				  <input type="text" class="input_char char1  " placeholder="I" <?php search(0) ?> >
				  <input type="text" class="input_char char2 "  placeholder="D" <?php search(1) ?> >
				  <input type="text" class="input_char char3 "  <?php search(2) ?> >
				  <input type="text" class="input_char char4 "  <?php search(3) ?> >
				  <input type="text" class="input_char char5  last"  <?php search(4) ?> >
				  
				  <input type="text" id="search_pin_text" pattern="[0123456789]*" minlength="4" maxlength="4" class="form-control" placeholder="PIN" >
				  <input type="button" id="search_btn" class="btn btn-default btn-sm" value="Suchen">
				</div>
			<?php 
				$reg=['/\R+/','/>\s+</' ];
				$repl=['','><'];
				$html=preg_replace($reg,$repl,ob_get_contents() );
				ob_end_clean();
				echo $html;
				?>
			</div>
			<div class="search_results">
			
			</div>
		</li>
	</ul>
  </div>
  <div class="panel-footer">
 		<div class="row">
<?php

	if($ERR_MESSAGE !== false) {
		echo <<<MSG
		<div class="alert alert-danger timeout_alert"  role="alert">
		{$ERR_MESSAGE}
		</div>
MSG;
	}

	if($MESSAGE !== false) {
		echo <<<MSG
		<div class="alert alert-success  timeout_alert"  role="alert">
		{$MESSAGE}
		</div>
MSG;
	}
	?>
		</div> <!-- row -->
 		<div class="row">
			<div class="col-xs-5">					
			</div>
			<div class="btn-group col-xs-7 button_bottom_row"  role="group">
				<form method="post">
					<button type="submit" name="action" value="logout" class="btn btn-default image_only logout">Logout</button>
				</form>
			</div>
		</div> <!-- row -->
  </div>
</div>

<div class="modal modal_dlg busy_modal" tabindex="-1" role="dialog" >
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
	   <div class="modal-body">
	   <div class="spinner">
		  <div class="rect1"></div>
		  <div class="rect2"></div>
		  <div class="rect3"></div>
		  <div class="rect4"></div>
		  <div class="rect5"></div>
		</div>
	   </div>
    </div>
  </div>
</div>


<div class="modal fade modal_dlg" tabindex="-1" role="dialog" >
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
       <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h5 class="modal-title" >....</h5>
      </div>
	   <div class="modal-body"></div>
    </div>
  </div>
</div>
