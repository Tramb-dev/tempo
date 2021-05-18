<?php
	unset($_SESSION['tree']);

	$sql = 'SELECT name, real_id, infos
			FROM voc
			WHERE parent_id=-1
			ORDER BY real_id';	
			
	$result = Tempo::$db->query($sql);
	$list = Tempo::$db->rows($result);
	Tempo::$db->free($result);

	$infos = NULL;
	
	/*
	** Load units for the overlayer at the begginin to avoid latence error
	*/
	$sql = 'SELECT *
			FROM conversion
			ORDER BY type, id';
	$result = Tempo::$db->query($sql);
	$final_value = Tempo::$db->rows($result);
	Tempo::$db->free($result);
	?>
<script language="javascript" type="application/javascript"><?php echo 'var units = \'' . json_encode($final_value) . '\';' ?></script>
<link rel="stylesheet" media="screen" type="text/css" title="Design" href="css/overlayer.css" />
<div id="standardModel" class="Window" style="height: 400px; width: 800px; z-index: 3000; display: block;">
	<div class="WinHeader jqDrag">
    	<div class="close_overlayer">
        	<img src="img/modal_close_btn.gif" alt="reset the box" title="reset the box" onClick="erase('0');"/>
        </div>
        <div class="WinTitle"></div>
    </div>
    
    <div class="Content" style="height: 315px;">
    	<div id="maincontent">
        	<div id="back" onclick="go_back();">
            	<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
            	<img src="img/backarrow.gif" alt="backarrow" title="Go back" />
            </div>
        	<div id="left_table">
            	<span>Actual choice</span>
                <div id="left_cell">
                </div>
            </div>
            <div id="middle_table">
            	<span>Choose a field</span>
                <div id="middle_cell">
                <?php foreach ($list as $value){ echo '<a id="' . $value['real_id'] . '" href="javascript:go_left(' . $value['real_id'] . ', \'' . addslashes($value['name']) . '\', \'f\');" onMouseOver="document.getElementById(\'right_cell\').innerHTML = \'' . $value['infos'] . '\';">' . $value['name'] . '</a>';} ?>
                </div>
            </div>
            <div id="right_table">
            	<span>Informations</span>
                <div id="right_cell">
                	Choose a field and click on it until you have a text area or a bouton to save your selection.
                </div>
            </div>
        </div>
    </div>
</div>
