<?php
	include 'header.php';

/*
** Register a new user, however, he is not activated by default
*/
	// Check if the fields are well filled.
	$complete = array('name', 'last_name', 'login', 'pass', 'pass_confirm', 'email', 'email_confirm');
	$count = 7;
	foreach($complete AS $value)
	{
		if(isset($_POST[$value]) && $_POST[$value] != '')
		{
			$count--;
		}
		else
		{
			$$value = '';
		}
	}	
	
	if($count == 1)		
	{
		Display::message('You have 1 field left to fill.');
	}
	else if($count > 1 && $count <= 7 && count($_POST) != 0)
	{
		Display::message('You have ' . $count . ' fields left to fill.');
	}
	// If everthing is filled
	else if($count == 0)
	{
		$name = htmlspecialchars($_POST['name']);
		$last_name = htmlspecialchars($_POST['last_name']);
		$login = htmlspecialchars($_POST['login']);
		$pass = md5($_POST['pass']);
		$pass_confirm = md5($_POST['pass_confirm']);
		$email = htmlspecialchars($_POST['email']);
		$email_confirm = htmlspecialchars($_POST['email_confirm']);
		$lab = htmlspecialchars($_POST['lab']);
		
		// Check all data
		if(Tempo::$user->nickname_exists($login))
		{
			Display::message('Login already present in the database. Choose another.');
		}
		else if($pass != $pass_confirm)
		{
			Display::message('Check your password.');
		}
		else if($email != $email_confirm)
		{
			Display::message('Check your email adress.');
		}
		else if(Tempo::$user->email_exists($email))
		{
			Display::message('Email already present in the database.');
		}
		else if(!Tempo::$user->email_valid($email))
		{
			Display::message('Email not valid.');
		}
		// If all is good, we insert data into the database
		else
		{
			$sql = 'SELECT nextval(\'users_user_id_seq\')';
			$id = Tempo::$db->request($sql);
			$user_ip = Tempo::$session->__construct();
			
			$insert = array(
				'user_id'	=>		$id['nextval'],
				'name'	=>			$name,
				'last_name'	=>		$last_name,
				'login'	=>			$login,
				'password'	=>		$pass,
				'email'	=>			$email,
				'ip'	=>			$user_ip,
				'u_auth'	=>		0,
				'is_activated'	=>	'f',
				'creation_date'	=>	Date::insert_time(CURRENT_TIME),
				'lab_id'	=>		$lab,
				'u_last_visit'	=>	Date::insert_time(CURRENT_TIME),
			);
			Tempo::$db->insert('users', $insert);
			Tempo::$user->confirm_administrator($id['nextval'], $name, $email, $user_ip);

			Display::message('You have been registered into the database. You will receive an email when you will be activated.', ROOT . 'index.php', 5);
			exit();
		}
	}
?>
<form action="new_account.php" method="post">
<table class="account">
	<tr>
   	  <td><label>Name : </label></td>
        <td><input name="name" type="text" value="<?php echo $name; ?>" size="30" /></td>
    </tr>
    <tr>
   	  <td><label>Last name : </label></td>
        <td><input name="last_name" type="text" value="<?php echo $last_name; ?>" size="30" /></td>
    </tr>
    <tr>
		<td><label>Login : </label></td>
        <td><input name="login" type="text" value="<?php echo $login; ?>" size="30" /></td>
    </tr>
    <tr>
    	<td><label>Password : </label></td>
        <td><input name="pass" type="password" size="30" /></td>
    </tr>
    <tr>
    	<td><label>Password confirmation : </label></td>
        <td><input name="pass_confirm" type="password" size="30" /></td>
    </tr>
    <tr>
		<td><label>Email : </label></td>
        <td><input name="email" type="text" value="<?php echo $email; ?>" size="30" /></td>
    </tr>
    <tr>
    	<td><label>Email confirmation : </label></td>
        <td><input name="email_confirm" type="text" value="<?php echo $email_confirm; ?>" size="30" /></td>
    </tr>
    <tr>
		<td><label>Laboratory : </label></td>
      <td>    	
       	<select name="lab">
				<?php 
					$sql = 'SELECT lab_id, name
							FROM lab';
					$result = Tempo::$db->query($sql);
					while($labo = Tempo::$db->row($result))
					{
						$str = '<option value="' . $labo['lab_id'] . '"';
						if($labo['lab_id'] == $lab)
						{
							$str .= ' selected="selected"';
						}
						$str .= '>' . $labo['name'] . '</option>';
						echo $str;
					}
					Tempo::$db->free($result);
				?>
       	</select>
	  </td>
    </tr>
</table>
<input type="submit" value="Register" />
</form>
<?php	
	include 'footer.php';
?>