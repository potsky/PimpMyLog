$(function() {
	$('table').addClass('table table-striped table-bordered table-hover table-condensed');

	$('#regextest').submit( function( event ) {

		$.ajax( {
			url  : 'test.REMOVE_UPPERCASE.php' ,
			type : 'POST',
			data : {
				'r' : $( '#inputRegEx' ).val(),
				'l' : $( '#inputLog' ).val(),
				'm' : $( '#inputMatch' ).val(),
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

});
