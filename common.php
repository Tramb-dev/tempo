<?php
/*
** Return data from a file (extension or file).
*/
function get_file_data($filename, $type)
{
	$tmp = explode('.', basename($filename));
	switch ($type)
	{
		case 'extension' :
			return (strtolower($tmp[count($tmp) - 1]));
		break;

		case 'filename' :
			unset($tmp[count($tmp) - 1]);
			return (implode('.', $tmp));
		break;
	}
}

/*
** Write data into a file.
** -----
** $code ::			Data to write
*/
function file_write($filename, $code)
{
	$fd = @fopen($filename, 'w');
	if (!$fd)
	{
		return (FALSE);
	}
	flock($fd, LOCK_EX);
	fwrite($fd, $code);
	flock($fd, LOCK_UN);
	fclose($fd);
	@chmod($filename, 0666);
	return (TRUE);
}

/*
** Convert the number given by the user in another unit, 
-----
** $num:		number given by the user in a unit choosen by the user.
** $unit_id:	id linked to the unit choosen by the user.
*/
function convert_numeric($num, $unit_id)
{
	$sql = 'SELECT id, conversion, ref
			FROM conversion
			WHERE id=' . $unit_id;
	$result = Tempo::$db->query($sql);
	$convert = Tempo::$db->row($result);
	Tempo::$db->free($result);
	
	if($convert['id'] != $convert['ref'])
	{
		$num .= $convert['conversion'];
		eval("\$num = $num;");
	}
	return $num;
}
?>