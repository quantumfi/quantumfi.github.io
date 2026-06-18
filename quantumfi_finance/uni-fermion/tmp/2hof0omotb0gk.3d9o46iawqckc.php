<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
	<base href="/">
    <link rel="icon" href="/img/logo.png">

    <title>FERMION | Fermi Crypto Currency</title>

    <!--link rel="stylesheet" href="//quantumfi.net/shared/bootstrap/css/bootstrap.min.css"-->
    <link rel="stylesheet" href="//quantumfi.com.au/shared/bootstrap/css/bootstrap.flatly.min.css">
    <!--link rel="stylesheet" href="/minority-game/views/css/dashboard.css"-->
	<link rel="stylesheet" href="views/css/main.css">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="views/js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="/minority-game/views/js/lt_IE9/html5shiv.min.js"></script>
      <script src="/minority-game/views/js/lt_IE9/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="views/js/jquery.min.js"></script>
    <script src="//quantumfi.com.au/shared/bootstrap/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="views/js/ie10-viewport-bug-workaround.js"></script>
	<!--div class="container"-->
	<?php if (isset($err_message)): ?>
	<div class="alert alert-dismissible alert-danger"><?= ($err_message) ?></div> 
	<?php endif; ?>
	<?php if (isset($message)): ?>
	<div class="alert alert-dismissible alert-success"><?= ($message) ?></div> 
	<?php endif; ?>
	<?php if (isset($warn_message)): ?>
	<div class="alert alert-dismissible alert-warning"><?= ($warn_message) ?></div> 
	<?php endif; ?>
	
	<div class="navbar navbar-default">
		<div class="container">
		<div class="navbar-header">
		  <a href="gamespace" class="navbar-brand"><img src="/img/logo.png" title="FERMION | Fermi Crypto Currency" alt="FERMION | Fermi Crypto Currency" height="40" style="top: 10px;position: absolute; left: 10px;border-radius: 3px;"> </a>
		  <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		  </button>
		</div>
		<div class="navbar-collapse collapse" id="navbar-main">
		  <ul class="nav navbar-nav">
			<li><a>FERMION</a></li>
		  </ul>
		  <?php if (isset($SESSION['user'])): ?>
		  <ul class="nav navbar-nav navbar-right">
			<li><a href="createGame"><span class="glyphicon glyphicon-plus"></span> Create Game</a></li>
			<li><a href="logoutGame"><span class="glyphicon glyphicon-list"></span> Game List</a></li>
			<li><a href="gamespace"><span class="glyphicon glyphicon-refresh"></span> Refresh</a></li>
			<li><a href="logout" onclick="return confirm('Are you sure to quit this game?')"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
		  </ul>
		  <?php endif; ?>
		</div>
		</div>
	</div>

	<?php echo $this->render($inc,NULL,get_defined_vars(),0); ?>
	<!--/div-->
</body></html>