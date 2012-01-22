<?

class user {
	
	const tbl = "user" ;
	
	function __construct( $vars ) {
		
		$this->cookie_expiry = time()+60*60*24*7 ;
		
		$this->data = $vars ;
		
	}

	function fail() {
	
		$url = "./login.php" ;
		$notify = "?flag=failed" ;
		
		$args = func_get_args() ;
		
		if ( isset($args[0]) and $args[0] == "notify" ) {
		
			$url .= $notify ;
		
		}
	
		header("Location: $url") ;
	
	}
	
	function get() {
	
		global $db ;
			
		if ( !isset( $this->id ) ) {
		
			if ( $this->id = $_COOKIE["login"] ) {
			
				if ( $data = $db->SelectRows("select id from " . self::tbl . " where id = " . $this->id ) ) {
					
					return $this->id ;
				
				}

			}
			
			$this->fail() ;

		}
		
		return $this->id ;
	
	}
	
	function login() {
	
		global $db ;
		
		extract( $this->data ) ;
		
		if ( $data = $db->SelectRows("select id from " . self::tbl . " where email = '$email' and pword = password('$pword')" ) ) {
			
			$this->session_init( $data[0][0] ) ;
			
		} else {
		
			$this->fail("notify") ;
		
		}
	
	}
	
	function session_init( $id ) {
	
		setcookie("login", $id , $this->cookie_expiry) ;
		
		header("Location: ./index.php");
	
	}

}