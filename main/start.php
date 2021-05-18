<?php
require_once 'config.php';
include_once 'common.php';

/*
** Magic method allowing the dynamic loading of classes
*/
function __autoload($classname)
{
	$classname = strtolower($classname);
	tempo_import($classname);
}

/*
** Intelligent include a file from the class folder
** -----
** $file ::		Nom du fichier
*/
function tempo_import($filename)
{
	static $store;

	if (!isset($store[$filename]))
	{
		$split = explode('_', $filename);
		if (file_exists(ROOT . '/main//class/class_' . $filename . '.php'))
		{
			include_once(ROOT . '/main/class/class_' . $filename . '.php');
		}
		else if (file_exists(ROOT . '/main/class/' . $split[0] . '/' . $filename . '.php'))
		{
			include_once(ROOT . '/main/class/' . $split[0] . '/' . $filename . '.php');
		}
		else if (file_exists(ROOT . '/main/' . $split[0] . '/' . $filename . '.php'))
		{
			include_once(ROOT . '/main/' . $split[0] . '/' . $filename . '.php');
		}
		else if (file_exists(ROOT . '/main/' . $filename . '.php'))
		{
			include_once(ROOT . '/main/' . $filename . '.php');
		}
		$store[$filename] = TRUE;
	}
}


/*
** Allow to access everywhere to the globals
*/
class Tempo extends Tempo_model
{
	public static $db;
	public static $date;
	public static $user;
	public static $session;
}


// Sql class object
Tempo::$db = new Db_pg($db_server, $db_login, $db_pass, $db);
if (Tempo::$db->_get_id() === NULL)
{
	trigger_error('Impossible de se connecter à la base de donnée : ' . Tempo::$db->sql_error());
}

// Session class object
Tempo::$session = new Session();
// User class object
Tempo::$user = new User();
?>