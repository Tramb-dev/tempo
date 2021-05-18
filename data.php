<?php
	include 'header.php';

	if (Tempo::$session->is_logged()) // TODO : see below
	{
		if(isset($_GET['exp']))
		{
			?><div id="display_exp">
            <?php
			$sql = 'SELECT s.serie_id, s.parent_id, s.exp_id, s.type_id, s.title, s.description, s.serie_date, s.date_created, s.date_modified, s.u_creator, s.u_modif, t.name AS type, u.name, u.last_name, prot.name AS prot_name, modif.name AS modif_name, modif.last_name AS modif_last_name
					FROM series s
					LEFT JOIN serie_type t
						ON s.type_id = t.type_id
					LEFT JOIN users u
						ON s.u_creator = u.user_id
					LEFT JOIN users modif
						ON s.u_modif = modif.user_id
					LEFT JOIN protocols prot
						ON s.protocol_id = prot.protocol_id
					WHERE s.serie_id = ' . htmlspecialchars($_GET['exp']);
			$results = Tempo::$db->query($sql);
			$data = Tempo::$db->row($results);
			?>
                	<label>Title: </label><?php echo $data['title']; ?><br />              
                	<label>Description: </label><?php echo $data['description']; ?><br />          
                	<label>Experiment's date: </label><?php $date = Date::convert_date($data['serie_date']); echo $date['hour'] . ':' . $date['minute'] . ' the ' . $date['month'] . '-' . $date['day'] . '-' . $date['year']; ?><br />
                	<label>Creator: </label><a href="user.php?u_id=<?php echo $data['u_creator']; ?>"><?php echo $data['name'] . ' ' . $data['last_name']; ?></a><br />          
                	<label>Protocol used: </label><?php echo $data['prot_name']; ?><br />
                	<label>Type of experiment: </label><?php echo $data['type']; ?><br />
                
                <?php if(isset($data['u_modif']) && $data['u_modif'] != ''){ ?>
                	<label>Modified by: </label><a href="user.php?u_id=<?php echo $data['u_modif']; ?>"><?php echo $data['modif_name'] . ' ' . $data['modif_last_name']; ?></a><br />
                <?php } 
			Tempo::$db->free($results);
			echo '</div>';

			// Display a link to modify the experiment if we are the creator or we have rights on.
			if(($data['u_creator'] == $_SESSION['uid'] && $_SESSION['u_auth'] == 2) || $_SESSION['u_auth'] > 3)
			{
				echo '<div style="text-align:center;"><a href="' . ROOT . 'post_exp.php?modify_exp=' . $data['serie_id'] . '">Modify this experiment</a></div>';
			}

			?>
            <table class="consult" id="display_params">
				<tr>
					<th width="15px">*</th>
					<th>Parameters</th>
					<th>Dependencies</th>
				</tr>
            <?php
			$sql = 'SELECT value_int, value_text, parent_id, concat, serie_param_id
					FROM serie_params
					WHERE serie_id = ' . htmlspecialchars($_GET['exp']);
			$results = Tempo::$db->query($sql);
			$i = 1;
			$arr = array();
			while($params = Tempo::$db->row($results))
			{
				$arr[$i] = $params['serie_param_id'];
				$str = '';
				$j = 0;
				$explode = explode('.', $params['concat']);
				foreach($explode as $value)
				{
					$sql = 'SELECT name
							FROM voc
							WHERE real_id = \'' . $value . '\'';
					$result = Tempo::$db->request($sql);
					if($j != 0) $str .= ' ---> ';
					$str .= $result['name'];
					$j++;
				}
				if(isset($params['value_int']) && $params['value_int'] != NULL)
				{
					if(isset($params['unit_id']) && $params['unit_id'] != 0)
					{
						// TODO : display the number with the user's unit (need a conversion)
					}
					else
					{
						$str .= ' ---> ' . rtrim($params['value_int'], '0');
					}
				}
				elseif(isset($params['value_text']) && $params['value_text'] != '')
				{
					$str .= ' ---> ' . $params['value_text'];
				}
				echo '<tr>';
					echo '<td>' . $i . '</td>';
					echo '<td>' . $str . '</td>';
					echo '<td>' . array_search($params['parent_id'], $arr) . '&nbsp;</td>'; //&nbsp; is a fix for IE7
				echo '</tr>';
				$i++;
			}
			Tempo::$db->free($results);
			echo '</table>';
			if(($data['u_creator'] == $_SESSION['uid'] && $_SESSION['u_auth'] == 2) || $_SESSION['u_auth'] > 3)
			{
				echo '<div align="center"><a href="' . ROOT . 'post_exp.php?modify_exp=' . $data['serie_id'] . '">Modify this experiment</a></div>';
			}
		}
		else
		{
			$sql = 'SELECT s.serie_id, s.type_id, s.title, s.description, s.serie_date, s.is_valid, t.name AS type, u.name, u.last_name
					FROM series s
					LEFT JOIN serie_type t
						ON s.type_id = t.type_id
					LEFT JOIN users u
						ON s.u_creator = u.user_id';
			$results = Tempo::$db->query($sql);
			?>
			<table class="consult">
				<tr>
					<th width="15px">*</th>
					<th>Title</th>
					<th>Description</th>
					<th>User</th>
					<th>Type</th>
					<th width="120px">Date</th>
				</tr>
			<?php
			$i = 1;
			while($data = Tempo::$db->row($results))
			{
				if($data['is_valid'] == TRUE)
				{
					$date = Date::convert_date($data['serie_date']);
					echo '<tr>';
						echo '<td>' . $i . '</td>';
						echo '<td><a href="data.php?exp=' . $data['serie_id'] . '">' . $data['title'] . '</a></td>';
						echo '<td id="consult_description">' . $data['description'] . '</td>';
						echo '<td>' . $data['name'] . ' ' . $data['last_name'] . '</td>';
						echo '<td>' . $data['type'] . '</td>';
						echo '<td>' . $date['hour'] . ':' . $date['minute'] . ' the ' . $date['month'] . '-' . $date['day'] . '-' . $date['year'] . '</td>';
					echo '</tr>';
					$i++;
				}
			}
			Tempo::$db->free($results);
			?>
			</table>
			<?php
		}
	}
	else
	{
		Display::error('You must be logged to see data.', TRUE, ROOT . 'index.php');
	}
	include 'footer.php';
?>