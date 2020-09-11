<?php
$col_classes=" col-xs-5 ";

?>
<nav class="navbar-default navbar-fixed-top navbar_top">
<div class="panel panel-default">
  <div class="panel-heading text-center"><h4>CoronaID Listen abrufen</h4></div>
  </div>
  <form method="post" id="frm_data">
	  <div >
			<div class="row">
			  <div class="form-group form-group-sm col-xs-12">
				<div  class="col-xs-3 col-sm-2 top_center">Anzahl:</div>
				<div class="col-xs-8 col-sm-4 num_ids">
					<div class="input-group input-group-sm">
						<input type="number" name="num_corona_ids" min="1" max="500" required="required"
								class="form-control text-center" id="num_corona_ids" 
								inputmode="numeric" pattern="[0-9]*" placeholder="Wieviel ID's?" value="20" >
					  <span class="input-group-btn">
						<button class="btn btn-default" type="submit" name="action" value="get_coronaid_list" >Anfordern</button>
					  </span>
					</div> <!-- input group -->
				</div>
			  </div>

			</div> <!-- row -->
	  </div>
	</form>
</nav>

<div class="panel panel-default container-fluid">
	<div class="panel-body">

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
		<table class="base_list" id="table_base_list">
			<thead>
				<tr>
					<td class='c_counter'>LfdNr.</td>
					<td  class='c_id_only'>CoronaID</td>
					<td class='c_id'>CoronaID</td>
					<td class='c_pin'>PIN</td>				
				</tr>
			</thead>
			<tbody>
<?php
		$cnt=0;
		foreach($CoronaIdList as $cid) {
			$cnt++;
			echo "<tr class='corona_id_row'><td class='c_counter'>{$cnt}</td><td class='c_id_only'>{$cid['corona_key']}</td><td class='c_id'>{$cid['corona_key']}</td><td class='c_pin'>{$cid['pin']}</td></tr>";
		}
?>		
		</tbody></table>
	</div><!-- panel-body -->
</div><!-- container -->

<nav class="navbar-default navbar-fixed-bottom navbar_bottom">
      <div class="container">
		<div class="row">
			<div class="btn-group col-xs-12 button_bottom_row"  role="group">
				<form method="post" class="form-inline" >
					<div class="dropdown">
						<button type="button"  class="btn btn-default image_only gen_pdf dropdown-toggle" data-toggle="dropdown"  <?php echo empty($CoronaIdList)?" disabled='disabled' ":""; ?>>
								PDF
								<span class="caret"></span>
						</button>
						  <ul class="dropdown-menu">
							  <li><a href="#" class="papersize" id="list_a4" >Liste (A4)</a></li>
							  <li><a href="#" class="papersize" id="list_a5">Liste (A5)</a></li>
							  <li><a href="#" class="papersize" id="singlepage_a4">Einzelseiten (A4)</a></li>
							  <li><a href="#" class="papersize" id="singlepage_a5">Einzelseiten (A5)</a></li>
						  </ul>
					</div>				
					
					
					<button type="submit" name="action" value="logout" class="btn btn-default image_only logout">Logout</button>
					<button type="button"  class="btn btn-default image_only list_print" onclick="javascript:window.print();" <?php echo empty($CoronaIdList)?" disabled='disabled' ":""; ?> >Drucken</button>
					<button type="button"  class="btn btn-default image_only list_csv"  <?php echo empty($CoronaIdList)?" disabled='disabled' ":""; ?> >CSV</button>
				</form>
			</div>
		</div> <!-- row -->
      </div> <!-- container -->
</nav>

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