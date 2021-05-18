<?php
/*
** Manage the user's session.
*/

class Session extends Tempo_model
{
	// User IP
	public $ip;
	
	// Session ID
	public $sid;

	/*
	** Return the user's IP adress
	*/
	public function __construct()
	{
		$this->ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $this->ip;
	}
	
	/*
	** Delete a session variable
	** -----
	** $var ::		Variable to delete
	*/
	public function delete($var)
	{
		unset($var);
	}
	
	/*
	** Check if the user is logged. Return FALSE if not.
	*/
	public function is_logged()
	{
		if(!isset($_SESSION['sid']))
		{
			return FALSE;
		}
		return TRUE;
	}
	
	/*
	** Create a session ID
	*/
	public function sid()
	{
		$this->sid = md5(uniqid(CURRENT_TIME));
		return $this->sid;
	}
	
	/*
	** Add the ID to the url
	** -----
	** $url ::		URL to modify
	** $force ::	If TRUE, we force the addition of the SID into the URL
	*/
	public function add_sid($url, $force = FALSE)
	{
		if ($force || self::is_logged())
		{
			$add_end = '';
			if (preg_match('/#([a-z0-9_]+)$/i', $url, $match))
			{
				$add_end = '#' . $match[1];
				$url = preg_replace('/#([a-z0-9_]+)$/i', '', $url);
			}
			$url .= (strstr($url, '?') ? '&amp;' : '?') . 'sid=' . self::sid() . $add_end;
		}	
		return $url;
	}
	
	/*
	** Logout the user from the website
	** -----
	** $sid ::		SID to logout
	*/
	public function logout($sid)
	{
		session_unset();
		session_destroy();
	}
}

?>