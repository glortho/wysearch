
var dic = {
	
	req: {},
	
	binding: {
		
		clicks: {
			
			add_buttons: function() {
				$('#submit_def').click( 
					function() { dic.submit_def( this ) }
				) ;
				$('#cancel_def').click( function() { $('#new').remove() ; } );
			},

			add_def: function() {

				$('.add').click( function() {
					var htm = 
					"<tr id='new'>" +
						"<td class='def'></td>" +
						"<td class='def'>" +
							"<span class='source'></span> &nbsp; " +
							"<span class='definition'>" +
								"<textarea id='def_new' rows='2' cols='50'></textarea>" +
								"<input id='submit_def' name='submit' style='position:relative; bottom:15px; left:2px' type='button' value='done'> " +
								"<input id='cancel_def' name='cancel' style='position:relative; bottom:15px; left:2px' type='button' value='cancel'>" +
							"</span>" +
						"</td>" +
					"</tr>" ;
					var anc = $(this).closest("tr") ;
					if ( anc.nextAll('.term_row:first').length > 0 ) {
						anc.nextAll('.term_row:first').before( htm ) ;
					} else {
						anc.nextAll('tr:last').after( htm ) ;
					}

					$('#def_new').focus() ;
					dic.selectedInput = $('#def_new') ;

					dic.binding.clicks.add_buttons() ;

				});
			},
			
			def: function( el ) {
				el.bind( "click" , function() {
					if ( dic.keydown.command == true ) { 
						$(this).unbind("click") ;
						dic.make_editable( $(this) ) ;
					}
				}) ;
			},
			
			delete_def: function( el ) {
				
				$('.delete').click( function() {
					var term = el.parent().attr("id") ;
					if ( confirm( "Are you sure you want to delete this term and all definitions?" ) ) {
						$.post( "./hyperactive.php" , { flag: "delete_term" , id: term } , function( data ) {
							el.closest("tr").fadeOut("slow", function() {
								dic.search.go() ;
							}) ;
						}) ;
					}

				});	
			},
			
			edit_buttons: function( el ) {
				var id = el.attr("id") ;

				el.children("input[name=submit_edit]").click( function() {
					dic.submit_edit( id ) ; 
				}) ;

				el.children("input[name=cancel]").click( function() {
					$("#" + id ).html( $("#" + id).children("textarea").val() ) ;
					dic.binding.clicks.def( $("#" + id ) ) ; 
				}) ;
			},
			
			new_def: {
				
				init: function( el ) {
					
					dic.binding.clicks.add_def() ;
					dic.binding.clicks.delete_def( el ) ;
					
				}
				
			},
			
			peek_inside: function( el ) {
				dic.peek_url = el.attr("href") ;
				$.facebox( { ajax: dic.peek_url } ) ; 				
			},

			init: function() {
				
				if ( dic.user == 1 ) {
					this.def( $("span.definition") ) ;
				}
			}
			
		},
		
		hover: {
			
			category_hover: function() {
			
				if ( dic.user == 1 ) {
				
					$('.label_header').hoverIntent({ 
						over: function() {
							var x = $(this).width() + 52 ;
							var y = $(this).closest('.label').offset().top - 50 ;
							$(this).append("<a class='addspan' href='#'><img onclick='dic.kill_click=true' align='middle' src='./images/add-icon.png' width='20' height='20' /></a>" ) ;
							$('.addspan').css({ "left": x , "top": y }).click( function() {
								dic.add_new($(this)) ;
							});
						},
						interval: 200 ,
						out: function() {
							$(".addspan").remove() ;
						},
						timeout: 200
					}) ;
				
				}
				
			},
			
			def_hover: function() {
				
				if ( dic.user == 1 ) {
				
					$('td.def').parent().hoverIntent({
						over: function() {
							$(this).children(".def_spacer").append("<img onclick='dic.delete_def(this)' class='delete' align='right' src='./images/delete-icon.png' height='16' width='16' />") ;
							$(this).find(".votes").show() ;
	
						},
						interval: 100 ,
						out: function() {
							$(this).children(".def_spacer").children(".delete").remove() ;
							$(this).find(".votes").hide() ;
						},
						timeout: 100
					});
					
				}
				
			},
			
			tab_hover: function() {
				$(".tab").hoverIntent({
					over: function() {
						$(this).hide( "fast", function() {
							$("div.body").slideDown("fast") ;
						})
					},
					interval: 50,
					out: function() {},
					timeout: 100
				}) ;
			},
			
			term_hover: function() {
			
				if ( dic.user == 1 ) {
				
					$('td.def_term b').hoverIntent({ 
						over: function() {
							var x = $(this).width() + 52 ;
							var y = $(this).parent().offset().top - 42 ;
							$(this).append(
								" <span class='addspan'><img align='left' class='add' src='./images/add-icon.png' height='20' width='20' />" + 
								" <img class='delete' align='right' src='./images/delete-icon.png' height='20' width='20' /></span>"
								) ;
							$('.addspan').css({ "left": x , "top": y }) ;
	
							dic.binding.clicks.new_def.init( $(this) ) ;
						},
						interval: 200 ,
						out: function() {
							$(".addspan").remove() ;
						},
						timeout: 200
					}) ;
				
				}
					
			},
			
			vote_hover: function() {
				
				if ( dic.user == 1 ) {
				
					$(".votes").hoverIntent({
						over: function() {
							dic.vote_action_show($(this));
						},
						interval: 80 ,
						out: function() {
							dic.vote_show($(this));
						},
						timeout: 80
					}) ;
					
				}
			},
			
			init: function() {
				
				dic.initializer(this);				
			}
			
		},
		
		init: function() {
			
			this.hover.init() ;
			this.clicks.init() ;
			
		}
		
	},
	
	//TODO: better abort (using jquery ajax queue?)
	
	abort_ajax: function() {
		if ( typeof(dic.req.dic) != "undefined" && dic.req.dic != 0 ) {
			for ( r in dic.req ) {
				if ( dic[r] != 0 ) {
					dic[r].abort() ;
				}
			}
		}
		return false ;
	},
	
	add_new: function( el ) {
		var val = $('#term').val() ;
		var htm = 
			"<div class='new'>" +
				"<form action='' method='post' onsubmit='javascript:dic.post_new(); return false;'>" +
					"<div class='label'>Term</div><div class='field'><input id='term_new' name='term' type='text' size='50' value='" + val + "'></div><br>" +
					"<div class='label'>Def</div><div class='field'><input id='def_new' name='def' type='text' size='50'></div><br>" +
					"<div class='label'>Source</div><div class='field'><input id='source_new' name='source' type='text' size='8'></div><br>" +
					"<div class='label'><input id='submit_new' name='submit_new' type='submit' value='done'></div>"
				"</form>" + 
			"</div>" ;
		$.facebox( htm ) ;
		$('#term_new').focus() ;
		dic.selectedInput = $('#term_new') ;
	},
		
	checkbox_clicked: function( el ) {
			
		if ( dic.keydown.o ) {
			var checked = !el.attr("checked") ;
			el.attr("checked" , checked ) ;
		} else {
			var checked = el.attr("checked") ;
		}
		
		id = el.attr("id") ;
		
		switch( id ) {
			case "starts" :
				$('#exact').attr("checked" , false ) ;
				break;
			case "exact" :
				$('#starts').attr("checked" , false ) ;
				break;
			case "fuzzy" :
				if ( checked ) {
					$("#slider_box").fadeIn("fast") ;
					dic.prefs.fuzzy.level_set(1) ;
				} else {
					$("#slider_box").css("display","none") ;
					dic.prefs.fuzzy.tip_retract() ;
				}
				break;
			case "indef" :
				if ( el.attr("checked") ) {
					$("#exact").attr("checked" , false ) ;
				} else {
					var term = $("#interm") ;
					if ( !term.attr("checked") ) {
						term.attr("checked",true);
					}
				}
				break;
			case "interm" :
				var def = $("#indef") ;
				if ( !el.attr("checked") && !def.attr("checked") ) {
					def.attr("checked",true);
				}
				break;
			default:
				break;
		}

		//$('#term').focus() ; 
		//dic.selectedInput = $('#term') ;
		dic.prefs.view.humanize() ;
	},
	
	delete_def: function( el ) {
		if( confirm("Are you sure you want to delete this definition?") ) {
			var id = $(el).parent().next().children(".definition").attr("id");
			$.post( "./hyperactive.php" , { flag: "delete_definition" , id: id } , function( data ) {
				$(el).closest("tr").fadeOut("slow", function() {
					$(this).remove() ;
				}) ;
			}) ;
		}
	},
	
	get_categories_html: function() {
		
		var out = "<div class='label' id='dic_label'><img class='icon' src='./images/dic.gray.png' width='30' height='30' /> <span class='label_header'>Dictionaries ( <img class='indicator' src='./images/indicator.gif' /> )</span></div>" ;
		if ( dic.user == 1 ) {
			out += "<div class='label' id='fmp_label'><img class='icon' src='./images/fmp.gray.png' width='30' height='30' /> <span class='label_header'>FileMaker ( <img class='indicator' src='./images/indicator.gif' /> )</span></div>" ;
		}
		out += "<div class='label' id='spot_label'><img class='icon' src='./images/spot.gray.jpeg' width='30' height='30' /> <span class='label_header'>Spotlight ( <img class='indicator' src='./images/indicator.gif' /> )</span></div>" ;
		return out ;
	},
		
	get_user: function() {
		if ( typeof(dic.user) == "undefined" || dic.user == "" ) {
			return $.cookie("login") ;		
		}
		return dic.user ;
	},
	
	initializer: function( obj ) {
		for ( o in obj ) {
			if ( o != "init" ) {
				eval("obj." + o + "()") ;
			}
		}
	},
	
	ischinese: function( term ) {
		return ( term.match(/[a-z]/gi) == null ? true : false ) ;
	},
	
	make_editable: function( el ) {
		var htm = el.html() ;
		var x = 90 ; //$(this).width()/6 ;
		var y = el.parent().height()/9 ;
		y = ( y < 2 ) ? 2 : y ;
		var htm_new = "<textarea id='edit_old' rows=" + y + " cols=" + x + ">" + htm + "</textarea><br><input name='submit_edit' style='position:relative; bottom:0px; left:26px' type='button' value='done'> <input name='cancel' style='position:relative; bottom:0px; left: 25px' type='button' value='cancel'>" ;

		el.html( htm_new ) ;
		$('#edit_old').focus() ; 
		dic.selectedInput = $('#edit_old') ;

		dic.binding.clicks.edit_buttons( el ) ;
	},
	
	prefs: {
		
		apply: function() {
			if ( this.selected && this.selected.length > 0 ) { 	//set in dic.init.get_settings
				var dicarray = this.selected.split("&") ;		//format is standard url args, so get array of args
				for ( i in dicarray ) {
					var item = dicarray[i] ;
					if ( item.length > 0 ) {
						var item = dicarray[i].split("=");	//split into {key,val}
						switch( item[0] ) {
							case "fuzzyl" :
								this.fuzzy.level_set( item[1] ) ;
								break;
							case "" : 	//in case one is empty, do nothing
								break;
							default:
								var checked = ( item[1] === "true" ) ; // only slider require different treatment, rest are dom els
								$("#" + item[0]).attr("checked" , checked ) ;
								break;
						}
					}
				}
				if ( $("#fuzzy").attr("checked") == true ) { $("#slider_box").show()}
			} else {
				//this.selected = dic.get_defaults() ;
			}
		},
		
		fuzzy: {
			
			level_get: function() {
				return this.level ;
			},
			
			level_set: function(val) {

				var tipel = $("#tip") ;

				if ( $(".options").is(":visible") && !tipel.is(":visible") ) {
					tipel.slideDown("fast") ;
				}
				if ( typeof(val.slider_pos) != "undefined" ) { 	// caller function is fuzzy_stop (having dragged the slider manually)
					var l = $("div[id^=level]").length ;
				} else {									// caller is apply_settings or a keypress
					var slider_val = (val-0.1)*25 ;
					this.slider.compare( slider_val ) ;
					this.slider.set( slider_val ) ;
					var l = val ;
				} 	
				this.level = l ;
				dic.prefs.view.humanize() ;
			},
			
			slider: {
				
				compare: function(val) {
					if ( val < 25 ) {
						$("#level2").remove() ;
						$("#level3").remove() ;
						$("#level4").remove() ;
					}
					if ( val >= 25 ) {
						if ( $("#level2").length == 0 ) {
							$("#tip_content").append("<div class='tip_item' id='level2'><strong>2</strong> Try similar <b>root letters</b> and <b>vowels</b></div>" );
						}
						$("#level3").remove() ;
						$("#level4").remove() ;
					}
					if ( val >= 50 ) {
						if ( $("#level3").length == 0 ) {
							$("#tip_content").append("<div class='tip_item' id='level3'><strong>3</strong> Try adding/subtracting <b>particles</b></div>" );
						}
						$("#level4").remove() ;
					}
					if ( val >= 75 ) {
						if ( $("#level4").length == 0 ) {
							$("#tip_content").append("<div class='tip_item' id='level4'><strong>4</strong> Tibetan black magic</div>" );
						}
					}
				},
				
				move: function(event,ui) {
					if ( !$("#tip").is(":visible") ) {
						$("#tip").slideDown("fast") ;
					}
					$('.ui-slider-handle').blur();
					this.compare( ui.value ) ;
				},
				
				set: function( val ) {
					if ( val > -1 ) {
						$("#slider").slider("option" , "value" , val ); //set fuzzy slider
						this.level = val ;
					}
				},
				
				stop: function(event,ui) {

					dic.prefs.fuzzy.level_set( { slider_pos: ui.value } ) ;

				}
			},
			
			tip_retract: function() {
				$("#tip").slideUp("fast");
			}
			
		},
		
		get: function() {
			
			this.selected = {};
			
			function get_abbreviations() {
				var abbr_checked = $("input[id^=dic_]:checked");
				if ( !abbr_checked.length ) {
					return "no dictionaries selected" ;
				} else {
					var abbr = $("input[type=checkbox][id^=dic_]") ;
					if ( abbr.length == abbr_checked.length ) {
						return "all dictionaries" ;
					} else {
						var abbr = new Array() ;
						var arr = "" ;
						abbr_checked.each( function() {
							arr = $(this).attr("id").split("_") ;
							abbr.push(arr[1]);
						});
						return abbr.join(", ");
					}
				}
			}
			
			$("input[type=checkbox]").each( function(){
				var that = dic.prefs;
				id = $(this).attr("id") ;
				that.selected[id] = $(this).attr("checked") ;
			});

			this.selected["abbreviations"] = get_abbreviations() ;
			this.selected["fuzzyl"] = this.fuzzy.level_get() ;

			if ( arguments.length ) {
				this.selected["string"] = "" ;
				for ( o in this.selected ) {
					this.selected["string"] += "&" + o + "=" + this.selected[o] ;
				}
			}

			return this.selected;
		},
		
		storage: {
			
			retrieve: function() {
				return $.cookie("options");
			},
	
			save: function() {
				var that = dic.prefs;
				var options = that.get() ;
				var store = "" ;
				for ( i in options ) {
					store += "&" + i + "=" + options[i] ;
				}
				$.cookie( "options" , store , { expires: 360 } ) ;
				that.selected = options ;
				that.view.toggle(0) ;
				$.facebox("Settings saved!") ;
				window.setTimeout( function() {$(document).trigger('close.facebox')} , 1300);
				$("#term").focus() ;
				return false; 
			}
		
		},
		
		view: {
		
			humanize: function() {
				var that = dic.prefs;
				var opts = that.get() ;
				var scope = new Array();
				var precision = new Array();
				for ( o in opts ) {
					if ( opts[o] !== false) {
						if ( o == "exact" ) {
							precision.push("exactly like this") ;
						} else if ( o == "starts" ) {
							precision.push("begins with this") ;
						} else if ( o == "interm" ) {
							scope.push("terms") ;
						} else if ( o == "indef" ) {
							scope.push("definitions") ;
						} else if ( o == "fuzzy" ) {
							var fl = that.fuzzy.level_get() ;
							precision.push("fuzzified: " + fl ) ;
	
						}
					}
				}
				var str = "" ;
				if ( precision.length > 0 ) {
					str += "<strong>{</strong> <span class='exp'>" + precision.join(", ") + "</span> <strong>}</strong>&nbsp; " ;
				} else {
					str += "<strong>{</strong> <span class='exp'>anywhere</span> <strong>}</strong>&nbsp; " ;
				}
				str += "<span class='exp'>in</span> &nbsp;<strong>{</strong> <span class='exp'>" + scope.join(", ") + "</span> <strong>}</strong>&nbsp; " ;
				str += "<span class='exp'>from</span> &nbsp;<strong>{</strong> <span class='exp'>" + opts.abbreviations + "</span> <strong>}</strong>" ;
	
				$('.options_text').html(str) ;
			},

			toggle: function( flag ) {
				if ( typeof(flag) == "undefined") {
					flag = !$(".options").is(":visible") ;
				}
	
				if ( !flag ) { // get rid of checkboxes to see text only
					if ( $("#tip").is(":visible") ) {
						$("#tip").slideUp("fast", function() {
							$('.options').slideUp("fast") ;
						});
					} else {
						$('.options').slideUp("fast") ;
					}
				} else {
					//$('.options_text').fadeOut("fast" , function() { 
						$('.options').slideDown("fast") ;
					//});
				}
			},
			
			toggle_dictionaries: function() {
				this.dic_toggle = !this.dic_toggle ;
				$("input[type=checkbox][id^=dic_]").attr("checked", !this.dic_toggle ) ;
				dic.prefs.view.humanize();
			}
		}
		
	},
	
	post_new: function() {
		var term = $('#term_new').val() ;
		var def = $('#def_new').val() ;
		var source = $('#source_new').val() ;
		var flag = "new" ;
		$.post( "./hyperactive.php" , { flag: flag , term: term , def: def , source: source } , function( data ) {
			if ( data != "" ) {
				$('.new').html( data ) ;
			} else {
				$(document).trigger('close.facebox') ;
			}
			$('#term').val( term ) ;
			dic.search.go() ;
		}) ;
		return false ;
	},
	
	search: {
		
		go: function() {
			
			function search_dictionary( term , options ) {
		
				//alert( "q=" + term + options ) ;
				
				dic.req["dic"] = $.ajax({
					type: "POST",
					url: "./hyperactive.php",
					data: "flag=lookup&q=" + term + options ,
					success: function(data){
		
					//$.post("./hyperactive.php" , { flag: "lookup" , q: term , s1: options.interm , s2: options.indef , ex: options.exact , st: options.starts, fuzzy: options.fuzzy, fuzzyl: options.fuzzyl, abbreviations: options.abbreviations } , function( data ) {
		
						dic.req.dic = 0 ;
						
						$("#dic_label").append( data ) ;
		
						dic.dic_count = $('#dic_count').val() ;
		
						$('#dic_label img.indicator').replaceWith( dic.dic_count ) ;
		
						dic.binding.init() ;
					
					}
				});
			}
			
			function search_filemaker( term , options ) {
		
				dic.req["fmp"] = $.ajax({
					type: "POST",
					url: "./hyperactive.php",
					data: "flag=fmp&q=" + term ,
					success: function(data){
				
					//$.post( "./hyperactive.php" , { flag: "fmp" , q: term } , function( data ) {
		
						dic.req.fmp = 0 ;
						dic.fmp_count = 0 ;
		
						$("#fmp_label").append( data ).find("input[id^=fmp_count_]").each( function() {
							dic.fmp_count += parseInt($(this).val()) ;
						}) ;
		
						$('#fmp_label img.indicator').replaceWith( dic.fmp_count.toString() ) ;
		
						if ( typeof(window['dic_count']) == "undefined" || dic.dic_count == "0" ) {
							$('#fmp_label .results_table').show() ;
						}
		
						$('#fmp_label .spotlight_kind').click( function() {
							var par = $(this).parent() ;
							if ( par.nextAll(".spotlight_header").length > 0 ) {
								var el = par.nextUntil(".spotlight_header:first") ;
							} else {
								var el = par.nextAll() ;
							}
							el.toggle() ;
						});
					}
				});
			}
			
			function search_spotlight( term , options ) {
				
				dic.req["spot"] = $.ajax({
					type: "POST",
					url: "./hyperactive.php",
					data: "flag=spotlight&q=" + term ,
					success: function(data){
				
						//$.post( "./hyperactive.php" , { flag: "spotlight" , q: term } , function( data ) {
		
						dic.req.spot = 0 ;
						
						$("#spot_label").append( data ) ;
		
						dic.spot_count = $('#spot_count').val() ;										
		
						$("#spot_label")
							.find(".indicator")
								.replaceWith( dic.spot_count )
							.end()
							.find(".spot_ref").
								click( function() {
									dic.binding.clicks.peek_inside($(this)) ;
									return false ;
		
								});
		
						if ( typeof(window['dic_count']) == "undefined" || dic.dic_count == "0" ) {
							$('#spot_label .results_table').show() ;
						}
		
						$('#spot_label .spotlight_kind').click( function() {
							var par = $(this).parent() ;
							if ( par.nextAll(".spotlight_header").length > 0 ) {
								var el = par.nextUntil(".spotlight_header:first") ;
							} else {
								var el = par.nextAll() ;
							}
							el.toggle() ;
						});
						
					}
		
				});	
			}
			
			var term = $("#term").val() ;
			
			if ( this.validate( term ) ) {
						
				//dic.abort_ajax() ; // TODO: fix this abort! right now it reloads the page on .abort()
	
				dic.prefs.view.toggle(0) ; // shouldn't need to do this, but just in case options are still showing
	
				$("#output").empty() ;
				$("#external").empty() ;
	
				var options = dic.prefs.get( "string" ) ; // string param tells options to also include a url string of args
	
				if ( options.interm || options.indef ) {
	
					$('title').html("ReSearch: " + term ) ;
					var win = window.location ;
					window.location = "http://" + win.hostname + win.pathname + "#q=" + term ;
	
					var head = dic.get_categories_html() ;
					$("#output").append( head ) ;
	
					search_dictionary( term , options.string ) ;
					
					if ( dic.user == 1 ) {
						search_filemaker( term, options ) ;
					}
	
					search_spotlight( term , options ) ;
					
					//search internet sources (may need to put this back in search_dictionary before dic.binding.init())
					
					if ( dic.ischinese( term ) ) {
	
						this.google( "zdic.net", term , 0 ) ;
						this.google( "nciku.com", term , 0 ) ;
	
					} else {
	
						this.google( "tbrc.org", term , 0 ) ;
						this.google( "jstor.org", term , 0 ) ;
						this.google( "otdo.aa.tufs.ac.jp", term , 0 ) ;
	
					}
	
					this.google( "" , term , 0 ) ;
	
				}
				return false ;
			}
		},
		
		google: function( site , term , start ) {
		
			var header = "" ;
			
			switch( site ) {
				
				case "tbrc.org": 
					header = "TBRC" ; break ;
				case "books.google.com": 
					header = "Google Books" ; break ;
				case "jstor.org": 
					header = "JSTOR" ; break ;
				case "nciku.com": 
					header = "NCIKU" ; break ;
				case "zdic.net": 
					header = "ZDIC" ; break ;
				case "otdo.aa.tufs.ac.jp":
					header = "Dunhuang" ; break ;					
				default: 
					header = "Google" ; break ;
				
			}
			
			var header_display = $("#" + header).children(".results_group").css("display") ;		
			
			if ( dic.global_term != term ) {
				$("#external").empty() ;
				dic.global_term = term ;
			}
					
			var q = ( site != "" ) ? "site:" + site + " " : "" ;
			q += "\"" + term + "\"" ;
			
			var url = "http://ajax.googleapis.com/ajax/services/search/web?q=" + q + "&rsz=large&v=1.0&callback=?&start=" + start + "&key=ABQIAAAAPx_9rYqOcMbR1P86dhjbLBQH-wIUnrTxOqn0uq2q7qb9I-e9QBQwwhLwxsysZibUOeIjwoh-INJKkg" ;
			
			$.getJSON( url , function (data) {
			   
				if (data.responseData.results && data.responseData.results.length > 0) {
					
					var results = data.responseData.results;
					var count = data.responseData.cursor.estimatedResultCount ;
					
					htm = "<div id='" + header + "' class='results_header'><span class='results_header_text'>" + header + "</span><span style='float:right'><span class='prev'> &lt; </span> " + count + " <span class='next' href=''> &gt; </span></span><div class='results_group'" ;
					
					htm += " style='display:" + header_display + "'>" ; 
	
					for (var i=0; i < results.length; i++) {	
							
						var content = results[i].content ;
												
						htm += "<div class='results_title'><a target='_blank' href='" + results[i].unescapedUrl + "'>" + results[i].titleNoFormatting + "</a></div>" ;
						htm += "<div class='results'>" + content + "</div>" ;
						
						if ( site == "") {
							htm += "<div class='results_url'>" + results[i].visibleUrl + "</div>" 
						}
					} 
					
					htm += "</div></div>" ;
					
					if ( $( "#" + header ).length > 0 ) {
						
						var el = $( "#" + header ) ;
						el.replaceWith( htm ) ;
						
					} else {
						
						var el = $("#external") ;
						el.append( htm ) ;
					}
					
					$("#" + header ).children(".results_header_text").click( function() {
						
						$(this).nextAll("div.results_group").toggle()	;
						
					}).end().find('.prev').click( function() {
						
						var prev = ( start - 8 < 0 ) ? 0 : start - 8 ;
						dic.search.google( site , term , prev ) ;
						return false ;
						
					}).end().find('.next').click( function() {
						
						var next = start + 8 ;
						dic.search.google( site , term , next ) ;
						return false ;
						
					}) ;
	
			   }
	
			});
		},
		
		validate: function( term ) {
			var valid = {} ;
			valid["term_exists"] = ( term != "" ) ;
			valid["dics_checked"] = $("input[type=checkbox][id^=dic]:checked").length ;
			if ( valid.term_exists && valid.dics_checked  ) {
				return true ;
			} else if ( !valid.dics_checked ) {
				$.facebox("Select a dictionary, my friend.") ; //$.facebox( {ajax:"http://localhost/~jed/dic/hyperactive.php?flag=source_list"} ) ;
			}
			return false ;
		}
	},
	
	submit_def: function( el ) {
		var flag = "new_sub" ;
		var id = $(el).closest("tr").prevAll(".term_row:first").children("td").attr("id") ;
		$.post( './hyperactive.php' , { flag: flag , term_id: id , def: $('#def_new').val() } , function( data ) {
			dic.search.go() ;
		})
	},
	
	submit_edit: function( id ) {
		var flag = "edit" ;
		$.post( "./hyperactive.php" , { flag: flag , id: id , def: $('#edit_old').val() } , function( data ) {
			$("#" + id ).html( data ) ;
			dic.binding.clicks.def( $("#" + id) ) ;
		}) ;
	},

	vote: function( el, dir ) {
		var def = $(el).closest(".votes").prev(".definition")
		id = def.attr("id") ;
		def = def.html() ;
		var htm = 
			"<div class='details'>" +
				"Include as much info as you can about the text or context that led you to vote this way.<br/>" +
				"<form action='' method='post' onsubmit='javascript:dic.vote_post(" + id + "," + dir + "); return false;'>" +
					"<div class='label'>Nickname</div><div class='field'><input id='context_nickname' name='context_nickname' type='text' size='50'value='nldz'/></div><br>" +
					"<div class='label'>Title</div><div class='field'><input id='context_title' name='context_title' type='text' size='50'/></div><br>" +
					"<div class='label'>Author</div><div class='field'><input id='context_author' name='context_author' type='text' size='50'/></div><br>" +
					"<div class='label'>Sect</div><div class='field'><input id='context_sect' name='context_sect' type='text' size='50'/></div><br>" +
					"<div class='label'>Text Genre</div><div class='field'><input id='context_genre' name='context_genre' type='text' size='50'/></div><br>" +
					"<div class='label'>Date or Time Period</div><div class='field'><input id='context_date' name='context_date' type='text' size='20' value=''/></div><br>" +
					"<div class='label'>Page.line</div><div class='field'><input id='context_page' name='context_page' type='text' size='10'/></div><br>" +
					"<div class='label'>Note</div><div class='field'><textarea rows='3' cols='40' name='context_notes' id='context_notes'>" + def + "</textarea></div><br>" +
					"<div class='label'><input id='submit_new' name='submit_new' type='submit' value='done'/></div>" +
				"</form>" + 
			"</div>";
		$.facebox(htm) ;
		$("#context_nickname").focus() ;
			
		/* autocomplete later
		
		$("#context_nickname").focus().autocomplete("./hyperactive.php?flag=ac_nickname", {
			matchContains: true,
			width: 200,
			scroll: false
		}).result(function(event,data,formatted){alert(data.id)});
		*/
	},
	
	vote_action_show: function( el ) {
		dic.vote_count = el.children(".vote_count") ;
		dic.vote_count.replaceWith("<span class='vote_buttons'><a class='vote' href='#' onclick='dic.vote(this,\"1\");return false'>Up</a> | <a class='vote' href='#' onclick='dic.vote(this,\"-1\");return false'>Down</a> | <a class='vote' href='#' onclick='dic.vote_info(this);return false'>Info...</a></span>" ) ;
	},
	
	vote_info: function( el ) {
		var id = $(el).closest(".votes").prev(".definition").attr("id") ;
		$.post( "./hyperactive.php" , {
			flag: "vote_info" ,
			definition_id: id 
			},
			function( data ) {
				$.facebox( data ) ;
			}
		);
	},
	
	vote_post: function( id , dir ) {
		
		$.post( "./hyperactive.php" , {
			flag:"vote",
			id: id,
			dir: dir,
			cn: $('#context_nickname').val(),
			ct: $('#context_title').val(),
			ca: $('#context_author').val(),
			cs: $('#context_sect').val(),
			cg: $('#context_genre').val(),
			cd: $('#context_date').val(),
			cp: $('#context_page').val(),
			cnt: $('#context_notes').val()
			} , 
			function( data ) {
								
				$(document).trigger('close.facebox') ;
				
				var targel = $("#" + id ).next() ;
				dic.vote_show( targel ) ;
				
				// dynamically increment displayed vote count
				
				var targel = targel.children(".vote_count") ;
				var old_votes = targel.html() ;
				var new_votes = Number(old_votes) + Number(dir) ;
				
				// move incremented row to new place relevant to other votes
				// TODO: change this so that it looks up if incrementing and down if decrementing
				// and fix the check for self
				
				var tr = targel.closest("tr") ;
				var trfirst = tr.prevAll(".term_row:first") ;
				if ( tr.nextAll('.term_row:first').length > 0 ) {
					var trend = tr.nextAll('.term_row:first') ;
				} else {
					var trend = tr.nextAll('tr:last') ;
				}
				trend = trend.html() ;
				
				var i = 0 ;
				do {
					trcheck = trfirst.nextAll("tr").eq(i) ;
					trcount = trcheck.find(".vote_count").html() ;
					if ( Number(trcount) <= new_votes ) {
						i = -1 ;
					} else {
						i++ ;
					}
				} while ( trcheck.html() != trend && i > -1 ) ;
				
				if ( trcheck.html() != tr.html() ) {
					
					tr.fadeOut("slow", function() {
						$(this).children(".def_spacer").children(".delete").remove() ;
						$(this).find(".votes").hide() ;
						$(this).insertBefore( trcheck ).find(".vote_count").html(new_votes).end().fadeIn() ;
					}) ;
					
				} else {
					
					tr.find(".vote_count").html(new_votes) ;
					
				}
									
				// TODO: show new number for a moment in targel
			}
		);
	},
	
	vote_show: function( el ) {
		el.children(".vote_buttons").replaceWith(dic.vote_count) ;
	},

	init: function() {
		
		binding = {
			
			clicks: {

				bind_checkbox: function() {

					$('input[type=checkbox]').change( function() { 
						dic.checkbox_clicked( $(this) ) ;
					});

				},

				doc: function() {
					$(document).bind("click", function(event) {
						if( !$(event.target).closest(".options_container").length ) {
							dic.prefs.view.toggle(0);
						} ;
					});
				},
				
				help: function() {
					$(".help").click(function(){
						$.facebox({ ajax: "./help.html"});
					});
				},
				
				labels: function() {
					$(".label_header, .icon").live("click", function() { 
						if ( !dic.kill_click ) {
							$(this).nextAll(".results_table").toggle() 						
						}
					});
				},
				
				summary: function() {
					$(".summary").live("dblclick" , function() {
						var el = $(this);
						var margin = (el.data("margin") > 0) ? el.data("margin") : 200 ;
						margin += 200;
						el.data("margin", margin) ;
						var pos = el.children(".summary_index").html() ;
						var url = dic.peek_url ; // + "&pos=" + pos + "&margin=" + margin ;
						$.post( url , { pos: pos , margin: margin } , function( data ) {
							el.children(".summary_snippet").html("..." + data + "...");
						});
					})
				},

				init: function() {

					dic.kill_click = false ;

					dic.initializer(this);
				}


			},
			
			hover: {
				
				body_hover: function() {
				
					if ( dic.user == 1 ) {
				
						$("div.body").hoverIntent({
							over: function() { $('.tools_right').fadeIn("fast"); },
							interval: 100,
							out: function() { $('.tools_right').fadeOut("fast") },
							timeout: 500
						});
					}
				},
				
				options_hover: function() {
					$('.options_container').hoverIntent ({
						over: function() { dic.prefs.view.toggle(1) },
						interval: 100,
						out: function(event) { dic.prefs.view.toggle(0) },
						timeout: 500
					});
				},
				
				slider_hover: function() {
					$('#slider').hoverIntent ({
						over: function() { $("#tip:hidden").slideDown("fast") },
						interval: 20,
						out: function() {},
						timeout: 100
					});
				},
				
				init: function() {
					dic.initializer(this) ;
				}
			},
			
			keys: {
				
				keydown: function() {
					
					dic.keydown = {} ;
					
					function enter() {

						if ( dic.keydown.command ) {	// if command also down, submit
							var id = dic.selectedInput.attr("id") ;
							if ( id == "def_new" ) {
								dic.submit_def( dic.selectedInput ) ;
							} else if ( id == "edit_old" ) {
								id = dic.selectedInput.closest("span").attr("id") ;
								dic.submit_edit( id ) ; 
							}
						}

					}
					
					$(document).bind(($.browser.opera ? "keypress" : "keydown"), function(event) {

						//alert(event.keyCode);
						
						switch(event.keyCode) {

							case 13: 	// return

								enter() ;
								break ;
								
							case 16: 	// shift
							
								dic.keydown["shift"] = true ;
								break ;

							case 17: 	// control

								dic.keydown["control"] = true ;
								$("#term").blur();
								break ;
								
							case 27: 	// esc
							
								if ( $("#facebox").length ) {
									$("#term").focus() ;
								}
								break;

							case 93: 	// command (right)

								dic.keydown["command"] = true ;
								break ;

							case 91: 	// command (left)

								dic.keydown["command"] = true ;
								break ;
								
								
							case 48: 	// 0
							
								if ( dic.keydown.control ) {
									dic.view.toggle_dictionaries() ;
								}
								break ;
								
							case 49: 	// 1
							
								if ( dic.keydown.f ) {
									dic.prefs.fuzzy.level_set(1)
								}
								break;
								
							case 50: 	// 2

								if ( dic.keydown.f ) {
									dic.prefs.fuzzy.level_set(2)
								}								
								break;
								
							case 51: 	// 3

								if ( dic.keydown.f ) {
									dic.prefs.fuzzy.level_set(3)
								}
								break;

							case 52: 	// 4

								if ( dic.keydown.f ) {
									dic.prefs.fuzzy.level_set(4)
								}
								break;

							case 66: 	// b
							
								if ( dic.keydown.o ) {
									dic.checkbox_clicked( $('#starts') ) ;
								}
								break;
							
							case 68: 	// d

								if ( dic.keydown.o ) {
									dic.checkbox_clicked( $('#indef') ) ;
								}
								break;

							case 69: 	// e

								if ( dic.keydown.o ) {
									dic.checkbox_clicked( $('#exact') ) ;
								}
								break;

							case 70: 	// f

								if ( dic.keydown.o ) {
									dic.checkbox_clicked( $('#fuzzy') ) ;
								}
								dic.keydown["f"] = true ;
								break;

							case 76: 	// l

								if ( dic.keydown.control ) {
									$(".tab").hide();
									$("div.body").slideDown("fast") ;
									$("#term").focus().select() ;
									scroll(0,0) ;
								}
								break ;

							case 78: 	// n

								if ( dic.keydown.control ) {
									dic.add_new() ;
								}
								break ;
								
							case 79: 	// o

								if ( dic.keydown.control && dic.keydown.o ) {
									dic.prefs.view.toggle() ;
								} else if ( dic.keydown.control ) {
									dic.keydown["o"] = true ;
								}
								break ;

							case 83: 	// s
							
								if ( dic.keydown.control ){
									dic.prefs.storage.save();
								}
								break;

							case 84: 	// t
								if ( dic.keydown.o ) {
									dic.checkbox_clicked( $('#interm') ) ;
								}
								break;

							default:
								dic.keydown.command = false;
								dic.keydown.shift = false;
								dic.keydown.control = false;
								break;

						}

					});
					
				},
				
				keyup: function() {
										
					$(document).bind("keyup", function(event) {
						
						switch(event.keyCode) {
							
							case 16: 	// shift

								dic.keydown["shift"] = false ;
								break ;
							
							case 17:

								dic.keydown["control"] = false ;
								dic.keydown["o"] = false ;
								if ( !$("#facebox").is(":visible") ) {
									$("#term").focus() ;
								} else {
									//alert("here");
								}
								break ;	

							case 70:
							
								dic.keydown["f"] = dic.keydown.control ? true : false ;
								break;
							
							case 79: 	// o

								dic.keydown["o"] = dic.keydown.control ? true : false ;
								break;
								
							case 93:

								dic.keydown["command"] = false ;
								break ;	

							case 91:

								dic.keydown["command"] = false ;
								break ;				

							default:

								break;

						} ;

					});
				},
				
				init: function() {
					
					dic.initializer(this);
					
				}
			
			},
			
			init: function() {

				this.hover.init() ;
				this.keys.init() ;
				this.clicks.init() ;
				
			}
			
		} ;
		
		function consume_external() {
			
			var q = ( location.search == '' ) ? location.hash : location.search ;
			//var x = q ;
			if ( q != "" ) {
				q = q.split(/q=/) ;
				q = q[1].replace( /\+/g , " " ) ;
				//$('#term').val(x) ;
				$('#term').val(unescape(q)) ;
				dic.search.go() ;
			}	
		}
		
		function load() {
			$(".options_dicpic").load("./hyperactive.php?flag=source_list", function() {
				
				dic.user = dic.get_user() ;
				
				dic.prefs.selected = dic.prefs.storage.retrieve() ;

				dic.prefs.apply( dic.prefs.selected ) ;

				dic.prefs.view.humanize() ;

				binding.init() ;

				consume_external() ;
			}) ;
		}

		load() ;
		
	}
		
} ;


$(function() {
	
	$('#term').focus() ;
	dic.selectedInput = $('#term') ;
	
	
	$('textarea, input').focus( function() {
		dic.selectedInput = $(this) ;
		$(this).select() ;
	});
	
	$('textarea, input').blur( function() {
		dic.selectedInput = false ;
	});
	
	$("#slider").slider({
		animate: false, 
		range: 'min',
		stop: function(event,ui) {
			dic.prefs.fuzzy.slider.stop(event,ui);
		},
		slide: function(event,ui) {
			dic.prefs.fuzzy.slider.move(event,ui);
		}
	}) ;
	
	/* TODO: figure out autocomplete stuff
	
	$("#term").autocomplete( "./hyperactive.php?flag=ac_history", {
		matchContains: true,
		width: 200,
		scroll: false
	}).result(function(event,data,formatted){dic.search.go()});
	*/
	
	dic.init() ;
					
	
});