<?php
	include 'header.php';
	
	// Manage the account of a member
	
	$complete = array('name', 'last_name', 'login', 'email', 'previous_pass', 'pass', 'pass_confirm', 'lab_id');

	$sql = 'SELECT name, last_name, login, password, email, lab_id
			FROM users
			WHERE user_id=' . $_SESSION['uid'];
	$result = Tempo::$db->request($sql);
	$update = array();

	$p_pass = $result['password'];
	$post = FALSE;
	
	foreach($complete AS $value)
	{
		if($value == 'pass' || $value == 'previous_pass' || $value == 'pass_confirm')
		{
			if(isset($_POST[$value]) && ($_POST[$value] != ''))
			{
				if($value == 'pass')
				{
					$update['password']	= md5($_POST['pass']);
					$$value = md5($_POST['pass']);
				}
				else
				{
					$$value = md5($_POST[$value]);
				}
				$post = TRUE;
			}
			else
			{
				$$value = '';
			}
		}
		else
		{
			if(isset($_POST[$value]) && ($_POST[$value] != '') && ($_POST[$value] != $result[$value]))
			{
				$update[$value]	= htmlspecialchars($_POST[$value]);
				$$value = htmlspecialchars($_POST[$value]);
				$post = TRUE;
			}
			else
			{
				$$value = $result[$value];
			}
		}
	}

	if(Tempo::$user->nickname_exists($login) && array_key_exists('login', $update))
	{
		Display::message('Login already present in the database. Choose another.');
	}
	else if(array_key_exists('password', $update) && ($previous_pass != $p_pass))
	{
		Display::message('Wrong previous password.');
	}
	else if(($pass != $pass_confirm) && array_key_exists('password', $update))
	{
		Display::message('Check your password.');
	}
	else if(!(Tempo::$user->email_valid($email)) && array_key_exists('email', $update))
	{
		Display::message('Email not valid.');
	}
 	else if($post)
	{
		Tempo::$db->update('users', $update, 'WHERE user_id=\'' . $_SESSION['uid'] . '\'');
		
		Display::message('Updated!');
	}
?>	
<form action="account.php" method="post">
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
    	<td><label>Previous password : </label></td>
        <td><input name="previous_pass" type="password" size="30" /></td>
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
		<td><label>Laboratory : </label></td>
      <td>    	
       	<select name="lab_id">
				<?php 
					$sql = 'SELECT lab_id, name
							FROM lab';
					$result = Tempo::$db->query($sql);
					while($lab = Tempo::$db->row($result))
					{
						$str = '<option value="' . $lab['lab_id'] . '"';
						if($lab['lab_id'] == $lab_id)
						{
							$str .= ' selected="selected"';
						}
						$str .= '>' . $lab['name'] . '</option>';
						echo $str;
					}
					Tempo::$db->free($result);
				?>
       	</select>
	  </td>
    </tr>
</table>
<div align="center"><a href="<?php echo ROOT . 'user.php?u_id=' . $_SESSION['uid']; ?>">View my experiments</a></div>
<input type="submit" value="Submit" />
</form>
<?php	
	include 'footer.php';
?>