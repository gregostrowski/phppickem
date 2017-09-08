<?php
require('includes/application_top.php');

$weekStats = array();
$playerTotals = array();
$possibleScoreTotal = 0;
calculateStats();

include('includes/header.php');
?>
<h1>Standings</h1>
<h3>Weekly Stats</h3>
<div class="table-responsive">
<table class="table table-striped">
	<tr><th align="left">Week</th><th align="left">Winner(s)</th><th>Score</th></tr>
<?php
if (isset($weekStats)) {
	$i = 0;
	foreach($weekStats as $week => $stats) {
		$winners = '';
		if (is_array($stats[winners])) {
			foreach($stats[winners] as $winner => $winnerID) {
				$tmpUser = $login->get_user_by_id($winnerID);
				switch (USER_NAMES_DISPLAY) {
					case 1:
						$winners .= ((strlen($winners) > 0) ? ', ' : '') . trim($tmpUser->firstname . ' ' . $tmpUser->lastname);
						break;
					case 2:
						$winners .= ((strlen($winners) > 0) ? ', ' : '') . $tmpUser->userName;
						break;
					default: //3
						$winners .= ((strlen($winners) > 0) ? ', ' : '') . '<abbr title="' . trim($tmpUser->firstname . ' ' . $tmpUser->lastname) . '">' . $tmpUser->userName . '</abbr>';
						break;
				}
			}
		}
		$rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
		echo '	<tr' . $rowclass . '><td>' . $week . '</td><td>' . $winners . '</td><td align="center">' . $stats[highestScore] . '/' . $stats[possibleScore] . '</td></tr>';
		$i++;
	}
} else {
	echo '	<tr><td colspan="3">No weeks have been completed yet.</td></tr>' . "\n";
}
?>
</table>
</div>

<h3>User Stats</h3>
<div class="row">
	<div class="col-md-4 col-xs-12">
		<b>By Name</b><br />
		<div class="table-responsive">
			<table class="table table-striped">
				<tr><th align="left">Player</th><th align="left">Week Wins</th><th>Wins</th></tr>
			<?php
			if (isset($playerTotals)) {
				//arsort($playerTotals);
				$i = 0;
				foreach($playerTotals as $playerID => $stats) {
					$rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
					$pickRatio = $stats[score] . '/' . $possibleScoreTotal;
					$pickPercentage = number_format((($stats[score] / $possibleScoreTotal) * 100), 2) . '%';
					switch (USER_NAMES_DISPLAY) {
						case 1:
							echo '	<tr' . $rowclass . '><td class="tiny">' . $stats[name] . '</td><td class="tiny" align="center">' . $stats[wins] . '</td><td class="tiny" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
						case 2:
							echo '	<tr' . $rowclass . '><td class="tiny">' . $stats[userName] . '</td><td class="tiny" align="center">' . $stats[wins] . '</td><td class="tiny" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
						default: //3
							echo '	<tr' . $rowclass . '><td class="tiny"><abbr title="' . $stats[name] . '">' . $stats[userName] . '<abbr></td><td class="tiny" align="center">' . $stats[wins] . '</td><td class="tiny" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
					}
					$i++;
				}
			} else {
				echo '	<tr><td colspan="3">No weeks have been completed yet.</td></tr>' . "\n";
			}
			?>
			</table>
		</div>
	</div>
	<div class="col-md-4 col-xs-12">
		<b>By Week Wins</b><br />
		<div class="table-responsive">
			<table class="table table-striped">
				<tr><th align="left">Player</th><th align="left">Week Wins</th><th>Wins</th></tr>
			<?php
			if (isset($playerTotals)) {
				arsort($playerTotals);
				$i = 0;
				foreach($playerTotals as $playerID => $stats) {
					$rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
					$pickRatio = $stats[score] . '/' . $possibleScoreTotal;
					$pickPercentage = number_format((($stats[score] / $possibleScoreTotal) * 100), 2) . '%';
					switch (USER_NAMES_DISPLAY) {
						case 1:
							echo '	<tr' . $rowclass . '><td class="tiny">' . $stats[name] . '</td><td class="tiny" align="center">' . $stats[wins] . '</td><td class="tiny" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
						case 2:
							echo '	<tr' . $rowclass . '><td class="tiny">' . $stats[userName] . '</td><td class="tiny" align="center">' . $stats[wins] . '</td><td class="tiny" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
						default: //3
							echo '	<tr' . $rowclass . '><td class="tiny"><abbr title="' . $stats[name] . '">' . $stats[userName] . '</abbr></td><td class="tiny" align="center">' . $stats[wins] . '</td><td class="tiny" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
					}
					$i++;
				}
			} else {
				echo '	<tr><td colspan="3">No weeks have been completed yet.</td></tr>' . "\n";
			}
			?>
			</table>
		</div>
	</div>
	<div class="col-md-4 col-xs-12">
		<b>By Total Wins</b><br />
		<div class="table-responsive">
			<table class="table table-striped">
				<tr><th align="left">Player</th><th align="left">Week Wins</th><th>Wins</th></tr>
			<?php
			if (isset($playerTotals)) {
				$playerTotals = sort2d($playerTotals, 'score', 'desc');
				$i = 0;
				foreach($playerTotals as $playerID => $stats) {
					$rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
					$pickRatio = $stats[score] . '/' . $possibleScoreTotal;
					$pickPercentage = number_format((($stats[score] / $possibleScoreTotal) * 100), 2) . '%';
					switch (USER_NAMES_DISPLAY) {
						case 1:
							echo '	<tr' . $rowclass . '><td class="tiny">' . $stats[name] . '</td><td class="tiny" align="center">' . $stats[wins] . '</td><td class="tiny" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
						case 2:
							echo '	<tr' . $rowclass . '><td class="tiny">' . $stats[userName] . '</td><td class="tiny" align="center">' . $stats[wins] . '</td><td class="tiny" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
						default: //3
							echo '	<tr' . $rowclass . '><td class="tiny"><abbr title="' . $stats[name] . '">' . $stats[userName] . '</abbr></td><td class="tiny" align="center">' . $stats[wins] . '</td><td class="tiny" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
					}
					$i++;
				}
			} else {
				echo '	<tr><td colspan="3">No weeks have been completed yet.</td></tr>' . "\n";
			}
			?>
			</table>
		</div>
	</div>

	<div class="col-xs-12">
		<?php
			echo '<iframe src="'.HEAD_TO_HEAD_URL.'" width="100%" height="500"></iframe>';
		?>
	</div>

	<?php
	$week = (int)$_GET['week'];
	if (empty($week)) {
		//get current week
		$week = (int)getCurrentWeek();
	}

	//get array of player picks
	$playerPicks = array();
	$sql = "select p.userID, p.weekNum, p.survivor, s.gameID ";
	$sql .= "from " . DB_PREFIX . "picksummary p ";
	$sql .= "inner join " . DB_PREFIX . "users u on p.userID = u.userID ";
	$sql .= "inner join " . DB_PREFIX . "schedule s on p.weekNum = s.weekNum ";
	$sql .= "where u.userName <> 'admin' AND (p.survivor = s.homeID OR p.survivor = s.visitorID) ";
	$sql .= "order by p.userID, s.gameID";
	$query = $mysqli->query($sql);
	$i = 0;
	while ($row = $query->fetch_assoc()) {
		$playerPicks[$row['userID']][$row['weekNum']] = $row;
		$i++;
	}
	$query->free;

	$sql = "select distinct weekNum from " . DB_PREFIX . "schedule order by weekNum;";
	$query = $mysqli->query($sql);
	?>

	<!-- <h3>Survivor</h3>
	<div class="col-xs-12">
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
				<tr>
					<th>Player</th>
					<?php
					$totalWeeks = 0;
					while ($row = $query->fetch_assoc()) {
						echo '<th>'.$row['weekNum'].'</th>';
						$totalWeeks++;
					}
					$query->free;
					?>
					<th>Score</th>
				</tr>
			</thead>
			<tbody>
			<?php
				foreach($playerPicks as $userID => $playerRow) {
					$tmpUser = $login->get_user_by_id($userID);
					$tmpScore = 0;
					$weeksPlayed = 0;
					echo '	<tr>' . "\n";
					switch (USER_NAMES_DISPLAY) {
						case 1:
							echo '		<td>' . trim($tmpUser->firstname . ' ' . $tmpUser->lastname) . '</td>';
							break;
						case 2:
							echo '		<td>' . trim($tmpUser->userName) . '</td>';
							break;
						default: //3
							echo '		<td><abbr title="' . trim($tmpUser->firstname . ' ' . $tmpUser->lastname) . '">' . trim($tmpUser->userName) . '</abbr></td>';
							break;
					}

					foreach($playerRow as $weekSurvivor) {
						echo '	<td>' . $weekSurvivor['survivor'] . '</td>';
						$weeksPlayed++;
					}
					if($weeksPlayed != $totalWeeks) {
						echo '<td colspan="'.($totalWeeks - $weeksPlayed).'"></td>';
					}
					echo '<td>'.$tmpScore.'/'.$totalWeeks.'</td>';
					echo '</tr>';
				}
			?>
			</tbody>
			</table>
		</div>
	</div> -->

</div>

<?php
include('includes/comments.php');

include('includes/footer.php');
?>