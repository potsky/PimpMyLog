/**
 * Cut a type and returns an object with set values
 *
 * type_parser( 'date:H:i:s' )     => parser = date, param = 'H:i:s' , cut = -1
 * type_parser( 'date:H:i/s/100' ) => parser = date, param = 'H:i/s' , cut = 100
 * type_parser( 'date' )           => parser = date, param = ''      , cut = -1
 * type_parser( )                  => parser = txt,  param = ''      , cut = -1
 *
 * @param   {string}  type  the type
 *
 * @return  {object}        the well-formatted object with parser,param,cut keys
 */
var type_parser = function( type ) {
	"use strict";
	var parser = 'txt';
	var param  = '';
	var cut    = 0;
	if ( type !== undefined ) {
		var a = type.split( '/' );
		if ( a[1] !== undefined ) {
			cut    = parseInt( type.split( '/' ).slice(-1) , 10 );
			parser = type.split( '/' ).slice(0,-1).join( '/' );
		} else {
			parser = type;
		}
		a = parser.split( ':' );
		if ( a[1] !== undefined ) {
			param  = parser.split( ':' ).slice(1).join( ':' );
			parser = a[0];
		}
	}
	return { 'parser' : parser , 'param' : param , 'cut' : cut };
};



/**
 * Cut val at cut chars and add &hellip; if val is really cutted
 * if cut > 0 : nianian...
 * if cut < 0 : ...aniania
 *
 * @param   {string}   val  the value to cut
 * @param   {integer}  cut  the count of chars to keep
 *
 * @return  {string}        the cutted value
 */
var val_cut = function( val , cut ) {
	"use strict";
	if ( cut === undefined ) {
		return val;
	}
	if ( cut === 0 ) {
		return val;
	}
	if ( val.length <= Math.abs(cut) ) {
		return val;
	}
	if ( cut > 0 ) {
		return val.substr( 0 , cut ) + '&hellip;';
	}
	else {
		return '&hellip;' + val.substr( cut );
	}
};


/**
 * Sort column according to field in right direction
 *
 * @param   {string}  field      the name of the column
 * @param   {integer} direction  the direction 1 ascending or -1 descending
 *
 * @return  {void}
 */
function s( field , direction ) {
	"use strict";
	sort  = field;
	sorto = direction;
	reload_page( true );
	get_logs( false , true );
}


/**
 * Return the offset in bytes in the log file of the oldest displayed log line
 *
 * @return  {integer}  the offset in bytes
 */
function get_top_offset() {
	return parseInt( $('#logsbody').find( 'tr:last-child' ).data('offset') , 10);
}


/**
 * Ajax call to get logs
 *
 * @param   {boolean}  load_default_values  If set to true, the ajax will use default values for the selected file if there are available
 * @param   {boolean}  load_full_file       If set to true, the log file will be parsed without keeping history. It is a slow process but mandatory when search of file have changed.
 * @param   {boolean}  load_from_get        If set to true, the GET parameters will override default values. This is used for the first launch for example.
 * @param   {boolean}  load_more            If set to true, we will load more logs append to the bottom of the table
 *
 * @return  {void}
 */
var get_logs     = function( load_default_values , load_full_file , load_from_get , load_more ) {
	"use strict";

	var wanted_lines;

	// Disable load more button
	$('.loadmore').button('loading');

	// Auto refresh stop
	if ( auto_refresh_timer !== null ) {
		clearTimeout( auto_refresh_timer );
		auto_refresh_timer = null;
	}

	// Load default values from file
	if ( load_default_values === true ) {

		// Load parameters from the query string
		if ( load_from_get === true ) {

			var found;

			// Notification
			if ( query_parameters.n === 'true' ) {
				set_notification( true );
			} else if ( query_parameters.n === 'false' ) {
				set_notification( false );
			} else {
				set_notification( files[file].notify );
			}

			// Max
			found = files[file].max;
			if ( typeof query_parameters.m !== undefined ) {
				$('#max option').each(function(){
					if ( this.value === query_parameters.m ) {
						found = query_parameters.m;
					}
				});
			}
			set_max( found );

			// Auto-refresh
			found = files[file].refresh;
			if ( typeof query_parameters.r !== undefined ) {
				$('#autorefresh option').each(function(){
					if ( this.value === query_parameters.r ) {
						found = query_parameters.r;
					}
				});
			}
			set_auto_refresh( found );

			// Displayed columns
			if ( query_parameters.t !== undefined ) {
				set_columns( query_parameters.t.split(',') );
			} else {
				set_columns( files[file].thinit );
			}

			// Sorting
			if ( query_parameters.o !== undefined ) {
				sort = query_parameters.o;
			} else {
				sort = files[file].sort;
			}

			if ( query_parameters.p !== undefined ) {
				sorto = query_parameters.p;
			} else {
				sorto = files[file].order;
			}
		}
		else {
			set_max( files[file].max );
			set_auto_refresh( files[file].refresh );
			set_notification( files[file].notify );
			set_columns( files[file].thinit );
			sort = files[file].sort;
		}
		// Manage the export button
		if ( files[file].export === false ) {
			$('#export').hide();
			$('#noexport').show();
		}
		else if ( ( export_default === false ) && ( ! files[file].export ) ) {
			$('#export').hide();
			$('#noexport').show();
		}
		else {
			$('#noexport').hide();
			$('#export').show();
		}

		load_full_file = true;
	}
	else {
		load_default_values = false;
	}

	// Set new paremeters in the url
	reload_page( true );

	// Load full logs and not increment
	if ( load_full_file === true ) {
		reset           = 1;
		file_size       = 0;
		load_more       = false;
		last_line       = '';
		has_loaded_more = false;
	}
	else {
		reset     = 0;
		load_full_file = false;
	}

	$('.loader').toggle();
	loading      = true;
	wanted_lines = $('#max').val();

	var post_values = {
			'ldv'         : load_default_values,
			'file'        : file,
			'filesize'    : file_size,
			'max'         : wanted_lines,
			'search'      : $('#search').val(),
			'csrf_token'  : csrf_token,
			'lastline'    : last_line,
			'reset'       : reset,
	};

	// We must read more logs
	if ( load_more === true ) {

		// set the offset to the last parsed byte to tell ajax where to begin
		post_values['sp'] = get_top_offset();

		// We will never clear logs again and we will display all logs on page until a full reset
		has_loaded_more   = true;
	}

	// Go for ajaxing
	$.ajax( {
		url      : 'inc/getlog.pml.php?' + (new Date()).getTime() + '&' + querystring,
		data     : post_values,
		type     : 'POST',
		dataType : 'json'
	} )
	.fail( function ( logs ) {
		// Layout
		$('.loader').toggle();
		loading = false;

		if ( logs.responseText.indexOf( 'Pimp My Log Login Match' ) > -1 ) {
			notify( "Pimp my Logs [" + files[file].display + "]" , lemma.youhavebeendisconnected );
			document.location.reload();
			return;
		}

		// Error
		if ( logs.error ) {
			$(".result").hide();
			$("#error").show();
			$('#errortxt').html( logs.responseText );
			notify( "Pimp my Logs [" + files[file].display + "]" , lemma.error );
			return;
		}

	})
	.done( function ( logs ) {

		// Layout
		$('.loader').toggle();
		loading   = false;

		// The last line is not sent when we load more logs
		// It is only sent when we are on the bottom of the log file
		if ( logs.lastline ) {
			last_line = logs.lastline;
			file_size = logs.newfilesize;
		}

		// Error
		if ( logs.error ) {
			$(".result").hide();
			$("#error").show();
			$('#errortxt').html( logs.error );
			notify( notification_title.replace( '%i' , file ).replace( '%f' , files[file].display ) , lemma.error );
			return;
		}

		// PHP Internal notices
		if ( logs.warning ) {
			pml_alert( logs.warning , 'warning' );
		}
		if ( logs.notice ) {
			pml_alert( logs.notice , 'info' );
		}
		if ( logs.singlewarning ) {
			pml_singlealert( logs.singlewarning , 'warning' );
		}
		if ( logs.singlenotice ) {
			pml_singlealert( logs.singlenotice , 'info' );
		}

		// Render
		$( '#error' ).hide();
		$( '.result' ).show();

		// No log message
		if ( logs.full ) {
			if ( logs.found === false ) {
				var nolog = lemma.no_log;
				if ( logs.search !== '' ) {
					if ( logs.regsearch ) {
						nolog = lemma.search_no_regex.replace( '%s' , '<code>' + logs.search + '</code>' );
					} else {
						nolog = lemma.search_no_regular.replace( '%s' , '<code>' + logs.search + '</code>' );
					}
				}
				$( '#nolog' ).html( nolog ).show();
				$( '#logshead' ).hide();
			}
			else {
				$( '#nolog' ).text( '' ).hide();
				$( '#logshead' ).show();
			}
		}
		else {
			if ( logs.logs ) {
				$( '#nolog' ).text( '' ).hide();
				$( '#logshead' ).show();
			}
		}

		// Search regex understood ?
		if ( logs.regsearch ) {
			$( '#searchctn' ).addClass('has-success');
			$( '#searchctn' ).prop( 'title' , lemma.regex_valid );
		} else {
			$( '#searchctn' ).removeClass('has-success');
			$( '#searchctn' ).prop( 'title' , lemma.regex_invalid );
		}

		// Header
		if ( logs.headers ) {
			$( '#logshead' ).text( '' );
			$( '.thmenucol' ).remove();

			var thtr = $('<tr>').addClass( file );
			sorto = ( parseInt( sorto , 10 ) === 1 ) ? 1 : -1;
			var sortn = -1;
			for ( var h in logs.headers ) {

				$('.thmenuicon').removeClass( 'text-danger' );

				var ic = '';
				if ( sort === h ) {
					sortn = sorto * -1;
					var q = ( sorto === 1 ) ? 'up' : 'down';
					ic    = '&nbsp;<span class="glyphicon glyphicon-chevron-' + q + '"></span>';
				}
				var a = $( '<th style="white-space:nowrap;"><a href=\'javascript:s("' + h + '",' + sortn + ')\'>' + logs.headers[ h ] + ic + '</a></th>' ).addClass( h ).appendTo( thtr );

				var f;
				if ( is_column_displayed( h ) ) {
					f = 'on';
				} else {
					$(a).hide();
					f = 'off';
				}
				$( '<li class="thmenucol"><a href="#" class="btn btn-default thmenuitem thmenu' + f + '" data-h="' + h + '" title="' + lemma.toggle_column.replace( '%s' , logs.headers[ h ] ) + '">' + logs.headers[ h ] + '</a></li>' ).appendTo( '.thmenu' );
			}
			thtr.appendTo( '#logshead' );
			set_column_icon();

			// Refresh th menu buttons when click
			$('.thmenuitem').click(function(e) {
				e.stopPropagation(); // Do not close the dropdown
				if ( $(this).hasClass('thmenuon') ) {
					remove_column( $(this).attr('data-h') );
				}
				else {
					add_column( $(this).attr('data-h') );
				}
			});
		}


		// Body
		if ( logs.full ) {
			$( '#logsbody' ).text( '' );
		}

		// New logs are available so remove the previous new log let marker
		if ( logs.logs !== undefined ) {
			$( '#logsbody tr' ).removeClass( 'newlog' );
		}

		var uaparser = new UAParser();
		var rowidx   = 0;
		var rows     = [];

		for ( var log in logs.logs ) {

			var tr = $('<tr>')
						.addClass( file )
						.data( 'log' , logs.logs[ log ].pml )
						.data( 'offset' , logs.logs[ log ].pmlo );

			for ( var c in logs.logs[ log ] ) {

				if ( ( 'pml' === c ) || ( 'pmlo' === c ) || ( 'pmld' === c ) ) {
					continue;
				}

				var type        = type_parser( files[file].format.types[ c ] );
				var val         = logs.logs[log][ c ];
				var title       = val;
				var output_html = true;

				// Prepare values
				if ( val === '-' ) {
					val = '';
				}

				if ( type.parser === 'uaw3c' ) {
					type.parser = 'ua';
					val = val.replace( /\+/g , ' ' );
				}

				// Parse values
				if ( 'badge' === type.parser )
				{
					var clas;
					if ( type.param === 'http' )
					{
						clas = badges[ type.param ][ logs.logs[log][ c ].substr( 0 , 1 ) ];
					}
					else if ( type.param === 'severity' )
					{
						clas = badges[ type.param ][ logs.logs[log][ c ].toLowerCase() ];
						if ( clas === undefined )
						{
							clas = badges[ type.param ][ logs.logs[log][ c ] ];
						}
					}
					if ( clas === undefined )
					{
						clas = 'default';
					}
					val = '<span class="label label-' + clas + '">' + val_cut( val , type.cut ) + '</span>';
				}
				else if ( 'date' === type.parser ) {
					title = logs.logs[ log ].pml;
					val   = val_cut( val , type.cut );
				}
				else if ( 'numeral' === type.parser ) {
					if ( val !== '' ) {
						if ( type.param !== '' ) {
							val = numeral( val ).format( type.param );
						}
					}
				}
				else if ( 'ip' === type.parser ) {
					if ( type.param === 'geo' ) {
						val = '<a href="' + geoip_url.replace( "%p" , val ) + '" target="linkout">' + val_cut( val , type.cut ) + '</a>';
					} else {
						val = '<a href="' + type.param + '://' + val + '" target="linkout">' + val_cut( val , type.cut ) + '</a>';
					}
				}
				else if ( 'port' === type.parser ) {
					val = '<a href="' + port_url.replace( "%p" , val ) + '" target="linkout">' + val_cut( val , type.cut ) + '</a>';
				}
				else if ( 'link' === type.parser ) {
					val = '<a href="' + val + '" target="linkout">' + val_cut( val , type.cut ) + '</a>';
				}
				else if ( 'ua' === type.parser ) {
					var ua  = uaparser.setUA( val ).getResult();
					var uas = type.param.match(/\{[a-zA-Z.]*\}/g);
					var uaf = false;
					for (var k in uas) {
						var d;
						try {
							/*jshint -W061 */
							d = eval( 'ua.' + uas[k].replace('{','').replace('}','') );
							if ( d === undefined ) {
								d = '';
							}
						} catch (e) {
							d = '';
						}
						if ( d !== '' ) {
							uaf        = true;
							type.param = type.param.replace( uas[k] , d );
						}
					}
					if ( uaf === true ) {
						val = $.trim( type.param );
					}
				}
				else if ( 'preformatted' === type.parser ) {
					val         = val_cut( val.replace(/(?:\\r\\n|\\r|\\n)/g , "\n" ).replace(/\\t/g , "    " ) , type.cut );
					output_html = false;
				}
				else if ( 'prefake' === type.parser ) {
					val = val_cut( val.replace(/(?:\r\n|\r|\n)/g,'<br/>') , type.cut );
				}
				else {
					val         = val_cut( val , type.cut );
					output_html = false;
				}

				if ( output_html === false ) {
					val = val.replace( /&hellip;/g , '...' );
				}

				var b = (output_html === true ) ? $( '<td></td>' ).html( val ) : $( '<td></td>' ).text( val );
				b     = b.prop( "title" , title ).addClass( 'pml-' + c + " pml-" + type.parser ).appendTo( tr );
				if ( ! is_column_displayed( c ) ) $( b ).hide();
			}

			if ( ! logs.full ) {
				tr.addClass('newlog');
				rowidx++;
			}

			rows.push( tr );
		}

		// display all logs so append to bottom
		if ( ( logs.full ) || ( load_more === true ) ) {
			$('#logsbody').append( rows );
		}

		// display only new logs, so append to top
		else {
			$('#logsbody').prepend( rows );

			// Do not remove lines if user has clicked on Load More button at least one time
			if ( has_loaded_more !== true ) {
				var rowd = $('#logsbody tr').length;
				if ( rowd > wanted_lines ) {
					rowd = rowd - wanted_lines;
					$('#logsbody').find( 'tr:nth-last-child(-n+' + rowd + ')' ).remove();
				}
			}
		}

		// Load more button
		// We can call get_top_offset() now because rows have not been sorted and the older log line is the last one
		var older_line_offset = get_top_offset();
		if ( ( older_line_offset <= 1 ) || ( ( logs.search !== '' ) && ( parseInt( logs.lpo , 10 ) <= 1) ) ) {
			$('.loadmore').text( $('.loadmore').data('nomore-text') ).addClass('disabled').prop('disabled','disabled').attr('title','');
		} else {
			$('.loadmore').button('reset').attr( 'title' , sprintf( lemma.loadmore , numeral( older_line_offset ).format('0 b') ) );
		}

		// Sort table
		if ( sort !== undefined ) {
			// Find column
			var i   = 0,
				col = -1;
			$('#logshead tr th').each(function() {
				if ( $(this).hasClass( sort ) ) {
					col = i;
				}
				i++;
			});
			if ( col >= 0 ) {
				var tbody = document.getElementById( 'logsbody' ),
					trs   = Array.prototype.slice.call( tbody.rows , 0 );
				trs = trs.sort( function (a, b) {
					a = a.cells[col].getAttribute("title");
					b = b.cells[col].getAttribute("title");
					return ( $.isNumeric( a ) && $.isNumeric( b ) ) ? sorto * ( parseFloat( a ) - parseFloat( b ) ) : sorto * a.toLowerCase().localeCompare( b.toLowerCase() );
				});
				for( i = 0 ; i < trs.length; ++i ) {
					tbody.appendChild( trs[i] );
				}
			}
		}

		// Footer
		var rowct = '';
		var rowc  = $('#logsbody tr').length;
		if ( rowc === 1 ) {
			rowct = lemma.display_log + ' ';
		} else if ( rowc > 1 ) {
			rowct = lemma.display_nlogs.replace( '%s' , rowc ) + ' ';
		}
		$("#footer").html( rowct + logs.footer );

		// Notification
		if ( first_launch === false ) {
			if ( logs.full ) {
				if ( logs.fingerprint !== fingerprint ) {
					notify( notification_title.replace( '%i' , file ).replace( '%f' , files[file].display ) , lemma.new_logs );
					fingerprint = logs.fingerprint;
				}
			} else {
				if ( rowidx === 1 ) {
					notify( notification_title.replace( '%i' , file ).replace( '%f' , files[file].display ) , lemma.new_log );
				} else if ( rowidx > 1 ) {
					notify( notification_title.replace( '%i' , file ).replace( '%f' , files[file].display ) , lemma.new_nlogs.replace( '%s' , rowidx ) );
				}
			}
		}
		first_launch = false;

		// Auto refresh go
		var p = Math.max( 0 , parseInt( $('#autorefresh').val() , 10 ) );
		if ( p > 0 ) {
			auto_refresh_timer = setTimeout( function() { get_logs(); } , p * 1000 );
		}

	} )
	.always( function () {
	});
};
