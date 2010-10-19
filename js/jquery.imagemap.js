/*
 * jquery.imagemap.js 1.2
 * 
 * Copyright (c) 2010 Tanabicom, LLC
 * http://www.tanabi.com
 *
 * Released under the MIT license:
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/* USAGE
 *
 * $(selector).imagemap([...])
 *
 * You pass in an array of objects representing your image map.  E.g. :
 *
 * [
 *   {top_x: 0, top_y: 0, bottom_x: 10, bottom_y: 10, link: 'whatever.html'},
 *   {top_x: 0, top_y: 0, bottom_x: 10, bottom_y: 10, callback: function(ele){ ... }}
 * ]
 *
 * In the case of 'callback', first argument will be the DOM element and the
 * second argument will be the event.
 *
 * Also to make life easier (to define maps) you can use:
 *
 * $(selector).imagecoords();
 *
 * And it will let you click around and pop an alert box to get coordinates.
 *
 * ffGetPosition -- I found this on:
 * http://www.hiteshargrawal.com/javascript/calculating-div-position-in-javascript
 *
 * I take no credit for it :)
 */
jQuery.imagemap = {	inelement: false,
					ffGetPosition: 	function(obj) {
										var topValue = 0;
										var leftValue = 0;
										var follow = obj;
										
										while(follow){
											leftValue += follow.offsetLeft;
											topValue += follow.offsetTop;
											follow = follow.offsetParent;
										}
										
										obj.actualPosX = leftValue;
										obj.actualPosY = topValue;
									},
					getEventX:	function(ev){
									/*
									 * I got a report that sometime IE 8 passes
									 * the originalEvent as ev instead of passing
									 * the jQuery Event... this will fix that,
									 * hopefully!
									 */
									if(typeof ev.originalEvent == 'undefined'){
										var oe = ev;
									}else{
										var oe = ev.originalEvent;
									}
									
									if(typeof oe.offsetX != 'undefined'){
										return oe.offsetX;
									}
									
									if(typeof ev.originalTarget.actualPosX == 'undefined'){
										jQuery.imagemap.ffGetPosition(ev.originalTarget);
									}
									
									return ev.originalEvent.pageX - ev.originalTarget.actualPosX;
								},
					getEventY:	function(ev){
									/*
									 * I got a report that sometime IE 8 passes
									 * the originalEvent as ev instead of passing
									 * the jQuery Event... this will fix that,
									 * hopefully!
									 */
									if(typeof ev.originalEvent == 'undefined'){
										var oe = ev;
									}else{
										var oe = ev.originalEvent;
									}
									
									if(typeof oe.offsetY != 'undefined'){
										return oe.offsetY;
									}
									
									if(typeof ev.originalTarget.actualPosY == 'undefined'){
										jQuery.imagemap.ffGetPosition(ev.originalTarget);
									}
									
									return ev.originalEvent.pageY - ev.originalTarget.actualPosY;
								}
					};
jQuery.fn.imagemap = function(mapinfo) {
	return this.each(function(){
		jQuery(this).click(function(ev){
			var x = jQuery.imagemap.getEventX(ev);
			var y = jQuery.imagemap.getEventY(ev);
			
			for(var i = 0; i < mapinfo.length; i++){
				if((x >= mapinfo[i].top_x) &&
				   (y >= mapinfo[i].top_y) &&
				   (x <= mapinfo[i].bottom_x) &&
				   (y <= mapinfo[i].bottom_y)){
					if(typeof mapinfo[i]['link'] != 'undefined'){
						window.location = mapinfo[i]['link'];
					}else{
						mapinfo[i]['callback'](this,ev);
					}
					
					return;
				}
			}
		});
		
		jQuery(this).mousemove(function(ev){
			var x = jQuery.imagemap.getEventX(ev);
			var y = jQuery.imagemap.getEventY(ev);
			
			for(var i = 0; i < mapinfo.length; i++){
				if((x >= mapinfo[i].top_x) &&
				   (y >= mapinfo[i].top_y) &&
				   (x <= mapinfo[i].bottom_x) &&
				   (y <= mapinfo[i].bottom_y)){
					if(!jQuery.imagemap.inelement){
						$(this).css('cursor','pointer');
						jQuery.imagemap.inelement = true;
					}
					
					return;
				}
			}
			
			if(jQuery.imagemap.inelement){
				$(this).css('cursor','default');
				jQuery.imagemap.inelement = false;
			}
		});
	});
};
jQuery.fn.imagecoords = function() {
	return this.each(function(){
		jQuery(this).click(function(ev){
			var x = jQuery.imagemap.getEventX(ev);
			var y = jQuery.imagemap.getEventY(ev);
			
			alert('Clicked: ' + x + ',' + y);
		});
	});
};

