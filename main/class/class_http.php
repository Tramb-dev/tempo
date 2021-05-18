<?php
/*
** Manage headers, redirections, meta ...
*/
class Http extends Tempo_model
{
	// Page access method
	const GET = 'get';
	const POST = 'post';

	/*
	** Clean variables GET, POST and COOKIE
	*/
	public static function clean_gpc()
	{
		// Delete all variables created by register_globals
		// stripslashes() all the GPC variables for the compatibilité with the DB
		$gpc = array('_GET', '_POST', '_COOKIE');
		$magic_quote = (get_magic_quotes_gpc()) ? TRUE : FALSE;
		$register_globals = TRUE;//(ini_get('register_globals')) ? TRUE : FALSE;

		if ($register_globals || $magic_quote)
		{
			foreach ($gpc AS $value)
			{
				if ($register_globals)
				{
					foreach ($GLOBALS[$value] AS $k => $v)
					{
						if ($k != 'debug')
						{
							unset($GLOBALS[$k]);
						}
					}
				}
				
				if ($magic_quote && isset($GLOBALS[$value]))
				{
					$GLOBALS[$value] = array_map_recursive('stripslashes', $GLOBALS[$value]);
				}
			}
		}
	}
	
	/*
	** Send an HTTP header
	** -----
	** $key ::		Key to send
	** $value ::	Value
	** $replace ::	Erase the previous values
	*/
	public static function header($key, $value, $replace = NULL)
	{
		if ($replace === NULL)
		{
			header($key . ': ' . $value);
		}
		else
		{
			header($key . ': ' . $value, $replace);
		}
	}

	/*
	** Page access method
	*/
	public static function method()
	{
		if (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			return (self::POST);
		}
		return (self::GET);
	}

	/*
	** Automatically redirect.
	** -----
	** $url ::	URL destination
	** $time ::	Time befor the redirection, not redirected if less to 0, redirected immediatly by a header if 0, else redirected by a meta refresh.
	*/
	public static function redirect($url, $time = 0)
	{
		if ($time < 0)
		{
			return ;
		}
		else if($time == 0)
		{
			self::header('location', str_replace('&amp;', '&', Tempo::$session->add_sid($url)));
			exit;
		}
		else
		{
			echo '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '">';
		}
	}

	/*
	** Send a cookie
	** -----
	** $name ::		Name of the cookie
	** $value ::	Value of the cookie
	** $time ::		Expiration time
	*/
	public static function cookie($name, $value, $time)
	{
		setcookie(COOKIE_NAME . $name, $value, $time, COOKIE_PATH, COOKIE_DOMAIN, FALSE, TRUE);
	}

	/*
	** Return the value of a cookie
	** -----
	** $name ::		Name of the cookie
	*/
	public static function getcookie($name)
	{
		$cookie_name = COOKIE_NAME . $name;
		return (isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : NULL);
	}

	/*
	** Return the content of a file on server
	** -----
	** $server ::		Server adress
	** $filename ::		Filename
	*/
	public static function get_file_on_server($server, $filename)
	{
		if ($content = file_get_contents($server . $filename))
		{
			return ($content);
		}
		return (FALSE);
	}

	/*
	** Useof the GZIP compression ?
	*/
	public static function check_gzip()
	{
		if (extension_loaded('zlib') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false) && ini_get('zlib.output_compression') == 'Off')
		{
			ob_start('ob_gzhandler');
		}
		else
		{
			ob_start();
		}
	}

	/*
	** If we want no cache on pages
	*/
	public static function no_cache()
	{
		self::header('Cache-Control', 'post-check=0, pre-check=0', FALSE);
		self::header('Expires', '0');
		self::header('Pragma', 'no-cache');
	}

	/*
	** File downloading
	** -----
	** $filename ::		Filename
	** $content ::		Content of this file
	** $type ::			Mime type of this file
	*/
	public static function download($filename, &$content, $type = 'text/x-delimtext')
	{
		self::header('Pragma', 'no-cache');
		self::header('Content-Type', $type . '; name="' . $filename . '"');
		self::header('Content-disposition', 'inline; filename=' . $filename);

		echo $content;
		exit;
	}
}
?>