<?php
	include 'header.php';

/*
**	Display the user's information and allow to activate him by an administrator if he is not activated.
*/	
	if(isset($_GET['u_id']))
	{
		$u_id = htmlspecialchars($_GET['u_id']);

		$sql = 'SELECT u.user_id, u.lab_id, u.name, u.last_name, u.email, u.u_last_visit, u.is_activated, l.name AS l_name
				FROM users u
				LEFT JOIN lab l
					ON u.lab_id = l.lab_id
				WHERE user_id=\'' . $u_id . '\'';
		$data = Tempo::$db->request($sql);
?>	
<table class="user">
	<tr>	
       <td><label>Name : </label></td>
       <td><?php echo $data['name']; ?></td>
	</tr>
    <tr>	
       <td> <label>Last name : </label></td>
        <td><?php echo $data['last_name']; ?></td>
	</tr>
    <tr>	
        <td><label>Laboratory : </label></td>
        <td><?php echo $data['l_name']; ?></td>
	</tr>
    <tr>	
        <td><label>Last visit : </label></td>
        <td><?php echo $data['u_last_visit']; ?></td>
    </tr>
</table>
<?php
		if($data['is_activated'] == 'f' && $_SESSION['u_auth'] > 3 && !isset($_GET['activate']))
		{
			echo '<div align="center"><a href="user.php?u_id=' . $u_id . '&amp;activate=t">Activate this user.</a></div>';
		}
		else if($data['is_activated'] == 'f' && $_SESSION['u_auth'] <= 3 && !isset($_GET['activate']))
		{
			echo 'User not activated.';
		}
		else if(isset($_GET['activate']) && $_GET['activate'] == 't' && $data['is_activated'] == 'f' && $_SESSION['u_auth'] > 3)
		{
			Tempo::$user->confirm_account($u_id, $data['name'], $data['email']);
			
		}
?>
<table class="members">
	<tr>
    	<th>Title</th>
        <th>Description</th>
        <th>Creation date</th>
    </tr>
<?php
/*
**	Displays the experiments validated of the user, or all experiments if they are created by this user.
*/
		$sql = 'SELECT title, description, serie_date, serie_id, is_valid
				FROM series
				WHERE u_creator=' . $u_id;
		$result = Tempo::$db->query($sql);
		while ($value = Tempo::$db->row($result))
		{
			if($value['is_valid'] == TRUE || $u_id = $_SESSION['uid'])
			{
				$date = Date::convert_date($value['serie_date']);
				echo '<tr>';
				echo '<td><a href=post_exp.php?modify_exp=' . $value['serie_id'] . '>' . $value['title'] . '</a></td>';
				echo '<td>' . $value['description'] . '</td>';
				echo '<td>' . $date['hour'] . ':' . $date['minute'] . ' the ' . $date['month'] . '-' . $date['day'] . '-' . $date['year'] . '</td>';
				echo '</tr>';
			}
		}
		Tempo::$db->free($result);
?>
</table>
<?php	
	}
	else
	{
		Display::message('Page not found', ROOT . 'index.php');
	}
	
	
	include 'footer.php';
?>