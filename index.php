<?php
	include 'header.php';
?>

<div class="right_box">
<?php
	// Test if the user is connected. If not, perhaps it is a new user -> we show the register box.
	if(Tempo::$session->is_logged())
	{
		$count = 0;
		if($_SESSION['u_auth'] > 3)
		{
			$sql = 'SELECT user_id, login, is_activated
					FROM users';
			$result = Tempo::$db->query($sql);
			while($data = Tempo::$db->row($result))
			{
				if($data['is_activated'] == 'f')
				{
					echo '<a href="user.php?u_id=' . $data['user_id'] . '">Activate <b>' . $data['login'] . '</b></a><br />';
					$count++;
				}
			}
			Tempo::$db->free($result);
		}
		if($count == 0)
		{
			echo 'Nobody to activate';
		}
	}
	else
	{
	?>
		Want to register ? <a href="new_account.php">Click here</a>
</div>
<?php
	}
	include 'footer.php';
?>