/**
 * Permform an ajax action with object parameter and call the success callback
 *
 * @param   {object}    object  some POST values
 * @param   {function}  done    the success fallback
 *
 * @return  {void}
 */
var pml_action = function ( object , done ) {
	progressbar_active();
	$.ajax( {
		url      : 'configure.php?' + new Date().getTime() ,
		data     : object,
		type     : 'POST',
		dataType : 'json'
	} )
	.fail( function ( a , b, c ) {
		progressbar_color( 'danger' );
		progressbar_set( 100 );
		$('<div class="alert alert-danger fade in">' + c.message + '</div>').appendTo("#error");
	})
	.done( function ( data ) {
		if ( data.error ) {
			progressbar_color( 'danger' );
			progressbar_set( 100 );
			$('<div class="alert alert-danger fade in">' + data.error + '</div>').appendTo("#error");
		} else {
			done( data );
		}
	})
	.always( function ( data ) {
		if ( data.reload ) {
			$("#reload").show();
		} else {
			$("#reload").hide();
		}
		if ( data.next ) {
			$("#next").show();
		} else {
			$("#next").hide().unbind('click');
		}
		progressbar_deactive();
	});
};


/**
 * Activate the striped effect of the progressbar
 *
 * @return  {void}
 */
var progressbar_active = function() {
	$('.progress').addClass('progress-striped active');
};


/**
 * Deactivate the striped effect of the progressbar
 *
 * @return  {void}
 */
var progressbar_deactive = function() {
	$('.progress').removeClass('progress-striped active');
};


/**
 * Set the progress bar at the specified value
 *
 * @param   {integer}  value  the value between 0 and 100
 *
 * @return  {void}
 */
var progressbar_set = function( value ) {
	var v = Math.max( 0 , Math.min( 100 , parseInt( value , 10 ) ) );
	$('.progress .sr-only').text( lemma.complete.replace( '%s' , v ) );
	$('.progress-bar').prop( 'aria-valuenow' , v ).width( v + '%' );
};


/**
 * Change the progressbar color
 *
 * @param   {string}  value  a bootstrap class or null for default color
 *
 * @return  {void}
 */
var progressbar_color = function( value ) {
	$('.progress-bar').removeClass('progress-bar-success progress-bar-info progress-bar-warning progress-bar-success progress-bar-danger');
	if ( value !== undefined ) {
		$('.progress-bar').addClass( 'progress-bar-' + value );
	}
};


$(function() {

	///////////////////////////////////////////
	// Check if config.inc.php already exist //
	///////////////////////////////////////////
	progressbar_set(10);
	pml_action( { s : 'exist' } , function( data ) {

		///////////////////////////////////
		// Check if we can write at root //
		///////////////////////////////////
		progressbar_set(20);
		pml_action( { s : 'touch' } , function( data ) {

			/////////////////////////
			// Cannot write a root //
			/////////////////////////
			if ( data.notice ) {
				$( '#user' ).html( data.notice );
				progressbar_color( 'warning' );
			}

			///////////////////
			// Find logs now //
			///////////////////
			else {
				$( '#user' ).html( lemma.pleasewait );
				progressbar_set( 50 );
				pml_action( { s : 'find' } , function( data ) {

					/////////////////////////////////////////
					// Let user choose which logs he wants //
					/////////////////////////////////////////
					$( '#user' ).html( data.notice );

					if ( data.found === 0 ) {
						progressbar_color( 'danger' );
					}
					else if ( data.found == 1 ) {
						progressbar_color( 'warning' );
					}
					else {
						progressbar_color( );
					}


					$( '#find' )
						.addClass( 'table table-striped table-bordered table-hover table-condensed' )
						.append('<thead><tr><th> </th><th>' + lemma.software + '</th><th>' + lemma.path + '</th><th>' + lemma.file + '</th><th>' + lemma.type + '</th><th>' + lemma.readable + '</th></tr></thead><tbody></tbody>');

					for ( var software in data.files ) {
						for ( var path in data.files[ software ] ) {
							if ( data.files[ software ][ path ] === true ) {
								$( '#find tbody' ).append('<tr><td></td><td>' + software + '</td><td>' + path + '</td><td></td><td></td><td><span class="label label-success">' + lemma.yes + '</span></td></tr>');
							}
							else if ( data.files[ software ][ path ] === false ) {
								$( '#find tbody' ).append('<tr><td></td><td>' + software + '</td><td>' + path + '</td><td></td><td></td><td><span class="label label-danger">' + lemma.no + '</span></td></tr>');
							}
							else {
								for ( var type in data.files[ software ][ path ] ) {
									for ( var f in data.files[ software ][ path ][ type ] ) {
										var file = data.files[ software ][ path ][ type ][ f ];
										$( '<tr>' )
											.data( 'file' , { s:software , p:path , t:type , f:file } )
											.html( '<td><input type="checkbox"></td><td>' + software + '</td><td>' + path + '</td><td>' + file + '</td><td>' + type + '</td><td><span class="label label-success">' + lemma.yes + '</span></td>' )
											.appendTo( '#find tbody' );
									}
								}
							}
						}
					}

					$( '#find tbody tr').click( function() {
						var cb = $( this ).find( 'input[type="checkbox"]' );
						if ( $(cb) ) {
							var state = !$( cb ).prop('checked');
							$( cb ).prop( 'checked' , state );
							if ( state ) {
								$(this).addClass( 'success' );
							} else {
								$(this).removeClass( 'success' );
							}
						}
					});

					$( '#find tbody tr input[type="checkbox"]').each(function() {
						$(this).prop( 'checked' , true );
						$(this).parents('tr').addClass( 'success' );
					});

					$( '#next' ).unbind('click').click(function() {
						alert('Hopla on continue !');
					});



				});

			}

		});

	});

});
