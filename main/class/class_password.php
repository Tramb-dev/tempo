<?php
/*
** Check the password security
*/

class Password extends Fsb_model
{
	// Contain graduated data of the password
	public $grade_data = array();

	// For the generation of password
	const LOWCASE = 1;
	const UPPCASE = 2;
	const NUMERIC = 4;
	const SPECIAL = 8;
	const ALL = 255;

	/*
	** Generation of a password
	** -----
	** $length ::	Length of the password
	** $type ::		Type of char used
	*/
	public static function generate($length = 8, $type = self::ALL)
	{
		$chars = '';
		$list = array(
			self::LOWCASE =>	'abcdefghijklmnopqrstuvwxyz',
			self::UPPCASE =>	'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
			self::NUMERIC =>	'0123456789',
			self::SPECIAL =>	'&#@{}[]()-+=_!?;:$%*',
		);

		foreach ($list AS $key => $value)
		{
			if ($type & $key)
			{
				$chars .= $value;
			}
		}

		$password = '';
		$max = strlen($chars) - 1;
		for ($i = 0; $i <= $length; $i++)
		{
			$password .= $chars[mt_rand(0, $max)];
		}

		return ($password);
	}
	
	/*
	** Test the strength of a password, return 1, 2, 3 or 4 in function of this stength.
	** 1 is a low pass, 4 is a strength pass
	** -----
	** $password_str ::		Password to evaluate
	*/
	public function grade($password_str)
	{
		$this->grade_data = array('len' => 1, 'char_type' => 1, 'average' => 0);

		// First evaluation : the password length
		$len = strlen($password_str);
		$len_step = array(6, 8, 11, 15);
		$this->grade_data['len'] = 0;
		foreach ($len_step AS $k => $v)
		{
			if ($len >= $v)
			{
				$this->grade_data['len'] = $k + 1;
			}
		}

		// Second evaluation : verify the char type of the password, letters, numbers, special chars
		$char_type = array('alpha_min' => 0, 'alpha_maj' => 0, 'number' => 0, 'other' => 0);
		for ($i = 0; $i < $len; $i++)
		{
			if ($this->is_alpha_min($password_str[$i]))
			{
				$char_type['alpha_min']++;
			}
			else if ($this->is_alpha_maj($password_str[$i]))
			{
				$char_type['alpha_maj']++;
			}
			else if ($this->is_number($password_str[$i]))
			{
				$char_type['number']++;
			}
			else
			{
				$char_type['other']++;
			}
		}

		$number_type = 0;
		foreach ($char_type AS $type)
		{
			if ($type > 0)
			{
				$number_type++;
			}
		}
		$this->grade_data['char_type'] = $number_type;
		
		// Third evaluation : verify the average of these char type in the word.
		$average = ceil($len / 4 / 1.5);
		foreach ($char_type AS $type)
		{
			if ($type >= $average)
			{
				$this->grade_data['average']++;
			}
		}
		return (round(($this->grade_data['len'] + $this->grade_data['char_type'] + $this->grade_data['average']) / 3));
	}
	
	/*
	** Verify if the char is a small case char
	*/
	private function is_alpha_min($char)
	{
		if ($char >= 'a' && $char <= 'z')
		{
			return (TRUE);
		}
		return (FALSE);
	}
	
	/*
	** Verify if the char is a upper case char
	*/
	private function is_alpha_maj($char)
	{
		if ($char >= 'A' && $char <= 'Z')
		{
			return (TRUE);
		}
		return (FALSE);
	}

	/*
	** Verify if the char is a number
	*/
	private function is_number($char)
	{
		if ($char >= '0' && $char <= '9')
		{
			return (TRUE);
		}
		return (FALSE);
	}
}
