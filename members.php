<?php
	include 'header.php';
	
	if(Tempo::$session->is_logged())
	{
?>
<table class="members">
	<tr>
    	<th>*</th>
    	<th>Name</th>
        <th>Last name</th>
        <th>Laboratory</th>
        <th>Last visit</th>
    </tr>
<?php
	$sql = 'SELECT u.user_id, u.name, u.last_name, u.is_activated, u.u_last_visit, l.name AS l_name
			FROM users u
			LEFT JOIN lab l
				ON u.lab_id = l.lab_id
			ORDER BY u.user_id';
	$result = Tempo::$db->query($sql);
	$i = 1;
	while($data = Tempo::$db->row($result))
	{
		($data['is_activated'] == 'f') ? $activated = ' *' : $activated = '';
		echo '<tr>';
		echo '<td>' . $i . $activated . '</td>';
		echo '<td><a href=user.php?u_id=' . $data['user_id'] . '>' . $data['name'] . '</a></td>';
		echo '<td><a href=user.php?u_id=' . $data['user_id'] . '>' . $data['last_name'] . '</a></td>';
		echo '<td>' . $data['l_name'] . '</td>';
		echo '<td>' . $data['u_last_visit'] . '</td>';
		echo '</tr>';
		$i++;
	}
?>
</table>
<?php
	}
	else
	{
		Display::error('You need to be logged.', TRUE, ROOT . 'index.php', 3);
	}

	include 'footer.php';
?>