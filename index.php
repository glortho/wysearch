<?
	
	require_once "./inc/php/init.php" ;

	$user->get() ;

?>
<!DOCTYPE html>

<html>
<head>
	<meta charset="utf-8" />
	<title>ReSearch</title>
	<link rel="stylesheet" href="./inc/css/facebox.css" />
	<link rel="stylesheet" href="./inc/css/jquery-ui-1.8.1.custom.css" />
	<link rel="stylesheet" href="./inc/css/jquery.autocomplete.css" />
	<link rel="stylesheet" href="./inc/css/dic.css" />
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>  
	<script>!window.jQuery && document.write('<script src="./inc/js/jquery-1.4.2.min.js"><\/script>')</script>
	<script src="./inc/js/facebox.js"></script>
	<script src="./inc/js/hoverIntent.mini.js"></script>
	<script src="./inc/js/jquery-ui-1.8.1.custom.min.js"></script>
	<script src="./inc/js/jquery.cookie.js"></script>
	<script src="./inc/js/jquery.autocomplete.js"></script>
	<script src="./inc/js/dic.js"></script>
</head>

<body>

<header>
	<div class="tab"> </div>
	<div class="body">
		<div class="help">( ? )</div>
		<form id="term_form" action="" method="" onsubmit="javascript:dic.lookup(); return false;" style="display:inline">
			<input id="term" type="text" size="40"/>
			<div class="options_container">
				<div class="options_text" style="display:inline"></div>
				<div class="options" style="display:none;">
					<div style="overflow: hidden; float:left; margin:3px; background-color: #f0f0f0; padding: 4px 4px 4px 4px; border: 1px solid #ccc; height: 100%">
						<div class="option_set">
							<input type="checkbox" id="interm" checked /> terms&nbsp;
							<input type="checkbox" id="indef" /> definitions
						</div>
						<div class="option_set">
							<input type="checkbox" id="exact" checked /> exact&nbsp;
							<input type="checkbox" id="starts" /> begins with
						</div>
						<div class="option_set">
							<input type="checkbox" id="fuzzy" /> fuzzy
							<div id="slider_box" style="display:none; position:absolute; left: 67px; top: 69px; font-size: 0.9em; z-index: 101">
								<div id="slider" style="overflow-y: hidden; overflow-y: hidden; width: 70px;"></div>
							</div>
						</div>	
					</div>
					<div class="options_dicpic"></div>
					<div class="tools_right">
						<a href="javascript:dic.settings.save();return false;" title="save settings"><img align="left" src="./images/disk16.png" alt="save settings" title="save settings" width="14" height="14"/></a>
					</div>
				</div>
				<div id="tip" class="options_tip">
					<div id="tip_content">
						<div class="tip_item" id='level1'><strong>1</strong> Try different <b>prefixes</b> and <b>headletters</b></div>
					</div>
				</div>
			</div>
		</form>
	</div>	
</header>

<div id="external"></div>	
<div id="output"></div>	


<script type="text/javascript">
  var uservoiceOptions = {
    key: 'wylie',
    host: 'wylie.uservoice.com', 
    forum: '87693',
    alignment: 'right',
    background_color:'#005588', 
    text_color: 'white',
    hover_color: '#017501',
    lang: 'en',
    showTab: true
  };
  function _loadUserVoice() {
    var s = document.createElement('script');
    s.src = ("https:" == document.location.protocol ? "https://" : "http://") + "cdn.uservoice.com/javascripts/widgets/tab.js";
    document.getElementsByTagName('head')[0].appendChild(s);
  }
  _loadSuper = window.onload;
  window.onload = (typeof window.onload != 'function') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };
</script>

</body>
</html>