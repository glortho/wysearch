<?

class spotlight extends template {

	const header = "
		<input type='hidden' id='spot_count' value='{count}'>
 		<table class='results_table' id='spot_table' border=\"0\" width=\"\">" ;

	const result = "<div class='spotlight_row'><span class=\"spotlight_kind\">{kind}</span> &nbsp; <span class=\"spotlight_file\">{display}</span></div><br/>" ;

	const result_header = "
			<tr class='spotlight_header'><td class='spotlight_kind'>{kind}</td></tr>" ;

	const result_table = "
			<tr>
				<td class='spotlight_row'><a class='spot_ref' href=\"./inc/php/opener.php?q={query}&f={file}\">{display}</a></td>
			</tr>" ;

	const result_table_admin = "
		<tr>
			<td class='spotlight_row'><a title='{file}' class='spot_ref' href=\"./inc/php/opener.php?q={query}&f={file}\">{display}</a></td>
		</tr>" ;

	const footer = "
		</table>" ;

	function __construct( $vars ) {

		$this->data = $vars ;

	}

	function body() {

		global $user ;

		$user_id = $user->get() ;

		$template = ( $user_id == 1 ) ? self::result_table_admin : self::result_table ;

		foreach ( $this->data as $kind => $file ) {

			if ( $user_id == 1 ) {
				echo $this->templatize( $kind , self::result_header ) ;
			}

			foreach( $file as $data ) {

				$data["query"] = $this->q ;

				echo $this->templatize( $data , $template ) ;

			}

		}

	}

	function display() {

		if ( $this->data ) {

			$this->header() ;
			$this->body() ;
			$this->footer() ;

		} else {

			$this->fail() ;
		}

	}

	function fail() {

		echo "<input type='hidden' id='spot_count' value='0'>" ;

	}

	function footer() {

		echo self::footer ;

	}

	function header() {

		echo $this->templatize( $this , self::header ) ; // str_replace( "{count}" , $this->count , self::header ) ;

	}

	function search_spotlight() {

		global $user ;

		$this->q = trim(stripslashes($this->data["q"])) ;

		//TODO: split on comma and run two different searches with terms as headers (with subcounts) collapsed by default

		$outarr = array() ;

		$limiter = ( $user->get() == 1 ) ? "" : ""; //"&websearch AND " ;

		$scr = "/usr/bin/mdfind \"" . $limiter . "\\\"$this->q\\\"\" | sort" ;

		//echo "$scr";

		exec( $scr , $outarr , $res ) ;

		echo $res;

		$this->data = array() ;

		$this->count = 0 ;

		foreach ( $outarr as $file ) {
			$data = shell_exec( "mdls -name \"kMDItemDisplayName\" -name \"kMDItemKind\" \"$file\"" ) ;
			//echo "$data<br>";
			$data = explode( "\n" , $data ) ;
			$file_data = array() ;
			foreach ( $data as $item ) {
				$item = explode( "= " , $item ) ;
				$file_data[] = str_replace( "\"" , "" , $item[1] ) ;
			}
			$this->data[$file_data[1]][] = array( "file" => $file , "display" => $file_data[0] ) ;
			$this->count++ ;

		}

		ksort( $this->data ) ;

		$this->display() ;

	}

}

?>
