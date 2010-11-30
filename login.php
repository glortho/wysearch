<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
 
<head profile="http://gmpg.org/xfn/11"> 
	<title>ReSearch: Tibetan Wylie Search</title> 
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
	<link rel="stylesheet" href="./inc/css/login.css" type="text/css" media="all" />  
	<script src="./inc/js/jquery-1.4.2.min.js"></script>
</head> 
<body>

<?

if ( $_REQUEST["flag"] == "failed" ) {

?>

<div class="flash">Login failed. Try again.</div>

<? } ?>

<div class="form">
	<form class="form" action="./hyperactive.php">
		<input type="hidden" name="flag" value="login" />
	
		<p class="email">
			<input type="text" name="email" id="email" />
			<label for="email">E-mail</label>
		</p>
	
		<p class="password">
			<input type="password" name="pword" id="pword" />
			<label for="pword">Password</label>
		</p>
	
		<p class="submit">
			<input type="submit" value="Go" />
		</p>
	
	</form>
</div>

<?

if ( $_REQUEST["flag"] == "failed" ) {

?>

<script type="text/javascript">
	$(function(){
		setTimeout( function() { $(".flash").fadeIn("slow") } , 300 );
		setTimeout( function() { $(".flash").fadeOut("slow") } , 3000 );
	});
</script>

<? } ?>
</body>
</html>