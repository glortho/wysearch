<?

class vote_source {

	const tbl = "vote_source" ;
	
	function __construct( $args ) {
		
		$this->data = $args ;

	}
	
	function autocomplete() {
		
		global $db ;
		
		$q = $this->data['q'] ;
		
		$sql = "
			select nickname, title, id from vote_source where nickname like '$q%' order by nickname, title
		";
		
		if ( $data = $db->SelectRows( $sql , MYSQL_ASSOC )) {
			foreach( $data as $row ) {
				extract( $row ) ;
				$out[] = "$nickname - $title - $id" ;
			}
			echo implode("\r\n",$out) ;
		} else {
			echo "{No data}" ;
		}
		
	}
	
	function get_source_id( $nickname , $author_id ) {
				
		extract($this->data) ;
		
		if ( !empty( $nickname ) ) {
			
			$args['tbl'] = self::tbl ;
			$args['condition'] = "nickname = '$nickname'" ;
			$args['keyval'] = array( "title" => $title , "author_id" => $author_id , "sect" => $cs , "genre" => $cg , "date" => $cd , "nickname" => $nickname ) ;
			
			return $this->get_id( $args ) ;
		}
		return true ; // okay if it's empty
	}
	
}

?>