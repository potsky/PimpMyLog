/*
*  JAIL: jQuery Asynchronous Image Loader
*
* Copyright (c) 2011-12 Sebastiano Armeli-Battana (http://www.sebastianoarmelibattana.com)
*
* By Sebastiano Armeli-Battana (@sebarmeli)
* Licensed under the MIT license.
* https://github.com/sebarmeli/JAIL/blob/master/MIT-LICENSE.txt
*
* Tested with jQuery 1.3.2+ on FF 2+, Opera 10+, Safari 4+, Chrome 8+ on Win/Mac/Linux
* and IE 6/7/8 on Win.
*
* Contributor : Derek Lindahl - @dlindahl
*
* @link http://github.com/sebarmeli/JAIL
* @author Sebastiano Armeli-Battana
* @date 14/10/2012
* @version 1.0.0
*
*/
;(function ( name, definition ){

	var theModule = definition(jQuery),
		hasDefine = typeof define === 'function' && define.amd;

	if ( hasDefine ){  // AMD module

		define( name , ['jquery'], theModule );
		
	}  else { // assign 'jail' to global objects
		
		( this.jQuery || this.$ || this )[name] = theModule;

	}
}( 'jail', function ($) {

	var $window = $( window ),
		
		// Defaults parameters
		defaults = {
			id : 'jail',
			timeout : 1,
			effect : false,
			speed : 400,
			triggerElement: null,
			offset : 0,
			event : 'load',
			callback : null,
			callbackAfterEachImage : null,
			placeholder : false,
			loadHiddenImages : false
		},
		
		// current stack of images
		currentStack = [],
		
		// true if 'callback' fn is called
		isCallbackDone = false;
		
	/* 
	* Public function defining 'jail'
	*
	* @module jail
	* @param elems : images to load - jQuery elements
	* @param opts : configurations object
	*/
	$.jail = function( elems, opts ) {

		var elements = elems || {},
			options = $.extend( {}, defaults, opts );
	
		// Initialize plugin
		$.jail.prototype.init( elements, options );
		
		// When the event is not specified the images will be loaded with a delay
		if(/^(load|scroll)/.test( options.event )) {
			// 'load' event
			$.jail.prototype.later.call( elements, options );
		} else {
			$.jail.prototype.onEvent.call( elements, options );
		}
	};
	
	/* 
	* Method in charge of initializing the plugin, storing
	* the 'element triggering the image to load' in a data attribute 
	* for each image and displaying the placeholder image (if existing)
	*
	* @method init
	* @param {Array} elems Images to load - jQuery elements
	* @param {Object} opts Configurations object
	*/
	$.jail.prototype.init = function( elements, options ) {
		
		// Store the selector triggering jail into 'triggerElem' data for the images selected 
		elements.data("triggerElem", ( options.triggerElement ) ? $( options.triggerElement ) : $window );
		
		// Use a placeholder in case it is specified
		if ( !!options.placeholder ) {
			elements.each(function(){
				$(this).attr( "src", options.placeholder );
			});
		}
	};
	
		
	/* 
	* Function called when 'event' is different from "load" or "scroll". Two scenarios:
	* a) Element triggering the images to be loaded (events available on the element: "click", "mouseover", "scroll")
	* b) Event on the image itself triggering the image to be loaded
	*
	* @param options : configurations object
	*/
	$.jail.prototype.onEvent = function( options ) {
		var images = this;
		
		if (!!options.triggerElement) {
			
			// Event on the 'triggerElement' obj
			_bindEvent( options, images );

		} else {
			
			// Event on the image itself
			images.on( options.event + '.' + options.id, {options: options, images: images}, function(e) {
				var $img = $(this),
					options = e.data.options,
					images = e.data.images;
				
				currentStack = $.extend( {}, images );
				
				// Load the image
				_loadImage( options, $img );

				// Image has been loaded so there is no need to listen anymore
				$(e.currentTarget).unbind( e.type + '.' + options.id );
			});
		}
	};
	
	/* 
	* Method called when "event" is equals to "load" (default) or "scroll". The visible images will be 
	* loaded after a specified timeout (or after 1 ms). The scroll method will be bound to the window
	* to load  the images not visible onload.
	*
	* @param options : configurations object
	*/
	$.jail.prototype.later = function( options ) {
		var images = this;

		// After [timeout] has elapsed, load the visible images
		setTimeout(function() {
			
			currentStack = $.extend( {}, images );
			
			//Load the visible ones
			images.each(function(){
				_loadImageIfVisible( options, this, images );
			});
			
			// When images become available (scrolling or resizing), they will be loaded 
			options.event = "scroll";
			_bindEvent( options, images );
			
		}, options.timeout);
	};
		
	/* 
	* Bind _bufferedEventListener() to the event on window/triggerElement. The handler is bound to 
	* resizing the window as well
	*
	* @param options : configurations object
	* @param images : images in the current stack
	*/
	function _bindEvent ( options, images ) {
		var triggerElem = false;

		if (!!images) {
			triggerElem = images.data("triggerElem");
		}
		
		// Check if there are images to load
		if (!!triggerElem && typeof triggerElem.on === "function") {
			triggerElem.on( options.event + '.' + options.id, {options:options, images : images}, _bufferedEventListener );
			$window.on( 'resize.'+options.id, {options:options, images : images}, _bufferedEventListener );
		}
	}

	/* 
	* Remove any elements that have been loaded from the jQuery stack.
	* This should speed up subsequent calls by not having to iterate over the loaded elements.
	*
	* @param stack : current images stack
	*/
	function _purgeStack ( stack ) {
		// number of images not loaded
		var i = 0;

		if (stack.length === 0) { return; }

		// Check on existence of 'data-src' attribute to verify if the image has been loaded
		while(true) {
			if(i ===  stack.length) {
				break;
			} else {
				if ($(stack[i]).attr('data-src')) {
					i++;
				} else {
					stack.splice( i, 1 );
				}
			}
		}
	}

	/* 
	* Event handler for the images to be loaded. Function called when 
	* there is a triggerElement or when there are images to be loaded after scrolling 
	* or resizing window/container 
	*
	* @param e : event
	*/
	function _bufferedEventListener (e) {
		var images = e.data.images,
			options = e.data.options;

		images.data('poller', setTimeout(function() {
			
			currentStack = $.extend( {}, images );
			_purgeStack(currentStack);
			
			// Load only the images left
			$(currentStack).each(function (){
				if (this === window) {
					return;
				}
				_loadImageIfVisible(options, this, currentStack);
			});
			
			//Unbind when there are no images
			if ( _isAllImagesLoaded (currentStack) ) {
				$(e.currentTarget).unbind( e.type + '.' + options.id );
				return;
			} 
			// When images are not in the viewport, let's load them when they become available
			else if (options.event !== "scroll"){
			
				// When images become available (scrolling or resizing), they will be loaded 
				var container = (/scroll/i.test(options.event)) ? images.data("triggerElem") : $window;
			
				options.event = "scroll";
				images.data("triggerElem", container);
				_bindEvent( options, $(currentStack) );
			}
		}, options.timeout));
	}
	
	/* 
	* Check if all the images are loaded
	*
	* @param images : images under analysis
	* @return boolean
	*/
	function _isAllImagesLoaded ( images ) {
		var bool = true;
		
		$(images).each(function(){
			if ( !!$(this).attr("data-src") ) {
				bool = false;
			}
		});
		return bool;
	}

	/* 
	* Load the image if visible in the viewport
	*
	* @param options : configurations object
	* @param image : image under analysis
	* @param images : list of images to load
	*/
	function _loadImageIfVisible ( options, image, images ) {
		var $img = $(image),
			container = (/scroll/i.test(options.event)) ? images.data("triggerElem") : $window,
			isVisible = true;

		// If don't you want to load hidden images (default beahviour)
		if ( !options.loadHiddenImages ) {
			isVisible = _isVisibleInContainer( $img, container, options ) && $img.is(":visible");
		}
		
		// Load the image if it is not hidden and visible in the screen
		if( isVisible && _isInTheScreen( container, $img, options.offset ) ) {
			_loadImage( options, $img );
		}
	}
	
	/* 
	* Function that returns true if the image is visible inside the "window" (or specified container element)
	*
	* @param $ct : container - jQuery obj
	* @param $img : image selected - jQuery obj
	* @param optionOffset : offset
	*/
	function _isInTheScreen ( $ct, $img, optionOffset ) {
		var is_ct_window  = $ct[0] === window,
			ct_offset  = (is_ct_window ? { top:0, left:0 } : $ct.offset()),
			ct_top     = ct_offset.top + ( is_ct_window ? $ct.scrollTop() : 0),
			ct_left    = ct_offset.left + ( is_ct_window ? $ct.scrollLeft() : 0),
			ct_right   = ct_left + $ct.width(),
			ct_bottom  = ct_top + $ct.height(),
			img_offset = $img.offset(),
			img_width = $img.width(),
			img_height = $img.height();
		
		return (ct_top - optionOffset) <= (img_offset.top + img_height) &&
			(ct_bottom + optionOffset) >= img_offset.top &&
				(ct_left - optionOffset)<= (img_offset.left + img_width) &&
					(ct_right + optionOffset) >= img_offset.left;
	}

	/* 
	* Main function --> Load the images copying the "data-href" attribute into the "src" attribute
	*
	* @param options : configurations object
	* @param $img : image selected - jQuery obj
	*/
	function _loadImage ( options, $img ) {
		
		// Use cache Image object to show images only when ready
		var cache = new Image();

		cache.onload = function() {
			$img.hide().attr("src", cache.src);
			
			$img.removeAttr('data-src');
			// Images loaded with some effect if existing
			if( options.effect) {

				if ( options.speed ) {
					$img[options.effect](options.speed);
				} else {
					$img[options.effect]();
				}
				$img.css("opacity", 1);
				$img.show();
			} else {
				$img.show();
			}
		
			_purgeStack(currentStack);
		
			// Callback after each image is loaded
			if ( !!options.callbackAfterEachImage ) {
				options.callbackAfterEachImage.call( this, $img, options );
			}
			
			if ( _isAllImagesLoaded (currentStack) && !!options.callback && !isCallbackDone ) {
				options.callback.call($.jail, options);
				isCallbackDone = true;
			}
		};

		/*
		 * Basic on error handler. For now we limit
		 * the plugin to exectute any callback passed
		 * in the options object. Let the developer
		 * decide what to do with it.
		 */
		cache.onerror = function(){
			
			if(!('error' in options)) return;

			var args = Array.prototype.slice.call(arguments, 0);
			args = [$img, options].concat(args);
			options.error.apply($.jail, args);
		};

		cache.src = $img.attr("data-src");
	}
		
	/* 
	* Return if the image is visible inside a "container" / window. There are checks around
	* "visibility" CSS property and around "overflow" property of the "container"
	*
	* @param $img : image selected - jQuery obj
	* @param container : container object
	* @param options : configurations object
	*/
	function _isVisibleInContainer ( $img, container, options ){

		var parent = $img.parent(),
			isVisible = true;

		while ( parent.length && parent.get(0).nodeName.toUpperCase() !== "BODY" ) {
			// Consider the 'overflow' property
			if ( parent.css("overflow") === "hidden" ) {
				if (!_isInTheScreen(parent, $img, options.offset)) {
					isVisible = false;
					break;
				}
			} else if ( parent.css("overflow") === "scroll" ) {
				if (!_isInTheScreen(parent, $img, options.offset)) {
					isVisible = false;
					$(currentStack).data("triggerElem", parent);
					
					options.event = "scroll";
					_bindEvent(options, $(currentStack));
					break;
				}
			}
			
			if ( parent.css("visibility") === "hidden" || $img.css("visibility") === "hidden" ) {
				isVisible = false;
				break;
			}
			
			// If container is not the window, and the parent is the container, exit from the loop
			if ( container !== $window && parent === container ) {
				break;
			}
			
			parent = parent.parent();
		}
		
		return isVisible;
	}

	// Small wrapper
	$.fn.jail = function( options ) {

		new $.jail( this, options );
		
		// Empty current stack
		currentStack = [];
		
		return this;
	};
	
	return $.jail;
}));
