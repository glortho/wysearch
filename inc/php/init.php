<?

	require_once "conf.php";
	require_once "fx.php";
	require_once "classes/db.php";
	require_once "classes/template.php";
	require_once "classes/user.php";
	
	$db = new db() ;
	$user = new user( $_REQUEST ) ;

?>