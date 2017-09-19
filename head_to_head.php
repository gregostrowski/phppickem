<?php
require('includes/application_top.php');

$weekStats = array();
$playerTotals = array();
$possibleScoreTotal = 0;
calculateStats();

include('includes/header.php');
?>

<h3>Head to Head Standings</h3>

<?php
include('includes/comments.php');

include('includes/footer.php');
?>