<?php
require('includes/application_top.php');

$week = (int)$_GET['week'];
if (empty($week)) {
	//get current week
	$week = (int)getCurrentWeek();
}

$cutoffDateTime = getCutoffDateTime($week);
$weekExpired = ((date("U", time()+(SERVER_TIMEZONE_OFFSET * 3600)) > strtotime($cutoffDateTime)) ? 1 : 0);

include('includes/header.php');

//display week nav
$sql = "select distinct weekNum from " . DB_PREFIX . "schedule order by weekNum;";
$query = $mysqli->query($sql);
$weekNav = '<div class="navbar3"><b>Go to week:</b> ';
$i = 0;
while ($row = $query->fetch_assoc()) {
	if ($i > 0) $weekNav .= ' | ';
	if ($week !== (int)$row['weekNum']) {
		$weekNav .= '<a href="results.php?week=' . $row['weekNum'] . '">' . $row['weekNum'] . '</a>';
	} else {
		$weekNav .= '<b>' . $row['weekNum'] . '</b>';
	}
	$i++;
}
$query->free;
$weekNav .= '</div>' . "\n";
echo $weekNav;

//get array of games
$allScoresIn = true;
$games = array();
$sql = "select * from " . DB_PREFIX . "schedule where weekNum = " . $week . " order by gameTimeEastern, gameID";
$query = $mysqli->query($sql);
while ($row = $query->fetch_assoc()) {
	$games[$row['gameID']]['gameID'] = $row['gameID'];
	$games[$row['gameID']]['homeID'] = $row['homeID'];
	$games[$row['gameID']]['visitorID'] = $row['visitorID'];

	$games[$row['gameID']][$row['homeID']]['score'] = $row['homeScore'];
	$games[$row['gameID']][$row['visitorID']]['score'] = $row['visitorScore'];
	$games[$row['gameID']]['overtime'] = $row['overtime'];
	$games[$row['gameID']]['final'] = $row['final'];

	$homeScore = (int)$row['homeScore'];
	$visitorScore = (int)$row['visitorScore'];
	if ($homeScore + $visitorScore > 0) {
		if ($homeScore > $visitorScore) {
			$games[$row['gameID']]['winnerID'] = $row['homeID'];
		}
		if ($visitorScore > $homeScore) {
			$games[$row['gameID']]['winnerID'] = $row['visitorID'];
		}
	} else {
		$games[$row['gameID']]['winnerID'] = '';
		$allScoresIn = false;
	}
}
$query->close();

# include('includes/column_right.php');
if (!$allScoresIn) {
	include('includes/column_countdown.php');
}

//get array of player picks
$playerPicks = array();
$playerTotals = array();
$sql = "select p.userID, p.gameID, p.pickID, p.points ";
$sql .= "from " . DB_PREFIX . "picks p ";
$sql .= "inner join " . DB_PREFIX . "users u on p.userID = u.userID ";
$sql .= "inner join " . DB_PREFIX . "schedule s on p.gameID = s.gameID ";
$sql .= "where s.weekNum = " . $week . " and u.userName <> 'admin' ";
$sql .= "order by p.userID, s.gameTimeEastern, s.gameID";
$query = $mysqli->query($sql);
$i = 0;
while ($row = $query->fetch_assoc()) {
	$playerPicks[$row['userID']][$row['gameID']] = $row['pickID'];
	if (!empty($games[$row['gameID']]['winnerID']) && $row['pickID'] == $games[$row['gameID']]['winnerID']) {
		//player has picked the winning team
		$playerTotals[$row['userID']] += 1;
	} else {
		$playerTotals[$row['userID']] += 0;
	}
	$i++;
}
$query->free;
?>
<script type="text/javascript">
$(document).ready(function(){
	$(".table tr").mouseover(function() {
		$(this).addClass("over");}).mouseout(function() {$(this).removeClass("over");
	});
	$(".table tr").click(function() {
		if ($(this).attr('class').indexOf('overPerm') > -1) {
			$(this).removeClass("overPerm");
		} else {
			$(this).addClass("overPerm");
		}
	});
	//$( "#table1" ).draggable({ containment: "#table1-container", scroll: false })
});
</script>
<style type="text/css">
.pickTD { width: 24px; font-size: 9px; text-align: center; }
</style>

		<div class="bg-primary">
			<b>Results - Week <?php echo $week; ?></b>
		</div>

<?php
if (!$allScoresIn) {
	echo '<p style="font-weight: bold; color: #DBA400;">* Not all scores have been updated for week ' . $week . ' yet.</p>' . "\n";
}

$hideMyPicks = hidePicks($user->userID, $week);
if ($hideMyPicks && !$weekExpired) {
	echo '<p style="font-weight: bold; color: green;">* Your picks are currently hidden to other users.</p>' . "\n";
}
?>

<div class="panel"><div class="panel-body">
<div class="table-responsive no-margin">
<table class="table table-striped no-margin">
	<thead>
		<tr><th align="left">Scores</th></tr>
	</thead>
	<tbody>
<?php
	// make list of winners
	$winnerList = array();
	//loop through all games
	echo '<tr>' . "\n";
	echo '<td>Away</td>' . "\n";
	foreach($games as $game) {
		$winnerList[] = $game['winnerID'];
		if ($game['visitorID'] == $game['winnerID'] && (int)$game['final'] == 1) {
			// echo '<td><span class="winner">' . $game[$game['visitorID']]['score'] . '</span></td>' . "\n";
			echo '<td style="font-size:60%;"><span class="winner">' . $game['visitorID'] . "&nbsp;-&nbsp;" . $game[$game['visitorID']]['score'] . '</span></td>' . "\n";
		} else {
			// echo '<td> ' . $game[$game['visitorID']]['score'] . ' </td>' . "\n";
			echo '<td style="font-size:60%;">' . $game['visitorID'] . "&nbsp;-&nbsp;" . $game[$game['visitorID']]['score'] . '</td>' . "\n";
		}
	}
	echo '</tr>' . "\n";
	echo '<tr>' . "\n";
	echo '<td>Home</td>' . "\n";
	foreach($games as $game) {
		if ($game['homeID'] == $game['winnerID'] && (int)$game['final'] == 1) {
			// echo '<td><span class="winner">' . $game[$game['homeID']]['score'] . '</span></td>' . "\n";
			echo '<td style="font-size:60%;"><span class="winner">' . $game['homeID'] . "&nbsp;-&nbsp;" . $game[$game['homeID']]['score'] . '</span></td>' . "\n";
		} else {
			// echo '<td> ' . $game[$game['homeID']]['score'] . ' </td>' . "\n";
			echo '<td style="font-size:60%;">' . $game['homeID'] . "&nbsp;-&nbsp;" . $game[$game['homeID']]['score'] . '</td>' . "\n";
		}
	}
	echo '</tr>' . "\n";
?>
	</tbody>
</table>
</div>
</div></div>

<div class="panel self-picks"><div class="panel-body">
<div class="table-responsive no-margin">
<table class="table table-striped no-margin">
	<thead>
		<tr><th align="left">Player</th><th colspan="<?php echo sizeof($games) -1 ; ?>">Week <?php echo $week; ?></th><th class="center">Tie Breaker</th><th>Survivor</th><th align="left">Score</th></tr>
	</thead>
	<tbody></tbody>
</table>
</div></div></div>
<script>
	$(document).ready(function () {
		if($(".mypick").length) {
			$(".self-picks tbody").html($(".mypick").clone());
		} else {
			$(".self-picks").hide();
		}
	});
</script>

<?php
if (sizeof($playerTotals) > 0) {
?>
<div class="panel"><div class="panel-body">
<div class="table-responsive no-margin">
<table class="table table-striped no-margin">
	<thead>
		<tr><th align="left">Player</th><th colspan="<?php echo sizeof($games) -1 ; ?>">Week <?php echo $week; ?></th><th class="center">Tie Breaker</th><th>Survivor</th><th align="left">Score</th></tr>
	</thead>
	<tbody>
<?php
	$i = 0;
	arsort($playerTotals);
	foreach($playerTotals as $userID => $totalCorrect) {
		$hidePicks = hidePicks($userID, $week);
		$tieBreaker = abs(getMondayCombinedScore($week) - getTieBreaker($userID, $week));

		$survivor = getSurvivorPick($userID, $week);
		$survivorEl = '';
		$survivorPick = '';

		if ($i == 0) {
			$topScore = $totalCorrect;
			$winners[] = $userID;
		} else if ($totalCorrect == $topScore) {
			$winners[] = $userID;
		}
		$tmpUser = $login->get_user_by_id($userID);
		$rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
		//echo '	<tr' . $rowclass . '>' . "\n";
		echo '	<tr '. ($userID == $user->userID ? "class='mypick'":"") .'>' . "\n";
		$nameFormat = "";
		switch (USER_NAMES_DISPLAY) {
			case 1:
				$nameFormat = trim($tmpUser->firstname . ' ' . $tmpUser->lastname);
				break;
			case 2:
				$nameFormat = trim($tmpUser->userName);
				break;
			default: //3
				$nameFormat = '<abbr title="' . trim($tmpUser->firstname . ' ' . $tmpUser->lastname) . '">' . trim($tmpUser->userName) . '</abbr>';
				break;
		}
		$adminPickCount = "";
		if($user->is_admin) {
			$adminPickCount = '('. sizeof($playerPicks[$userID]) . '/'. sizeof($games) .')';
		}
		echo '		<td colspan="4">' . $nameFormat . ' ' . $adminPickCount . '</td></tr>' . "\n";
		echo '<tr '. ($userID == $user->userID ? "class='mypick'":"") .'>' . "\n";
		//loop through all games
		foreach($games as $game) {
			$pick = '';
			$pick = $playerPicks[$userID][$game['gameID']];

			if(empty($pick)){$pick = 'no_pick';}
			// $score = $game[$pick]['score'] ;
			// $pick = '<img src="images/helmets_small/' . $pick . 'R.gif" / title="'.$pick.'">';
			$pick = '<img src="images/logos/' . $pick . '.svg" / title="'.$pick.'" height="28" width="42">';

			if (!empty($game['winnerID'])) {
				//score has been entered
				if ($playerPicks[$userID][$game['gameID']] == $game['winnerID']) {
					$pick = '<span class="winner">' . $pick . '</span>';
				}
			} else {
				//mask pick if pick and week is not locked and user has opted to hide their picks
				$gameIsLocked = gameIsLocked($game['gameID']);
				if (!$gameIsLocked && !$weekExpired && $hidePicks && (int)$userID !== (int)$user->userID) {
					$pick = '***';
					$tieBreaker = '***';
					if($survivor == $game['visitorID'] || $survivor == $game['homeID']) {
						$survivorPick = '***';
					}
				}
			}
			// echo '		<td class="pickTD"><img src="images/helmets_small/' . $pick . 'R.gif" /></td>' . "\n";
			echo '		<td class="pickTD">' . $pick . '</td>' . "\n";
		}
		if(!is_null($survivor) && $survivor != '') {
			if($survivorPick == '***') {
				$survivorEl = $survivorPick;
			} else {
				$survivorEl = '<img src="images/logos/' . $survivor . '.svg" / title="'.$survivor.'" height="28" width="42">';
				if(in_array($survivor, $winnerList)) {
					$survivorEl = '<span class="winner">' . $survivorEl . '</span>';
				}
			}
		}
		echo '<td class="center"> '. $tieBreaker .' </td>';
		echo '<td class="pickTD">'. $survivorEl .'</td>';
		echo '		<td nowrap><b>' . $totalCorrect . '/' . sizeof($games) . '</b></td>' . "\n";
		echo '	</tr>' . "\n";
		$i++;
	}

	//if all scores entered, display winner
	if ($allScoresIn) {
		foreach($winners as $winnerID) {
			$winner = $login->get_user_by_id($winnerID);
			if (strlen($winnersHtml) > 0) $winnersHtml .= ', ';
			switch (USER_NAMES_DISPLAY) {
				case 1:
					$winnersHtml .= trim($winner->firstname . ' ' . $winner->lastname);
					break;
				case 2:
					$winnersHtml .= trim($winner->userName);
					break;
				default: //3
					$winnersHtml .= '<abbr title="' . trim($winner->firstname . ' ' . $winner->lastname) . '">' . $winner->userName . '</abbr>';
					break;
			}
		}
		echo '	<tr><th colspan="' . (sizeof($games) + 2) . '" align="left">Week Winner: ' . $winnersHtml . '</th></tr>' . "\n";
	}
?>
	</tbody>
</table>
</div>
</div></div>
<?php
	//display list of absent players
	$sql = "select * from " . DB_PREFIX . "users where `status` = 1 and userID not in(" . implode(',', array_keys($playerTotals)) . ") and userName <> 'admin'";
	$query = $mysqli->query($sql);
	if ($query->num_rows > 0) {
		$absentHtml = '<p><b>Absent Players:</b> ';
		$i = 0;
		while ($row = $query->fetch_assoc()) {
			if ($i > 0) $absentHtml .= ', ';
			switch (USER_NAMES_DISPLAY) {
				case 1:
					$absentHtml .= trim($row['firstname'] . ' ' . $row['lastname']);
					break;
				case 2:
					$absentHtml .= $row['userName'];
					break;
				default: //3
					$absentHtml .= '<abbr title="' . trim($row['firstname'] . ' ' . $row['lastname']) . '">' . $row['userName'] . '</abbr>';
					break;
			}
			$i++;
		}
		echo $absentHtml;
	}
	$query->free;
}

include('includes/column_stats.php');

include('includes/comments.php');

include('includes/footer.php');
