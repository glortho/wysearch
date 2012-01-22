<?

require_once "./inc/php/init.php" ;

$flag = $_REQUEST["flag"] ;

switch( $flag ) {
	
	case "login" :			
	
		$user->login() ;
		
		break ;

	case "lookup" :	
			
		require_once "./inc/php/tiblib.php";		// array of wylie tibetan in tibetan alphabetical order
		require_once "./inc/php/classes/lookup.php";		
	
		$lookup = new lookup( $_REQUEST ) ;
		$lookup->search_dictionary() ;
		
		break ;
		
	case "new" :				// new term
	
		require_once "./inc/php/classes/term.php";
		require_once "./inc/php/classes/definition.php";
		
		$term = new term( $_REQUEST ) ;
		if ( !$term->create_new() ) {
			$term->show_result() ;
		}	
		break ;
			
	case "new_sub" :			// new definition

		require_once "./inc/php/classes/definition.php";

		$def = new definition( $_REQUEST ) ;
		if ( !$def->create_new() ) {
			$def->show_result() ;
		}	
		break ;
		
	case "edit" :				// edit definition

		require_once "./inc/php/classes/definition.php";

		$def = new definition( $_REQUEST ) ;
		$def->edit() ;
		$def->show_result() ;

		break ;	
		
	case "delete_term" :		// delete term	
	
		require_once "./inc/php/classes/term.php";
		require_once "./inc/php/classes/definition.php";
		
		$term = new term( $_REQUEST ) ;
		if ( !$term->delete() ) {
			$term->show_result() ;
		}	
		break ;
				
	case "delete_definition" :	// delete definition	

		require_once "./inc/php/classes/definition.php";

		$def = new definition( $_REQUEST ) ;
		if ( !$def->delete() ) {
			$def->show_result() ;
		}	
		break ;

	case "spotlight" :

		require_once "./inc/php/classes/lookup.php";

		$lookup = new lookup( $_REQUEST ) ;
		$lookup->spotlight() ;

		break ;
		
	case "fmp" :		
		
		require_once "./inc/php/classes/fm.php";
		
		$classes = get_declared_classes() ;		

		foreach( $classes as $class ) {			

			if ( stripos( $class , "db_" ) !== false ) {

				$lookup = new $class( $_REQUEST ) ;
				$lookup->search_fmp() ;
			}
			
		}
		
		break ;
		
	case "vote" :
	
		require_once "./inc/php/classes/vote.php";
		
		$vote = new vote( $_REQUEST ) ;
		
		if( !$vote->cast() ) {
			$vote->show_result() ;
		} ;

		break;
		
	case "vote_info" :

		require_once "./inc/php/classes/vote.php";

		$vote = new vote( $_REQUEST ) ;

		if( !$vote->show_info() ) {
			$vote->show_result() ;
		} ;

		break;
		
	case "ac_history" :

		require_once "./inc/php/classes/history.php";

		$history = new history( $_REQUEST ) ;
		$history->autocomplete() ;

		break;
			
	case "ac_nickname" :
	
		require_once "./inc/php/classes/vote_source.php";
		
		$source = new vote_source( $_REQUEST ) ;
		$source->autocomplete() ;
		
		break;
		
	case "source_list" :
	
		require_once "./inc/php/classes/source.php";
		
		$source = new source( $_REQUEST ) ;
		$source->list_all("html") ;
		break;
		
	case "upload" :
	
		$putdata = fopen("php://input", "r");

		/* Open a file for writing */
		$fp = fopen("myputfile.ext", "w");

		/* Read the data 1 KB at a time
		   and write to the file */
		while ($data = fread($putdata, 1024))
		  fwrite($fp, $data);

		/* Close the streams */
		fclose($fp);
		fclose($putdata);
		break;
			
	default:
	
		break ;

}


?>