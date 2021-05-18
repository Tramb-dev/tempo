// JavaScript Document
var previous;

/*
** Allows the AJAX connection
*/
function getXhr(){
					var xhr = null; 
	if(window.XMLHttpRequest) // Firefox et autres
	   xhr = new XMLHttpRequest(); 
	else if(window.ActiveXObject){ // Internet Explorer 
	   try {
				xhr = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			}
	}
	else { // XMLHttpRequest non supporté par le navigateur 
	   alert("Your browser does not allow XMLHTTPRequest objects..."); 
	   xhr = false; 
	} 
					return xhr;
}

/*
** Make a pause
--
** millis: time in milliseconds. The program will be stopped during this time.
*/
function pausecomp(millis)
{
var date = new Date();
var curDate = null;

do { curDate = new Date(); }
while(curDate-date < millis);
} 

/*
** when we click on a field in the middle cell. We check if the field selected contains a textarea to display.
*/
function go_left(id, name, is_field)
{
	new_middle(id, name, false, true, is_field);
	pausecomp(50);
	check_parent(id, name);
	previous = id;
}

/*
** when we click on a field in the left cell.
*/
function go_right(id, name, is_field)
{
	new_middle(id, name, true, true, is_field);	
}

/*
** Load the middle_cell.
--
** id: id of the selected field
** name: name of the selected field
** pop_tab: define if we remove the last entry of the table. For example, when we change the selection at the left cell, we have to remove the last entry, but in other cases not.
** session: define if we save the selection into the session array. We need to save the selected field into this array to remind the position and after to save it in the database.
** is_field: define if we have to add a textarea
*/
function new_middle(id, name, pop_tab, session, is_field)
{	
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			var tab = new Array();
			tab = eval('('+xhr.responseText+')');
			var final_value ='';
			
			if(is_field == 't')
			{
				var list = session_tab(true);
				if(list == undefined || list == 'null') list = '';
				final_value = '<div id="form"><label for="' + id + '">Enter data : <br /></label><input type="text" name="input" id="' + id + '"/>' + display_unit() + '<div id="depends">' + list + '</div><input type="button" value="submit" onClick="save();" /><br /></div>';
			}
			else if(tab == '')
			{
				var list = session_tab(true);
				if(list == undefined) list = '';
				final_value = '<div id="form"><div id="depends">' + list + '</div><input type="button" value="submit" onClick="save();" /></div>';
			}
			else
			{
				for(i = 0; i < tab.length; i++)
				{
					var infos = '';
					if(tab[i].infos != null)
					{
						infos = tab[i].infos;
					}
					final_value += '<a id="' + tab[i].real_id + '" href="javascript:go_left(' + tab[i].real_id + ', \'' + addslashes(tab[i].name) + '\', \'' + tab[i].is_field + '\');" onMouseOver="document.getElementById(\'right_cell\').innerHTML = \'' + infos + '\';">' + tab[i].name + '</a>';
				}
			}
			document.getElementById("middle_cell").innerHTML = final_value;
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("id="+id+"&name="+name+"&pop="+pop_tab+"&session="+session);
}

/*
** Load the left_cell.
--
** parent: id of the grandpa of the current selection to load the left_cell
** name: name of the selected field
** pop_tab: define if we remove the last entry of the table. For example, when we change the selection at the left cell, we have to remove the last entry, but in other cases not.
** session: define if we save the selection into the session array. If we are just loading the left cell, we don't need to save.
*/
function new_left(parent, name, pop_tab, session)
{
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			var tab = new Array();
			tab = eval('('+xhr.responseText+')');
			var final_value ='';
			for(i = 0; i < tab.length; i++)
			{
				var infos = '';
				if(tab[i].infos != null)
				{
					infos = tab[i].infos;
				}
				
				var selected = '';
				if(tab[i].real_id == previous)
				{
					selected = 'style="background-color:#e1c6de;color:#4e4c4f;"';
				}
				final_value += '<a id="' + tab[i].real_id + '" ' + selected + 'href="javascript:go_right(' + tab[i].real_id + ', \'' + addslashes(tab[i].name) + '\', \'' + tab[i].is_field + '\');" onMouseOver="document.getElementById(\'right_cell\').innerHTML = \'' + infos + '\';">' + tab[i].name + '</a>';
			}
			document.getElementById("left_cell").innerHTML = final_value;
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("id="+parent+"&name="+name+"&pop="+pop_tab+"&session="+session);
}

/*
** This function check the parent (in most cases the grandpa to load the left_cell).
*/
function check_parent(id, name)
{
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			var parent = parseInt(xhr.responseText);
			new_left(parent, name, false, false);
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("check_parent="+id);
}

/*
** Linked to the little arrow at the left of the overlayer. It allows to go back into the selection.
*/
function go_back()
{
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			var parent = eval('('+xhr.responseText+')');
			if(parent != -1)
			{
				new_left(parent[1], false, false);
				pausecomp(50);
				new_middle(parent[0], false, false);
			}
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("go_back=true");
}

/*
** Display the selection div
*/
function display(tab)
{
	var final_value = '';
	for(i = 0; i < tab.length; i++)
	{
		var tab_id = '';
		var temp_value = '';
		var s_delete1 = s_delete2 = '';
		var j = 0;
		for(var a in tab[i])
		{
			if (j != 0 && a != 'depends' && a != 'entry' && a != 'unit' && a != 's_delete')
			{
				tab_id += ',';
				temp_value += ' ---> ';
			}
			if(a != 'depends' && a != 'entry' && a != 'unit' && a != 's_delete')
			{
				tab_id += j + '=' + tab[i][a]['id'] + ':' + tab[i][a]['name'];
				temp_value +=  tab[i][a]['name'] ;
				j++;
			}
		}
		if(tab[i]['entry'] != null)
		{
			tab_id += ',entry=' + tab[i]['entry'];
			temp_value += ' ---> ' + tab[i]['entry'];
			if(tab[i]['unit'] != null && tab[i]['unit']['id'] != 0)
			{
				temp_value += ' ' + tab[i]['unit']['name'];
				tab_id += ',unit=' + tab[i]['unit']['id'] + ':' + tab[i]['unit']['name'];
			}
		}
		if(tab[i]['depends'] != null)
		{
			tab_id += ',depends=' + tab[i]['depends'];
			temp_value += ' ---> <i>depends on selection ' + (parseInt(tab[i]['depends'])+1) + '</i>';
		}
		if(tab[i]['s_delete'] == 1)
		{
			s_delete1 = '<strike><i>';
			s_delete2 = '</i></strike>';
			change_link = '<a href="javascript:reload_selection(' + i + ');">reload selection</a></div>';
		}
		else
		{
			change_link = '<a href="javascript:check_depends(' + i + ');">delete selection</a></div>';
		}
		final_value += '<div class="line_selection">' + s_delete1 + 'Selection ' + (parseInt(i)+1) + ' : ' + temp_value + s_delete2 + '&nbsp;&nbsp;&nbsp;<a href="javascript:reload_overlayer(\'' + tab_id + '\', ' + i + ');">modify</a> / ' + change_link;
	}
	document.getElementById("selection").innerHTML = '<b>Your selection : </b><br />' + final_value + '<div id="hidden"></div>';
}

/*
** Save the current selection into an array, display this array on inline mode (for users) and changes the table to zero.
------
** entry	Text input data
** units	Unit choosen
** list		The selection linked to the actual selection
** change	The selection we are modifying
*/
function save()
{
	var entry = null;
	if(document.forms.exp.input != undefined) entry = document.forms.exp.input.value;
	
	if(document.forms.exp.depend == undefined) var list = '&depend=' + 0;
	else var list = '&depend=' + document.forms.exp.depend.selectedIndex;
	
	if(document.forms.exp.conversion == undefined) var s_units = '&unit=' + 0;
	else var s_units = '&unit=' + document.forms.exp.conversion.value;
	
	if(document.forms.exp.change == undefined) var change = '&change=' + null;
	else var change = '&change=' + document.forms.exp.change.value;
	
	// If we select a unit, we need number in the parameter given.
	if(s_units !== ('&unit=' + 0) && isNaN(entry) == true)
	{
		alert('You must enter a number if you select an unit');
	}
	else
	{	
		var xhr = getXhr();
		xhr.onreadystatechange = function()
		{
			if(xhr.readyState == 4 && xhr.status == 200)
			{
				var tab = eval('('+xhr.responseText+')');
				display(tab);
				document.getElementById("left_cell").innerHTML = '';
				new_middle(-1, false, false, false);
				
				if(document.forms.exp.input != undefined) document.forms.exp.input.value = null;
				if(document.forms.exp.depend != undefined) document.forms.exp.depend.selectedIndex = '';
				if(document.forms.exp.conversion != undefined) document.forms.exp.conversion.value = 0;
				if(document.forms.exp.change != undefined) document.forms.exp.change.value= '';
			}
		}
		xhr.open("POST","ajax.php",true);
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		xhr.send('save='+entry+s_units+list+change);
	}
}

/*
** Return the session array to see what we have already save into and modify it if we need.
** -----
** display_list		if we want to display the list with the dependencies
** id				if we are modifying a selection, we have the previous id selected
** current			if we are modifying a selection, we have the current selection we are changing so we don't display it.
*/
function session_tab(display_list, id, current)
{
	if(document.forms.exp.change == undefined) var change = false;
	else var change = true;
	
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			var tab = eval('('+xhr.responseText+')');
			
			if(tab != null)
			{
				if(display_list == true)
				{
					if(!(tab.length == 1 && (change)))
					{
						var final_value = '<br />Depends on a previous selection ?<br /><select id="depend"><option value="null">Independent</option>';
						
						for(i = 1; i <= tab.length; i++)
						{
							// If selections are after the current, we don't display them to keep an "order". Can be desactivated (in this case activate the other choice).
							if(i <= current || current == undefined)
							//if(i != (parseInt(current) + 1))
							{
								var selected = '';
								if(i == (parseInt(id)+1)) selected = ' selected="selected"';
								final_value += '<option value="' + i + '"' + selected + '>Selection ' + i + '</option>';
							}
						}
						final_value += '</select><br />';
						document.getElementById("depends").innerHTML = final_value;
					}
				}
				else display(tab);
			}
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("session_tab=true");
}

/*
** Erase all data if the user has click on the erase bouton or just the actual selection if the user pushed the close button.
** -----
** value: if equal to 0, erase the actual selection, if equal to 1 erase all.
*/
function erase(value)
{
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			if(value == 1)
			{
				document.getElementById("selection").innerHTML = '<b>Your selection : </b><br />nothing at the moment.';
			}
			document.getElementById("left_cell").innerHTML = '';
			document.getElementById("hidden").innerHTML = '';
			new_middle(-1, false, false);
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("erase="+value);
}

function addslashes(str) 
{
	str = str.replace(/\\/g,'\\\\');
	str = str.replace(/\'/g,'\\\'');
	str = str.replace(/\"/g,'\\"');
	str = str.replace(/\0/g,'\\0');
	return str;
}
function stripslashes(str) 
{
	str = str.replace(/\\'/g,'\'');
	str = str.replace(/\\\\/g,'\\');
	str = str.replace(/\\0/g,'\0');
	str = str.replace(/\\"/g,'"');
	return str;
}

/*
** This function allows the user to reload a previous selection into the overlayer.
*/
function reload_overlayer(str, selection)
{
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			var count = 0;
			var tab_length = 0; // Bug with tab.length
			var tab = new Array();
			tab = eval('('+xhr.responseText+')');

			for(a in tab)
			{
				if(a == 'entry' || a == 'depends' || a == 'unit' || a == 's_delete')
				{
					count++;
				}
				tab_length++;
			}
			
			if(count > 0)
			{
				var nb = tab_length - count - 1;
				var temp = tab[nb];
			}
			else
			{
				var temp = tab.pop();
			}
			check_parent(temp['id'], temp['name'], false);
			reload_middle(temp['id'], tab['entry'], tab['depends'], tab['unit'], selection);
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("reload_overlayer="+str);
}

/*
** Reload the middle when the user want to reload a selection, with all parameters previously filled
-------
** id:			last id selected by the user
** field:		contains the user's entry or false if no field
** depends:		contains the dependency or false if not
** s_units:		contains the unit id or false if not
** selection:	selection concerned by the change
*/
function reload_middle(id, field, depends, s_units, selection)
{
	(field != null) ? v_field = '<label for="' + id + '">Enter data : <br /></label><input type="text" name="input" id="' + id + '" value="' + field + '"/>' : v_field = '';
	if(s_units != null)
	{
		v_units = display_unit(s_units['id']);
	}
	else if(field != null)
	{
		v_units = display_unit();
	}
	else
	{
		v_units = '';
	}
	final_value = '<div id="form">' + v_field + v_units + '<div id="depends">' + session_tab(true, depends, selection) + '</div><input type="button" value="submit" onClick="save();" /></div>';
	document.getElementById("middle_cell").innerHTML = final_value;
	document.getElementById("hidden").innerHTML = '<input type="hidden" name="change" id="change" value="' + selection + '" />"';
}

/*
** Check if there are dependencies of the selected selection.
*/
function check_depends(selection)
{
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			var tab = eval('('+xhr.responseText+')');
			if(tab == false)
			{
				if(confirm('Would you delete the selection ' + (parseInt(selection) + 1) + ' ?'))
				{
					s_delete(selection);
				}
			}
			else
			{
				var final_value = '';
				var i = 0;
				var delete_tab = new Array();
				delete_tab.push(selection);
				for(var b in tab)
				{
					var temp_value = '';
					var j = 0;
					for(var a in tab[b])
					{
						if (j != 0 && a != 'depends' && a != 'entry' && a != 'unit' && a != 's_delete')
						{
							temp_value += ' ---> ';
						}
						if(a != 'depends' && a != 'entry' && a != 'unit' && a != 's_delete')
						{
							temp_value +=  tab[b][a]['name'] ;
							j++;
						}
					}
					if(tab[b]['entry'] != null)
					{
						temp_value += ' ---> ' + tab[b]['entry'];
						if(tab[i]['unit']['id'] != '0') temp_value += ' ' + tab[b]['unit']['name'];
					}
					if(tab[b]['depends'] != null)
					{
						temp_value += ' ---> <i>depends on selection ' + (parseInt(tab[b]['depends'])+1) + '</i>';
					}
					if(tab[b]['s_delete'] == 1)
					{
						temp_value = '<strike><i>' + temp_value + '</i></strike>';
					}					
					final_value += '<div class="line_selection">Selection ' + (parseInt(b)+1) + ' : ' + temp_value +  '</div>';
					delete_tab.push(b);
				}
				document.getElementById("selection").innerHTML = '<div id="s_delete" align="center"><b><u>These selections are depending of the selection ' + (parseInt(selection)+1) + '. Do you want to delete them all or break the link ?</u></b><br /><br /><input type="button" value="Delete all" onClick="s_delete(\'' + delete_tab + '\')" /><input type="button" value="Break the link" onClick="break_link(\'' + selection + '\')" /><input type="button" value="Cancel" onClick="session_tab(false)" /><br /></div>' + final_value;
			}
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("check_depends="+selection);
}

/*
** This function delete a selection (just a line) or a mutli-selection.
*/
function s_delete(selection)
{
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			session_tab(false);
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("delete="+selection);
}

/*
** Erase dependencies
*/
function break_link(value)
{
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			s_delete(value);
			session_tab(false);
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("delete_depends="+value);
}

/*
** Reload a previously erased selection.
*/
function reload_selection(selection)
{
	var xhr = getXhr();
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == 200)
		{
			session_tab(false);
		}
	}
	xhr.open("POST","ajax.php",true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("reload_selection="+selection);
}

/*
** Display a list with units
----
** id	Given id for the selected="selected"
*/
function display_unit(id)
{
	var opt_group = '';
	var i = 0;
	var tab = eval(units);
	var final_value = '<select name="conversion"><option value="0">No unit</option>';
	
	for(a in tab)
	{
		var selected = '';
		if(opt_group != tab[a]['type'])
		{
			if(i == 0) final_value += '<optgroup label="' + tab[a]['type'] + '"';
			
			else final_value += '</optgroup><optgroup label="' + tab[a]['type'] + '"';
			
			opt_group = tab[a]['type'];
		}
		if(tab[a]['id'] === id) selected = 'selected="selected"';
		final_value += '<option value="' + tab[a]['id'] + '" ' + selected + '>' + tab[a]['units'] + '</option>';
		i++;
	}
	final_value += '</select>';
	return final_value;
}

