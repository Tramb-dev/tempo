<?php
/*
**	Displays message to the user with some parameters
*/
class Display extends Tempo_model
{
	/*
	** Display an error message
	** -----
	** $str ::			Message to display.
	** $fatal ::		TRUE : Stop the script, FALSE : continue
	** $redirect ::		If contain an url, redirect to this url
	** $time ::			Time to redirect
	*/
	public function error($str, $fatal = TRUE, $redirect = FALSE, $time = 3)
	{
		echo '<div class="error" align="center">' . $str . '</div>';
		
		if($redirect != FALSE)
		{
			Http::redirect($redirect, $time);
		}
		
		if($fatal == TRUE)
		{
			exit();
		}
	}

	/*
	** Display an simple message in a box
	** -----
	** $str ::			Message to display.
	** $redirect ::		If contain an url, redirect to this url
	** $time ::			Time to redirect
	*/
	public function message($str, $redirect = FALSE, $time = 3)
	{
		echo '<div class="portalMessage" align="center">' . $str . '</div>';

		if($redirect != FALSE)
		{
			Http::redirect($redirect, $time);
		}
	}





}

?>