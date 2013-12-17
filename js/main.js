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

var version_cb = function( data ) {
	console.log( data );
};

window.addEventListener( "load" , function () {
	setTimeout( function () {
		window.scrollTo( 0 , 1 );
	} , 0 );
} );

