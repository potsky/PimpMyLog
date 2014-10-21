/**
 * Initialization
 *
 * @return  {void}
 */
$(function() {
	"use strict";


	// Change password - reset modal
	//
	$('#cpModal').on('show.bs.modal', function (e) {
		$('#cpErr').hide();
		$('#password1group').removeClass('has-error');
		$('#password2group').removeClass('has-error');
		$('#password3group').removeClass('has-error');
		$('#cpSave').button('reset');
		$('#password1').val('');
		$('#password2').val('');
		$('#password3').val('');
	});


	// Change password - on submit
	//
	$('#changepassword').on('submit', function () {

		$('#password1group').removeClass('has-error');
		$('#password2group').removeClass('has-error');
		$('#password3group').removeClass('has-error');
		$('#cpErr').hide();
		$('#cpSave').button('loading');

		$('#cpErr').hide();

		$.ajax( {
			url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
			type     : 'POST',
			dataType : 'json',
			data     : {
				'password1'  : $('#password1').val(),
				'password2'  : $('#password2').val(),
				'password3'  : $('#password3').val(),
				'csrf_token' : csrf_token,
				'action'     : 'change_password',
			}
		} )
		.always( function() {
			$('#cpSave').button('reset');
		})
		.fail( function ( e ) {
			$('#cpErrM').html( e.responseText ).show();
			$('#cpErr').show();
		})
		.done( function ( r ) {
			if ( r.ok ) {
				$('#notice').html('<div class="alert alert-success" role="alert">
					<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					' + r.ok + '</div>');
				$('#cpModal').modal('hide');
			}
			else if ( r.errors ) {
				$( '#cpErrUl').html('');
				for ( var a in r.errors ) {
					$( '#cpErrUl').append( '<li>' + r.errors[a] + '</li>' );
				}
				for ( var a in r.fields ) {
					$( '#' + r.fields[a] + 'group' ).addClass('has-error');
				}
				$('#cpErr').show();
			}
		});

		event.preventDefault();
		return false;
	});
});

