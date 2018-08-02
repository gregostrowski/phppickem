<?php
require_once('includes/application_top.php');
require('includes/classes/team.php');

if ($_SESSION['logged'] === 'yes' && !$user->is_admin) {
  header("Location: entry_form.php");
  die('<h1>You are not authorized to view this page</h1>');
  }
if (!isset($_REQUEST['user'])) $user->userID = 1; else $user->userID = $_REQUEST['user'];
if ($_POST['action'] == 'Submit') {

  $week = $_POST['week'];
  $cutoffDateTime = getCutoffDateTime($week);

  //update summary table
  $sql = "delete from " . DB_PREFIX . "picksummary where weekNum = " . $_POST['week'] . " and userID = " . $_POST['userID'] . " and year = " . SEASON_YEAR . ";";
  $mysqli->query($sql) or die('Error updating picks summary: ' . $mysqli->error);
  $sql = "insert into " . DB_PREFIX . "picksummary (weekNum, userID, showPicks, tieBreakerPoints, survivor, year) values (" . $_POST['week'] . ", " . $_POST['userID'] . ", " . (int)$_POST['showPicks'] . ", " . (int)$_POST['tiebreaker'] . ", '" . $_POST['survivor'] . ", ".SEASON_YEAR.");";
  $mysqli->query($sql) or die('Error updating picks summary: ' . $mysqli->error);

  //loop through non-expire weeks and update picks (Greg edit: ignore time, admin can do anything they want)
  $sql = "select * from " . DB_PREFIX . "schedule where weekNum = " . $_POST['week'] . " and year = " . SEASON_YEAR;
  //" and (DATE_ADD(NOW(), INTERVAL " . SERVER_TIMEZONE_OFFSET . " HOUR) < gameTimeEastern and DATE_ADD(NOW(), INTERVAL " . SERVER_TIMEZONE_OFFSET . " HOUR) < '" . $cutoffDateTime . "');";
  $query = $mysqli->query($sql);
  if ($query->num_rows > 0) {
    while ($row = $query->fetch_assoc()) {
      $sql = "delete from " . DB_PREFIX . "picks where userID = " . $_POST['userID'] . " and gameID = " . $row['gameID'];
      $mysqli->query($sql) or die('Error deleting picks: ' . $mysqli->error);

      if (!empty($_POST['game' . $row['gameID']])) {
        $sql = "insert into " . DB_PREFIX . "picks (userID, gameID, pickID) values (" . $_POST['userID'] . ", " . $row['gameID'] . ", '" . $_POST['game' . $row['gameID']] . "')";
        $mysqli->query($sql) or die('Error inserting picks: ' . $mysqli->error);
      }
    }
  }
  $query->free;
  //header('Location: results.php?week=' . $_POST['week']);
  //exit;
} else {
  $week = (int)$_REQUEST['week'];
  if (empty($week)) {
    //get current week
    $week = (int)getCurrentWeek();
  }
  $cutoffDateTime = getCutoffDateTime($week);
  $firstGameTime = getFirstGameTime($week);
  $teamList = getTeamsList();
}

include('includes/header.php');

//display week nav
$sql = "select distinct weekNum from " . DB_PREFIX . "schedule where year = " . SEASON_YEAR ." order by weekNum;";
$query = $mysqli->query($sql);
$weekNav = '<div id="weekNav" class="row">';
$weekNav .= '  <div class="navbar3 col-xs-12"><b>Go to week:</b> ';
$i = 0;
if ($query->num_rows > 0) {
  while ($row = $query->fetch_assoc()) {
    if ($i > 0) $weekNav .= ' | ';
    if ($week !== (int)$row['weekNum']) {
      $weekNav .= '<a href="admin_entry_form.php?week=' . $row['weekNum'] . '">' . $row['weekNum'] . '</a>';
    } else {
      $weekNav .= $row['weekNum'];
    }
    $i++;
  }
}
$query->free;
$weekNav .= '  </div>' . "\n";
$weekNav .= '</div>' . "\n";
echo $weekNav;
?>

    <div class="row">

      <div id="content" class="col-md-8 col-xs-12">
        <h2>Week <?php echo $week; ?> - Make Your Picks:</h2>
        <p>Please make your picks below for each game.</p>

<form action="admin_entry_form.php?week=<?php echo $week;?>" method="post" >
<select name="user" onchange="this.form.submit();">
<option></option>
<?php
$sql = "SELECT userName, userID FROM " . DB_PREFIX . "users order by userName asc ";
echo $sql;
$query = $mysqli->query($sql);
while ($result = mysqli_fetch_array($query,MYSQLI_ASSOC)) {

if ($user->userID == $result['userID']) $sel = "Selected"; else $sel="";
echo "<option $sel value=\"$result[userID]\">$result[userName]</option> \n";

}?>
<input type="hidden" name="week" value="<?php echo $week;?>">
</select>
</form>


        <script type="text/javascript">
        function checkform() {
          //make sure all picks have a checked value
          var f = document.entryForm;
          var allChecked = true;

          var allR = document.getElementsByTagName('input');
          for (var i=0; i < allR.length; i++) {
            if(allR[i].type == 'radio') {
              if (!radioIsChecked(allR[i].name)) {
                allChecked = false;
              }
            }
          }
          if (!allChecked) {
              return confirm('One or more picks are missing for the current week.  Do you wish to submit anyway?');
          }
          if (document.getElementById('tiebreaker').value == ""){
            return confirm('You have not entered a tiebreaker score!  Do you want to submit anyway?');
          }
          if(document.getElementById('survivor').value === "") {
            return confirm('You have not entered a survivor pick');
          }
          return true;
        }
        function radioIsChecked(elmName) {
          var elements = document.getElementsByName(elmName);
          for (var i = 0; i < elements.length; i++) {
            if (elements[i].checked) {
              return true;
            }
          }
          return false;
        }
        </script>
  <div style="float: right; width: 270px; margin-right: 10px"><?php //include('includes/comments.php'); ?></div>
  <?php
    //get existing picks
  $picks = getUserPicks($week, $user->userID);
  $survivorPicks = getSurvivorPrevPicks($user->userID);
  $survivorPick = "";

  //get show picks status
  $sql = "select * from " . DB_PREFIX . "picksummary where weekNum = " . $week . " and year = " . SEASON_YEAR . " and userID = " . $user->userID . ";";
  $query = $mysqli->query($sql);
  if ($query->num_rows > 0) {
    $row = $query->fetch_assoc();
    $showPicks = (int)$row['showPicks'];
    $tiebreaker = $row['tieBreakerPoints'];
    $survivorPick = $row['survivor'];
  } else {
    $showPicks = 1;
    $tiebreaker="";
  }
  $query->free;

  //display schedule for week
  $sql = "select s.*, (DATE_ADD(NOW(), INTERVAL " . SERVER_TIMEZONE_OFFSET . " HOUR) > gameTimeEastern or DATE_ADD(NOW(), INTERVAL " . SERVER_TIMEZONE_OFFSET . " HOUR) > '" . $cutoffDateTime . "')  as expired ";
  $sql .= "from " . DB_PREFIX . "schedule s ";
  $sql .= "inner join " . DB_PREFIX . "teams ht on s.homeID = ht.teamID ";
  $sql .= "inner join " . DB_PREFIX . "teams vt on s.visitorID = vt.teamID ";
  $sql .= "where s.weekNum = " . $week . " and year = " . SEASON_YEAR . " ";
  $sql .= "order by s.gameTimeEastern, s.gameID";
  //echo $sql;
  $query = $mysqli->query($sql) or die($mysqli->error);
  if ($query->num_rows > 0) {
    echo '<form name="entryForm" action="admin_entry_form.php" method="post" onsubmit="return checkform();">' . "\n";
    echo '<input type="hidden" name="week" value="' . $week . '" />' . "\n";
    //echo '<table cellpadding="4" cellspacing="0" class="table1">' . "\n";
    //echo '  <tr><th>Home</th><th>Visitor</th><th align="left">Game</th><th>Time / Result</th><th>Your Pick</th></tr>' . "\n";
    echo '    <div class="row" style="margin-top: 15px">'."\n";
    echo '      <div class="col-xs-12">'."\n";
    $i = 0;
    while ($row = $query->fetch_assoc()) {
      $scoreEntered = false;
      $homeTeam = new team($row['homeID']);
      $visitorTeam = new team($row['visitorID']);
      $homeScore = (int)$row['homeScore'];
      $visitorScore = (int)$row['visitorScore'];
      $winnerID = null;
      $visitorClass = "";
      $homeClass = "";
      $correctClass = "";
      echo '<div class="col-sm-6">' . "\n";
      echo '  <div class="matchup panel">' . "\n";
      if (!empty($homeScore) || !empty($visitorScore)) {
        //if score is entered, show score
        $scoreEntered = true;
        $homeScore = (int)$row['homeScore'];
        $visitorScore = (int)$row['visitorScore'];
        if ($homeScore > $visitorScore) {
          $winnerID = $row['homeID'];
          $homeClass = "winner";
          $visitorClass = "loser";
        } else if ($visitorScore > $homeScore) {
          $winnerID = $row['visitorID'];
          $visitorClass = "winner";
          $homeClass = "loser";
        };
        $correctClass = $winnerID == $picks[$row['gameID']]['pickID'] ? 'text-success' : 'text-danger';
        //$winnerID will be null if tie, which is ok
        echo '    <div class="panel-title title final '. $correctClass .'">Final</div>' . "\n";
      } else {
        //else show time of game
        echo '    <div class="panel-title title">' . formatDateTimezone($row['gameTimeEastern']) . '</div>' . "\n";
      }
      echo '      <div class="panel-body">' . "\n";
      // Visitor
      echo '      <label for="' . $row['gameID'] . $visitorTeam->teamID . '" class="team label-for-check '. $visitorClass .'" >' . "\n";
      echo '        <input class="radio-input" type="radio" name="game' . $row['gameID'] . '" value="' . $visitorTeam->teamID . '" id="' . $row['gameID'] . $visitorTeam->teamID . '"' . (($picks[$row['gameID']]['pickID'] == $visitorTeam->teamID || ($picks[$row['gameID']]['pickID'] == '' && $fanaticPick == $visitorTeam->teamID)) ? ' checked' : '') . ' />'."\n";
      echo '         <img src="images/logos/'.$visitorTeam->teamID.'.svg" />' . "\n";
      echo '        <div class="details">' . "\n";
      echo '          <div class="name"><span>'. $visitorTeam->city . '</span> <span>' . $visitorTeam->team .'</span></div>' . "\n";
      $teamRecord = trim(getTeamRecord($visitorTeam->teamID,$week));
      $teamStreak = trim(getTeamStreak($visitorTeam->teamID,$week));
      echo '          <div class="record">'.$teamRecord. ', '. $teamStreak .'</div>'. "\n";
      echo '        </div>' . "\n";
      echo '        <div class="score">'. ($scoreEntered ? $visitorScore : "") . '</div>' . "\n";
      echo '      </label>' . "\n";
      // Home
      echo '      <label for="' . $row['gameID'] . $homeTeam->teamID . '" class="team label-for-check '. $homeClass .'" >' . "\n";
      echo '        <input class="radio-input" type="radio" name="game' . $row['gameID'] . '" value="' . $homeTeam->teamID . '" id="' . $row['gameID'] . $homeTeam->teamID . '"' . (($picks[$row['gameID']]['pickID'] == $homeTeam->teamID || ($picks[$row['gameID']]['pickID'] == '' && $fanaticPick == $homeTeam->teamID)) ? ' checked' : '') . ' />'."\n";
      echo '         <img src="images/logos/'.$homeTeam->teamID.'.svg" />' . "\n";
      echo '        <div class="details">'. "\n";
      echo '          <div class="name"><span>'. $homeTeam->city . '</span> <span>' . $homeTeam->team .'</span></div>' . "\n";
      $teamRecord = trim(getTeamRecord($homeTeam->teamID,$week));
      $teamStreak = trim(getTeamStreak($homeTeam->teamID,$week));
      echo '          <div class="record">'.$teamRecord. ', '. $teamStreak .'</div>'. "\n";
      echo '        </div>' . "\n";
      echo '        <div class="score">'. ($scoreEntered ? $homeScore : "") . '</div>' . "\n";
      echo '      </label>' . "\n";
      echo '    </div>' . "\n"; // panel-body
      echo '  </div>' . "\n"; // matchup
      echo '</div>'; //col-sm-6

    }
    echo '    </div>' . "\n";
    if (SHOW_TIEBREAKER_POINTS) {
        echo '          <div title="Tiebreaker" class="row bg-row1">'."\n";
        echo '            <div class="col-xs-12 center">' . "\n";
        echo '              <p>Combined score in Monday night\'s game<br /><strong>'.$visitorTeam->team.' vs '. $homeTeam->team.'</strong><br />'." \n";
        echo '              <input style="text-align:center;" type="text" name="tiebreaker" id="tiebreaker" maxlength="3" size=12 value="' . $tiebreaker . '" /> ' . " \n";
        echo '            </div>'."\n";
        echo '          </div>'."\n";

    } else {
        echo '          <input type="hidden" name="tiebreaker" id="tiebreaker" value="0" />' . "\n";
    }

    echo '          <div title="Tiebreaker" class="row bg-row1">'."\n";
    echo '            <div class="col-xs-12 center">' . "\n";
    echo '              <p>Survior Pick  <br />'."\n";
    echo '                <select name="survivor" id="survivor">'."\n";
    if($survivorPick == "") {
      echo '                  <option value=""></option>'."\n";
    }
    foreach( $teamList as $team) {
      if(!in_array($team, $survivorPicks) or $team == $survivorPick) {
        echo '<option value="'.$team.'" '. ($team == $survivorPick ? "selected" : "") .'>'.$team.'</option>'."\n";
      }
    }
    echo '                </select>'."\n";
    echo '              </p>'."\n";
    echo '            </div>'."\n";
    echo '          </div>'."\n";

    echo '<input type="hidden" name="userID" value="'.$_REQUEST['user'].'">';
    echo '<p class="noprint col-sm-12"><input type="submit" name="action" value="Submit" class="btn btn-primary" /></p>' . "\n";
    if (ALWAYS_HIDE_PICKS) {
      echo '<p class="noprint"><input type="hidden" name="showPicks" id="showPicks" value="0"' . (($showPicks) ? ' checked="checked"' : '') . ' /> <label for="showPicks">' . "\n";
    } else {
      echo '<p class="noprint"><input type="checkbox" name="showPicks" id="showPicks" value="1"' . (($showPicks) ? ' checked="checked"' : '') . ' /> <label for="showPicks">Allow others to see my picks</label></p>' . "\n";
    }
    echo '</form>' . "\n";
  }

echo '  </div>'."\n"; // end col
echo '  </div>'."\n"; // end entry-form row

//echo '<div id="comments" class="row">';
//include('includes/comments.php');
//echo '</div>';

include('includes/footer.php');

?>