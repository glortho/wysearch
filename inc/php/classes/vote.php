<?

class vote extends template {

	const tbl = "vote" ;
	
	const info_html = "
		<table class='vote_info_table' border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
			{up_header}
			{out_up}
			{down_header}
			{out_down}
		</table>
		";
			
	const info_item = "
			<tr>
				<td class='vote_info_dts'>{created}</td>
				<td class='vote_info_nickname'><strong>{nickname}</strong>, p. {page_line}</td>
				<td class='vote_info_note'>{note}</td>
			</tr>
		";
		
	const info_up_header = "<tr><th class='vote_info_header' id='vote_info_up_header' colspan='3'>Up ( {tally_up} )</th></tr>" ;
	const info_down_header = "<tr><th class='vote_info_header' id='vote_info_down_header' colspan='3'>Down ( {tally_down} )</th></tr>";
	
	function __construct( $args ) {
		
		$this->data = $args ;

	}
		
	function cast() {
		
		global $db ;
		
		$author_id = $this->get_source_author() ;
		$source_id = $this->get_source( $author_id ) ;
		
		extract( $this->data ) ;
		
		return $db->Insert( self::tbl , array( "vote_source_id" => $source_id , "definition_id" => $id , "note" => $cnt , "page_line" => $cp , "increment" => $dir ) ) ;
	}
	
	function get_source( $author_id ) {
		
		require_once "./inc/php/classes/vote_source.php";
		$source = new vote_source( $_REQUEST );
		
		return $source->get_source_id( $this->data['cn'] , $author_id ) ;
	}
	
	function get_source_author() {
		
		require_once "./inc/php/classes/author.php";
		$author = new author( $_REQUEST );

		return $author->get_author_id( $this->data['ca'] ) ;			
	}
	
	function info_display( $data ) {
		
		$info['out_up'] = "" ;
		$info['out_down'] = "" ;
		$info['tally_up'] = 0 ;
		$info['tally_down'] = 0 ;
		
		foreach( $data as $row ) {
						
			$body = $this->templatize( $row , self::info_item ) ;
			
			if ( $row['increment'] == 1 ) {
				$info['out_up'] .= $body ;
				$info['tally_up']++ ;
			} else {
				$info['out_down'] .= $body ;
				$info['tally_down']++ ;
			}
		}
		
		$info['up_header'] = ( $info['tally_up'] > 0 ) ? $info['up_header'] = $this->templatize( $info , self::info_up_header) : "" ;
 		$info['down_header'] = ( $info['tally_down'] > 0 ) ? $this->templatize( $info , self::info_down_header ) : "" ;	
		
		echo $this->templatize( $info , self::info_html ) ;
	}
	
	function show_info() {
		
		global $db ;
		
		$sql = "
			select v.id, v.created, nickname, page_line, increment, v.note
			from " . self::tbl . " as v
			left join vote_source as vs on v.vote_source_id = vs.id
			where definition_id = " .  $this->data['definition_id'] . "
			order by created desc";
		
		//echo $sql ;
		
		if ( $data = $db->SelectRows( $sql , MYSQL_ASSOC ) ) {
			
			$this->info_display( $data ) ;
			
			return true ;
		}
		return false;
	}
	
	function show_result() {
		echo "ERROR" ;
	}
	
	
	/*
	if ( $data = $db->Query( "update definition set votes = ( votes $this->dir ) where id = $this->id" ) ) {
		return true ;
	} else {
		$this->error["critical"][] = "Couldn't update record ($data. $this->id)" ;
		return false ;
	}
	*/	
}

?>