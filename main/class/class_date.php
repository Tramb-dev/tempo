<?php
/*
** Convert the time to unify dataset in the database.
*/
class Date extends Tempo_model
{
	public function __construct()
	{
		$timezone = timezone_open('Europe/London');
		
		return $timezone;
	}
	
	/*
	** Format the time to be insered correctly in the database (timestamp with/without timezone).
	** -----
	** $timestamp ::		Timestamp
	** $tz ::				Timezone, if FALSE, set to the timezone of the database.
	*/
	public function insert_time($timestamp, $tz = FALSE)
	{
		if($tz = FALSE)
		{
			$time = gmdate('Y-m-d G:i:s');
		}
		else
		{
			$time = gmdate('Y-m-d G:i:sO');
		}
		return $time;
	}
	
	/*
	** Return an array with all details of the date. In most cases sorted by the database.
	** -----
	** $date ::		Date. Format : "2006-12-12 10:00:00.5+02".
	*/
	public function convert_date($date)
	{
		$tab = date_parse($date);
		if ($tab['minute'] < 10) $tab['minute'] = '0' . $tab['minute'];
	
		return $tab;
	}
	
	/*
	** Format the 10 first minutes by adding a 0. Needed by the date() function.
	*/
	public function format_minutes()
	{
		$ary = array();
		for($i = 0; $i < 10; $i++)
		{
			$ary[$i] = '0' . $i;
		}
		for($i = 10; $i < 60; $i++)
		{
			$ary[$i] = $i;
		}
		return $ary;
	}
}
?>