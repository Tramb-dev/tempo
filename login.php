<?php
	include 'header.php';

/*
** Log in the user on the website
*/
	$login = NULL;
	if(isset($_POST['log']) && isset($_POST['pass']))
	{
		$login = htmlspecialchars($_POST['log']);
		$password = $_POST['pass'];
		
		// First, we just verify if the password given by the user is correct (correct link to the login and match the md5 saved in the database).
		$sql = 'SELECT password, is_activated
				FROM users
				WHERE login=\'' . $login . '\'';
		$tmp = Tempo::$db->request($sql);

		if($tmp['password'] != md5($password))
		{
			Display::message('Bad login or password');
		}
		// The user can not log in if he is not activated
		else if($tmp['is_activated'] == 'f')
		{
			Display::message('You are not yet activated.');
		}
		else
		{
			$sql = 'SELECT user_id, u_auth, login
					FROM users
					WHERE login=\'' . $login . '\'';
			$result = Tempo::$db->request($sql);
			
			$_SESSION['uid'] = $result['user_id'];
			$_SESSION['u_auth'] = $result['u_auth'];
			$_SESSION['login'] = $result['login'];
			$_SESSION['sid'] = Tempo::$session->sid();
			
			// Update the user's last visit
			$insert = Tempo::$db->update('users', array(
					'u_last_visit'	=>	Date::insert_time(CURRENT_TIME),
			), 'WHERE user_id=\'' . $result['user_id'] . '\'');
		}
	}
	
	// If the user is already logged in, we redirect him to the index, no need to relog in.
 	if(Tempo::$session->is_logged())
	{
		Http::redirect('index.php', 0);
	}
	else
	{
?>	
<form action="login.php" method="post">
<table class="login" align="center">	
	<tr>
		<td><label>Login : </label></td>
        <td><input name="log" type="text" value="<?php echo $login; ?>" size="30" /></td>
    </tr>
    <tr>
    	<td><label>Password : </label></td>
        <td><input name="pass" type="password" size="30" /></td>
    </tr>
</table>
    <div align="center"><input type="submit" value="Submit" /></div>
</form>    
 
<?php
	}
?>