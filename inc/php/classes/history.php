<?

class history {
	
	const tbl = "history" ;
	
	function __construct( $args ) {
		
		$this->data = $args ;
		
	}
	
	function add() {
		
		global $db ;
		
		if ( $data = $db->Insert( self::tbl , array( "query" => $this->data ) ) ) {
			return true ;
		}
		return false ;
	}
	
	function autocomplete() {
		
		global $db ;
		
		if ( $data = $db->SelectRows( "select query, created from " . self::tbl . " group by query order by created desc") ) {
			
			foreach ( $data as $row ) {
				
				echo $row[0]."\r\n" ;
				
			}
			
		} else {
			
			echo "ERROR" ;
			
		}
		
	}
	
}

?>