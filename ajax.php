<?php
/*
**	Ajax files: its job is to send information to JScript for the overlayer.
*/
	include 'main/start.php';
	session_start();
	
	/*
	** Return a list of vocabulary from a parent.
	*/
	if(isset($_POST['id']))
	{
		$sql = 'SELECT name, is_field, real_id, infos
				FROM voc
				WHERE parent_id=\'' . htmlspecialchars($_POST['id']) . '\'
				ORDER BY real_id';	
				
		$result = Tempo::$db->query($sql);
		$list = Tempo::$db->rows($result);
		Tempo::$db->free($result);
		
		// We put the ID into a session. If we have selected an ID from the left cell, we remove the last entry and save the new.
		if(isset($_POST['pop']) && $_POST['pop'] == 'true' && $_POST['session'] == 'true')
		{
			$pop = array('s_delete', 'unit', 'depends', 'entry');
			foreach($pop as $value)
			{
				if(isset($_SESSION['tree'][$value]))
				{
					unset($_SESSION['tree'][$value]);
				}
			}
			array_pop($_SESSION['tree']);
		}
		if(!isset($_POST['session']) || $_POST['session'] == 'true')
		{
			(isset($_SESSION['tree'])) ? $count = count($_SESSION['tree']) : $count = 0;
			$_SESSION['tree'][$count]['id'] = $_POST['id'];
			$_SESSION['tree'][$count]['name'] = $_POST['name'];
		}
		
		echo json_encode($list);
	}
	/*
	** Send the protocol used
	*/
	elseif(isset($_POST['protocol']))
	{
		$sql = 'SELECT description
				FROM protocols
				WHERE protocol_id =\'' . htmlspecialchars($_POST['protocol']) . '\'';
		$result = Tempo::$db->request($sql);
		
		echo $result;
	}
	/*
	** Check the grandpa to reload the left cell.
	*/
	elseif(isset($_POST['check_parent']))
	{
		if(!isset($_SESSION['tree']))
		{
			echo '-1';
		}
		else
		{
			echo search_grandpa($_POST['check_parent']);
		}
	}
	/*
	** Used when we want to go back with the little arrow at the left side.
	*/
	elseif(isset($_POST['go_back']) && $_POST['go_back'] == 'true')
	{
		// We are just at the beginning of the selection, we don't need to go back.
		if(!isset($_SESSION['tree']) || count($_SESSION['tree']) == 1)
		{
			echo '-1';
		}
		else
		{
			$var = end($_SESSION['tree']);
			$tab[0] = search_grandpa($var['id']);
			array_pop($_SESSION['tree']);
			$var = end($_SESSION['tree']);
			$tab[1] = search_grandpa($var['id']);
			
			echo json_encode($tab);
		}
	}
	/*
	** When we have finished the selection, data are stored in another session array. We save all the selection, with the value of the textarea if it is, and with the dependence on another selection if we have.
	*/
	elseif(isset($_POST['save']) && isset($_POST['depend']))
	{
		if(!isset($_SESSION['tree']))
		{
			echo 'Error';
		}
		else
		{
			if(($_POST['save']) != 'null' && $_POST['save'] != '') $_SESSION['tree']['entry'] = $_POST['save'];
			
			if(isset($_POST['unit']) && $_POST['unit'] != 0)
			{
				$unit = search_unit($_POST['unit']);
				$_SESSION['tree']['unit']['name'] = $unit;
				$_SESSION['tree']['unit']['id'] = addslashes($_POST['unit']);
			}
			elseif(isset($_POST['unit']) && $_POST['unit'] == 0)
			{
				$_SESSION['tree']['unit']['name'] = 'null';
				$_SESSION['tree']['unit']['id'] = 0;
			}
			
			if($_POST['depend'] != 0) $_SESSION['tree']['depends'] = $_POST['depend'] - 1;

			if(isset($_POST['change']) && $_POST['change'] != 'null')
			{
				$change = $_POST['change'];
				
				// Verification of a difference between what the user send and the already present data in the session array (TRUE == different arrays).
				if(recursive_comparison($_SESSION['tree'], $_SESSION['exps'][$change]) == TRUE)
				{
					unset($_SESSION['exps'][$change]);
					$_SESSION['exps'][$change] = $_SESSION['tree'];
				}
			}
			else
			{
				$_SESSION['exps'][] = $_SESSION['tree'];
			}
			unset($_SESSION['tree']);
			ksort($_SESSION['exps']);
			echo json_encode($_SESSION['exps']);
		}
	}
	/*
	** Return the session array to see what we have already save into and modify it if we need.
	*/
	elseif(isset($_POST['session_tab']))
	{
		if(!isset($_SESSION['exps']) || $_SESSION['exps'] == NULL) echo 'null';
		
		else echo json_encode($_SESSION['exps']);
	}
	/*
	** Erase all data if the user has clicked on the erase bouton or just actual selection if the user pushed the close button.
	*/
	elseif(isset($_POST['erase']))
	{
		unset($_SESSION['tree']);
		if($_POST['erase'] == 1)
		{
			unset($_SESSION['exps']);
		}
	}
	/*
	** Delete a selection (a line of the exps array)
	*/
	elseif(isset($_POST['check_depends']))
	{
 		$check = htmlspecialchars($_POST['check_depends']);
		$tab = recursive_search($check, $_SESSION['exps']);
		if($tab == FALSE)
		{
			echo 'false';
		}
		else
		{
			foreach($tab as $value)
			{
				$_SESSION['temp_exps'][$value] = $_SESSION['exps'][$value];
			}
			echo json_encode($_SESSION['temp_exps']);
			unset($_SESSION['temp_exps']);
		}
	}
	/*
	** Delete a selection (a line of the exps array) or multi-selection
	*/
	elseif(isset($_POST['delete']) && $_POST['delete'] != NULL)
	{
		$delete = explode(',', htmlspecialchars($_POST['delete']));
		
		if(is_array($delete))
		{
			foreach($delete as $value)
			{
				$_SESSION['exps'][$value]['s_delete'] = TRUE;
			}
		}
	}
	/*
	** Erase dependencies
	*/
	elseif(isset($_POST['delete_depends']) && $_POST['delete_depends'] != NULL)
	{
		$delete = htmlspecialchars($_POST['delete_depends']);
		foreach($_SESSION['exps'] as $key => $value)
		{
			if(isset($value['depends']) && $value['depends'] == $delete)
			{
				unset($_SESSION['exps'][$key]['depends']);
			}
		}
	}
	/*
	** Reload a previously erased selection.
	*/
	elseif(isset($_POST['reload_selection']) && $_POST['reload_selection'] != NULL)
	{
		$reload = htmlspecialchars($_POST['reload_selection']);
		$_SESSION['exps'][$reload]['s_delete'] = NULL;
	}
	/*
	** Reload the overlayer with values previously entered.
	** Data are given in this form : 0=1:Biological sample, 1=817:Identification of the source, depends=1, entry=paper
	** It means that we have first the id 1 with the name Biological sample then the id 817 ... with a dependence of the selection 1 (selection 2 for the user)
	*/
	elseif(isset($_POST['reload_overlayer']))
	{
		if(isset($_SESSION['tree'])) unset($_SESSION['tree']);
		$reload = explode(',', htmlspecialchars($_POST['reload_overlayer']));
		foreach($reload as $value)
		{
			$tab = array();
			$tab = explode('=', $value);
			if(is_numeric($tab[0]) || $tab[0] == 'unit')
			{
				$tab[1] = explode(':', $tab[1]);
				$_SESSION['tree'][$tab[0]]['id'] = $tab[1][0];
				$_SESSION['tree'][$tab[0]]['name'] = $tab[1][1];
			}
			else
			{
				$_SESSION['tree'][$tab[0]] = $tab[1];
			}
		}
		echo json_encode($_SESSION['tree']);
	}
	
/*
** Search into the actual selection tab the parent of the id given in parameters.
*/
function search_grandpa($v)
{
	foreach($_SESSION['tree'] as $key => $value)
	{
		if($value['id'] === $v)
		{
			$pos = $key - 1; 
			if($pos == '-1') return '-1';
			
			else return $_SESSION['tree'][$pos]['id'];
		}
	}
}

function search_unit($id)
{
	$sql = 'SELECT units
			FROM conversion
			WHERE id=' . addslashes($id);
	$result = Tempo::$db->query($sql);
	$unit = Tempo::$db->row($result);
	Tempo::$db->free($result);
	
	return $unit['units'];
}

/*
** This function search recursivly if they are other value linked to the first value. Return an array with concerned value.
** -----
** $id ::				Value to search in the array.
** $tab ::				Multidimensional array in which we will search the values.
** $arr[]['id'] 		Contain the current selection.
** $arr[]['depends']	From which selection is depending.
*/
function recursive_search($id, $tab)
{
	foreach($tab as $key => $value)
	{
		if(isset($value['depends']) && $value['depends'] == $id)
		{
			$arr[] = $key;
			$temp = recursive_search($key, $tab);
			if($temp != FALSE) $arr[] = $temp[0];
		}
	}
	
	if(isset($arr)) return $arr;
	else return FALSE;
}

/*
** Compare recursivly 2 arrays and return true if they are different or false if they are similar
*/
function recursive_comparison($tab1, $tab2)
{
	foreach($tab1 as $key => $value)
	{
		if(is_array($value) && isset($tab2[$key]) && is_array($tab2[$key]))
		{
			if(recursive_comparison($value, $tab2[$key])) return TRUE;
		}
		elseif(!isset($tab2[$key]) || (is_array($value) && !is_array($tab2[$key])))
		{
			return TRUE;
		}
		else
		{
			if($value !== $tab2[$key]) return TRUE;
		}
	}
	return FALSE;
}
?>