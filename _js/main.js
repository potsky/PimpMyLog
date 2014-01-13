/* global FastClick */
/* jshint unused: false */

$( document ).ready( function () {
	"use strict";

	// To increase mobile click
	$( function () {
		FastClick.attach( document.body );
	} );

	// A page need informations about versions
	if ( $('.pmlversion').length > 0 ) {
		$.ajax({
// GitHub response is text/plain and it make IE9 crash on the jsonp request
//			url: 'https://raw.github.com/potsky/PimpMyLog/master/version.js?callback=?',
//			url: 'http://beta.pimpmylog.com/version.js?callback=?',
			url: 'http://demo.pimpmylog.com/version.js?callback=?',
			type: 'GET',
			dataType: 'jsonp',
			jsonp: 'version_cb'
		})
		.done(function() {})
		.fail(function() {})
		.always(function() {});
	}
} );


var pml_version_cb = function( data ) {
	"use strict";

	var types = {
		'fixed' : {
			'name'  : 'Fixed',
			'class' : 'success'
		},
		'new' : {
			'name'  : 'New',
			'class' : 'warning'
		},
		'changed' : {
			'name'  : 'Changed',
			'class' : 'info'
		}
	};

	// Only display version
	if ( $('.pmlversionctn').length > 0 ) {
		$('.pmlversionctn').html( 'v ' + data.version );
	}

	// Display the full change log
	if ( $('.pmlchangelog-full').length > 0 ) {
		var changelog = '';
		for ( var version in data.changelog ) {
			changelog += '<div class="panel panel-default">';
			changelog += '  <div class="panel-heading">';
			changelog += '    <h3 class="panel-title">';
			changelog += '      <strong>Version ' + version + '</strong>';
			changelog += ( data.changelog[version].released !== undefined ) ? ' - released on ' + data.changelog[version].released : '';
			changelog += '    </h3>';
			changelog += '  </div>';
			changelog += '  <div class="panel-body">';
			if ( data.changelog[version].notice !== undefined ) {
				changelog += '<div class="alert alert-warning">' + data.changelog[version].notice + "</div><br/>";
			}
			for ( var type in types ) {
				if ( data.changelog[version][type] !== undefined ) {
					for ( var item in data.changelog[version][type] ) {
						var text = data.changelog[version][type][item].replace( /#([0-9]*)/g , '<a href="https://github.com/potsky/PimpMyLog/issues/$1" target="ghissue">#$1</a>' );
						changelog += '<div class="row"><div class="col-sm-2"><span class="label label-' + types[type].class + '">' + types[type].name + '</span></div><div class="col-sm-10">' + text + '</div></div>';
					}
				}
			}
			changelog += '  </div>';
			changelog += '</div>';
			changelog += '<br/>';
		}
		$('.pmlchangelog-full').html( changelog );
	}

	// Display the change log for the selected versions
	if ( $('.pmlchangelog').length > 0 ) {
		$('.pmlchangelog').each( function() {
			var version   = $(this).data('version');
			var changelog = '';
			if ( data.changelog[version] === undefined ) {
				changelog += '<div class="alert alert-danger">Version ' + version + ' does not exist in the <code>master</code> branch!</div>';
			}
			else {
				if ( data.changelog[version].notice !== undefined ) {
					changelog += '<div class="alert alert-warning">' + data.changelog[version].notice + "</div>";
				}
				for ( var type in types ) {
					if ( data.changelog[version][type] !== undefined ) {
						for ( var item in data.changelog[version][type] ) {
							text = data.changelog[version][type][item].replace( /#([0-9]*)/g , '<a href="https://github.com/potsky/PimpMyLog/issues/$1" target="ghissue">#$1</a>' );
							changelog += '<div class="row"><div class="col-sm-2"><span class="label label-' + types[type].class + '">' + types[type].name + '</span></div><div class="col-sm-10">' + text + '</div></div>';
						}
					}
				}
			}
			$(this).html( changelog );
		});
	}
};
