<?php
header('Content-Type:text/html; charset=utf-8');
header('X-UA-Compatible:IE=Edge,chrome=1'); //IE8 respects this but not the meta tag when under Local Intranet

function auto_version($file)
{
  if(strpos($file, '/') !== 0 || !file_exists($_SERVER['DOCUMENT_ROOT'] . $file))
    return $file;

  $mtime = filemtime($_SERVER['DOCUMENT_ROOT'] . $file);
  return preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
}
?>
<!DOCTYPE html>
<html xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Smada Pick'em</title>

	<base href="<?php echo SITE_URL; ?>" />
	<link rel="stylesheet" type="text/css" media="all" href="css/bootstrap.min.css?v=1" />
	<link rel="stylesheet" type="text/css" media="all" href="<?php echo auto_version('css/all.css?v=2'); ?>" />
	<link rel="stylesheet" type="text/css" media="all" href="css/custom.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/jquery.countdown.css" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/modernizr-2.7.0.min.js"></script>
	<script type="text/javascript" src="js/svgeezy.min.js"></script>
	<script type="text/javascript" src="js/jquery.main.js"></script>

	<script type="text/javascript" src="js/jquery.jclock.js"></script>
	<script type="text/javascript" src="js/jquery.plugin.min.js"></script>
	<script type="text/javascript" src="js/jquery.countdown.min.js"></script>
</head>

<body>
	<header id="header">
		<div id="top-nav">
			<!-- Static navbar -->
			<div class="navbar navbar-default" role="navigation">
				<div class="container">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<div id="logo" class="navbar-brand"><img src="images/logos/NFL.svg" alt="NFL Pick 'Em <?php echo SEASON_YEAR; ?>" class="img-responsive" /></div>
						<div id="site-title" class="navbar-brand">Smada Pick'em</div>
					</div>
					<div class="navbar-collapse collapse">
						<ul class="nav navbar-nav">
							<li<?php echo (($activeTab == 'home') ? ' class="active"' : ''); ?>><a href="./">Home</a></li>
							<?php if ($user->userName !== 'admin') { ?>
							<li><a href="entry_form.php<?php echo ((!empty($_GET['week'])) ? '?week=' . (int)$_GET['week'] : ''); ?>">Entry Form</a></li>
							<?php } ?>
							<li><a href="results.php<?php echo ((!empty($_GET['week'])) ? '?week=' . (int)$_GET['week'] : ''); ?>">Results</a></li>
							<li><a href="standings.php">Standings</a></li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">NFL News <b class="caret"></b></a>
								<ul class="dropdown-menu">
									<li><a href="nflnews.php">News</a></li>
									<li><a href="teams.php">Teams</a></li>
									<li><a href="schedules.php">Schedules</a></li>
								</ul>
							</li>
							<?php if ($_SESSION['logged'] === 'yes' && $user->is_admin) { ?>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
								<ul class="dropdown-menu">
									<li><a href="scores.php">Enter Scores</a></li>
									<li><a href="admin_entry_form.php">Edit User Picks</a></li>
									<li><a href="send_email.php">Send Email</a></li>
									<li><a href="users.php">Update Users</a></li>
									<li><a href="schedule_edit.php">Edit Schedule</a></li>
									<li><a href="email_templates.php">Email Templates</a></li>
								</ul>
							</li>
							<?php } ?>
						</ul>
						<ul class="nav navbar-nav navbar-right">
							<!-- <li><a href="rules.php" title="Rules/Help"><span class="glyphicon glyphicon-book"></span> <span class="text">Rules/Help</span></a></li> -->
							<li class="dropdown">
								<!--a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $_SESSION['loggedInUser']; ?> <b class="caret"></b></a-->
								<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <span class="text"><?php echo $_SESSION['loggedInUser']; ?></span> <b class="caret"></b></a>
								<ul class="dropdown-menu">
									<li><a href="user_edit.php">My Account</a></li>
									<li><a href="logout.php">Logout <?php echo $user->userName; ?></a></li>
								</ul>
							</li>
						</ul>
					</div><!--/.nav-collapse -->
				</div>
			</div>
		</div>
	</header>
	<div class="container">
	<div id="pageContent">
	<?php
	if ($user->is_admin && is_array($warnings) && sizeof($warnings) > 0) {
		echo '<div id="warnings">';
		foreach ($warnings as $warning) {
			echo $warning;
		}
		echo '</div>';
	}
	?>
