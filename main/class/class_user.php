<?php

/*
** Manage users
*/
class User extends Tempo_model
{
	/*
	** Return TRUE if the login is already present into the database
	** -----
	** $nickname ::		Login
	*/
	public static function nickname_exists($nickname)
	{
		$sql = 'SELECT login
				FROM users
				WHERE LOWER(login) = \'' . Tempo::$db->escape(strtolower($nickname)) . '\'';
		$return = Tempo::$db->get($sql, 'login');

		return ($return);
	}

	/*
	** Return TRUE if the email is already used in the database
	** -----
	** $email ::		Email adress
	*/
	public static function email_exists($email)
	{
		$sql = 'SELECT email
				FROM users
				WHERE LOWER(email) = \'' . Tempo::$db->escape(strtolower($email)) . '\'';
		$return = Tempo::$db->get($sql, 'email');

		return ($return);
	}

	/*
	** Return TRUE if the email match the regex and if the email domain exists.
	** Inspired from http://fr.php.net/manual/fr/function.checkdnsrr.php#74809
	** -----
	** $email ::	Email adress
	*/
	public static function email_valid($email, $check_server = TRUE) 
	{
		if (OS_SERVER == 'windows') // if the OS is Windows, set $check_server to FALSE.
		{
			$check_server = FALSE;
		}

		if (preg_match('/^\w[-.\w]*@(\w[-._\w]*\.[a-zA-Z]{2,}.*)$/', $email, $match))
		{
			// DNS verification functions do not run on Windows
/* 			if (!$check_server)
			{
				return (TRUE);
			}

			$check = FALSE;
			if (function_exists('checkdnsrr'))
			{
				$check = checkdnsrr($match[1] . '.', 'MX');
				if (!$check)
				{
					$check = checkdnsrr($match[1] . '.', 'A');
				}
			}
			else if (function_exists('exec'))
			{
				$result = array();
				exec('nslookup -type=MX ' . $match[1], $result);
				foreach ($result as $line)
				{
					if (substr($line, 0, strlen($match[1])) == $match[1])
					{
						$check = TRUE;
						break;
					}
				}
			}

			// If fails, we verify the existence of the server with fsockopen()
			if (!$check)
			{
				$errno = 0;
				$errstr = '';
				$check = @fsockopen($match[1], 25, $errno, $errstr, 5);
			}

 */			return (TRUE);
		}
		return (FALSE);
	}

	/*
	** Confirmation by an administrator
	** -----
	** $user_id ::			User ID
	** $user_nickname ::	User login
	** $user_email ::		User email
	** $user_ip ::			User IP
	*/
	public static function confirm_administrator($user_id, $user_nickname, $user_email, $user_ip)
	{
		// Email sending to the user
		$mail = new Notify_mail();
		$mail->AddAddress($user_email);
		$mail->Subject = 'Registration to Tempo Database';
		$mail->set_file(ROOT . 'lg/register_admin.txt');
		$mail->Send();
		$mail->SmtpClose();

		// On récupère les informations pour notifier les administrateurs
		$sql = 'SELECT email
				FROM users
				WHERE u_auth > 3';
		$result = Tempo::$db->query($sql);
		while ($row = Tempo::$db->row($result))
		{
			$mail = new Notify_mail();
			$mail->AddAddress($row['email']);
			$mail->Subject = 'Confirm account';
			$mail->set_file(ROOT . 'lg/confirm_account.txt');
			$mail->set_vars(array(
				'NICKNAME' =>		htmlspecialchars($user_nickname),
				'EMAIL' =>			$user_email,
				'IP' =>				$user_ip,
			));
			$mail->Send();
		}
		Tempo::$db->free($result);
	}

	/*
	** Confirm and activate the user account
	*/
	public static function confirm_account($user_id, $name, $email)
	{
			Tempo::$db->update('users', array(
				'is_activated' 	=>	't',
				'u_auth'		=>	'2',
			), 'WHERE user_id = ' . $user_id . ' AND is_activated = \'f\'');

			// Email sending to the user
			$mail = new Notify_mail();
			$mail->AddAddress($email);
			$mail->Subject = 'Account activated';
			$mail->set_file(ROOT . 'lg/confirm_message.txt');
			$mail->set_vars(array(
				'NICKNAME' =>		htmlspecialchars($name),
			));
			$mail->Send();

			return (TRUE);
	}
}
/* EOF */