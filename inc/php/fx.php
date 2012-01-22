<?
	
	function CreateChecks($field, $src, $sep) {
		global $db, $cr;
		$fieldVar = "$".$field;
		eval("global $fieldVar; \$val = $fieldVar;");
		if (is_array($src)) {
			//
		} else {
			if (strpos($src,"select ") !== false) {
				$sql = $src;
			} else {
				$sql = "select ".$db->TableToKP($field).", $field from ".$src ;
			}
			if ($res = $db->SelectRows($sql,MYSQL_NUM)) {
				
				foreach($res as $row) {
					echo "<input type='checkbox' name='$field"."[]' value='$row[0]'";
					if (is_array($val)) {
						foreach ($val as $item) {
							if ($row[0] == $item) { echo " checked";}
						}
					} else {
						if ($row[0] == $val) { echo " checked";}
					}
					echo "> ";
					echo isset($row[1]) ? $row[1] : $row[0];
					echo "$sep$cr";
				}
				
			} else {
			
				/* values weren't found in the database */
			
				echo "<input type='text' name='$field'>$cr";
			}
		}
	}

	function CreateRadios($field, $src, $sep) {
		global $db, $cr;
		$fieldVar = "$".$field;
		eval("global $fieldVar; \$val = $fieldVar;");
		
		if (is_array($src)) {
			//
		} else {
			if (strpos($src,"select ") !== false) {
				$sql = $src;
			} else {
				$sql = "select ".$db->TableToKP($field).", $field from ".$src ;
			}
			
			if ($res = $db->SelectRows($sql)) {
				
				foreach($res as $row) {
					echo "<input type='radio' name='$field' value='$row[0]'";
					if (is_array($val)) {
						foreach ($val as $item) {
							if ($row[0] == $item) { echo " checked";}
						}
					} else {
						if ($row[0] == $val) { echo " checked";}
					}
					echo "> ";
					echo isset($row[1]) ? $row[1] : $row[0];
					echo "$sep$cr";
				}
				
			} else {
			
				/* values weren't found in the database */
			
				echo "<input type='text' name='$field'>$cr";
			}
		}
	}


	/*
	
	0. <select name=
	1. array of keys=>values for pop-up
	[2. match value or, if none, "key=val" to signify that key and val should be same
	 3. if match value specified, "key=val" to signify that key and val should be same
	 4. onChange=]
	
	*/
    
    function CreatePop() {
		global $db, $cr;
		$nokey = false;
		$args = func_get_args();
		$field = $args[0];
		$src = $args[1];
		if (isset($args[2]) and $args[2] != "key=val") {
			$match = $args[2];

		} else if ($args[2] == "key=val") {
			$fieldVar = "$".$field;
			@eval("global $fieldVar; \$val = $fieldVar;");
			//$match = $val;
			$nokey = true;
		}
		if (isset($args[3]) and $args[3] == "key=val") {
			$nokey = true;
		}
		$js = isset($args[4]) ? $args[4] : false;
					
		echo "<select name='$field' id='$field'";
		if ($js) { echo " onChange=\"$js\"";}
		echo ">$cr";
		echo "<option value=''>...</option>$cr";
		if (is_array($src)) {
			foreach($src as $key => $value) {
				if (is_array($value)) {
					$key = $value[0];
					$value = $value[1];
				}
				if ($nokey) {$opkey = $value;} else {$opkey = $key;} 
				echo "<option value='$opkey'";
				if (is_array($match)) {
					foreach ($match as $val2) {
						if ($opkey == $val2) { echo " selected";}
					}
				} else {
					if ($opkey == $match) { echo " selected";}
				}
				echo ">".stripslashes($value)."</option>$cr";
			}
		}
		echo "</select>$cr";
	}
	
	/*
	
	0. <select name=
	1. array of keys=>values for pop-up
	[2. match value or, if none, "key=val" to signify that key and val should be same
	 3. if match value specified, "key=val" to signify that key and val should be same
	 4. onChange=]
	
	*/
	
	function ShowError($txt) {
		if (is_array($txt)) {
			$txt = implode("<br>",$txt);
		}
		echo "<div class='error'>$txt</div>$cr";
	}
	
	if(!function_exists('stripos'))
		{
		   function stripos($haystack,$needle,$offset = 0)
		   {
			 return(strpos(strtolower($haystack),strtolower($needle),$offset));
		   }
		}
		
	function strnpos( $haystack, $needle, $nth, $offset = 0 )
	{
	   if( 1 > $nth || 0 === strlen( $needle ) )
	   {
	       return false;
	   }

	   //  $offset is incremented in the call to strpos, so make sure that the first call starts at the right position by initially decrementing $offset.
	   --$offset;
	   do
	   {
	      $offset = strpos( $haystack, $needle, ++ $offset );
	   } while( --$nth  && false !== $offset );

	   return $offset;
	}

	function debug_ShowArray() {
		$args = func_get_args();
		$arr = $args[0];
		$demote = isset($args[1]) ? $args[1] : 0;
		foreach ($arr as $key => $val) {
			for ($i = 0; $i < $demote; $i++) {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			echo "$key => $val<br>";
			if (is_array($val)) {
				debug_ShowArray($val,$demote+1);
			}
		}
	}

?>