
/**
 * Set the btn with the provided value
 * If the value is not set, it will simply ajust inner variables
 *
 * @param  {string}  a  the value of the wanted selected option
 */
var notification_class = 'warning';
var set_notification   = function( a ) {
	"use strict";
	if ( a === undefined ) {
		a = notification;
	}
	if ( a === true ) {
		$('#notification').removeClass('btn-warning btn-success btn-danger btn-default').addClass('active btn-' + notification_class );
		notification = true;
	} else {
		$('#notification').removeClass('btn-warning btn-success btn-danger active').addClass('btn-default' );
		notification = false;
	}
};


/**
 * Return if notification is set or not
 *
 * @return  {Boolean}
 */
var is_notification = function() {
	"use strict";
	return $('#notification').hasClass('active');
};


/**
 * Just display a notification on the desktop
 *
 * @param   {string}  title    the title
 * @param   {string}  message  the message
 *
 * @return  {void}
 */
var notify = function ( title , message ) {
	"use strict";
	if ( 'webkitNotifications' in window ) {
		var havePermission = window.webkitNotifications.checkPermission();
		if ( havePermission === 0 ) {
			notification_class = 'success';
			set_notification();
			if ( ( notification === true ) && ( title !== undefined ) && ( notification_displayed === false ) ) {
				notification_displayed = true;
				var noti = window.webkitNotifications.createNotification(
					'img/icon72.png', title , message
				);
				noti.onclick = function () {
					window.focus();
					noti.close();
				};
				noti.onclose = function() {
					notification_displayed = false;
				};
				noti.show();
				setTimeout(	function(){try {noti.close();}catch(e){}} , 5000 );
			}
		}
		else if ( havePermission === 2 ) {
			notification_class = 'danger';
			set_notification();
		}
		else {
			notification_class = 'warning';
			set_notification();

			window.webkitNotifications.requestPermission(function() {
				notify( title , message );
			});
		}
	}
	else if ( 'Notification' in window ) {
		if ( window.Notification.permission === 'default') {
			notification_class = 'warning';
			set_notification();

			window.Notification.requestPermission(function () {
				notify( title , message );
			});
		}
		else if ( window.Notification.permission === 'granted') {
			notification_class = 'success';
			set_notification();
			if ( ( notification === true ) && ( title !== undefined ) && ( notification_displayed === false ) ) {
				notification_displayed = true;
				var n = new window.Notification( title , { 'body': message , 'tag' : 'Pimp My Log' } );
				n.onclick = function () {
					this.close();
				};
				n.onclose = function () {
					notification_displayed = false;
				};
			}
		}
		else if ( window.Notification.permission === 'denied') {
			notification_class = 'danger';
			set_notification();
			return;
		}
	}
};
