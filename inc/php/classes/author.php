<?

class author {

	const tbl = "vote_source_author" ;
	
	function __construct( $args ) {
		
		$this->data = $args ;

	}
	
	function get_author_id( $name ) {
		
		if ( !empty( $name ) ) {
			
			$args["tbl"] = self::tbl ;
			$args["condition"] = "name = '$name'" ;
			$args["keyval"] = array("name" => $name ) ;
			
			return $this->get_id( $args ) ;
		}
		return true ; // okay if it's empty
	}
	
}

?>