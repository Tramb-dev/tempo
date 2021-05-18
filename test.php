<?php include 'header.php';

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

$arr1 = array(1, 2, array(3, 4), 5);
$arr2 = array(1, 2, array(2, 3, 4), 5);
$arr3 = array(1, 2, array(3, 4), 5);

				if(recursive_comparison($arr1, $arr2) === TRUE) echo 'test réussi';
				else echo 'test échoué';
