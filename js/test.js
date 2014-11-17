$(function() {
	"use strict";

	// Ctrl-R to Test a regex
	$(document).keypress( function(e) {
		if ( ( e.which == 114 ) || ( e.which == 18 ) ) {
			if ( e.ctrlKey ) {
				if ( document.location.hash === '#retestertab' ) {
					$('#regextest').submit();
				}
			}
		}
	});

	// Logo click
	$('.logo').click(function() {
		document.location.href = '..';
	});

	$('table').addClass('table table-striped table-bordered table-hover table-condensed');

	$('#regextest').submit( function( event ) {
		$('#regexTestertestBtn').button('loading');
		$.ajax( {
			url  : 'test.php' ,
			type : 'POST',
			data : {
				'r'      : $( '#inputRegEx' ).val(),
				'l'      : $( '#inputLog' ).val(),
				'm'      : $( '#inputMatch' ).val(),
				't'      : $( '#inputTypes' ).val(),
				'u'      : $( '#inputMultiline' ).val(),
				'action' : 'regextest'
			} ,
			dataType: 'json'
		} )
		.fail( function ( d , e , f ) {
			$('#regexTestertestBtn').button('reset');
			$( "#regexresult" ).html( f.message );
		})
		.done( function ( d ) {
			$('#regexTestertestBtn').button('reset');
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

		$('.clipboard').on( 'mouseover', function() {

	        var self = $(this);

	        //turn off this listening event for the element that triggered this
	        $(this).off('mouseover');

			//initialize zclip
			if ( $(this).data('source') ) {

				$(this).zclip({
					path:'../js/ZeroClipboard.swf',
					copy:function(){
						return $( $(this).data('source') ).text();
					},
					afterCopy:function() {
						$( self ).popover( {
							html      : true ,
							animation : true ,
							placement : $(this).data('placement'),
							delay     : { show: 100, hide: 5000 },
							content   : $(this).data('text'),
						} ).popover( 'show' );
						$( self ).on('hidden.bs.popover', function () {
							$( self ).show();
						});
					}
				});
			}

			else {

				$(this).zclip({
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
						$( self ).popover( {
							html      : true ,
							animation : true ,
							placement : 'right',
							delay     : { show: 100, hide: 5000 },
							content   : lemma.configuration_copied
						} ).popover( 'show' );
						$( self ).on('hidden.bs.popover', function () {
							$( self ).show();
						});
					}
				});

			}
		});

		$( '#authsave' ).submit(function( event ) {
			var username  = $('#username').val();
			var password  = $('#password').val();
			var password2 = $('#password2').val();
			var go        = true;

			$('#usernamegroup').removeClass('has-error').removeClass('has-success').tooltip('hide');
			$('#passwordgroup').removeClass('has-error').removeClass('has-success').tooltip('hide');
			$('#password2group').removeClass('has-error').removeClass('has-success').tooltip('hide');

			if ( username.length === 0 ) {
				$('#usernamegroup').addClass('has-error').tooltip('show');
				go = false;
			}
			if ( password.length < 6 ) {
				$('#passwordgroup').addClass('has-error').tooltip('show');
				go = false;
			}
			if ( password2 !== password ) {
				$('#password2group').addClass('has-error').tooltip('show');
				go = false;
			}
			if ( go === true ) {
				return;
			}
			event.preventDefault();
			return false;
		});

		$(function () {
			if ( location.hash ) {
				var activeTab = $('[href=' + location.hash + ']');
				activeTab && activeTab.tab('show');
			}
		});

		$('.nav-tabs a').click(function (e) {
		    $(this).tab('show');
		    var scrollmem = $('body').scrollTop();
		    window.location.hash = this.hash;
		    $('html,body').scrollTop(scrollmem);
		});
	});
});
