<?

class term extends template {
	
	const tbl = "term" ;
	
	function __construct( $vars ) {
		
		$this->data = $vars ;
		
	}
	
	function create_new() {
		
		$this->term = trim(stripslashes( $this->data["term"] )) ;
		
		if ( $this->term_id = $this->get_term_id() ) {
			
			$this->definition = new definition( $this->data ) ;
			
			if ( !$this->definition->create_new() ) {
				
				$this->error["critical"][] = "Definition creation failed." ;
				return false ; 
			}
			
		} else {
			
			$this->error["critical"][] = "Term creation failed" ;
			return false ;
		}
		
		$this->result = "Added!" ;
		return true ;
	}
	
	function delete() {
		
		global $db ;
	
		$this->id = $this->data["id"] ;
	
		$def = new definition( $_REQUEST ) ;
	
		if ( $def->delete() ) {
			
			if ( $db->DeleteRows( self::tbl , "where id = $this->id" ) ) {
			
				return true ;

			} else {
				$this->error["critical"][] = "Could not delete term.</p>" ;			
				return false ;
			}
		} else {
			$this->error["critical"][] = "Could not delete definitions so did not delete term.</p>" ;			
			return false ;
		}
	}
	
	function get_term_id() {
	
		global $db ;
		
		if ( !empty($this->term) ) {
			
			$args['tbl'] = self::tbl ;
			$args['condition'] = "term = '$this->term'" ;
			$args['keyval'] = array( "term" => $this->term ) ;
			
			return $this->get_id( $args ) ;
		} 
		return false ;
	}
	
	function show_result() {
		
		if( isset( $this->error["critical"] ) ) {
			$error = implode( "<br>" , $this->error["critical"] ) ;
			$error .= implode( "<br>" , $this->definition->error["critical"] ) ;
			//$error .= debug_showArray( $this->data ) ;
		} else {
			$error = "Unknown error" ;
		}
		echo $error ;
	}
	
}

?>