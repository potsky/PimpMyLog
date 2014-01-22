$(function() {
	"use strict";

	// Logo click
	$('.logo').click(function() {
		location.reload();
	});

	$('table').addClass('table table-striped table-bordered table-hover table-condensed');

	$('#regextest').submit( function( event ) {

		$.ajax( {
			url  : 'test.REMOVE_UPPERCASE.php' ,
			type : 'POST',
			data : {
				'r' : $( '#inputRegEx' ).val(),
				'l' : $( '#inputLog' ).val(),
				'm' : $( '#inputMatch' ).val(),
				't' : $( '#inputTypes' ).val(),
				'u' : $( '#inputMultiline' ).val(),
				's' : '1',
			} ,
			dataType: 'json'
		} )
		.fail( function ( d , e , f ) {
			$( "#regexresult" ).html( f.message );
		})
		.done( function ( d ) {

			$( "#regexresult" ).html( d.msg );
			$( '.form-group' ).removeClass( 'has-error' );
			if ( d.err ) {
				$( '#GP' + d.err ).addClass( 'has-error' );
				$( '#' + d.err ).focus();
			}
		});

		event.preventDefault();
	});

	$(document).ready(function(){
		$('a.clipboard').zclip({
			path:'../js/ZeroClipboard.swf',
			copy:function(){
				var t = '{\n';
				t += '  "SOFTWARE_ID": {\n';
				t += '    "display"   : "SOFTWARE NAME",\n';
				t += '    "path"      : "SOFTWARE PATH",\n';
				t += '    "format"    : {\n';
				t += '      "multiline": ' + JSON.stringify($( '#inputMultiline' ).val()) + ',\n';
				t += '      "regex"    : ' + JSON.stringify($( '#inputRegEx' ).val()) + ',\n';
				t += '      "match"    : ' + $( '#inputMatch' ).val() + ',\n';
				t += '      "types"    : ' + $( '#inputTypes' ).val() + '\n';
				t += '    }\n';
				t += '  }\n';
				t += '}\n';
				return t;
			},
			afterCopy:function() {
				$( 'a.clipboard' ).popover( {
					html      : true ,
					animation : true ,
					placement : 'right',
					container : 'body',
					delay     : { show: 100, hide: 5000 },
					content   : "Configuration array has been copied to your clipboard!"
				} ).popover( 'show' );
				$('a.clipboard').on('hidden.bs.popover', function () {
					$( 'a.clipboard' ).show();
				});
			}
		});
	});

});
