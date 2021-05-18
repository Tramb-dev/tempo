<?php

/*
** All classes must extend this class
*/
class Tempo_model
{
	/*
	** Intelligent display of an object
	*/
	public function __toString()
	{
		$str = '<b>Classname :</b> ' . get_class($this) . '<br />';
		$str .= '<b>Properties :</b><ul style="margin: 0">';
		foreach ($this AS $property => $value)
		{
			$str .= '<li><b>' . $property . '</b> = <pre style="display: inline">' . var_export($value, TRUE) . '</pre></li>';
		}
		$str .= '</ul>';
		
		return ($str);
	}

	/*
	** Method overloading
	** Functions could now do $this->set_my_attribute('value') that will done $this->_set('my_attribute', 'value')
	** and $this->_get_my_attribute() that will done $this->_get('my_attribute')
	*/
	public function __call($method, $attr)
	{
		$before = substr($method, 0, 5);
		$after = substr($method, 5);
		if ($before == '_set_')
		{
			$this->_set($after, $attr[0]);
			return ;
		}
		else if ($before == '_get_')
		{
			return ($this->_get($after));
		}

		// For the magic method __sleep() during the serialization
		if ($method == '__sleep')
		{
			return (array_keys(get_object_vars($this)));
		}

		trigger_error('Call to undefined method ' . $method . ' in class ' . get_class());
	}

	/*
	** Property allocation
	*/
	public function _set($property, $value)
	{
		$this->$property = $value;
	}

	/*
	** Property value
	*/
	public function _get($property)
	{
		if (isset($this->$property))
		{
			return ($this->$property);
		}
		return (NULL);
	}
}
/* EOF */