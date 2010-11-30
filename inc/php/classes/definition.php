<?

class definition {

	function __construct( $args ) {
		
		$this->data = $args ;

	}
	
	function create_new() {
		
		$this->definition = trim(stripslashes( $this->data["def"] )) ;
		
		if ( !empty( $this->definition ) ) {
			
			if ( $this->term_id = $this->get_term_id() ) {
				
				$this->source = new source( $this->data ) ;

				if ( $this->source_id = $this->source->get_id() ) {

					if ( $this->result = $this->insert_definition() ) {
						
						return true ;
						
					} else {
						
						$this->error["critical"][] = "Couldn't insert definition." ;
					}

				} else {

					$this->error["critical"][] = "Couldn’t get source id." ;
				}
				
			} else {
				
				$this->error["critical"][] = "Couldn’t get term id." ;
			}		
			
		} else {
			
			$this->error["critical"][] = "Failed on definition creation: No definition!" ;
		}
		
		return false ;
		
	}
	
	function delete() {
		
		global $db , $term ;
		
		$sql =  isset( $term->id ) ? "term_id = $term->id" : "id = " . $this->data["id"] ;

		if ( $db->DeleteRows( "definition" , "where $sql" ) ) {
			return true ;
		} else {
			$this->error["critical"][] = "Could not delete definition." ;
			return false ;
		}
	}
	
	function edit() {
		
		$this->definition = trim(stripslashes( $this->data["def"] )) ;
		$this->id = $this->data["id"] ;
		
		if ( !empty( $this->definition ) ) {
			
			return $this->update() ;
			
		} else {
			
			$this->error["critical"][] = "Definition is empty!" ;
			return false ;
		}
		
	}
	
	function get_term_id() {
		
		global $term ;
		
		if ( isset( $term ) ) {
			return $term->term_id ;
		} else if ( isset( $this->data["term_id"] ) ) {
			return $this->data["term_id"] ;
		}
		
		$this->error["critical"][] = "Couldn't find term id." ;
		return false ;
		
	}
	
	function insert_definition() {
		
		global $db ;
		
		return $db->Insert( "definition" , array("definition" => $this->definition , "term_id" => $this->term_id , "source_id" => $this->source_id ) ) ;
	}
	
	function show_result() {
		if( isset( $this->error["critical"] ) ) {
			$out = implode( "<br>" , $this->error["critical"] ) ;
			$out .= debug_showArray( $this->data ) ;
		} else {
			$out = $this->definition ;
		}
		echo $out ;
	}
	
	function update() {
		
		global $db ;
		
		if ( $data = $db->Update( "definition" , array("definition" => $this->definition ) , "where id = $this->id" ) ) {
			return true ;
		} else {
			$this->error["critical"][] = "Couldn't update record ($data. $this->id: $this->definition)" ;
			return false ;
		}
		
	}
	
}

class source {
	
	const default_id = 1 ;
	
	function __construct( $args ) {
		
		$this->data = $args ;
		
	}
	
	function get_id() {
		
		global $db ;
				
		$this->source_abbreviation = $this->data["source"] ;
		
		if ( empty($this->source_abbreviation) ) { // no source supplied, use default
			return self::default_id ;
		} else if ( $data = $db->SelectRows( "select id from source where abbreviation = '$this->source_abbreviation'" ) ) { // look for source and return if found
			return $data[0][0] ;
		} else if ( $data = $db->Insert( "source" , array( "abbreviation" => $this->source_abbreviation ) ) ) {	// no source found, create new
			return $data ;
		}
		return false ;
		
	}
	
}

?>