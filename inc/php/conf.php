<?

	$mode = "" ;
   	
  define("DOC_ROOT",$_SERVER["DOCUMENT_ROOT"]);
	define("DB_HOST","localhost");
  	
	if ( $mode == "" ) {
	
		define("DOMAIN","localhost");
		define("DB_USER","");
		define("DB_PASS","");
		define("DB_NAME","dic");	
 		define("WEB_DIR","/dic/");
	
	}
   	
   	define("SITE_PATH",DOC_ROOT.WEB_DIR);
   	define("IMAGE_PATH",SITE_PATH."images/");
   	   	
   	$self = $_SERVER["PHP_SELF"];
   	$self_url = $_SERVER["REQUEST_URI"];
   	$cr = "\r\n";

?>
