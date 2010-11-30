<?

	require_once "./inc/php/classes/FileMaker.php" ;
	
	class fm extends template {		

		const usr = "admin" ;
		const pw = "mus1cs" ;
		const layout = "web" ;
		
		const header = "
			<input type='hidden' id='fmp_count_{db_flag}' value='{count}'>
			<table class='results_table' id='fmp_table_{header_id}' border=\"0\" width=\"\">
			<tr class='spotlight_header'><td colspan='2' class='spotlight_kind'><img src='./images/bluedot.png' align='left' width='16' height='16' class='fmdot' /> {db} ( {count} )</td></tr>" ;

		const result_header = "
				<tr><td class='def_spacer'></td><td class='fmp_header'></td></tr>" ;

		const result_table = "
				<tr>
					<td class='fmp_spacer'>{c}</td>
					<td class='fmp_row'>{data}</td>
				</tr>" ;
				
		const result_row = "
				<tr>
					<td class='fmp_field_label'>{label}</td>
					<td class='fmp_field_value'>{value}</td>
				</tr>" ;
			
		const footer = "
			</table>" ;
		
		function __construct( $vars ) {
			
			$this->data = $vars ;
			$this->fm = new FileMaker() ;
			
		}
		
		function build_field_row( $label , $field_name ) {
			
			$row['value'] = $this->record->getField( $field_name ) ;
			$row['value'] = str_replace( "\n" , "<br>" , $row['value'] ) ; 

			if ( !empty( $row['value'] ) ) {

				if ( $label == "Source" and !empty( $row['value'] ) ) {

					$row['value'] .= ", p. " . $this->record->getField( "pages" ) ;

				}

				$row['label'] = $label ;

				return $this->templatize( $row , self::result_row ) ;

			} else {
				return "" ;
			}
		}
		
		protected function build_field_rows( $rowarr ) {
			
			$out = "" ;
			
			foreach( $rowarr as $label => $field ) {
				$out .= $this->build_field_row( $label , $field ) ;
			}
			
			return $out ;

		}

		function build_request( $req ) {
			
			$this->q = $this->data["q"] ;
			
			$compoundFind = $this->fm->newCompoundFindCommand( self::layout );
			
			$findreq = array() ;
			
			$c = count( $req["fields"] ) ;
			
			for( $i = 0 ; $i < $c ; $i++ ) {
				
				$findreq[$i] = $this->fm->newFindRequest( self::layout );
				$findreq[$i]->addFindCriterion( $req["fields"][$i] ,  "=" . $this->q );
				$compoundFind->add($i,$findreq[$i]);
			}

			$compoundFind->addSortRule( $req["sort"] , 1, FILEMAKER_SORT_ASCEND);
			
			return $compoundFind ;
			
		}
		
		function display() {					
						
			if ( FileMaker::isError($this->data) ) { 
				
				$this->fail() ;
				
			} else {
						
				$this->records = $this->data->getRecords();						
				$this->count = count($this->records) ;
				
				$this->write_header() ;

				if ( $this->count > 0 ) {					

					$this->write_body() ;

				} else {

					$this->fail() ;
				}
				
				echo self::footer ;
			}
			
		}
		
		function fail() {
			
			//echo $this->data->code . ": " & &this->data->message
			
			echo "<input type='hidden' id='fmp_count_" . get_class($this) . "' value='0'>";
			
		}
		
		function passes_test() {
			
			return 1 ;
			
		}
		
		public function search_fmp() {

			$this->set_properties() ;
						
			$this->data = $this->build_request( $this->outreq )->execute() ;	
			
			$this->display() ;
			
		}
		
		function set_properties() {	

			$this->fm->SetProperty('database', $this->db ) ;
			$this->fm->SetProperty('username', self::usr ) ;
			$this->fm->SetProperty('password', self::pw ) ;
			
		}
		
		function write_body() {
			
			$c = 1 ;

			$search = array( "{#}" , "{fmp_data}" ) ;

			//var_dump($this->data);
						

			foreach( $this->records as $record ) {
				
				$this->record = $record ;
				
				if ( $this->passes_test() ) {
					
					$out['data'] = "<table>" ;
					$out['data'] .= $this->build_field_rows( $this->outrows ) ;
					$out['data'] .= "</table>" ;				

					$out['data'] = str_ireplace( $this->q , "<span class='highlight'>$this->q</span>" , $out['data'] ) ;

					$out['c'] = $c ;
					
					echo self::result_header ;
					echo $this->templatize( $out , self::result_table ) ;
					
				}				

				$c++ ;				

			}

		}
		
		function write_header() {
	
			echo $this->templatize( $this , self::header )  ;
			
		}	
	}
	
	class essay_class extends fm {
		
		protected $outreq = array(
			"fields" => array(
				'quote',
				'notes',
				'source::c.footnote.txt',
				'outline::title.txt'),
			"sort" => 'outline::title.txt') ;
			
		protected $outrows = array(
			"Quote" => "quote" ,
			"Notes" => "notes" ,
			"Source"=> "source::c.footnote.txt",
			"#" 	=> "outline::title.txt" );
		
	}
	
	class db_aum extends essay_class {
		
		protected $db = "aum_essay" ;
	
	}
	
	class db_bon extends fm {
		
		protected $db = "bonessay" ;
		
		protected $outreq = array( 
			"fields" => array(
				'quote',
				'notes',
				'source::c.footnote.txt',
				'outline::title.txt',
				'outline::title_variations'),
			"sort" => 'outline::title.txt') ;
		
		protected $outrows = array(
			"Quote" => "quote" ,
			"Notes" => "notes" ,
			"Source"=> "source::c.footnote.txt",
			"#" 	=> "c.tags.txt"	);
	}
	
	class db_cf extends fm {
		
		protected $db = "Chinese Flashcards Literary" ;
		
		protected $outreq = array(
			"fields" => array(
				'characters',
				'definitions::definition'),
			"sort" => 'pinyin') ;
		
		protected $outrows = array(
			"Term" 			=> "characters" ,
			"Pinyin" 		=> "pinyin" ,
			"Definitions" 	=> "c.related_defs_unfiltered.txt") ;
	}
	
	class db_es extends essay_class {
		
		protected $db = "esoessay" ;
	}
	
	class db_ma extends essay_class {
		
		protected $db = "mahayana essay" ;
	}
	
	class db_se extends essay_class {
		
		protected $db = "sadhana_essay" ;
	}
	
	class db_tb extends essay_class {

		protected $db = "tbessay" ;
	}
	
	class db_tf extends fm {
		
		protected $db = "Tibetan Flashcards" ;
		
		protected $outreq = array( 
			"fields" => array(
				'wylie',
				'definitions::definition'),
			"sort" => "wylie") ;
		
		protected $outrows = array(
			"Term" => "wylie" ,
			"Definitions" => "c.related_defs_unfiltered.txt" );
	}
	
	class db_txt extends fm {
		
		protected $db = "texts and sa bcad" ;
		
		protected $outreq = array(
			"fields" => array(
				'Title English' ,
				'Title Short' ,
				'Title Wylie' ,
				'Place of Rediscovery' ,
				'Section English' ,
				'Author Editor Compiler' ,
				'Colophon English' ,
				'Colophon Rediscovery English' ,
				'questions::question' ,
				'questions::answer' ,
				'sa bcad::Title English' ,
				'sa bcad::Title Wylie'),
			"sort" => "Title English") ;
		
		protected $outrows = array(
			"Title English" => "Title English" ,
			"Title Short" => "Title Short" ,
			"Title Wylie" => "Title Wylie" ,
			"Person" => "Author Editor Compiler" ,
			"Section English" => "Section English" ,
			"Colophon" => "Colophon English" ,
			"Colophon Rediscovery" => "Colophon Rediscovery English" ,
			"sa bcad" => "c.all_sabcad_titles.txt" ,
			"Questions" => "c.all_questions.txt" ,
			"Answers" => "c.all_answers.txt"
			);
	}
	
	
	
?>