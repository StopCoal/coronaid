<?php
	// require_once("functions.php");
?>
<div class="container center-block login_container">
		<div class="col-xs-12" >
			<div class="alert alert-danger <?php echo $login_error; ?> role="alert">
			 Anmeldung fehlgeschlagen!
			</div>
		    <div class="row mt-5">
				<form class="form-signin col" method="post" >
					<h1 class="h3 mb-3 font-weight-normal">Bitte anmelden</h1>
					<div class="form-group form-group-sm">
						<label for="username" class="control-label">Benutzer*innenname</label>
						<input type="text" id="username" name="username" class="form-control"  required autofocus>
					</div>
					<div class="form-group form-group-sm">
						<label for="password" class="control-label">Passwort</label>
						<input type="password" id="password" name="password" class="form-control"  required>
					</div>
					<button class="btn btn-lg btn-primary btn-block" name="action" value="login" type="submit" >Anmelden</button>
				</form>
			</div>
		</div>
</div>
