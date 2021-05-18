<?php
	include 'header.php';
/*
** Experiment post file.
*/

//	Are we logged and have we rights to write.
$sql = 'SELECT u_creator
		FROM series';
$u_id = Tempo::$db->request($sql);

if(Tempo::$session->is_logged() && $_SESSION['u_auth'] > 0)
{	
	/*
	** Managed the list with the time and the selected option.
	** -----
	** $int ::		Integer that is currently examinated.
	** $time ::		The integer saved in the database.
	*/
	function selected($int, $time)
	{
		$str = '<option value="' . $int . '" ';
		if($int == $time)
		{
			$str .= 'selected="selected"';
		}
		$str .= '>' . $int . '</option>';
		echo $str;
	}
		
	//	Are we modifying the experiment ? Requires rights level 2 (read and write its own experiment)
	if(isset($_GET['modify_exp']) && (($u_id['u_creator'] == $_SESSION['uid'] && $_SESSION['u_auth'] > 1) || $_SESSION['u_auth'] > 3))
	{
		$_GET['modify_exp'] = Tempo::$db->escape(htmlspecialchars($_GET['modify_exp']));
		
		$sql = 'SELECT *
				FROM series
				WHERE serie_id=' . $_GET['modify_exp'];
		$result = Tempo::$db->query($sql);
		$data = Tempo::$db->row($result);
		Tempo::$db->free($result);
		
		$title = stripslashes($data['title']);
		$description = stripslashes($data['description']);
		$date = Date::convert_date($data['serie_date']);
		$protocol = $data['protocol_id'];
		$hour = $date['hour'];
		$minute = $date['minute'];
		$day = $date['day'];
		$month = $date['month'];
		$year = $date['year'];
		$serie_type = $data['type_id'];
		$is_modified = $data['serie_id']; // This variable allows us to remember that it is a change
		$valid = $data['is_valid'];
		
		// Reload parameters
		$sql = 'SELECT *
				FROM serie_params
				WHERE serie_id=' . $_GET['modify_exp'];
		$results = Tempo::$db->query($sql);
		$i = 0;
		$arr = array();
		while($params = Tempo::$db->row($results))
		{
			$arr[$i] = $params['serie_param_id'];
			$j = 0;
			$explode = explode('.', $params['concat']);
			foreach($explode as $value)
			{
				$sql = 'SELECT name
						FROM voc
						WHERE real_id = \'' . $value . '\'';
				$result = Tempo::$db->request($sql);
				$_SESSION['exps'][$i][$j]['id'] = $value;
				$_SESSION['exps'][$i][$j]['name'] = $result['name'];
				$j++;
			}
			if($params['value_int'] != NULL) $_SESSION['exps'][$i]['entry'] = rtrim($params['value_int'], '0');
			elseif($params['value_text'] != '') $_SESSION['exps'][$i]['entry'] = $params['value_text'];
			
			$_SESSION['exps'][$i]['unit']['id'] = $params['unit_id']; // TODO : display and save into session the correct value (need a conversion)
			$sql = 'SELECT units
					FROM conversion
					WHERE id=\'' . $params['unit_id'] . '\'';
			$unit = Tempo::$db->request($sql);
			$_SESSION['exps'][$i]['unit']['name'] = $unit['units'];
			
			if($params['parent_id'] != '') $_SESSION['exps'][$i]['depends'] = array_search($params['parent_id'], $arr);
			$i++;
		}
		Tempo::$db->free($results);
	}
	else if(isset($_GET['modify_exp']) && $_SESSION['u_auth'] < 2)
	{
		Display::error('You can not edit this experiment.', TRUE, ROOT . 'index.php');
	}
	// It is a new experiment
	else
	{
		$title = '';
		$description = '';
		$protocol_id = '';
		$hour = date('G');
		$minute = date('i');
		$day = date('j');
		$month = date('n');
		$year = date('Y');
		$serie_type = '';
		$is_modified = 0;
		$valid = FALSE;
	}
?>
<script language="javascript" src="js/overlayer.js" type="text/javascript"></script>

<form name="exp" action="exp.php" method="post">
	<p>Title of the project : <input name="title" type="text" value="<?php echo $title; ?>" size="30" /></p>
    <p>Description : <br /><textarea name="description" cols="50" rows="5"><?php echo $description; ?></textarea></p>
    <p>Protocol used : <select name="protocol">
    						<?php $sql = 'SELECT * FROM protocols';
								$results = Tempo::$db->query($sql);
								while($prot = Tempo::$db->row($results))
								{
									($prot['protocol_id'] == $protocol_id) ? $selected = 'selected="selected"' : $selected = '';
									echo '<option value="' . $prot['protocol_id'] . '" ' . $selected . '">' . $prot['name'] . '</option>';
								}?>
    					</select>
    </p>
    <p>Experiment date :
    	<select name="hour">
			<?php for($h = 0; $h <= 23; $h++){ selected($h, $hour); } ?>
        </select> : 
        <select name="min">
			<?php for($min = 0; $min <= 60; $min++){ selected($min, $minute); } ?>
        </select> - 
		<select name="month">
			<?php for($m = 1; $m <= 12; $m++){ selected($m, $month); } ?>
        </select> / 
    	<select name="day">
			<?php for($d = 1; $d <= 31; $d++){ selected($d, $day); } ?>
		</select> / 
        <select name="year">
			<?php for($y = date('Y'); $y >= 1990; $y--){ selected($y, $year); } ?>
        </select>
        (hh:mm - MM/DD/YYYY)
    </p>
    <p>Type of experiment :
		<select name="serie_type">
         <?php $sql = 'SELECT type_id, name FROM serie_type';
		 	$results = Tempo::$db->query($sql);
			
			while($data = Tempo::$db->row($results))
			{
				echo '<option value="' . $data['type_id'] . '">' . $data['name'] . '</option>';
			} ?>
        </select>
    </p>
    <div id="selection"><b>Your selection : </b><br /><?php
    	if(isset($_SESSION['exps']))
		{
			ksort($_SESSION['exps']);
			foreach($_SESSION['exps'] as $key_arr => $arr)
			{
				$i = 0;
				$str = '';
				$tab_id = '';
				if(isset($arr['s_delete']) && $arr['s_delete'] == TRUE)
				{
					$s_delete1 = '<strike><i>';
					$s_delete2 = '</strike></i>';
				}
				else
				{
					$s_delete1 = '';
					$s_delete2 = '';
				}
				echo '<div class="line_selection">' . $s_delete1 . 'Selection ' . ($key_arr + 1) . ' : ';
				
				foreach($arr as $key => $value)
				{
					if($key != 0 && $key != 'depends' && $key != 'entry' && $key != 'unit' && $key != 's_delete')
					echo ' ---> ';
					
					if($key === 0 || ($key != 'depends' && $key != 'entry' && $key != 'unit' && $key != 's_delete'))
					{
						if($tab_id != '') $tab_id .= ',';
						
						$tab_id .= $i . '=' . $value['id'] . ':' . $value['name'];
						echo $value['name'];
						$i++;
					}
				}
				(isset($arr['unit']) && $arr['unit']['id'] != 0) ? $unit = $arr['unit']['name'] : $unit = '';
				if(isset($arr['entry']))
				{
					$tab_id .= ',entry=' . $arr['entry'];
					($unit != '') ? $tab_id .= ',unit=' . $arr['unit']['id'] . ':' . $arr['unit']['name'] : '';
					$str .= ' ---> ' . $arr['entry'] . ' ' . $unit;
				}
				if(isset($arr['depends']))
				{
					$tab_id .= ',depends=' . $arr['depends'];
					$str .= ' ---> <i>depends on selection ' . ($arr['depends'] + 1) . '</i>';
				}
				
				if(isset($str)) $str .= $s_delete2 .'&nbsp;&nbsp;&nbsp;<a href="javascript:reload_overlayer(\'' . $tab_id . '\', ' . $key_arr . ');">modify</a> / ';
				if(isset($arr['s_delete']) && $arr['s_delete'] == TRUE)
				{
					$str .= '<a href="javascript:reload_selection(' . $key_arr . ');">reload selection</a>';
				}
				else
				{
					$str .= '<a href="javascript:check_depends(' . $key_arr . ');">delete selection</a>';
				}
				echo $str;
				
				echo '</div>';
			}
		}
		else echo 'nothing at the moment.';
    ?>
    	<div id="hidden"></div>
    </div>
    <div id="overlayer">
		<?php require 'overlayer.php'; ?>
    </div>
    <br />
    <p>Would you valid this experiment ? <input type="checkbox" name="valid" <?php if($valid == 't') echo 'checked="checked"'; ?> value="on"/></p>
    <input type="hidden" name="is_modified" value="<?php echo $is_modified; ?>" />
    <div align="center">
    	<input type="reset" value="Erase" onClick="erase('1');">
    	<input type="submit" value="Save" />
    </div>
</form>
<?php
}
// If it is not the user who save this experiment, it call an error.
else if(Tempo::$session->is_logged() && $u_id['u_creator'] != $_SESSION['uid'])
{
	Display::error('You can not modify this experiment : you have no rights on.', TRUE, ROOT . 'index.php');
}
// If we are not logged.
else
{
	Display::error('You must be logged to enter data.', TRUE, ROOT . 'index.php');
}
	include 'footer.php';
?>