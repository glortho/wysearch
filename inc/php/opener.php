<?

require_once './init.php';

ini_set('memory_limit','300M');

class opener extends template {

  const target_dir = "/Users/Shared/files/" ;

  const summary = "<div class='summary'>{summary}</div>" ;

  const summary_item = "<div class='summary_index'>{index}</div><div class='summary_snippet'>...{summary}...</div>" ;

  function __construct( $vars ) {

    $this->data = $vars ;

    $this->file = $vars["f"] ;
    $this->q = stripslashes($vars["q"]) ;

    $this->file_arr = pathinfo( $this->file ) ;

    foreach( $this->file_arr as $prop => $val ) {
      $this->$prop = $val ;
    }

    // above results in $this->basename, $this->dirname, $this->filename, $this->extension

    switch ( $this->extension ) { //TODO: convert extension to lowercase before switch

    case "pdf":

      $this->output = $this->read_pdf() ;
      break;

    case "evernote":

      $this->output = $this->read_evernote() ;
      break;

    case "doc":

      $this->output = $this->read_word() ;
      break;

    case "docx":

      $this->output = $this->read_wordx() ;
      break;

    case "emlx":

      $this->output = $this->read_mail() ;
      break;

    default:
      break ;
    }
  }

  function convert_pdf( $target_dir ) {

    $copypath = $target_dir . $this->basename ;
    copy( $this->file , $copypath ) ;

    $outpath = preg_replace("/pdf$/i", "txt", $copypath) ;
    system("/usr/local/bin/pdftotext \"$copypath\"" , $ret);

    if ($ret == 0) {
      $ret = $outpath ;
    } else if ($ret == 127) {
      print "Could not find pdftotext tool.";
    } else if ($ret == 1) {
      print "Could not find pdf file.";
    } else {
      echo $ret ;
    }
    unlink($copypath);
    return $ret ;
  }

  function read_evernote() {

    $evernote_dir = "/Users/Shared/evernote/notes/" ;

    $data = shell_exec( "mdls -name kMDItemDisplayName -name kMDItemContentCreationDate \"$this->file\"" ) ;
    $data = explode( "\n" , $data ) ;
    $file_data = array() ;
    foreach ( $data as $item ) {
      $item = explode( "= " , $item ) ;
      $file_data[] = str_replace( "\"" , "" , $item[1] ) ;
    }
    $created = $file_data[0] ;
    $name = $file_data[1] ;

    $search 	= array( "/" , ":" ) ;
    $replace 	= array( "_" , "/" ) ;

    $name = str_replace( $search, $replace , $name ) ;

    //TODO: check to see how evernote handles duplicate note names and then use creation date to verify

    $html = $evernote_dir . $name . ".html" ;

    if ( file_exists( $html ) ) {
      $this->value = $name . "<br><br>" ;
      $this->value .= file_get_contents( $html ) ;
      $this->value = str_replace( "src=\"" , "src=\"$evernote_dir" ,  $this->value ) ;
      return $this->value ;
    }
    return false ;
  }

  function read_mail() {

    $headers = array("From","Date","To","Subject") ;

    $this->value = file_get_contents( $this->file ) ;

    $out = "" ;

    foreach( $headers as $header ) {

      $match = "$header:.*" ;
      if ( preg_match( "/$match/" , $this->value , $matches ) ) {
        $out .= $matches[0] . "\r" ;
      } else {
        //echo "fail";
      };
    }

    $search = array( "<" , ">" , "") ;
    $replace = array( "&lt;" , "&gt;" ) ;

    $out = nl2br( str_replace( $search , $replace , $out ) );

    return array( $this->value , $out ) ;

  }

  function read_pdf() {

    $readpath = self::target_dir . $this->filename . ".txt" ;

    if ( !file_exists( $readpath ) ) {

      $readpath = $this->convert_pdf( self::target_dir ) ;
      if ( is_numeric( $readpath ) ) return false ;

    } else {
      //echo "file exists";
    }
    $this->value = file_get_contents($readpath) ;
    return $this->value ;

  }

  function read_word() {

    $readpath = self::target_dir . $this->filename . ".txt" ;

    if ( !file_exists( $readpath ) ) {

      $fileHandle = fopen($this->file, "r");
      $line = @fread($fileHandle, filesize($this->file));
      $lines = explode(chr(0x0D),$line);
      $outtext = "";
      foreach($lines as $thisline)
      {
        $pos = strpos($thisline, chr(0x00));
        if (($pos !== FALSE)||(strlen($thisline)==0))
        {
        } else {
          $outtext .= $thisline." ";
        }
      }
      $this->value = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);

    } else {
      $this->value = file_get_contents($readpath) ;
    }

    return $this->value ;
  }

  function read_wordx() {

    $readpath = self::target_dir . $this->filename . ".txt" ;

    if ( !file_exists( $readpath ) ) {

      require_once "./classes/pclzip.php" ;

      $zip = new PclZip( $this->file );

      $list = $zip->extract(PCLZIP_OPT_BY_NAME, "word/document.xml", PCLZIP_OPT_EXTRACT_AS_STRING);
      if ($list == 0) {
        return "ERROR : ".$zip->errorInfo(true);
      } else {
        $this->value = $list[0]['content'] ;
        $list = $zip->extract(PCLZIP_OPT_BY_NAME, "word/footnotes.xml", PCLZIP_OPT_EXTRACT_AS_STRING);
        if ($list == 0) {
          echo "ERROR : ".$zip->errorInfo(true);
        } else {
          $this->value .= $list[0]['content'] ;
        }
        return $this->value ;
      }
    } else {
      return file_get_contents($readpath) ;
    }
  }

  function extend_summary() {
    $index = $this->data['pos'] ;
    $margin = $this->data['margin'] ;

    $pos = strnpos( $this->value , $this->q , $index ) ;
    $this->value = substr( $this->value , $pos - $margin , strlen( $this->q ) + ($margin*2) ) ;
    $this->value = nl2br( $this->value );
    $this->value = str_ireplace( $this->q , "<span class='highlight'>$this->q</span>" , $this->value ) ;

    echo $this->value ;

  }

  function summarize() {

    echo '<span style="font-size: 1.1em; color: #000; font-style: italic">Double-click near the <span class="highlight">highlighted word(s)</span> to expand the context.</span>
      <hr>';

    $args = func_get_args() ;

    if ( is_array( $args[0] ) ) {
      $header =  $args[0][1] . "<br><br>" ;
      $this->value = $args[0][0] ;
    } else {
      $header = "" ;
      $this->value = $args[0] ;
    }

    //$this->value = nl2br( $this->value );
    //$this->value = str_ireplace( $this->q , "<span class='highlight'>$this->q</span>" , $this->value ) ;

    $count = substr_count( $this->value , $this->q ) ;

    if ( $count ) {

      $pos = 0 ;
      $oldpos = 0;
      $margin = 200 ;

      $summary = "" ;

      for ( $i = 0 ; $i < $count ; $i++ ) {

        $pos = stripos( $this->value , $this->q , $pos + 1 ) ;

        if ( $pos - $margin > $oldpos ) {
          $display['index'] = $i + 1 ;
          $display['summary'] = substr( $this->value , $pos - $margin , strlen( $this->q ) + ($margin*2) ) ;
          $display['summary'] = nl2br($display['summary']);
          $display['summary'] = str_ireplace( $this->q , "<span class='highlight'>$this->q</span>" , $display['summary'] ) ;
          $out = $this->templatize( $display , self::summary_item ) ;
          $summary .= $this->templatize( $out , self::summary ) ;

          $oldpos = $pos ;
        }

      }

      echo $summary ;
      return true ;

    } else {

      echo $this->value ;
      return false ;

    }


  }


}

$opened_file = new opener( $_REQUEST ) ;

if ( isset($opened_file->data['pos']) && $opened_file->data['pos'] > 0 ) {
  $opened_file->extend_summary() ;
} else {
  $opened_file->summarize( $opened_file->output ) ;
}

?>
