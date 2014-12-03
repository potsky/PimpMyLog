/**
 * Initialization
 *
 * @return  {void}
 */
$(function() {
	"use strict";

	$('#changeLogModal').on('show.bs.modal', function (e) {
		$.ajax({
			url      : 'version.js?local=true&callback=?',
			type     : 'GET',
			dataType : 'jsonp',
			jsonp    : 'pml_version_cb'
		})
		.done(function() {})
		.fail(function() {})
		.always(function() {});
	});

});


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


	var changelog = '<div class="alert alert-info"><a href="https://github.com/potsky/PimpMyLog" class="alert-link" target="_blank">Star me</a> on Github if you <span class="glyphicon glyphicon-heart"></span> me!</div>';
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
					var text = data.changelog[version][type][item].replace( /#([0-9]+)/g , '<a href="https://github.com/potsky/PimpMyLog/issues/$1" target="ghissue">#$1</a>' );
					changelog += '<div class="row" style="margin-bottom:2px;"><div class="col-sm-2" ><span class="label label-' + types[type].class + '">' + types[type].name + '</span></div><div class="col-sm-10">' + text + '</div></div>';
				}
			}
		}
		changelog += '  </div>';
		changelog += '</div>';
		changelog += '<br/>';
	}

	changelog += 'Congrats, you have read the full change log. <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">Here is a <span class="glyphicon glyphicon-gift"></span></a> for you!';

	$('#changeLogModal .modal-body').html( changelog );
};
