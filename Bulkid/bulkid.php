<?php

	session_start();
	require_once(pathinfo(__FILE__,PATHINFO_FILENAME) . "_funcs.php");
		
?>
<!doctype html>
<html class="no-js" lang="">

<head>
  <meta charset="utf-8">
  <title>Corona ID Listen</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="/wp-content/uploads/2020/02/favicon-100x100.png" sizes="32x32" />
	<link rel="icon" href="/wp-content/uploads/2020/02/favicon.png" sizes="192x192" />
	<link rel="apple-touch-icon" href="/wp-content/uploads/2020/02/favicon.png" />


  <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS; ?>">
  <link rel="stylesheet" href="bulkid_main.css">

</head>

<body >
<?php include($inc); ?>

<script type="text/javascript" src="/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp"></script>
<script type="text/javascript" src="<?php echo BOOTSTRAP_JS; ?>"></script>
<!--<script type="text/javascript" src="/custom/libs/jsPDF/html2canvas.min.js"></script>-->
<script type="text/javascript" src="<?php echo JSPDF_POLY_JS; ?>"></script>
<script type="text/javascript" src="<?php echo JSPDF_JS; ?>"></script>
<script type="text/javascript" src="<?php echo JSPDF_AUTOTABLE_JS; ?>"></script>

<script type="text/javascript" src="bulkid_main.js"></script>
</body>
</html>