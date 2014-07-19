<form method="POST" action="">
<input type="text" name="nick" placeholder="nick" maxlength="<?php echo NICK_MAX_LENGTH?>"><br/>
<input type="password" name="password" placeholder="password" maxlength="<?php echo PASSWORD_MAX_LENGTH?>"><br/>
<input name="submit" type="submit" value="Register">
</form>

<?php
if(isset($_POST['submit'])){
	require_once 'php/class/DB.php';
	
	if(strlen($_POST['nick'] <= NICK_MAX_LENGTH) && strlen($_POST['password'] <= PASSWORD_MAX_LENGTH)){
		$db = new DB();
		$db -> create_new_user($_POST['nick'], $_POST['password']);
		
		echo '<br/>Account created.';
	}
}
?>
