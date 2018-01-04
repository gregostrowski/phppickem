<?php
require('includes/application_top.php');

$weekStats = array();
$playerTotals = array();
$possibleScoreTotal = 0;
$games = array();
calculateStats();
$weekExpired = ((date("U", time()+(SERVER_TIMEZONE_OFFSET * 3600)) > strtotime($cutoffDateTime)) ? 1 : 0);

include('includes/header.php');
?>
<h1>Standings</h1>
<h3>Week Wins</h3>
<div class="table-responsive">
<table class="table table-striped">
	<tr><th align="left">Week</th><th align="left">Winner</th><th>Total Wins</th></tr>
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
		echo '	<tr' . $rowclass . '><td><a href="/results.php?week=' . $week . '">' . $week . '</a></td><td>' . $winners . '</td><td>' . $stats[highestScore] . '/' . $stats[possibleScore] . '</td></tr>';
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
		<b>By Week Wins</b><br />
		<div class="table-responsive">
			<table class="table table-striped">
				<tr><th align="left">Player</th><th align="left">Week Wins</th><th>Total Wins</th></tr>
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
				<tr><th align="left">Player</th><th align="left">Week Wins</th><th>Total Wins</th></tr>
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
	$sql .= "where u.userName <> 'admin' AND (p.survivor = s.homeID OR p.survivor = s.visitorID) AND s.type = 'REG'";
	$sql .= "order by p.userID, s.gameID";
	$query = $mysqli->query($sql);
	$i = 0;
	$survivorTotals = array();
	while ($row = $query->fetch_assoc()) {
		$playerPicks[$row['userID']][$row['weekNum']] = $row;
		if (!empty($games[$row['gameID']]['winnerID']) && $row['survivor'] == $games[$row['gameID']]['winnerID']) {
			//player has picked the winning team
			$survivorTotals[$row['userID']] += 1;
		}
		$i++;
	}
	$query->free;

	$sql = "select distinct weekNum from " . DB_PREFIX . "schedule where type = 'REG' order by weekNum;";
	$query = $mysqli->query($sql);

	?>
<div class="row">
	<div class="col-xs-12">
		<h3>Survivor</h3>
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
				<tr>
					<th>Player</th>
					<?php
					$totalWeeks = 0;
					while ($row = $query->fetch_assoc()) {
						echo '<th style="text-align:center;">'.$row['weekNum'].'</th>';
						$totalWeeks++;
					}
					$query->free;
					?>
					<th>Streak</th>
					<th>Best</th>
				</tr>
			</thead>
			<tbody>
			<?php
				arSort($survivorTotals);
				foreach($survivorTotals as $userID => $totalCorrect) {
					$pick = '';
					$tmpUser = $login->get_user_by_id($userID);
					$tmpScore = 0;
					$origStreak = 0;
					$bestScore = 0;
					$weeksPlayed = 0;
					$alive = true;
					$hideMyPicks = 1;
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
					$playerRow = $playerPicks[$userID];
					foreach($playerRow as $weekSurvivor) {
						$gameIsLocked = gameIsLocked($weekSurvivor['gameID']);
						$hidePicks = hidePicks($userID, $week);
						$pick = '<img src="images/logos/' . $weekSurvivor['survivor'] . '.svg" / title="'.$weekSurvivor['survivor'].'" height="28" width="42">';
						if (!$gameIsLocked && !$weekExpired && $hidePicks && (int)$userID !== (int)$user->userID) {
							$pick = '***';
						}
						if (!empty($games[$weekSurvivor['gameID']]['winnerID'])) {
							//score has been entered
							if ($games[$weekSurvivor['gameID']]['winnerID'] == $weekSurvivor['survivor']) {
								$pick = '<span class="winner">' . $pick . '</span>';
								if($alive) {
									$origStreak++;
								}
								$tmpScore++;
								if($tmpScore > $bestScore) {
									$bestScore = $tmpScore;
								}
							} else {
								$alive = false;
								$tmpScore = 0;
							}
						}


						echo '	<td align="center">' . $pick . '</td>';
						$weeksPlayed++;
					}
					if($weeksPlayed != $totalWeeks) {
						echo '<td colspan="'.($totalWeeks - $weeksPlayed).'"></td>';
					}
					echo '<td>'.$origStreak.'</td>';
					echo '<td>'.$bestScore.'</td>';
					echo '</tr>';
				}
			?>
			</tbody>
			</table>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xs-12">
		<?php
			echo '<iframe src="'.HEAD_TO_HEAD_URL.'" width="100%" height="500"></iframe>';
		?>
	</div>
</div>

<?php
include('includes/comments.php');

include('includes/footer.php');
?>