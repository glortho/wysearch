<?

class source {
	
	const tbl = "source" ;
	
	function __construct( $args ) {
		
		$this->data = $args ;
		
	}
	
	function list_all( $format ) {
		
		global $db ;
		
		if ( $data = $db->SelectRows("select * from " . self::tbl . " order by abbreviation", MYSQL_ASSOC ) ) {
			
			$c = 1 ;
			
			if ( $format == "html" ) {
				
				$out[] = "<div class=\"\" style=\"float:left\">" ;
				
				foreach( $data as $row ) {
					
					if ( $c == 6 ) { $out[] = "</div><div class=\"\" style=\"float:right; margin-left: 5px\">";}

					$out[] = "<div class='dic_source'><input type=\"checkbox\" id=\"dic_" . $row['abbreviation'] . "\" checked /> <span class='dicname'>" . $row["name"] . "</span> (<span class='abbr'>" . $row["abbreviation"] . "</span>)</div>" ;
					
					$c++ ;

				}
				
				$out[] = "</div>" ;
				
			}
			
			$out = implode( "\r" , $out ) ;
			
			echo $out ;
			
		} else {
			echo "ERROR" ;
		}
		
	}
	
}

?>