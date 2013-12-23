<?

// TODO: look for , in term and edit sql/spotlight appropriately

class lookup extends template {

	const sql_template = "
		SELECT t.id as term_id, d.id as def_id,
		if( isnull(sum(v.increment)), 0, sum(v.increment)) as votes,
		t.term, d.definition, s.name, s.abbreviation
		FROM term as t
		left join definition as d on t.id = d.term_id
		left join source as s on d.source_id = s.id
		left join vote as v on d.id = v.definition_id
		where {sql}
		group by d.id
		order by votes desc, abbreviation desc
		";

	const header = "
		<input type='hidden' id='dic_count' value='{count}'>
		<table class='results_table' id='dic_table' border=\"0\" width=\"\">" ;

	const term_header = "
			<tr class='term_row'><td id='{term_id}' class='def_term' colspan='2'><b>{term}</b></td></tr>" ;


	const definition = "
			<tr>
				<td class='def_spacer'></td>
				<td class='def'>
					<span title='{source_name}' class='source'>{source_abbreviation}</span> &nbsp;
					<span id='{id}' class='definition'>{definition}</span>
					<span class='votes'>( <span class='vote_count'>{votes}</span> )</span>
				</td>
			</tr>" ;

	const footer = "
		</table>" ;

	const fail = "<div class='big'>སྟོང་པ།</div>" ;


	function __construct( $args ) {

		global $db ;

		$this->data = $args ;
		$this->q = $this->data["q"] ;

	}

	function add_abbreviations( $abbr ) {

		switch( $abbr ) {

			case "all dictionaries":
				$out = "";
				break;
			default:
				$out = " AND abbreviation in (". stripslashes($abbr) . ")" ;
				break;
		}
		return $out ;
	}

	function build_conditional( $q , $qregex , $f ) {

		if ( $this->data['exact'] == "true" ) { // exact

			$this->sql .= ( isset($qregex[0]) ) ? "$f REGEXP \"^$q$\"" : "$f = \"$q\"" ;

		} else if ( $this->data['starts'] == "true" ) {	// starts with

			$this->sql .= ( isset($qregex[0]) ) ? "$f REGEXP \"^$q\"" : "$f like \"$q%\"" ;

		} else {

			$this->sql .= "$f REGEXP \"$q\"" ;
		}
	}

	function build_fields( $arg_array ) {
		if ( $arg_array[0] == "false" ) {			// don't search terms, only defs
			$field[] = "d.definition" ;
		} else if ( $arg_array[1] == "false" ) {	// don't search defs, only terms
			$field[] = "t.term" ;
		} else {									// search both
			$field[] = "d.definition" ;
			$field[] = "t.term" ;
		}
		return $field ;
	}

	function build_output() {

		foreach ( $this->data as $row ) {
			extract( $row ) ;
			$outarr[$term]["id"] = $term_id ;
			$outarr[$term]["data"][] = array( "definition" => $definition , "source_abbreviation" => $abbreviation , "id" => $def_id, "source_name" => $name , "votes" => $votes ) ;
		}

		$this->count = count( $outarr ) ;

		$outarr = $this->sort_output( $outarr ) ;

		return $outarr ;

	}

	function build_sql_query() {

		extract( $this->data ) ;

		$this->q = trim(stripslashes($q)) ;

		$qarr = explode( "," , $this->q ) ;
		$qtotal = count($qarr) ;
		$qc = 1 ;

		$this->sql = "" ;

		$this->fields = $this->build_fields( array( $interm , $indef ) ) ; // $s1 and $s2 get extracted above

		$c = count( $this->fields ) - 1 ;

		foreach( $qarr as $q ) {

			$this->scrub_term( $q , $c ) ;

			if ( $qc < $qtotal ) { $this->sql .= " OR " ; $qc++ ;}

		}

		$this->sql .= $this->add_abbreviations( $abbreviations ) ;

		$query = $this->templatize( $this , self::sql_template ) ;

		return $query ;
	}

	function display() {

		if ( $this->data ) {

			$outarr = $this->build_output() ;

			$this->write_header() ;
			$this->write_body( $outarr ) ;
			$this->write_footer() ;

		} else {

			$this->fail() ;
		}

	}

	function fail() {

		echo "<input type='hidden' id='dic_count' value='0'>";
	}

	function find_set( $root , $abc ) {

		foreach( $abc as $set ) {
			if ( array_search($root,$set) !== false ) {
			 	return "(" . implode("|", $set) . ")" ;
			}
		}
		return $root ;
	}

	function fuzzify_root( $syl , $flags ) {

		preg_match( "/[aeiou]/" , $syl , $vowels ) ;
		$sylarr = explode( $vowels[0] , $syl );
		$root = $sylarr[0] ;
		$suffix = $sylarr[1] ;
		if ( strlen( $root ) > 1 ) {
			$rootcheck = substr( $root , -1 ) ;
			if ( array_search( $rootcheck, $flags ) !== false ) {
				$root = $this->get_root( $root ) ;
			} else {
				$root = $rootcheck ;
			}
		}
		return array( "root" => $root , "vowel" => $vowels[0] , "suffix" => $suffix ) ;
	}

	function fuzzify( $q ) {

		$this->fuzz_level = $this->data["fuzzyl"] ;

		if ($this->fuzz_level > 1 ) {
			$abc["k"] 	= 	array( "k" , "kh" , "g" );
			$abc["t"] 	= 	array( "t" , "th" , "d" );
			$abc["p"] 	= 	array( "p" , "ph" , "b" );
			$abc["dr"] 	= 	array( "kr", "khr", "gr", "dr", "pr", "phr", "br" ) ;
			$abc["s"] 	= 	array( "s" , "sr" ) ;
			$abc["c"] 	= 	array( "c" , "ch" ) ;
		}
		if ( $this->fuzz_level > 3 ) {
			$abc["c"][] = "ts" ;
			$abc["c"][] = "tsh" ;
			$abc["j"] = array( "j" , "dz") ;
		}
		if ( $this->fuzz_level > 0 ) {
			$ld = array("r", " la", " ru", " tu", " du", " su", " na") ;
			$ldstr = "(" . implode( "|" , $ld ) . ")?" ;
			$is = array("'i"," yi"," kyi"," gyi") ;
			$isstr = "(" . implode( "|" , $is ) . ")?s?" ;
		}
		if ($this->fuzz_level > 3 ) {
			$misc = "( nas| las| par| pa| ba| po| bo| ma| mo| dang)?" ;
		}

		$qarr = explode( " " , $q ) ;
		$flags = array( "g", "h", "r", "y" , "s" ) ;
		$pre = "(g|d|b|m|')?(r|s|l)?" ;

		$out = "" ;
		$count = count( $qarr ) ;
		$c = 1 ;

		foreach( $qarr as $syl ) {

			if ( array_search( " $syl" , $ld ) !== false ) { // if the syllable is a la don particle

				$out .= "$ldstr " ;

			} else if ( array_search( " $syl" , $is ) !== false ) { // if the syllable is genitive TODO: agentive

				$out .= "$isstr " ;

			} else if ( $syl == "pa" or $syl == "po" or $syl == "ba" or $syl == "par" or $syl == "por" or $syl == "bar" or $syl == "bor" ) {

				$out .= "(pa|po|ba|bo)r?" ;
				if ( $this->fuzz_level > 2 and $c < $count ) {
					$out .= "$isstr$ldstr" ;
				}
				$out .= " " ;

			} else if ( $syl == "nas" or $syl == "las" ) {

				$out .= "(l|n)as" ;
				if ( $this->fuzz_level > 2 and $c < $count ) {
					$out .= "$isstr$ldstr" ;
				}
				$out .= " " ;

			} else { // normal syllable

				$pieces = $this->fuzzify_root( $syl , $flags ) ;
				$out .= $this->subs( $pre , $pieces , $abc, $this->fuzz_level ) ;

				if ( $this->fuzz_level > 2 and $c < $count ) {

					$out .= $ldstr . $isstr . $misc. " " ;
				} else {
					$out .= " " ;
				}

			}

			$c++ ;
		}

		if ( $this->fuzz_level > 3 ) {
			$out .= "?" . $misc ;
		} else {
			$out .= "?" . "(pa|ba)?" ;
		}

		return trim( str_replace( "  " , " " , $out ) ) ;

	}

	function get_root( $syl ) {
		$candidate = substr( $syl , -1 ) ;
		$prev = substr( $syl , - 2 , 1 ) ;

		switch( $candidate ) {

			case "g" :

				$out = ( $prev == "n") ? "ng" : "g" ;
				break;

			case "h" :

				if ( $prev != "s" or strlen( $syl ) == 2 ) {
					$out = $prev . $candidate ;
				} else {
					if ( substr( $syl , - 3 , 1 ) == "t" ) {
						$out = "tsh" ;
					} else {
						$out = "sh" ;
					}
				}
				break;

			case "r" :

				$ind = $this->get_root_index( $syl , $prev , "h" ) ;
				$out = substr( $syl , -$ind ) ;
				break ;

			case "s" :

				$out = ( $prev == "t" ) ? "ts" : "s" ;
				break ;

			case "y" :

				$ind = $this->get_root_index( $syl , $prev , "h" ) ;

				$out = substr( $syl , -$ind ) ;
				break ;

			case "z" :

				$ind = $this->get_root_index( $syl , $prev , "d" ) ;
				$out = substr( $syl , -$ind ) ;
				break ;

			default:

				break;

		}
		return $out ;

	}

	function get_root_index( $syl, $prev , $check ) {
		if ( strlen( $syl ) == 2 or $prev != $check ) {
			$ind = 2 ;
		} else {
			$ind = 3 ;
		}
		return $ind ;
	}

	function historify() {

		require_once "./inc/php/classes/history.php";

		$history = new history( $this->q ) ;
		if ( !$history->add() ) {
			echo "ERROR adding to history." ;
		}

	}

	function scrub_term( $q , $c ) {

		$q = trim($q) ;

		if ( $this->data['fuzzy'] == "true" ) {
			$q = $this->fuzzify( $q ) ;
		}

		preg_match( "/(\(|\?|\*)/" , $q , $qregex ) ;

		for ( $i = 0 ; $i <= $c ; $i++ ) {

			$f = $this->fields[$i] ;

			$this->build_conditional( $q , $qregex, $f ) ;

			if ( $i < $c ) { $this->sql .= " OR " ; }
		}
	}

	function search_dictionary() {

		global $db ;

		$query = $this->build_sql_query() ;

		//echo $query ;

		if ( $this->data = $db->SelectRows( $query ,  MYSQL_ASSOC ) ) {

			$this->historify() ;

		};

		$this->display() ;
	}

	function sort_output( &$outarr ) {

		if ( $this->count < 200 or DOMAIN == "localhost" ) {
			uksort( $outarr , array( $this , "tibsort" ) ) ;
		} else {
			ksort( $outarr ) ;
		}
		return $outarr ;
	}

	function spotlight() {

		require_once "./inc/php/classes/spotlight.php";

		$spot = new spotlight( $this->data ) ;
		$spot->search_spotlight() ;
	}

	function sub_suffix( $suffix ) {
		if ( substr( $suffix, -1 ) != "s" ) {
			$suffix .= "s" ;
		}
		$suffix .= "?" ;
		return $suffix ;
	}

	function sub_vowels( $vowel, $suffix ) {

		//TODO: this is where po should be tried for pa, bo for ba, etc.

		if ( ( $vowel == "a" or $vowel == "e" ) and substr( $suffix , 0 ) != "g" and substr( $suffix , 0 , 2 ) != "ng" ) {
			$vowel = "(a|e)" ;
		}
		return $vowel ;
	}

	function subs( $pre , $pieces , $abc , $fuzz_level ) {

		if ( $fuzz_level > 1 ) {
			$root = $this->find_set( $pieces["root"] , $abc ) ;
			$vowel = $this->sub_vowels( $pieces["vowel"], $pieces["suffix"] ) ;
		} else {
			$root = $pieces["root"] ;
			$vowel = $pieces["vowel"] ;
		}
		$suffix = $this->sub_suffix( $pieces["suffix"] ) ;

		$out = $pre . $root . $vowel . $suffix ;

		return $out ;

	}

	function tibsort( $a , $b ) {

		$aarr = explode( " " , $a ) ;
		$barr = explode( " " , $b ) ;

		return $this->tibsortsub( $aarr , $barr , 0 ) ;

	}

	function tibsortsub( $aarr , $barr , $c ) {

		global $tib ;

		$ac = array_search( $aarr[$c] , $tib ) ;
		$bc = array_search( $barr[$c] , $tib ) ;

		//echo "$aarr[$c] - $ac | $barr[$c] $bc<br>";

		$c1 = $c+1 ;

		if ($ac == $bc ) {
			if ( $aarr[$c1] == "" ) {
				return -1 ;
			} else if ( $barr[$c1] == "" ) {
				return 1 ;
			} else {
				return $this->tibsortsub( $aarr , $barr , $c1 ) ;
			}
		} else {
			return ($ac < $bc) ? -1 : 1 ;
		}
	}

	function write_body( $data_array ) {

		foreach( $data_array as $term => $data ) {

			$this->term = $term ;
			$this->term_id = $data["id"] ;

			$this->write_term_header() ;

			foreach( $data["data"] as $definition_data ) {

				$this->write_definition( $definition_data ) ;

			}
		}

	}

	function write_definition( $definition_data ) {

		echo $this->templatize( $definition_data , self::definition ) ;
	}

	function write_footer() {
		echo self::footer ;
	}

	function write_header() {

		echo $this->templatize( $this , self::header ) ;
	}

	function write_term_header() {

		echo $this->templatize( $this , self::term_header ) ;

	}

}

?>
