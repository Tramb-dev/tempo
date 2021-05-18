<?php
	include 'header.php';

/*
** Save data in the database from post_exp.php. Temporary file, redirection on data.php. // TODO : see below
*/

//	Are we logged ? If not, we cannot enter data.
if(Tempo::$session->is_logged())
{	
	if (isset($_POST['title']) && isset($_POST['serie_type']) && isset($_SESSION['exps']))
	{
		$time = Date::insert_time(mktime(0, 0, 0, intval($_POST['month']), intval($_POST['day']), intval($_POST['year'])), TRUE);
   		$title = addslashes($_POST['title']);
		$description = (isset($_POST['description'])) ? addslashes($_POST['description']) : '';
		$protocol = addslashes($_POST['protocol']);
		$serie_type = addslashes($_POST['serie_type']);
		$is_modified = addslashes($_POST['is_modified']);
		$valid = 'f';
		if(isset($_POST['valid']) && $_POST['valid'] == 'on')
		{
			$valid = 't';
		}
		
		// If we are creating a new entry
		if($is_modified == 0)
		{
			$sql = 'SELECT nextval(\'series_serie_id_seq\')';
			$id = Tempo::$db->request($sql);
			
 			$ary = array(
				'serie_id'		=>	$id['nextval'],
				'title'			=>	$title,
				'description'	=>	$description,
				'protocol_id'	=>	$protocol,
				'type_id'		=>	$serie_type,
				'serie_date'	=>	$time,
				'date_created'	=>	Date::insert_time(CURRENT_TIME),
				'u_creator'		=>	$_SESSION['uid'],
			);
			Tempo::$db->insert('series', $ary);
			
			// We insert data from the overlayer
			$parent_tab = array();
			
			foreach($_SESSION['exps'] as $selection)
			{
				if(!isset($selection['s_delete']) || $selection['s_delete'] == NULL)
				{
					$sql = 'SELECT nextval(\'serie_params_serie_param_id_seq\')'; 
					$param_id = Tempo::$db->request($sql);
					
					$parent_tab[] = $param_id['nextval'];
					$concat = '';
					$tab = array(
						'serie_param_id' =>	$param_id['nextval'],
						'serie_id'		=>	$id['nextval'],
					);
				
					foreach($selection as $key => $value)
					{
						if(is_numeric($key))
						{
							if($concat != '') $concat .= '.';
							
							$concat .= $value['id'];
						}
						elseif($key == 'entry')
						{
							if(is_numeric($value))
							{
								$tab['value_int'] = $value;
							}
							else
							{
								$tab['value_text'] = htmlspecialchars($value);
								$tab['value_int'] = NULL;
							}
						}
						elseif($key == 'depends')
						{
							$tab['parent_id'] = $parent_tab[$value];
						}
						elseif($key == 'unit')
						{
							$tab['unit_id'] = $value['id'];
							if($value['id'] != 0)
							{
								$tab['value_int'] = convert_numeric($tab['value_int'], $value['id']);
							}
						}
					}
					$tab['concat'] = $concat;
					Tempo::$db->insert('serie_params', $tab);
				}
			}
			unset($_SESSION['exps']);
			Display::message('Experiment saved !', ROOT . 'data.php?exp=' . $id);
		}
		else // We are just modifying data thanks to the serie_id
		{
			$ary = array(
				'title'			=>	$title,
				'description'	=>	$description,
				'protocol_id'	=>	$protocol,
				'type_id'		=>	$serie_type,
				'serie_date'	=>	$time,
				'date_modified'	=>	Date::insert_time(CURRENT_TIME),
				'is_valid'		=>	$valid,
				'u_modif'		=>	$_SESSION['uid'],
			);
			Tempo::$db->update('series', $ary, 'WHERE serie_id=' . $is_modified); // TODO : for instance, the modified experiment is not completely saved (need to add paramaters).
			
			unset($_SESSION['exps']);
			Display::message('Experiment modified !', ROOT . 'data.php?exp=' . $is_modified);
		}
	}
	else
	{
		Display::error('There was an error in your data. Check them before sending again.', FALSE);
	}
}
else
{
	Display::error('You can not see this page because you are not log in. Please log in.', TRUE, ROOT . 'index.php');
}
	include 'footer.php';
	
?>