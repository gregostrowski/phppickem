<?php
require('includes/application_top.php');

include('includes/classes/class.formvalidation.php');

if ($_POST['action'] == 'update') {
	$my_form = new validator;
	if($my_form->checkEmail($_POST['email'])) { // check for good mail

		if ($my_form->validate_fields('firstname,lastname,email,password')) { // comma delimited list of the required form fields
			if ($_POST['password'] == $_POST['password2']) {
				$salt = substr($crypto->encrypt((uniqid(mt_rand(), true))), 0, 10);
				$secure_password = $crypto->encrypt($salt . $crypto->encrypt($_POST['password']));
				$sql = "update " . DB_PREFIX . "users ";
				$sql .= "set password = '".$secure_password."', salt = '".$salt."', firstname = '".$_POST['firstname']."', lastname = '".$_POST['lastname']."', email = '".$_POST['email']."' ";
				$sql .= "where userID = " . $user->userID . ";";
				//die($sql);
				$mysqli->query($sql) or die($mysqli->error);

				//set confirmation message
				$display = '<div class="responseOk">Account updated successfully.</div><br/>';
			} else {
				$display = '<div class="responseError">Passwords do not match, please try again.</div><br/>';
			}
		} else {
			$display = str_replace($_SESSION['email_field_name'], 'Email', $my_form->error);
			$display = '<div class="responseError">' . $display . '</div><br/>';
		}
	} else {
		$display = '<div class="responseError">There seems to be a problem with your email address, please check.</div><br/>';
	}
} else if ($_POST['action'] == 'editfanatic') {
	$fanaticPick = $_POST['fanatic'] == 'none' ? null : $_POST['fanatic'];

	$sql = "update ".DB_PREFIX."users ";
	$sql .= "set fanatic ='".$fanaticPick."' where userID = " . $user->userID . ";";
	$mysqli->query($sql) or die($mysqli->error);

	//set confirmation message
	$display = '<div class="responseOk">Fanatic pick updated successfully.</div><br/>';
}

include('includes/header.php');

$teamList = getTeamsList();
$fanaticPick = getUserFanatic($user->userID);

$sql = "select * from " . DB_PREFIX . "users where userID = " . $user->userID;
$query = $mysqli->query($sql);
if ($query->num_rows > 0) {
	$row = $query->fetch_assoc();
	$firstname = $row['firstname'];
	$lastname = $row['lastname'];
	$email = $row['email'];
}

if (!empty($_POST['firstname'])) $firstname = $_POST['firstname'];
if (!empty($_POST['lastname'])) $lastname = $_POST['lastname'];
if (!empty($_POST['email'])) $email = $_POST['email'];
?>
	<h1>Edit User Account Details</h1>
	<?php if(isset($display)) echo $display; ?>
	<div class="row">
		<div class="col-sm-6">
			<form action="user_edit.php" method="post" name="edituser">
				<input type="hidden" name="action" value="update">
				<fieldset>
					<legend style="font-weight:bold;">Enter User Details:</legend>
					<div class="form-group">
				    <label for="firstname">First Name</label>
				    <input class="form-control" type="text" name="firstname" placeholder="First Name" value="<?php echo $firstname; ?>">
				  </div>

				  <div class="form-group">
				    <label for="lastname">Last Name</label>
				    <input class="form-control" type="text" name="lastname" placeholder="Last Name" value="<?php echo $lastname; ?>">
				  </div>

				  <div class="form-group">
				    <label for="email">Email</label>
				    <input class="form-control" type="text" name="email" placeholder="Email" value="<?php echo $email; ?>" size="30">
				  </div>

				  <div class="form-group">
				    <label for="password">New Password</label>
				    <input class="form-control" type="password" placeholder="New Password" name="password" value="">
				  </div>

				  <div class="form-group">
				    <label for="password2">Confirm Password</label>
				    <input class="form-control" type="password" placeholder="Confirm Password" name="password2" value="">
				  </div>

				  <div class="form-group">
				  	<input type="submit" name="submit" value="Submit" class="btn btn-primary">
				  </div>
				</fieldset>
			</form>
		</div>
		<div class="col-sm-6">
			<form action="user_edit.php" method="post" name="editfanatic">
				<input type="hidden" name="action" value="editfanatic">
				<legend style="font-weight:bold;">&nbsp;</legend>
				<fieldset>
					<div class="form-group">
				    <label>Fanatic Pick (auto select this team on entry form)</label>
				    <div class="col-xs-8" style="padding-left:0">
							<select name="fanatic" id="fanatic" class="form-control">
								<?php
									echo '<option value="none">None</option>'."\n";
						    foreach( $teamList as $team) {
						    	echo '<option value="'.$team.'" '. ($team == $fanaticPick ? "selected" : "") .'>'.$team.'</option>'."\n";
						    }
					    ?>
					    </select>
					  </div>
					  <div class="col-xs-4" style="padding-right: 0">
					  	<input type="submit" name="SubmitFanatic" value="Update" class="btn btn-primary" />
					  </div>
				  </div>
			  </fieldset>
			</form>
		</div>
	</div>
<?php
include('includes/footer.php');
