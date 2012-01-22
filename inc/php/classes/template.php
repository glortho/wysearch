<?

class template {

	function __construct( $args ) {
		
		$this->data = $args ;

	}
	
	function get_id( $args ) {
		
		global $db ;
		
		if ( $id = $db->SelectRows("select id from " . $args['tbl'] . " where ". $args['condition']) ) {
			return $id[0][0] ;
		} else if( $id = $db->Insert( $args['tbl'] , $args['keyval'] ) ) {
			return $id ;
		} else {
			return false ;
		}

	}
	
	// TODO: make this better, using preg_replace or whatever; also, any way to make object and array notation the same?
	
	function templatize( $obj , $template ) {
		
		preg_match_all( "/{(.*?)}/" , $template , $matches ) ;
				
		if( is_array( $obj ) ) {
			foreach( $matches[0] as $key => $val ) {
				$var = $matches[1][$key] ;
				$template = str_replace( $val , $obj[$var] , $template );
			}
		} else if ( is_object( $obj ) ) {
			foreach( $matches[0] as $key => $val ) {
				$var = $matches[1][$key] ;
				$template = str_replace( $val , $obj->$var , $template );
			}
		} else {
			foreach( $matches[0] as $key => $val ) {
				$var = $matches[1][$key] ;
				$template = str_replace( $val , $obj , $template );
			}
		}

		return $template ;
	}
}

?>