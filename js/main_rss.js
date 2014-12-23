
/**
 * Refresh button in the RSS exModal
 *
 * @return  {void}
 */
var refresh_rss = function() {
	$('#exModalRefresh').button('loading');
	$.ajax({
		url      : $("#exModalUrl").text(),
		dataType : 'text',
		success  : function( a ) {
			$('#exModalRefresh').button('reset');
			$("#exModalCtn").text( a );
		}
	});
};

/**
 * Download the EXPORT feed
 *
 * @return  {boolean}  false
 */
var get_rss = function( format ) {
	$('#exModalResultLoading').show();
	$('#exModalResult').hide();
	$('#exModalRefresh').button('loading');

	$.ajax( {
		url      : 'inc/rss.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : {
			'csrf_token' : csrf_token,
			'action'     : 'get_rss_link',
			'file'       : file,
			'search'     : $('#search').val(),
			'format'     : format
		}
	} )
	.always( function() {
	})
	.fail( function ( e ) {
		$('#prBody').html( get_alert( 'danger' , c.message + '<hr/>' + e.responseText , false ) );
	})
	.done( function ( re ) {
		if ( re.singlewarning ) {
			pml_singlealert( re.singlewarning , 'warning' );
		}
		else if ( re.singlenotice ) {
			pml_singlealert( re.singlenotice , 'info' );
		}
		else if ( re.error ) {
			pml_singlealert( re.error , 'danger' );
		}
		else {
			// Force the file download
			if ( re.met === 'if' ) {
	  			document.body.innerHTML += "<iframe src='" + re.url + "' style='display: none;' ></iframe>";
			}
			// Open in a new window
			else {

				// No result preview
				if ( re.met === 'nd' ) {
					$('#exModalResultLoading').hide();
					$('#exModalResult').hide();
					$('#exModalRefresh').button('reset');
				}
				else {
					// Load the preview
					$.ajax({
						url      : re.url,
						dataType : 'text',
						success  : function( a ) {
							$("#exModalCtn").text( a );

							$('#exModalResultLoading').hide();
							$('#exModalResult').show();
							$('#exModalRefresh').button('reset');

						}
					});
				}

				// Title name
				$("#exModalFormat").text( format );

				// Display the URL
				$("#exModalUrl").text( re.url );

				// Local address warning
				if ( re.war === false ) {
					$("#exModalWar").hide();
				} else {
					$("#exModalWar").show();
				}

				// Active the link
				$("#exModalOpen").attr('href',re.url);

				// Show the modal
				$("#exModal").modal('show');

			}

		}
	});
	return false;
};

