<?

class db {

	function db() {
		$this->connectID = mysql_pconnect(DB_HOST . ":/tmp/mysql.sock", DB_USER, DB_PASS);
		mysql_select_db(DB_NAME, $this->connectID);
		$this->error = array();
	}

/*////////////////////////////////

FUNCTION: CheckCount

Checks to see if the query returns the desired number of rows. Default check is >0.
Returns true or false.

Arguments:

0. table to search
[1. query -- if this is omitted, all rows will be selected
 2. comparison -- operator and number to check against. For example, "==20" or "<330"]

*/

	function CheckCount() {
		$args = func_get_args();
		$tbl = $args[0];
		$where = isset($args[1]) ? "where ".$args[1] : "";
		$comparison = isset($args[2]) ? $args[2] : " > 0";
		
		$sql = "select * from $tbl $where";
		if ($count = $this->CountRows($sql)) {
			eval("\$go = $count $comparison ? true : false;");
			return $go;
		} else {
			return false;
		}
		return false;
	}
	
	function CountRows($sql) {
		$res = $this->Query($sql);
		if ($res) {
	 		return mysql_num_rows($res);
	 	} else {
	 		return 0;
	 	}
	}
	
	
/*////////////////////////////////

FUNCTION: DeleteRows

Deletes rows from a given table.

Arguments:

0. table to delete from
[1. where clause]

*/	
	
	function DeleteRows($tbl,$where) {
		$args = func_get_args();
		$tbl = $args[0];
		$where = isset($args[1]) ? $args[1] : "";
		
		return $this->Query("delete from $tbl $where");
	}
	
	
	function GetColumns($tbl) {
		if ($column_array = $this->SelectRows("show columns from $tbl", MYSQL_ASSOC)) {
			return $column_array;
		}
		return false;
	}
	
	
/*////////////////////////////////

FUNCTION: GetColumnNames

Returns in an array all column names from the given table, or false on failure.

Arguments:

0. table to retrieve columns from

*/

	function GetColumnNames($tbl) {
		$fieldArr = array();
		if ($column_array = $this->GetColumns($tbl)) {
			foreach($column_array as $col_details) {
				$columnArr[] = $col_details["Field"];
			}
			return $columnArr;
		}
		return false;
	}
	
	
/*

0. table name
[1. column name to get type for, all if not specified]

*/

	function GetColumnTypes() {
		$args = func_get_args();
		$tbl = $args[0];
		$col = isset($args[1]) ? $args[1] : "";
		if ($column_data = $this->GetColumns($tbl)) {
			if (!empty($col)) {
				foreach ($column_data as $col_details) {
					if ($col_details["Field"] == $col) {
						return $col_details["Type"];
					}
				}
				return false;
			} else {
				$type_array = array();
				foreach ($column_data as $col_details) {
					$type_array[] = $col_details["Type"];
				}
				return $type_array;
			}
		}
		return false;
	}
	
	
	
/*////////////////////////////////

FUNCTION: GetTableFromPrimaryKey

Pulls the table name out of the primary key name

Arguments:

0. primary key in the format <table_name>_id

*/

	function GetTableFromKey($pkey) {
		$nameArr = array();
		$tblArr = $this->SelectRows("show tables");
		foreach($tblArr as $key => $val) {
			foreach ($val as $tblname) {
				array_push($nameArr,$tblname);
			}
		}
		$extract = substr($pkey,0,-3);
		if (in_array($extract,$nameArr)) {
			return $extract;
		}
		return false;
	}
	
	
/*////////////////////////////////

FUNCTION: Insert

Inserts an array of fieldnames => field values into a table, using columns of the
same names as the fields for lining up, and returns the id. Calls GetColumnNames above.

Arguments:

0. table to insert into
1. array of fieldnames => fieldvalues


*/
	
	function Insert($tbl,$fieldArr) {
		if ($columnArr = $this->GetColumnNames($tbl)) {
			$insertCols = "(";
			$insertVals = "(";
			foreach ($fieldArr as $fieldName => $fieldVal) {
				if (in_array($fieldName,$columnArr)) {
					$insertCols .= $fieldName.",";
					$insertVals .="'".addslashes($fieldVal)."',";
				}
			}
			$insertCols = substr($insertCols,0,-1).")";
			$insertVals = substr($insertVals,0,-1).")";
			$sql = "insert into $tbl $insertCols values $insertVals";
			if ($res = $this->Query($sql)) {
				//return $sql ;
				return mysql_insert_id();
			} else {
				return false;
			}
		}
		return false;
	}


/*////////////////////////////////

FUNCTION: Query

Generic query, returning result of query.

Arguments:

0. full query

*/

	function Query($sql) {
	
		$res = mysql_query($sql, $this->connectID) ; //or die( mysql_error() );
		return $res;	
		
	}
	

/*////////////////////////////////

FUNCTION: SelectRows

Returns query results in an associative array or false if query failed or returned no results.

Arguments:

0. query

*/
	
	function SelectRows() {
		$args = @func_get_args();
		$sql = $args[0];
		$restype = isset($args[1]) ? $args[1] : MYSQL_BOTH;
		
		$res = $this->Query($sql);
		if ($res and mysql_num_rows($res) > 0) {
			$resArr = array();
			while($row = mysql_fetch_array($res, $restype)) {
				$resArr[] = $row;
			}
			return $resArr;
		}
		return false;
	}
	
	
	function TableToKP($txt) {
		$txt = explode("_",$txt);
		foreach($txt as $key => $val) {
			$txt[$key] = ucfirst($val);
		}
		$txt = "kp".implode("",$txt)."ID";
		return $txt;
	}
	
	
	function UnMD5($tbl,$id) {
		if (strpos($id,",") !== false) {
			$id = explode(",",$id);
			$str = true;
		}
		if (is_array($id)) {
			foreach ($id as $key => $val) {
				if (is_md5($val)) {
					$id[$key] = $this->UnMD5_SQL($tbl,$val);
				}
			}
			if ($str) {
				return implode(",",$id);
			} else {
				return $id;
			}
		} else if (is_md5($id)) {
			return $this->UnMD5_SQL($tbl,$id);
		} else {
			return $id;
		}
	}
	
	function UnMD5_SQL($tbl,$id) {
		$sql = "select kp".ucfirst($tbl)."ID from $tbl where md5(kp".ucfirst($tbl)."ID) = '$id'";
		$res = $this->Query($sql);
		if (mysql_num_rows($res) > 0) {
			$id = mysql_result($res,0,0);
		} else {
			$id = false;
		}
		mysql_free_result($res);
		return $id;
	}
	

/*////////////////////////////////

FUNCTION: Update

Update a table with an array of fieldnames => field values into a table, using columns of the
same names as the fields for lining up and an optional where clause. NOTE: all rows will be
updated if a where clause is not included. Calls GetColumnNames above.

Arguments:

0. table to update
1. array of fieldnames => fieldvalues
[2. where clause]

*/
	
	function Update() {
		$args = func_get_args();
		$tbl = $args[0];
		$fieldArr = $args[1];
		$where = isset($args[2]) ? $args[2] : "";
		
		if ($columnArr = $this->GetColumnNames($tbl)) {
			$updateStr = "";
			foreach ($fieldArr as $fieldName => $fieldVal) {
				//echo "$fieldName => $fieldVal<br>";
				if (in_array($fieldName,$columnArr)) {
					$updateStr .= $fieldName."='".addslashes($fieldVal)."',";
				}
			}
			$updateStr = substr($updateStr,0,-1);
			$sql = "update $tbl set $updateStr $where";
			$this->sql = $sql;
			return $this->Query($sql);
		}
		return false;
	}
	
}
?>