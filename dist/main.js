$( document ).ready( function () {
	$( function () {
		FastClick.attach( document.body );
	} );

	$.ajax({
		url: 'https://raw.github.com/potsky/PimpMyLog/master/version.json?callback=?',
		type: 'GET',
		dataType: 'jsonp',
		jsonp: 'version_cb'
	})
	.done(function() {})
	.fail(function() {})
	.always(function() {});


} );

var pml_version_cb = function( data ) {
	$('.pmlversion').html( 'v ' + data.version );
};
