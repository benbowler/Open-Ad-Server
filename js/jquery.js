/*===========================================jquery.dimensions.js===============================================================*/

(function($){
	
$.dimensions = {
	version: '1.2'
};

// Create innerHeight, innerWidth, outerHeight and outerWidth methods
$.each( [ 'Height', 'Width' ], function(i, name){
	
	// innerHeight and innerWidth
	$.fn[ 'inner' + name ] = function() {
		if (!this[0]) return;
		
		var torl = name == 'Height' ? 'Top'    : 'Left',  // top or left
		    borr = name == 'Height' ? 'Bottom' : 'Right'; // bottom or right
		
		return this.is(':visible') ? this[0]['client' + name] : num( this, name.toLowerCase() ) + num(this, 'padding' + torl) + num(this, 'padding' + borr);
	};
	
	// outerHeight and outerWidth
	$.fn[ 'outer' + name ] = function(options) {
		if (!this[0]) return;
		
		var torl = name == 'Height' ? 'Top'    : 'Left',  // top or left
		    borr = name == 'Height' ? 'Bottom' : 'Right'; // bottom or right
		
		options = $.extend({ margin: false }, options || {});
		
		var val = this.is(':visible') ? 
				this[0]['offset' + name] : 
				num( this, name.toLowerCase() )
					+ num(this, 'border' + torl + 'Width') + num(this, 'border' + borr + 'Width')
					+ num(this, 'padding' + torl) + num(this, 'padding' + borr);
		
		return val + (options.margin ? (num(this, 'margin' + torl) + num(this, 'margin' + borr)) : 0);
	};
});

// Create scrollLeft and scrollTop methods
$.each( ['Left', 'Top'], function(i, name) {
	$.fn[ 'scroll' + name ] = function(val) {
		if (!this[0]) return;
		
		return val != undefined ?
		
			// Set the scroll offset
			this.each(function() {
				this == window || this == document ?
					window.scrollTo( 
						name == 'Left' ? val : $(window)[ 'scrollLeft' ](),
						name == 'Top'  ? val : $(window)[ 'scrollTop'  ]()
					) :
					this[ 'scroll' + name ] = val;
			}) :
			
			// Return the scroll offset
			this[0] == window || this[0] == document ?
				self[ (name == 'Left' ? 'pageXOffset' : 'pageYOffset') ] ||
					$.boxModel && document.documentElement[ 'scroll' + name ] ||
					document.body[ 'scroll' + name ] :
				this[0][ 'scroll' + name ];
	};
});

$.fn.extend({
	position: function() {
		var left = 0, top = 0, elem = this[0], offset, parentOffset, offsetParent, results;
		
		if (elem) {
			// Get *real* offsetParent
			offsetParent = this.offsetParent();
			
			// Get correct offsets
			offset       = this.offset();
			parentOffset = offsetParent.offset();
			
			// Subtract element margins
			offset.top  -= num(elem, 'marginTop');
			offset.left -= num(elem, 'marginLeft');
			
			// Add offsetParent borders
			parentOffset.top  += num(offsetParent, 'borderTopWidth');
			parentOffset.left += num(offsetParent, 'borderLeftWidth');
			
			// Subtract the two offsets
			results = {
				top:  offset.top  - parentOffset.top,
				left: offset.left - parentOffset.left
			};
		}
		
		return results;
	},
	
	offsetParent: function() {
		var offsetParent = this[0].offsetParent;
		while ( offsetParent && (!/^body|html$/i.test(offsetParent.tagName) && $.css(offsetParent, 'position') == 'static') )
			offsetParent = offsetParent.offsetParent;
		return $(offsetParent);
	}
});

function num(el, prop) {
	return parseInt($.curCSS(el.jquery?el[0]:el,prop,true))||0;
};

})(jQuery);

/*===========================================jquery.selectboxes.min.js===================================================*/


(function($){$.fn.addOption=function(){var e=function(a,v,t,b){var c=document.createElement("option");c.value=v,c.text=t;var o=a.options;var d=o.length;if(!a.cache){a.cache={};for(var i=0;i<d;i++){a.cache[o[i].value]=i}}if(typeof a.cache[v]=="undefined")a.cache[v]=d;a.options[a.cache[v]]=c;if(b){c.selected=true}};var a=arguments;if(a.length==0)return this;var f=true;var m=false;var g,v,t;if(typeof(a[0])=="object"){m=true;g=a[0]}if(a.length>=2){if(typeof(a[1])=="boolean")f=a[1];else if(typeof(a[2])=="boolean")f=a[2];if(!m){v=a[0];t=a[1]}}this.each(function(){if(this.nodeName.toLowerCase()!="select")return;if(m){for(var a in g){e(this,a,g[a],f)}}else{e(this,v,t,f)}});return this};$.fn.ajaxAddOption=function(b,c,d,e,f){if(typeof(b)!="string")return this;if(typeof(c)!="object")c={};if(typeof(d)!="boolean")d=true;this.each(function(){var a=this;$.getJSON(b,c,function(r){$(a).addOption(r,d);if(typeof e=="function"){if(typeof f=="object"){e.apply(a,f)}else{e.call(a)}}})});return this};$.fn.removeOption=function(){var a=arguments;if(a.length==0)return this;var d=typeof(a[0]);var v,index;if(d=="string"||d=="object"||d=="function"){v=a[0];if(v.constructor==Array){var l=v.length;for(var i=0;i<l;i++){this.removeOption(v[i],a[1])}return this}}else if(d=="number")index=a[0];else return this;this.each(function(){if(this.nodeName.toLowerCase()!="select")return;if(this.cache)this.cache=null;var b=false;var o=this.options;if(!!v){var c=o.length;for(var i=c-1;i>=0;i--){if(v.constructor==RegExp){if(o[i].value.match(v)){b=true}}else if(o[i].value==v){b=true}if(b&&a[1]===true)b=o[i].selected;if(b){o[i]=null}b=false}}else{if(a[1]===true){b=o[index].selected}else{b=true}if(b){this.remove(index)}}});return this};$.fn.sortOptions=function(f){var a=typeof(f)=="undefined"?true:!!f;this.each(function(){if(this.nodeName.toLowerCase()!="select")return;var o=this.options;var d=o.length;var e=[];for(var i=0;i<d;i++){e[i]={v:o[i].value,t:o[i].text}}e.sort(function(b,c){o1t=b.t.toLowerCase(),o2t=c.t.toLowerCase();if(o1t==o2t)return 0;if(a){return o1t<o2t?-1:1}else{return o1t>o2t?-1:1}});for(var i=0;i<d;i++){o[i].text=e[i].t;o[i].value=e[i].v}});return this};$.fn.selectOptions=function(b,d){var v=b;var e=typeof(b);var c=d||false;if(e!="string"&&e!="function"&&e!="object")return this;this.each(function(){if(this.nodeName.toLowerCase()!="select")return this;var o=this.options;var a=o.length;for(var i=0;i<a;i++){if(v.constructor==RegExp){if(o[i].value.match(v)){o[i].selected=true}else if(c){o[i].selected=false}}else{if(o[i].value==v){o[i].selected=true}else if(c){o[i].selected=false}}}});return this};$.fn.copyOptions=function(b,c){var w=c||"selected";if($(b).size()==0)return this;this.each(function(){if(this.nodeName.toLowerCase()!="select")return this;var o=this.options;var a=o.length;for(var i=0;i<a;i++){if(w=="all"||(w=="selected"&&o[i].selected)){$(b).addOption(o[i].value,o[i].text)}}});return this};$.fn.containsOption=function(b,c){var d=false;var v=b;var e=typeof(v);var f=typeof(c);if(e!="string"&&e!="function"&&e!="object")return f=="function"?this:d;this.each(function(){if(this.nodeName.toLowerCase()!="select")return this;if(d&&f!="function")return false;var o=this.options;var a=o.length;for(var i=0;i<a;i++){if(v.constructor==RegExp){if(o[i].value.match(v)){d=true;if(f=="function")c.call(o[i],i)}}else{if(o[i].value==v){d=true;if(f=="function")c.call(o[i],i)}}}});return f=="function"?this:d};$.fn.selectedValues=function(){var v=[];this.find("option:selected").each(function(){v[v.length]=this.value});return v};$.fn.selectedOptions=function(){return this.find("option:selected")}})(jQuery);


/*===========================================jquery.tooltip.js===================================================*/

;(function($) {
	
		// the tooltip element
	var helper = {},
		// the current tooltipped element
		current,
		// the title of the current element, used for restoring
		title,
		// timeout id for delayed tooltips
		tID,
		// IE 5.5 or 6
		IE = $.browser.msie && /MSIE\s(5\.5|6\.)/.test(navigator.userAgent),
		// flag for mouse tracking
		track = false;
	
	$.tooltip = {
		blocked: false,
		defaults: {
			delay: 200,
			fade: false,
			showURL: true,
			extraClass: "",
			top: 15,
			left: 15,
			id: "tooltip",
			useclick: false
		},
		block: function() {
			$.tooltip.blocked = !$.tooltip.blocked;
		}
	};
	
	$.fn.extend({
		tooltip: function(settings) {
			settings = $.extend({}, $.tooltip.defaults, settings);
			createHelper(settings);
			if (settings.useclick) {
			 fn_over = function(){};
			 fn_click = save;
			} else {
			 fn_over = save;
			 fn_click = hide;
			}
			return this.each(function() {
					$.data(this, "tooltip", settings);
					this.tOpacity = helper.parent.css("opacity");
					// copy tooltip into its own expando and remove the title
					this.tooltipText = this.title;
					$(this).removeAttr("title");
					// also remove alt attribute to prevent default tooltip in IE
					this.alt = "";
				})
				 .mouseover(fn_over)
				 .click(fn_click)
				 .mouseout(hide);
		},
		fixPNG: IE ? function() {
			return this.each(function () {
				var image = $(this).css('backgroundImage');
				if (image.match(/^url\(["']?(.*\.png)["']?\)$/i)) {
					image = RegExp.$1;
					$(this).css({
						'backgroundImage': 'none',
						'filter': "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='" + image + "')"
					}).each(function () {
						var position = $(this).css('position');
						if (position != 'absolute' && position != 'relative')
							$(this).css('position', 'relative');
					});
				}
			});
		} : function() { return this; },
		unfixPNG: IE ? function() {
			return this.each(function () {
				$(this).css({'filter': '', backgroundImage: ''});
			});
		} : function() { return this; },
		hideWhenEmpty: function() {
			return this.each(function() {
				$(this)[ $(this).html() ? "show" : "hide" ]();
			});
		},
		url: function() {
			return this.attr('href') || this.attr('src');
		}
	});
	
	function createHelper(settings) {
		// there can be only one tooltip helper
		if( helper.parent )
			return;
		// create the helper, h3 for title, div for url
		helper.parent = $('<div id="' + settings.id + '"><h3></h3><div class="body"></div><div class="url"></div></div>')
			// add to document
			.appendTo(document.body)
			// hide it at first
			.hide();
			
		// apply bgiframe if available
		if ( $.fn.bgiframe )
			helper.parent.bgiframe();
		
		// save references to title and url elements
		helper.title = $('h3', helper.parent);
		helper.body = $('div.body', helper.parent);
		helper.url = $('div.url', helper.parent);
	}
	
	function settings(element) {
		return $.data(element, "tooltip");
	}
	
	// main event handler to start showing tooltips
	function handle(event) {
		// show helper, either with timeout or on instant
		if( settings(this).delay )
			tID = setTimeout(show, settings(this).delay);
		else  show();
		  
		
		// if selected, update the helper position when the mouse moves
		track = !!settings(this).track;
		
		$(document.body).bind('mousemove', update);
			
		// update at least once
		update(event);
	}
	
	// save elements title before the tooltip is displayed
	function save() {
		// if this is the current source, or it has no title (occurs with click event), stop
		if ( $.tooltip.blocked || this == current || (!this.tooltipText && !settings(this).bodyHandler) )
			return;

		// save current
		current = this;
		title = this.tooltipText;
		
		if ( settings(this).bodyHandler ) {
			helper.title.hide();
			var bodyContent = settings(this).bodyHandler.call(this);
			if (bodyContent.nodeType || bodyContent.jquery) {
				helper.body.empty().append(bodyContent)
			} else {
				helper.body.html( bodyContent );
			}
			helper.body.show();
		} else if ( settings(this).showBody ) {
			var parts = title.split(settings(this).showBody);
			helper.title.html(parts.shift()).show();
			helper.body.empty();
			for(var i = 0, part; (part = parts[i]); i++) {
				if(i > 0)
					helper.body.append("<br/>");
				helper.body.append(part);
			}
			helper.body.hideWhenEmpty();
		} else {
			helper.title.html(title).show();
			helper.body.hide();
		}
		
		// if element has href or src, add and show it, otherwise hide it
		if( settings(this).showURL && $(this).url() )
			helper.url.html( $(this).url().replace('http://', '') ).show();
		else 
			helper.url.hide();
		
		// add an optional class for this tip
		helper.parent.addClass(settings(this).extraClass);

		// fix PNG background for IE
		if (settings(this).fixPNG )
			helper.parent.fixPNG();
			
		handle.apply(this, arguments);
	}
	
	// delete timeout and show helper
	function show() {
		tID = null;
		if ((!IE || !$.fn.bgiframe) && settings(current).fade) {
			if (helper.parent.is(":animated"))
				helper.parent.stop().show().fadeTo(settings(current).fade, current.tOpacity);
			else
				helper.parent.is(':visible') ? helper.parent.fadeTo(settings(current).fade, current.tOpacity) : helper.parent.fadeIn(settings(current).fade);
		} else {
			helper.parent.show();
		}
		update();
	}
	
	function update(event)	{
		if($.tooltip.blocked)
			return;
		
		if (event && event.target.tagName == "OPTION") {
			return;
		}
		
		if ( !track && helper.parent.is(":visible")) {
			$(document.body).unbind('mousemove', update)
		}
		
		if( current == null ) {
			$(document.body).unbind('mousemove', update);
			return;	
		}

		helper.parent.removeClass("viewport-right").removeClass("viewport-bottom");
		
		var left = helper.parent[0].offsetLeft;
		var top = helper.parent[0].offsetTop;
		if (event) {
			left = event.pageX + settings(current).left;
			top = event.pageY + settings(current).top;
			var right='auto';
			if (settings(current).positionLeft) {
				right = $(window).width() - left;
				left = 'auto';
			}
			helper.parent.css({
				left: left,
				right: right,
				top: top
			});
		}
		
		var v = viewport(),
			h = helper.parent[0];
		// check horizontal position
		if (v.x + v.cx < h.offsetLeft + h.offsetWidth) {
			left -= h.offsetWidth + 20 + settings(current).left;
			
			if (left < v.x) {
				left = v.x;
			}
			
			helper.parent.css({left: left + 'px'}).addClass("viewport-right");
		}
		// check vertical position
		if (v.y + v.cy < h.offsetTop + h.offsetHeight) {
			top -= h.offsetHeight + 20 + settings(current).top;
			
			if (top < v.y) {
				top = v.y;
			}
			
			helper.parent.css({top: top + 'px'}).addClass("viewport-bottom");
		}
		
	}
	
	function viewport() {
		return {
			x: $(window).scrollLeft(),
			y: $(window).scrollTop(),
			cx: $(window).width(),
			cy: $(window).height()
		};
	}
	
	// hide helper and restore added classes and the title
	function hide(event) {
		if($.tooltip.blocked)
			return;
		// clear timeout if possible
		if(tID)
			clearTimeout(tID);
		// no more current element
		current = null;
		
		var tsettings = settings(this);
		function complete() {
			helper.parent.removeClass( tsettings.extraClass ).hide().css("opacity", "");
		}
		if ((!IE || !$.fn.bgiframe) && tsettings.fade) {
			if (helper.parent.is(':animated'))
				helper.parent.stop().fadeTo(tsettings.fade, 0, complete);
			else
				helper.parent.stop().fadeOut(tsettings.fade, complete);
		} else
			complete();
		
		if( settings(this).fixPNG )
			helper.parent.unfixPNG();
	}
	
})(jQuery);



/*===========================================jquery.jframe.js===================================================*/


jQuery.fn.waitingJFrame = function () {
    // Overload this function in your code to place a waiting event
    // message, like :  $(this).html("<b>loading...</b>");
}

function _jsattr(elem, key) {
	var res = jQuery(elem).attr(key);
	if (res == undefined) {
		return function() {};
	}
	if (jQuery.browser.msie) {
		return function() { eval(res); };
	}
	return res;
}


function jFrameSubmitInput(input) {
    var target = jQuery(input).getJFrameTarget();
    var this_callback = _jsattr(target, "onload");	
    for(i = 0; i< target[0].attributes.length; i++) {
		if (target[0].attributes[i].nodeName == 'onload') {
			var x_tmp = target[0].attributes[i].nodeValue; 
			var this_callback = function() { eval(x_tmp) };
		}
	}
    if (target.length) {
        var form = input.form;
        
        if (form.onsubmit && form.onsubmit() == false
            || target.preloadJFrame() == false) {
            return false;
        }
        jQuery(form).ajaxSubmit({
            target: target,
                    beforeSubmit: function(formArray) {
						  	
                    formArray.push({ name:"submit", value: jQuery(input).attr("value") });
                },
                    success: function() {
                    target.attr("src", jQuery(form).attr("action"));
				//	_jsattr(target, "onload")();
		    this_callback();
                    target.activateJFrame();
                }
            });
        return false;
    }
    return true;
}

jQuery.fn.preloadJFrame = function(initial) {
	if (!initial && _jsattr(this, "onunload")() == false) {
		return false;
	}
    jQuery(this).waitingJFrame();
}


jQuery.fn.getJFrameTarget = function() {
    // Returns first parent jframe element, if exists
    var div = jQuery(this).parents("div[src]").get(0);
    if (div) {
        var target = jQuery(this).attr("target");
        if (target) {
            return jQuery("#" + target);
        }
    }
    return jQuery(div);
};



jQuery.fn.loadJFrame = function(url, callback, initial) {
   
	var this_callback = _jsattr(this, "onload");
	for(i = 0; i< this[0].attributes.length; i++) {
		if (this[0].attributes[i].nodeName == 'onload') {
			var x_tmp = this[0].attributes[i].nodeValue; 
			var this_callback = function() { eval(x_tmp) };
		}
	}
    
    callback = callback || function(){};
    url = url || jQuery(this).attr("src");
    if (url && url != "#") {
        if (jQuery(this).preloadJFrame(initial) == false) {
            return false;
        }
        //alert(this);
        jQuery(this).load(url,
                     function() {
                         //alert('loaded');
                         jQuery(this).attr("src", url);
                         jQuery(this).activateJFrame();
                         jQuery(this).find("div[src]").each(function(i) {
                                 jQuery(this).loadJFrame();
                             } );
                         
                         this_callback();
                         callback();
                     });
    }
    else {
        jQuery(this).activateJFrame();
    }
};

jQuery.fn.activateJFrame = function() {
    // Add an onclick event on all <a> and <input type="submit"> tags
    jQuery(this).find("a")
    .not("[jframe='no']")
    .unbind("click")
    .click(function() {
            var target = jQuery(this).getJFrameTarget();
            if (target.length) {
                var href = jQuery(this).attr("href");
                if (href && href.indexOf('javascript:') != 0) {
                    target.loadJFrame(href);
                    return false;
                }
            }
            return true;
        } );

    jQuery(":image,:submit,:button", this)
    .not("[jframe='no']")
    .unbind("click")
    .click(function() {
			return jFrameSubmitInput(this);
		} );

	// Only for IE6 : enter key invokes submit event
    jQuery(this).find("form")
    .unbind("submit")
    .submit(function() {
			return jFrameSubmitInput(jQuery(":image,:submit,:button", this).get(0));
    } );
};


jQuery(document).ready(function() {
    jQuery(document).find("div[src]").each(function(i) {
            jQuery(this).loadJFrame(undefined, undefined, true);
    } );
} );

/*=============================================tree.js==========================================================================*/
function UnHide( eThis ){
   if( $(eThis).attr('state') == 'close' ){
      $(eThis).attr('state','open');
      eThis.parentNode.parentNode.parentNode.className = '';
   } else {
      $(eThis).attr('state','close');
      eThis.parentNode.parentNode.parentNode.className = 'cl';
   }
   return false;
}
/* ============================================jquery.treeview.js===============================================================*/


;
(function($) {

   $.extend($.fn, {
      swapClass : function(c1, c2) {
         var c1Elements = this.filter('.' + c1);
         this.filter('.' + c2).removeClass(c2).addClass(c1);
         c1Elements.removeClass(c1).addClass(c2);
         return this;
      },
      replaceClass : function(c1, c2) {
         return this.filter('.' + c1).removeClass(c1).addClass(c2).end();
      },
      hoverClass : function(className) {
         className = className || "hover";
         return this.hover(function() {
            $(this).addClass(className);
         }, function() {
            $(this).removeClass(className);
         });
      },
      heightToggle : function(animated, callback) {
         animated ? this.animate( {
            height : "toggle"
         }, animated, callback) : this.each(function() {
            jQuery(this)[jQuery(this).is(":hidden") ? "show" : "hide"]();
            if (callback)
               callback.apply(this, arguments);
         });
      },
      heightHide : function(animated, callback) {
         if (animated) {
            this.animate( {
               height : "hide"
            }, animated, callback);
         } else {
            this.hide();
            if (callback)
               this.each(callback);
         }
      },
      prepareBranches : function(settings) {
         if (!settings.prerendered) {
            // mark last tree items
            this.filter(":last-child:not(ul)").addClass(CLASSES.last);
            // collapse whole tree, or only those marked as closed, anyway
            // except those marked as open
            this.filter(
                  (settings.collapsed ? "" : "." + CLASSES.closed) + ":not(."
                        + CLASSES.open + ")").find(">ul").hide();
         }
         // return all items with sublists
         return this.filter(":has(>ul)");
      },
      applyClasses : function(settings, toggler) {

      this.filter("li").find("span").hoverClass();

      if (!settings.prerendered) {
     this.filter(":has(>ul:hidden)")
               .addClass(CLASSES.expandable)
               // .replaceClass(CLASSES.last, CLASSES.lastCollapsable);
               .replaceClass(CLASSES.last, CLASSES.lastExpandable);

         if ("undefined" != typeof(settings.persist_id)) {
            var node = jQuery('#' + settings.persist_id).parent();
            do {
               node.filter(':has(>ul.folder)')
                   .addClass(CLASSES.collapsable)
                   .removeClass(CLASSES.expandable);
               if (node.parent().parent().is('li')) {
                  node = node.parent().parent();
               } else {
                  node.filter(':has(>ul.folder)')
                      .addClass(CLASSES.lastCollapsable)
                      .removeClass(CLASSES.last)
                      .removeClass(CLASSES.lastExpandable);
                  break;
               }
            } while (true);
         }
/* end [+-] fixes */

         // handle open ones
         this.not(":has(>ul:hidden)")
             .addClass(CLASSES.collapsable)
             .replaceClass(CLASSES.last, CLASSES.lastCollapsable);

         // create hitarea
         this.prepend("<div class=\"" + CLASSES.hitarea + "\"/>").find(
               "div." + CLASSES.hitarea).each(function() {
            var classes = "";
            $.each($(this).parent().attr("class").split(" "), function() {
               classes += this + "-hitarea ";
            });
            $(this).addClass(classes);
         });
      }

      // apply event to hitarea
      this.find("div." + CLASSES.hitarea).click(toggler);
   },
   treeview : function(settings) {

      settings = $.extend( {
         cookieId : "treeview"
      }, settings);

      if (settings.add) {
         return this.trigger("add", [ settings.add ]);
      }

      if (settings.toggle) {
         var callback = settings.toggle;
         settings.toggle = function() {
            return callback.apply($(this).parent()[0], arguments);
         };
      }

      // factory for treecontroller
      function treeController(tree, control) {
         // factory for click handlers
         function handler(filter) {
            return function() {
               toggler.apply($("div." + CLASSES.hitarea, tree).filter(
                     function() {
                        // for plain toggle, no filter is provided, otherwise we
                        // need to check the parent element
                        return filter ? $(this).parent("." + filter).length
                              : true;
                     }));
               return false;
            };
         }
         // click on first element to collapse tree
         $("a:eq(0)", control).click(handler(CLASSES.collapsable));
         // click on second to expand tree
         $("a:eq(1)", control).click(handler(CLASSES.expandable));
         // click on third to toggle tree
         $("a:eq(2)", control).click(handler());
      }

      // handle toggle event
      function toggler() {
         $(this).parent()
         // swap classes for hitarea
               .find(">.hitarea").swapClass(CLASSES.collapsableHitarea,
                     CLASSES.expandableHitarea).swapClass(
                     CLASSES.lastCollapsableHitarea,
                     CLASSES.lastExpandableHitarea).end()
               // swap classes for parent li
               .swapClass(CLASSES.collapsable, CLASSES.expandable).swapClass(
                     CLASSES.lastCollapsable, CLASSES.lastExpandable)
               // find child lists
               .find(">ul")
               // toggle them
               .heightToggle(settings.animated, settings.toggle);
         if (settings.unique) {
            $(this).parent().siblings()
            // swap classes for hitarea
                  .find(">.hitarea").replaceClass(CLASSES.collapsableHitarea,
                        CLASSES.expandableHitarea).replaceClass(
                        CLASSES.lastCollapsableHitarea,
                        CLASSES.lastExpandableHitarea).end().replaceClass(
                        CLASSES.collapsable, CLASSES.expandable).replaceClass(
                        CLASSES.lastCollapsable, CLASSES.lastExpandable).find(
                        ">ul").heightHide(settings.animated, settings.toggle);
         }
      }

      function serialize() {
         function binary(arg) {
            return arg ? 1 : 0;
         }
         var data = [];
         branches.each(function(i, e) {
            data[i] = $(e).is(":has(>ul:visible)") ? 1 : 0;
         });
         $.cookie(settings.cookieId, data.join(""));
      }

      function deserialize() {
         var stored = $.cookie(settings.cookieId);
         if (stored) {
            var data = stored.split("");
            branches.each(function(i, e) {
               $(e).find(">ul")[parseInt(data[i]) ? "show" : "hide"]();
            });
         }
      }

      // add treeview class to activate styles
      this.addClass("treeview");

      // prepare branches and find all tree items with child lists
      var branches = this.find("li").prepareBranches(settings);

      switch (settings.persist) {
      case "cookie":
      var toggleCallback = settings.toggle;
      settings.toggle = function() {
         serialize();
         if (toggleCallback) {
            toggleCallback.apply(this, arguments);
         }
      };
      deserialize();
      break;
      case "location":
      var current = this.find("a").filter(function() {
         return this.href.toLowerCase() == location.href.toLowerCase();
      });
      if (current.length) {
         current.addClass("selected").parents("ul, li").add(current.next())
               .show();
      }
      break;
      // R2 added:
      case "id":
      var current = $('#' + settings.persist_id);
      if (current.length) {
         current.addClass("selected").parents("ul, li").add(current.next())
               .show();
      }
      break;
      }

      branches.applyClasses(settings, toggler);

      // if control option is set, create the treecontroller and show it
      if (settings.control) {
         treeController(this, settings.control);
         $(settings.control).show();
      }

      return this.bind("add", function(event, branches) {
         $(branches).prev().removeClass(CLASSES.last).removeClass(
               CLASSES.lastCollapsable).removeClass(CLASSES.lastExpandable)
               .find(">.hitarea").removeClass(CLASSES.lastCollapsableHitarea)
               .removeClass(CLASSES.lastExpandableHitarea);
         $(branches).find("li").andSelf().prepareBranches(settings)
               .applyClasses(settings, toggler);
      });
   }
   });

   // classes used by the plugin
   // need to be styled via external stylesheet, see first example
   var CLASSES = $.fn.treeview.classes = {
      open : "open",
      closed : "closed",
      expandable : "expandable",
      expandableHitarea : "expandable-hitarea",
      lastExpandableHitarea : "lastExpandable-hitarea",
      collapsable : "collapsable",
      collapsableHitarea : "collapsable-hitarea",
      lastCollapsableHitarea : "lastCollapsable-hitarea",
      lastCollapsable : "lastCollapsable",
      lastExpandable : "lastExpandable",
      last : "last",
      hitarea : "hitarea"
   };

   // provide backwards compability
   $.fn.Treeview = $.fn.treeview;

})(jQuery);

/* ============================================jquery.textarea_length.js===============================================================*/
function limitTextareaChars(textarea, limit) {
	var text = textarea.value; 
	var textlength = text.length;
	var limit_span = $(textarea).next('.limitation').find('span');
	
	if(textlength > limit) {
		textarea.value = text.substr(0,limit);
		$(limit_span).text(limit);
		return false;
	}
	else {
		$(limit_span).text(textlength);
		return true;
	}
}

function limitInputChars(input, limit) {
	var text = input.value; 
	var textlength = text.length;
	var limit_span = $(input).next('.limitation').find('span');
	$(limit_span).text(textlength);
	return true;
}
/* ============================================jquery.flash.js===============================================================*/
(function(){
	
var $$;
$$ = jQuery.fn.flash = function(htmlOptions, pluginOptions, replace, update) {
	
	// Set the default block.
	var block = replace || $$.replace;
	
	// Merge the default and passed plugin options.
	pluginOptions = $$.copy($$.pluginOptions, pluginOptions);
	
	// Detect Flash.
	if(!$$.hasFlash(pluginOptions.version)) {
		// Use Express Install (if specified and Flash plugin 6,0,65 or higher is installed).
		if(pluginOptions.expressInstall && $$.hasFlash(6,0,65)) {
			// Add the necessary flashvars (merged later).
			var expressInstallOptions = {
				flashvars: {  	
					MMredirectURL: location,
					MMplayerType: 'PlugIn',
					MMdoctitle: jQuery('title').text() 
				}					
			};
		} else if (pluginOptions.update) {

			block = update || $$.update;

		} else {

			return this;
		}
	}
	
	// Merge the default, express install and passed html options.
	htmlOptions = $$.copy($$.htmlOptions, expressInstallOptions, htmlOptions);
	
	// Invoke $block (with a copy of the merged html options) for each element.
	return this.each(function(){
		block.call(this, $$.copy(htmlOptions));
	});
	
};
$$.copy = function() {
	var options = {}, flashvars = {};
	for(var i = 0; i < arguments.length; i++) {
		var arg = arguments[i];
		if(arg == undefined) continue;
		jQuery.extend(options, arg);
		if(arg.flashvars == undefined) continue;
		jQuery.extend(flashvars, arg.flashvars);
	}
	options.flashvars = flashvars;
	return options;
};
$$.hasFlash = function() {
	// look for a flag in the query string to bypass flash detection
	if(/hasFlash\=true/.test(location)) return true;
	if(/hasFlash\=false/.test(location)) return false;
	var pv = $$.hasFlash.playerVersion().match(/\d+/g);
	var rv = String([arguments[0], arguments[1], arguments[2]]).match(/\d+/g) || String($$.pluginOptions.version).match(/\d+/g);
	for(var i = 0; i < 3; i++) {
		pv[i] = parseInt(pv[i] || 0);
		rv[i] = parseInt(rv[i] || 0);
		// player is less than required
		if(pv[i] < rv[i]) return false;
		// player is greater than required
		if(pv[i] > rv[i]) return true;
	}

	return true;
};

$$.hasFlash.playerVersion = function() {
	// ie
	try {
		try {
			var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
			try { axo.AllowScriptAccess = 'always';	} 
			catch(e) { return '6,0,0'; }				
		} catch(e) {}
		return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1];
	// other browsers
	} catch(e) {
		try {
			if(navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin){
				return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
			}
		} catch(e) {}		
	}
	return '0,0,0';
};

$$.htmlOptions = {
	height: 240,
	flashvars: {},
	pluginspage: 'http://www.adobe.com/go/getflashplayer',
	src: '#',
	type: 'application/x-shockwave-flash',
	width: 320		
};

$$.pluginOptions = {
	expressInstall: false,
	update: true,
	version: '6.0.65'
};

$$.replace = function(htmlOptions) {
	this.innerHTML = '<div class="alt">'+this.innerHTML+'</div>';
	jQuery(this)
		.addClass('flash-replaced')
		.prepend($$.transform(htmlOptions));
};

$$.update = function(htmlOptions) {
	var url = String(location).split('?');
	url.splice(1,0,'?hasFlash=true&');
	url = url.join('');
	var msg = '<p>This content requires the Flash Player. <a href="http://www.adobe.com/go/getflashplayer">Download Flash Player</a>. Already have Flash Player? <a href="'+url+'">Click here.</a></p>';
	this.innerHTML = '<span class="alt">'+this.innerHTML+'</span>';
	jQuery(this)
		.addClass('flash-update')
		.prepend(msg);
};

function toAttributeString() {
	var s = '';
	for(var key in this)
		if(typeof this[key] != 'function')
			s += key+'="'+this[key]+'" ';
	return s;		
};

function toFlashvarsString() {
	var s = '';
	for(var key in this)
		if(typeof this[key] != 'function')
			s += key+'='+encodeURIComponent(this[key])+'&';
	return s.replace(/&$/, '');		
};

$$.transform = function(htmlOptions) {
	htmlOptions.toString = toAttributeString;
	if(htmlOptions.flashvars) htmlOptions.flashvars.toString = toFlashvarsString;
	return '<embed ' + String(htmlOptions) + '/>';		
};

if (window.attachEvent) {
	window.attachEvent("onbeforeunload", function(){
		__flash_unloadHandler = function() {};
		__flash_savedUnloadHandler = function() {};
	});
}
	
})();
/* ============================================jquery.form.js===============================================================*/
(function($) {

$.fn.ajaxSubmit = function(options) {
    // fast fail if nothing selected (http://dev.jquery.com/ticket/2752)
    if (!this.length) {
        log('ajaxSubmit: skipping submit process - no element selected');
        return this;
    }

    if (typeof options == 'function')
        options = { success: options };

    // clean url (don't include hash vaue)
    var url = this.attr('action') || window.location.href;
    url = (url.match(/^([^#]+)/)||[])[1];
    url = url || '';

    options = $.extend({
        url:  url,
        type: this.attr('method') || 'GET'
    }, options || {});

    var veto = {};
    this.trigger('form-pre-serialize', [this, options, veto]);
    if (veto.veto) {
        log('ajaxSubmit: submit vetoed via form-pre-serialize trigger');
        return this;
    }

    if (options.beforeSerialize && options.beforeSerialize(this, options) === false) {
        log('ajaxSubmit: submit aborted via beforeSerialize callback');
        return this;
    }

    var a = this.formToArray(options.semantic);
    if (options.data) {
        options.extraData = options.data;
        for (var n in options.data) {
          if(options.data[n] instanceof Array) {
            for (var k in options.data[n])
              a.push( { name: n, value: options.data[n][k] } );
          }
          else
             a.push( { name: n, value: options.data[n] } );
        }
    }

    // give pre-submit callback an opportunity to abort the submit
    if (options.beforeSubmit && options.beforeSubmit(a, this, options) === false) {
        log('ajaxSubmit: submit aborted via beforeSubmit callback');
        return this;
    }

    // fire vetoable 'validate' event
    this.trigger('form-submit-validate', [a, this, options, veto]);
    if (veto.veto) {
        log('ajaxSubmit: submit vetoed via form-submit-validate trigger');
        return this;
    }

    var q = $.param(a);

    if (options.type.toUpperCase() == 'GET') {
        options.url += (options.url.indexOf('?') >= 0 ? '&' : '?') + q;
        options.data = null;  // data is null for 'get'
    }
    else
        options.data = q; // data is the query string for 'post'

    var $form = this, callbacks = [];
    if (options.resetForm) callbacks.push(function() { $form.resetForm(); });
    if (options.clearForm) callbacks.push(function() { $form.clearForm(); });

    // perform a load on the target only if dataType is not provided
    if (!options.dataType && options.target) {
        var oldSuccess = options.success || function(){};
        callbacks.push(function(data) {
            $(options.target).html(data).each(oldSuccess, arguments);
        });
    }
    else if (options.success)
        callbacks.push(options.success);

    options.success = function(data, status) {
        for (var i=0, max=callbacks.length; i < max; i++)
            callbacks[i].apply(options, [data, status, $form]);
    };

    // are there files to upload?
    var files = $('input:file', this).fieldValue();
    var found = false;
    for (var j=0; j < files.length; j++)
        if (files[j])
            found = true;

    // options.iframe allows user to force iframe mode
   if (options.iframe || found) {
       if (options.closeKeepAlive)
           $.get(options.closeKeepAlive, fileUpload);
       else
           fileUpload();
       }
   else
       $.ajax(options);

    // fire 'notify' event
    this.trigger('form-submit-notify', [this, options]);
    return this;


    // private function for handling file uploads (hat tip to YAHOO!)
    function fileUpload() {
        var form = $form[0];

        if ($(':input[name=submit]', form).length) {
            alert('Error: Form elements must not be named "submit".');
            return;
        }

        var opts = $.extend({}, $.ajaxSettings, options);
		var s = jQuery.extend(true, {}, $.extend(true, {}, $.ajaxSettings), opts);

        var id = 'jqFormIO' + (new Date().getTime());
        var $io = $('<iframe id="' + id + '" name="' + id + '" src="about:blank" />');
        var io = $io[0];

        $io.css({ position: 'absolute', top: '-1000px', left: '-1000px' });

        var xhr = { // mock object
            aborted: 0,
            responseText: null,
            responseXML: null,
            status: 0,
            statusText: 'n/a',
            getAllResponseHeaders: function() {},
            getResponseHeader: function() {},
            setRequestHeader: function() {},
            abort: function() {
                this.aborted = 1;
                $io.attr('src','about:blank'); // abort op in progress
            }
        };

        var g = opts.global;
        // trigger ajax global events so that activity/block indicators work like normal
        if (g && ! $.active++) $.event.trigger("ajaxStart");
        if (g) $.event.trigger("ajaxSend", [xhr, opts]);

		if (s.beforeSend && s.beforeSend(xhr, s) === false) {
			s.global && jQuery.active--;
			return;
        }
        if (xhr.aborted)
            return;

        var cbInvoked = 0;
        var timedOut = 0;

        // add submitting element to data if we know it
        var sub = form.clk;
        if (sub) {
            var n = sub.name;
            if (n && !sub.disabled) {
                options.extraData = options.extraData || {};
                options.extraData[n] = sub.value;
                if (sub.type == "image") {
                    options.extraData[name+'.x'] = form.clk_x;
                    options.extraData[name+'.y'] = form.clk_y;
                }
            }
        }

        // take a breath so that pending repaints get some cpu time before the upload starts
        setTimeout(function() {
            // make sure form attrs are set
            var t = $form.attr('target'), a = $form.attr('action');

			// update form attrs in IE friendly way
			form.setAttribute('target',id);
			if (form.getAttribute('method') != 'POST')
				form.setAttribute('method', 'POST');
			if (form.getAttribute('action') != opts.url)
				form.setAttribute('action', opts.url);

            // ie borks in some cases when setting encoding
            if (! options.skipEncodingOverride) {
                $form.attr({
                    encoding: 'multipart/form-data',
                    enctype:  'multipart/form-data'
                });
            }

            // support timout
            if (opts.timeout)
                setTimeout(function() { timedOut = true; cb(); }, opts.timeout);

            // add "extra" data to form if provided in options
            var extraInputs = [];
            try {
                if (options.extraData)
                    for (var n in options.extraData)
                        extraInputs.push(
                            $('<input type="hidden" name="'+n+'" value="'+options.extraData[n]+'" />')
                                .appendTo(form)[0]);

                // add iframe to doc and submit the form
                $io.appendTo('body');
                io.attachEvent ? io.attachEvent('onload', cb) : io.addEventListener('load', cb, false);
                form.submit();
            }
            finally {
                // reset attrs and remove "extra" input elements
				form.setAttribute('action',a);
                t ? form.setAttribute('target', t) : $form.removeAttr('target');
                $(extraInputs).remove();
            }
        }, 10);

        var nullCheckFlag = 0;

        function cb() {
            if (cbInvoked++) return;

            io.detachEvent ? io.detachEvent('onload', cb) : io.removeEventListener('load', cb, false);

            var ok = true;
            try {
                if (timedOut) throw 'timeout';
                // extract the server response from the iframe
                var data, doc;

                doc = io.contentWindow ? io.contentWindow.document : io.contentDocument ? io.contentDocument : io.document;

                if ((doc.body == null || doc.body.innerHTML == '') && !nullCheckFlag) {
                    // in some browsers (cough, Opera 9.2.x) the iframe DOM is not always traversable when
                    // the onload callback fires, so we give them a 2nd chance
                    nullCheckFlag = 1;
                    cbInvoked--;
                    setTimeout(cb, 100);
                    return;
                }

                xhr.responseText = doc.body ? doc.body.innerHTML : null;
                xhr.responseXML = doc.XMLDocument ? doc.XMLDocument : doc;
                xhr.getResponseHeader = function(header){
                    var headers = {'content-type': opts.dataType};
                    return headers[header];
                };

                if (opts.dataType == 'json' || opts.dataType == 'script') {
                    var ta = doc.getElementsByTagName('textarea')[0];
                    xhr.responseText = ta ? ta.value : xhr.responseText;
                }
                else if (opts.dataType == 'xml' && !xhr.responseXML && xhr.responseText != null) {
                    xhr.responseXML = toXml(xhr.responseText);
                }
                data = $.httpData(xhr, opts.dataType);
            }
            catch(e){
                ok = false;
                $.handleError(opts, xhr, 'error', e);
            }

            // ordering of these callbacks/triggers is odd, but that's how $.ajax does it
            if (ok) {
                opts.success(data, 'success');
                if (g) $.event.trigger("ajaxSuccess", [xhr, opts]);
            }
            if (g) $.event.trigger("ajaxComplete", [xhr, opts]);
            if (g && ! --$.active) $.event.trigger("ajaxStop");
            if (opts.complete) opts.complete(xhr, ok ? 'success' : 'error');

            // clean up
            setTimeout(function() {
                $io.remove();
                xhr.responseXML = null;
            }, 100);
        };

        function toXml(s, doc) {
            if (window.ActiveXObject) {
                doc = new ActiveXObject('Microsoft.XMLDOM');
                doc.async = 'false';
                doc.loadXML(s);
            }
            else
                doc = (new DOMParser()).parseFromString(s, 'text/xml');
            return (doc && doc.documentElement && doc.documentElement.tagName != 'parsererror') ? doc : null;
        };
    };
};


$.fn.ajaxForm = function(options) {
    return this.ajaxFormUnbind().bind('submit.form-plugin',function() {
        $(this).ajaxSubmit(options);
        return false;
    }).each(function() {
        // store options in hash
        $(":submit,input:image", this).bind('click.form-plugin',function(e) {
            var form = this.form;
            form.clk = this;
            if (this.type == 'image') {
                if (e.offsetX != undefined) {
                    form.clk_x = e.offsetX;
                    form.clk_y = e.offsetY;
                } else if (typeof $.fn.offset == 'function') { // try to use dimensions plugin
                    var offset = $(this).offset();
                    form.clk_x = e.pageX - offset.left;
                    form.clk_y = e.pageY - offset.top;
                } else {
                    form.clk_x = e.pageX - this.offsetLeft;
                    form.clk_y = e.pageY - this.offsetTop;
                }
            }
            // clear form vars
            setTimeout(function() { form.clk = form.clk_x = form.clk_y = null; }, 10);
        });
    });
};

// ajaxFormUnbind unbinds the event handlers that were bound by ajaxForm
$.fn.ajaxFormUnbind = function() {
    this.unbind('submit.form-plugin');
    return this.each(function() {
        $(":submit,input:image", this).unbind('click.form-plugin');
    });

};

$.fn.formToArray = function(semantic) {
    var a = [];
    if (this.length == 0) return a;

    var form = this[0];
    var els = semantic ? form.getElementsByTagName('*') : form.elements;
    if (!els) return a;
    for(var i=0, max=els.length; i < max; i++) {
        var el = els[i];
        var n = el.name;
        if (!n) continue;

        if (semantic && form.clk && el.type == "image") {
            // handle image inputs on the fly when semantic == true
            if(!el.disabled && form.clk == el)
                a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
            continue;
        }

        var v = $.fieldValue(el, true);
        if (v && v.constructor == Array) {
            for(var j=0, jmax=v.length; j < jmax; j++)
                a.push({name: n, value: v[j]});
        }
        else if (v !== null && typeof v != 'undefined')
            a.push({name: n, value: v});
    }

    if (!semantic && form.clk) {
        // input type=='image' are not found in elements array! handle them here
        var inputs = form.getElementsByTagName("input");
        for(var i=0, max=inputs.length; i < max; i++) {
            var input = inputs[i];
            var n = input.name;
            if(n && !input.disabled && input.type == "image" && form.clk == input)
                a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
        }
    }
    return a;
};


$.fn.formSerialize = function(semantic) {
    //hand off to jQuery.param for proper encoding
    return $.param(this.formToArray(semantic));
};

$.fn.fieldSerialize = function(successful) {
    var a = [];
    this.each(function() {
        var n = this.name;
        if (!n) return;
        var v = $.fieldValue(this, successful);
        if (v && v.constructor == Array) {
            for (var i=0,max=v.length; i < max; i++)
                a.push({name: n, value: v[i]});
        }
        else if (v !== null && typeof v != 'undefined')
            a.push({name: this.name, value: v});
    });
    //hand off to jQuery.param for proper encoding
    return $.param(a);
};


$.fn.fieldValue = function(successful) {
    for (var val=[], i=0, max=this.length; i < max; i++) {
        var el = this[i];
        var v = $.fieldValue(el, successful);
        if (v === null || typeof v == 'undefined' || (v.constructor == Array && !v.length))
            continue;
        v.constructor == Array ? $.merge(val, v) : val.push(v);
    }
    return val;
};


$.fieldValue = function(el, successful) {
    var n = el.name, t = el.type, tag = el.tagName.toLowerCase();
    if (typeof successful == 'undefined') successful = true;

    if (successful && (!n || el.disabled || t == 'reset' || t == 'button' ||
        (t == 'checkbox' || t == 'radio') && !el.checked ||
        (t == 'submit' || t == 'image') && el.form && el.form.clk != el ||
        tag == 'select' && el.selectedIndex == -1))
            return null;

    if (tag == 'select') {
        var index = el.selectedIndex;
        if (index < 0) return null;
        var a = [], ops = el.options;
        var one = (t == 'select-one');
        var max = (one ? index+1 : ops.length);
        for(var i=(one ? index : 0); i < max; i++) {
            var op = ops[i];
            if (op.selected) {
				var v = op.value;
				if (!v) // extra pain for IE...
                	v = (op.attributes && op.attributes['value'] && !(op.attributes['value'].specified)) ? op.text : op.value;
                if (one) return v;
                a.push(v);
            }
        }
        return a;
    }
    return el.value;
};


$.fn.clearForm = function() {
    return this.each(function() {
        $('input,select,textarea', this).clearFields();
    });
};

$.fn.clearFields = $.fn.clearInputs = function() {
    return this.each(function() {
        var t = this.type, tag = this.tagName.toLowerCase();
        if (t == 'text' || t == 'password' || tag == 'textarea')
            this.value = '';
        else if (t == 'checkbox' || t == 'radio')
            this.checked = false;
        else if (tag == 'select')
            this.selectedIndex = -1;
    });
};

$.fn.resetForm = function() {
    return this.each(function() {
        if (typeof this.reset == 'function' || (typeof this.reset == 'object' && !this.reset.nodeType))
            this.reset();
    });
};


$.fn.enable = function(b) {
    if (b == undefined) b = true;
    return this.each(function() {
        this.disabled = !b;
    });
};


$.fn.selected = function(select) {
    if (select == undefined) select = true;
    return this.each(function() {
        var t = this.type;

        if (t == 'checkbox' || t == 'radio')
            this.checked = select;
        else if (this.tagName.toLowerCase() == 'option') {
            var $sel = $(this).parent('select');
            if (select && $sel[0] && $sel[0].type == 'select-one') {
                // deselect all other options
                $sel.find('option').selected(false);
            }
            this.selected = select;
        }
    });
};

function log() {
    if ($.fn.ajaxSubmit.debug && window.console && window.console.log)
        window.console.log('[jquery.form] ' + Array.prototype.join.call(arguments,''));
};

})(jQuery);


/*===============================================fields.categories.js==================================================================*/

if (typeof Sppc == 'undefined') {
	var Sppc = {};
}

Sppc.CategorySelectorDialog$Class = {
	_dialog: null,
	_tree: null,
	field: null,
	
	constructor: function(config){
		jQuery.extend(true, this, Sppc.CategorySelectorDialog$Class);
		this.field = config.field;
		
		var ar={
				autoOpen: false,
				buttons: {},
				dialogClass: 'category-selector-dialog',
				resizable: false,
				width: 470
			};
		
		ar.buttons[this.field.getLL('close')] =  function(){jQuery(this).dialog('close'); $(document).unbind('.dialog-overlay'); };
		
		this._dialog = jQuery('<div></div>')
			.html('<h1 class="p0">' + this.field.getLL('selectCategoriesDialogTitle') + '</h1><div class="mt10 mb10"><ul id="category_tree"></ul></div><div class="mt10 clearBoth"></div>')
			.dialog(ar);
		
		
		var self = this;
		
		this._tree = jQuery('#category_tree').tree({
			//selectorDialog: this,
			data: {
				type: 'json',
				async: true,
				opts: {
					url: site_url + 'common/categories/get_children'
				}
			},
			ui: {
				dots: false,
				theme_path: site_url + 'css/jquery-tree/themes/default/style.css'
			},
			types: {
				'default': {
					clickable: false,
					renameable: false,
					deletable: false,
					creatable: false,
					draggable: false
				}
			},
			callback: {
				beforedata: function(node, tree) {
					if (!jQuery(node).attr('id')) {
						return {id: 1};
					} else {
						return {id: jQuery(node).attr('id').split('_')[1]};
					}
				},
				onparse: function(html, tree) {
					var categories = jQuery(html);
					var dialog = self;
					var field = dialog.field;
					for (var i = 0; i < categories.length; i++) {
						var category = jQuery(categories[i]);
						var categoryId = category.attr('id').split('_')[1];
						var addRemoveBtn = jQuery(document.createElement('span'))
							.bind('click', {'dialog': dialog}, dialog.onCategoryBtnClick);
						
						if (field.isCategorySelected(categoryId)) {
							addRemoveBtn.text(field.getLL('remove')).addClass('remove-category-link');
							category.addClass('selected');
						} else {
							addRemoveBtn.text(field.getLL('add')).addClass('add-category-link');
						}
						category.prepend(addRemoveBtn);
					}
					
					return categories;
				}
			}
		});
	},
	
	open: function() {
		this._dialog.dialog('open');
	},
	
	close: function() {
		this._dialog.dialog('close');
	},
	onCategoryBtnClick: function(e){
		var category = jQuery(this).parent();
		var categoryId = category.attr('id').split('_')[1];
		var dialog = e.data.dialog;
		var field = dialog.field;
		
		if (field.isCategorySelected(categoryId)) {
			field.removeCategory(categoryId);
			category.removeClass('selected');
		} else {
			var categoryTitle = dialog.trim(jQuery('> a:last', category).text());
			var result = field.addCategory(categoryId, categoryTitle);
			
			if (result) {
			category.addClass('selected');
			jQuery(this).text(field.getLL('remove'))
				.removeClass('add-category-link')
				.addClass('remove-category-link');
			}
		}
	},
	trim: function(str) {
		return str.replace(/^\s+|\s+$/g,"");
	}
};
Sppc.CategorySelectorDialog = Sppc.CategorySelectorDialog$Class.constructor;

Sppc.CategorySelectorField$Class = {
	selectedCategories: new Array(),
	formField: null,
	opendDialogBtn: null,
	containerEl: null,
	dialog: null,
	maxSelectedCategories: null,
	LL: {
		add: 'Add',
		remove: 'Remove',
		selectCategoriesDialogTitle: 'Select categories',
		close: 'Close',
		cantAddMoreCategories: 'You can not add more categories'
	},
	
	
	constructor: function(options){
		jQuery.extend(true, this, Sppc.CategorySelectorField$Class, options);
		this.formField = jQuery('#'+options.formField);
		this.opendDialogBtn = jQuery('#' + options.openDialogBtn);
		this.containerEl = jQuery('#'+options.containerEl);
		
		this.opendDialogBtn.bind('click', {field: this}, function(e){e.data.field.openDialog();});
		
		this.renderSelectedCategories();
		
		this.formField.data('CategorySelectorField', this);
	},

	renderSelectedCategories: function() {
		for(var i = 0; i < this.selectedCategories.length; i++) {
			var category = this.selectedCategories[i];
			this.renderSelectedCategory(category.id, category.name);
		}
	},
	
	renderSelectedCategory: function(categoryId, categoryTitle) {
		var categoryEl = jQuery(document.createElement('li'))
			.attr('id', 'selected_category_' + categoryId);
		
		var categoryTitle = jQuery(document.createElement('a'))
			.text(categoryTitle);
		
		var categoryRemoveBtn = jQuery(document.createElement('a'))
			.text(this.getLL('remove'))
			.addClass('floatr')
			.addClass('remove-category-link')
			.bind('click', {field: this}, this.onRemoveBtnClick);
		
		categoryEl.append(categoryRemoveBtn);
		categoryEl.append(categoryTitle);
		
		this.containerEl.append(categoryEl);
	},
	
	addCategory: function(categoryId, categoryTitle) {
		if ((this.maxSelectedCategories) && (this.selectedCategories.length >= this.maxSelectedCategories)) {
			alert(this.getLL('cantAddMoreCategories'));
			return false;
		}
		this.selectedCategories.push({'id': categoryId, 'name': categoryTitle});
		
		var categories = new Array();
		for(var i = 0; i < this.selectedCategories.length; i++) {
			categories.push(this.selectedCategories[i].id);
		}
		
		this.formField.attr('value', categories.join(','));
		jQuery('p.errorP', this.containerEl.parent()).remove();
		
		this.renderSelectedCategory(categoryId, categoryTitle);
		
		return true;
	},
	
	removeCategory: function(categoryId) {
		var selectedCategories = new Array();
		var categories = new Array();
		for(var i = 0; i < this.selectedCategories.length; i++) {
			if (this.selectedCategories[i].id != categoryId) {
				selectedCategories.push(this.selectedCategories[i]);
				categories.push(this.selectedCategories[i].id);
			}
		}
		
		this.selectedCategories = selectedCategories;
		this.formField.attr('value', categories.join(','));
		
		jQuery('#selected_category_'+categoryId).remove();
		jQuery('#category_'+categoryId).removeClass('selected');
		jQuery('#category_'+categoryId+' > span:first')
			.text(this.getLL('add'))
			.removeClass('remove-category-link')
			.addClass('add-category-link');
		
	},
	isCategorySelected: function(categoryId) {
		for(var i = 0; i < this.selectedCategories.length; i++){
			if (this.selectedCategories[i].id == categoryId) {
				return true;
			}
		}
		return false;
	},
	openDialog: function(e) {
		if (!this.dialog) {
			this.dialog = new Sppc.CategorySelectorDialog({field: this});
		}
		this.dialog.open();
	},
	getLL: function(label) {
		if (this.LL[label]) {
			return this.LL[label];
		} else {
			return label;
		}
	},
	onRemoveBtnClick: function(e) {
		var category = jQuery(this).parent();
		var categoryId = category.attr('id').split('_')[2];
		e.data.field.removeCategory(categoryId);
	},
	getSelectedCategories: function() {
		return this.selectedCategories;
	}
};
Sppc.CategorySelectorField = Sppc.CategorySelectorField$Class.constructor;


/* ============================================jquery.combobox.js===============================================================*/

jQuery.fn.combobox = function(options)
{
	var settings =
	{		
		comboboxContainerClass: null,
		comboboxValueContainerClass: null,
		comboboxValueContentClass: null,
		comboboxDropDownButtonClass: null,
		comboboxDropDownClass: null,
		comboboxDropDownItemClass: null,
		comboboxDropDownItemHoverClass: null,
		comboboxDropDownGroupItemHeaderClass: null,
		comboboxDropDownGroupItemContainerClass: null,
		animationType: "slide",
		width: "120px"
	};
	
	if (options)
	{
		jQuery.extend(settings, options);
	}
	
	
	this.onChange =
		function()
		{
			//Intentionally left empty
		};

	
	return this.each(
		function()
		{

			var _originalElementJQuery = jQuery(this);
			var _containerJQuery = null;
			var _containerDefaultStyle = "background-color:#ffffff;border-left: solid 2px #777;border-top: solid 2px #777;border-right: solid 1px #ccc;border-bottom: solid 1px #ccc;";
			var _containerEnforcedStyle = "padding:0;";
			var _dropDownListJQuery = null;
			var _dropDownListEnforcedStyle = "list-style-type:none;min-height:15px;padding-top:0;margin:0;max-height:200px;overflow:auto;";
			var _dropDownListDefaultStyle = "cursor:default;padding:2px;background:#fff;border-right:solid 1px #000;border-bottom:solid 1px #000;border-left:solid 1px #aaa;border-top:solid 1px #aaa;overflow:auto";
			var _dropDownListItemEnforcedStyle = "display:block;";
			var _dropDownListItemDefaultStyle = "cursor:default;padding-left:2px;font-weight:normal;font-style:normal;";
			var _dropDownListGroupItemContainerEnforcedStyle = "list-style-type:none;";
			var _dropDownListGroupItemContainerDefaultStyle = "padding-left:10px;margin-left:0;";
			var _dropDownListGroupItemHeaderEnforcedStyle = "";
			var _dropDownListGroupItemHeaderDefaultStyle = "font-style:italic;font-weight:bold;";			
			var _valueDisplayContainerJQuery = null;
			var _valueDisplayContainerEnforcedStyle = "position:relative;overflow:hidden;";
			var _valueDisplayJQuery = null;
			var _valueDisplayEnforcedStyle = "float:left;position:absolute;cursor:default;overflow:hidden;";
			var _dropDownButtonJQuery = null;
			var _dropDownButtonDefaultStyle = "overflow:hidden;width: 16px;height: 18px;color:#000;background: #D6D3CE;,font-family: verdana;font-size: 10px;cursor: default;text-align: center;vertical-align:middle;";
			var _dropDownButtonEnforcedStyle = "background-repeat:no-repeat;float:right;";
			var _dropDownButtonDefaultUnselectedStyle = "padding-left:0px;padding-top:1px;width:12px;height:13px;border-right:solid 2px #404040;border-bottom:solid 2px #404040;border-left:solid 2px #f0f0f0;border-top:solid 2px #f0f0f0";
			var _dropDownButtonDefaultSelectedStyle = "padding-left:1px;padding-top:3px;width:12px;height:13px;border:solid 1px #808080";
			var _dropDownButtonDefaultCharacter = "&#9660;";
			var _lastItemSelectedJQuery = null;
			var _lastValue = null;
			var _downdownListPositionIsInverted = false;
			var _maximumItemLength = 0;
			var _dropDownListOffset = null;

			String.format =
				function()
				{
					var currentString = null;
					if (arguments.length != 0)
					{
						currentString = arguments[0];
						for (var argumentIndex = 1; argumentIndex < arguments.length; argumentIndex++)
						{
							var modifiedString = new RegExp('\\{' + (argumentIndex - 1) + '\\}','gm');
							currentString = currentString.replace(modifiedString, arguments[argumentIndex]);
						}
					}
					
					return currentString;
				};
		
			function setInnerWidth(elementJQuery, width)
			{
				var differenceWidth = (elementJQuery.outerWidth() - elementJQuery.width());
				
				elementJQuery.width(width - differenceWidth);
			}
			
		
			function setInnerHeight(elementJQuery, height)
			{
				var differenceheight = (elementJQuery.outerHeight() - elementJQuery.height());
				
				elementJQuery.height(height - differenceheight);
			}

			function buildValueDisplay()
			{
				// A container for the Display Value and DropDownButton. A container is required as the child elements
				// are floated
				var valueDisplayContainerHTML = "";
				if (settings.comboboxValueContainerClass)
				{
					valueDisplayContainerHTML = String.format("<div class='{0}' style='{1}' unselectable='on'></div>", settings.comboboxValueContainerClass, _valueDisplayContainerEnforcedStyle);
				}
				else
				{
					valueDisplayContainerHTML = String.format("<div style='{0}' unselectable='on'></div>", _valueDisplayContainerEnforcedStyle);
				}
				
				// Create the equivalent of the select 'textbox' where the current selected value is shown
				var valueDisplayHTML = "";
				if (settings.comboboxValueContentClass)
				{
					valueDisplayHTML = String.format("<div class='{0}' style='{1}' unselectable='on'></div>", settings.comboboxValueContentClass, _valueDisplayEnforcedStyle);
				}
				else
				{
					valueDisplayHTML = String.format("<div style='{0}' unselectable='on'></div>", _valueDisplayEnforcedStyle);
				}
				
				var dropdownButtonHTML = "";
				if (settings.comboboxDropDownButtonClass)
				{
					dropdownButtonHTML = String.format("<div class='{1}' style='{0}' unselectable='on'></div>",_dropDownButtonEnforcedStyle, settings.comboboxDropDownButtonClass);
				}
				else
				{
					dropdownButtonHTML = String.format("<div style='{0}' unselectable='on'>{1}</div>", (_dropDownButtonEnforcedStyle + _dropDownButtonDefaultStyle), _dropDownButtonDefaultCharacter);
				}
				
				_valueDisplayJQuery = jQuery(valueDisplayHTML);
				_dropDownButtonJQuery = jQuery(dropdownButtonHTML);
				_valueDisplayContainerJQuery = jQuery(valueDisplayContainerHTML);
				
				_valueDisplayContainerJQuery.appendTo(_containerJQuery);
				_valueDisplayJQuery.appendTo(_valueDisplayContainerJQuery);
				_dropDownButtonJQuery.appendTo(_valueDisplayContainerJQuery);
			
				setDropDownButtonState(0);
			}

			function buildDropDownItem(childJQuery)
			{
				var dataItemHTML = "";
				var dataItemClass = null;
				var dataItemText = "";
				var dataItemValue = null;
				var dataItemStyle = "";
				var dataItemType = "option";
				
				if (childJQuery.is('option'))
				{
					dataItemText = childJQuery.text();
					dataItemValue = childJQuery.val();
					
					if (settings.comboboxDropDownItemClass)
					{
						dataItemClass = settings.comboboxDropDownItemClass;
						dataItemStyle = _dropDownListItemEnforcedStyle;
					}
					else
					{
						dataItemStyle = (_dropDownListItemEnforcedStyle + _dropDownListItemDefaultStyle);
					}
					
					if (dataItemClass)
					{						
						dataItemHTML = String.format("<li style='{0}' class='{1}'>{2}</li>", dataItemStyle, dataItemClass, dataItemText);
					}
					else
					{
						dataItemHTML = String.format("<li style='{0}'>{1}</li>", dataItemStyle, dataItemText);
					}
					
				}
				else
				{
					dataItemText = childJQuery.attr('label');
					dataItemValue = childJQuery.attr('class');
					dataItemType = "optgroup";
					
					if (settings.comboboxDropDownGroupItemHeaderClass)
					{
						dataItemClass = settings.comboboxDropDownGroupItemHeaderClass;
						dataItemStyle = _dropDownListGroupItemHeaderEnforcedStyle;
					}
					else
					{
						dataItemStyle = (_dropDownListGroupItemHeaderEnforcedStyle + _dropDownListGroupItemHeaderDefaultStyle);
					}
					
					if (dataItemClass)
					{						
						dataItemHTML = String.format("<li><span style='{0}' class='{1}'>{2}</span></li>", dataItemStyle, dataItemClass, dataItemText);
					}
					else
					{
						dataItemHTML = String.format("<li><span style='{0}'>{1}</span></li>", dataItemStyle, dataItemText);
					}
				}
				
				var dataItemJQuery = jQuery(dataItemHTML);
				dataItemJQuery.css("display", "inline");
				dataItemJQuery[0].dataValue = dataItemValue;
				dataItemJQuery[0].dataType = dataItemType;
				dataItemJQuery[0].title = dataItemText;
				
				return dataItemJQuery;
			}

			function recursivelyBuildList(parentJQuery, childrenOptionsJQuery)
			{
				childrenOptionsJQuery.each(
					function()
					{
						var childJQuery = jQuery(this);
						var builtDropDownItemJQuery = buildDropDownItem(childJQuery);
						parentJQuery.append(builtDropDownItemJQuery);
						var offsetLeft = builtDropDownItemJQuery.offset().left;
						
						offsetLeft -= _dropDownListOffset.left;
						
						if (offsetLeft < 0)
						{
							offsetLeft = 0;
						}
						
						var width = (offsetLeft + builtDropDownItemJQuery.outerWidth());
						if (width > _maximumItemLength)
						{
							_maximumItemLength = width;
						}
						
						applyMultipleStyles(builtDropDownItemJQuery, _dropDownListItemEnforcedStyle);
						
						if (childJQuery.is('optgroup'))
						{
							var dataGroupItemHTML = "";
							if (settings.comboboxDropDownGroupItemContainerClass)
							{
								dataGroupItemHTML = String.format("<ul style='{0}' class='{1}'></ul>", _dropDownListGroupItemContainerEnforcedStyle, settings.comboboxDropDownGroupItemContainerClass);
							}
							else
							{
								dataGroupItemHTML = String.format("<ul style='{0}'></ul>", (_dropDownListGroupItemContainerEnforcedStyle + _dropDownListGroupItemContainerDefaultStyle));
							}
							
							var dataGroupItemJQuery = jQuery(dataGroupItemHTML);
							builtDropDownItemJQuery.append(dataGroupItemJQuery);
							
							// If not an option, then the child of a Select must be an optgroup element
							recursivelyBuildList(dataGroupItemJQuery, childJQuery.children());
						}
					});
			}
			

			function buildDropDownList()
			{
				var originalElementChildrenJQuery = _originalElementJQuery.children();
				_lastItemSelectedJQuery = null;
				_lastValue = null;

				if (_dropDownListJQuery)
				{
					// Clear out any existing children elements
					_dropDownListJQuery.empty();
				}
				else
				{
					var dropDownHTML = "";
					if (settings.comboboxDropDownClass)
					{
						dropDownHTML = String.format("<ul class='{0}' style='{1}'></ul>", settings.comboboxDropDownClass, _dropDownListEnforcedStyle);
					}
					else
					{
						dropDownHTML = String.format("<ul style='{0}'></ul>", (_dropDownListEnforcedStyle + _dropDownListDefaultStyle));
					}
					
					_dropDownListJQuery = jQuery(dropDownHTML);
					// Create the equivalent of the drop down list where the all the values are shown
					_dropDownListJQuery.appendTo(_containerJQuery);
					
					// Enable the Drop Down List to be able to receive focus and key events
					_dropDownListJQuery.attr("tabIndex", 0);
				}
				
				// Create the internal list of values if they exist
				if (originalElementChildrenJQuery.length > 0)
				{
					_maximumItemLength = 0;
					_dropDownListOffset = _dropDownListJQuery.offset();
						
					recursivelyBuildList(_dropDownListJQuery, originalElementChildrenJQuery);
				}
			}
					
			function applyMultipleStyles(elementJQuery, multipleCSSStyles)
			{
				var stylePairArray = multipleCSSStyles.split(";");
				if (stylePairArray.length > 0)
				{
					for (var stylePairArrayIndex = 0; stylePairArrayIndex < stylePairArray.length; stylePairArrayIndex++)
					{
						var stylePair = stylePairArray[stylePairArrayIndex];
						var splitStylePair = stylePair.split(":");
						
						elementJQuery.css(splitStylePair[0], splitStylePair[1]);
					}
				}
			}

			function setDropDownButtonState(state)
			{
				if (settings.comboboxDropDownButtonClass)
				{
					var width = _dropDownButtonJQuery.width();
					var offset = state * width;
					var background_positionCSS = String.format("-{0}px 0px", offset);
					_dropDownButtonJQuery.css("background-position", background_positionCSS);
				}
				else
				{
					var style = _dropDownButtonDefaultUnselectedStyle;
					
					if (state == 1)
					{
						style = _dropDownButtonDefaultSelectedStyle;
					}
					
					applyMultipleStyles(_dropDownButtonJQuery, style);
				}
			}

			function updateDropDownListWidth()
			{
				//Drop down list element
				var dropdownListWidth = _containerJQuery.outerWidth();
				if (dropdownListWidth < _maximumItemLength)
				{
					dropdownListWidth = _maximumItemLength;
				}
				
				_dropDownListJQuery.width(dropdownListWidth);
			}
			

			function positionDisplayValue()
			{
				var displayValueHeight = _valueDisplayJQuery.outerHeight();
				var displayContainerHeight = _valueDisplayContainerJQuery.height();
				var difference = ((displayContainerHeight - displayValueHeight) / 2);
				
				if (difference < 0)
				{
					difference = 0;
				}			
				_valueDisplayJQuery.css("top", difference);
			}

			function applyLayout()
			{

				_containerJQuery.addClass(_originalElementJQuery[0].className);
				var displayValueWidth = (_valueDisplayContainerJQuery.width() - _dropDownButtonJQuery.outerWidth());
				setInnerWidth(_valueDisplayJQuery, displayValueWidth);
				var dropDownButtonHeight = _dropDownButtonJQuery.outerHeight();
				setInnerHeight(_valueDisplayContainerJQuery, dropDownButtonHeight);
				
				_dropDownListJQuery.css("position", "absolute");
				_dropDownListJQuery.css("z-index", "20000");
				
				updateDropDownListWidth();
				
				// Position the drop down list correctly, taking the main display control border into consideration
				var currentLeftPosition = _dropDownListJQuery.offset().left;
				var leftPosition = (currentLeftPosition - (_containerJQuery.outerWidth() - _containerJQuery.width()));
				_dropDownListJQuery.css("left", leftPosition + 1);
				_dropDownListJQuery.hide();
				
			}

	
			function bindItemEvents()
			{
				//jQuery("*", _dropDownListJQuery).not("ul").not("span").not("[@dataType='optgroup']").each(
				jQuery("*", _dropDownListJQuery).not("ul").not("span").not("optgroup").each(
					function()
					{
						var itemJQuery = jQuery(this);
						itemJQuery.click(
							function(clickEvent)
							{
								// Stops the click event propagating to the Container and the Container.onClick firing
								clickEvent.stopPropagation();
								
								container_onItemClick(itemJQuery);
							});
						
						itemJQuery.mouseover(
							function()
							{
								container_onItemMouseOver(itemJQuery);
							});
							
						itemJQuery.mouseout(
							function()
							{
								container_onItemMouseOut(itemJQuery);
							});
					});			
			}


			function bindBlurEvent()
			{
				_dropDownListJQuery.blur(
					function(blurEvent)
					{
						blurEvent.stopPropagation();
						
						dropDownListJQuery_onBlur();
					});
			}
			

			function bindContainerClickEvent()
			{
				_containerJQuery.click(
					function(clickEvent)
					{
						container_onClick();
					});
			}

			function unbindContainerClickEvent()
			{
				_containerJQuery.unbind("click");
			}
						

			function bindEvents()
			{
				_containerJQuery.keydown(
					function(keyEvent)
					{
						keyEvent.preventDefault();container_onKeyDown(keyEvent)
					});
					
				bindContainerClickEvent();
					
				bindBlurEvent();
					
				bindItemEvents();
			}
						

			function setDisplayValue()
			{
				var valueHasChanged = false;
				var originalElement = _originalElementJQuery[0];
				
				if (originalElement.length > 0)
				{
					var selectedText = originalElement[originalElement.selectedIndex].text;
					_valueDisplayJQuery.text(selectedText);
					_valueDisplayJQuery.attr("title", selectedText);
					
					// Reposition the display value based on height of the element after the text has changed
					positionDisplayValue();
					
					if (_lastValue)
					{
						if (_lastValue != _originalElementJQuery.val())
						{
							valueHasChanged = true;
						}
					}
					
					_lastValue = _originalElementJQuery.val();
					
					//  If the selected value has changed since the last click, fire the onChange event
					if (valueHasChanged)
					{
						// Check if the onChange event is being consumed, otherwise it will be undefined
						if (_originalElementJQuery.combobox.onChange)
						{
							_originalElementJQuery.combobox.onChange();
						}
					}
					
					// If _lastItemSelectedJQuery has been set, remove the highlight from it, before setting it to the current
					// value
					if (_lastItemSelectedJQuery)
					{
						toggleItemHighlight(_lastItemSelectedJQuery, false);
					}
					
					// Find the DropDown Item Element that corresponds to the current value in the Select element
					_lastItemSelectedJQuery = jQuery("li[value='" + _lastValue + "']", _dropDownListJQuery);
					
					toggleItemHighlight(_lastItemSelectedJQuery, true);
				}
			}
			

			function toggleItemHighlight(elementJQuery, isHighlighted)
			{
				if (elementJQuery)
				{
					if (settings.comboboxDropDownItemHoverClass)
					{
						if (isHighlighted)
						{
							elementJQuery.addClass(settings.comboboxDropDownItemHoverClass);
						}
						else
						{
							elementJQuery.removeClass(settings.comboboxDropDownItemHoverClass);
						}
					}
					else
					{
						if (isHighlighted)
						{
							elementJQuery.css("background", "#000");
							elementJQuery.css("color", "#fff");
						}
						else
						{
							elementJQuery.css("background", "");
							elementJQuery.css("color", "");
						}
					}
				}
			}


			function buildContainer()
			{
				var containerHTML = "";
				if (settings.comboboxContainerClass)
				{
					containerHTML = String.format("<div class='{0}' style='{1}'></div>", settings.comboboxContainerClass, _containerEnforcedStyle);
				}
				else
				{
					containerHTML = String.format("<div style='{0}' style='{1}'></div>", _containerDefaultStyle, _containerEnforcedStyle);
				}
				_containerJQuery = jQuery(containerHTML);
				_originalElementJQuery.before(_containerJQuery);
				_containerJQuery.append(_originalElementJQuery);
				_originalElementJQuery.hide();
				
				// Allow the custom jquery.combobox be able to receive focus and key events
				_containerJQuery.attr("tabIndex", 0);
			}

			function initialiseControl()
			{
				buildContainer();
				
				buildValueDisplay();
				
				buildDropDownList();
				
				applyLayout();
				
				bindEvents();
				
				setDisplayValue();
			}

			function setDropDownListFocus()
			{
				_dropDownListJQuery.focus();
			}

			function setAndBindContainerFocus()
			{
				_containerJQuery.focus();
				bindContainerClickEvent();
			}
			

			function slideUp(newTop)
			{
				_dropDownListJQuery.animate(
					{
						height: "toggle",
						top: newTop
					},
					"fast",
					setDropDownListFocus);
			}
			

			function slideDown(newTop)
			{
				_dropDownListJQuery.animate(
					{
						height: "toggle",
						top: newTop
					},
					"fast",
					setAndBindContainerFocus);
			}

			function getDropDownListTop()
			{
				var comboboxTop = _containerJQuery.position().top;
				var dropdownListHeight = _dropDownListJQuery.outerHeight();
				var comboboxBottom = (comboboxTop + _containerJQuery.outerHeight());
				var windowScrollTop = jQuery(window).scrollTop();
				var windowHeight = jQuery(window).height();	
				var availableSpaceBelow = (windowHeight - (comboboxBottom - windowScrollTop));
				var dropdownListTop;

				// Set values to display dropdown list below combobox as default				
				dropdownListTop = comboboxBottom;
				_downdownListPositionIsInverted = false;

				// Check if there is enough space below to display the full height of the drop down list
				if (availableSpaceBelow < dropdownListHeight)
				{
					// There is no available space below the combobox to display the dropdown list
					// Check if there is available space above. If not, then display below as default
					if ((comboboxTop - windowScrollTop)> dropdownListHeight)
					{
						// There is space above
						dropdownListTop = (comboboxTop - dropdownListHeight);
						_downdownListPositionIsInverted = true;
					}
				}
				
				return dropdownListTop;
			}
			
					
			function toggleDropDownList(isShown)
			{
				if (isShown)
				{
					if (_dropDownListJQuery.is(":hidden"))
					{
						
						unbindContainerClickEvent();
						
						// When the DropDown list is shown, highlist the current value in the list
						toggleItemHighlight(_lastItemSelectedJQuery, true);
		
						setDropDownButtonState(1);
						
						var dropdownListTop = getDropDownListTop();
						_dropDownListJQuery.css("top", dropdownListTop);
						_dropDownListJQuery.css("left", _containerJQuery.offset().left);
						
						switch (settings.animationType)
						{
							case "slide":
								if (_downdownListPositionIsInverted)
								{
									var comboboxTop = _containerJQuery.position().top;
									var containerHeight = _containerJQuery.outerHeight();

									_dropDownListJQuery.css("top", (comboboxTop - containerHeight));

									slideUp(dropdownListTop);
								}
								else
								{
									_dropDownListJQuery.slideDown("fast", setDropDownListFocus);
								}
								break;
								
							case "fade":
								_dropDownListJQuery.fadeIn("fast", setDropDownListFocus);
								break;
								
							default:
								_dropDownListJQuery.show();
								setDropDownListFocus();
						}
					}
				}
				else
				{
					if (_dropDownListJQuery.is(":visible"))
					{
						setDropDownButtonState(0);
						
						switch (settings.animationType)
						{
							case "slide":
								if (_downdownListPositionIsInverted)
								{
									comboboxTop = _containerJQuery.position().top;
									dropdownListHeight = _dropDownListJQuery.height();

									slideDown(comboboxTop - _containerJQuery.outerHeight());
								}
								else
								{
									_dropDownListJQuery.slideUp("fast", setAndBindContainerFocus)
								}
								break;
								
							case "fade":
								_dropDownListJQuery.fadeOut("fast", setAndBindContainerFocus);
								break;
								
							default:
								_dropDownListJQuery.hide();
								setAndBindContainerFocus();
						}
					}
				}
			}

				
			function selectValue(subSelector, char)
			{
				var originalElement = _originalElementJQuery[0];
				var currentIndex = originalElement.selectedIndex;
				var newIndex = -1;
				// {select}.length returns the array size of the options. Does not count optgroup elements
				var optionCountZeroBased = originalElement.length - 1;
				switch (subSelector)
				{
					case ":char":
						currentchar = $(originalElement.options[currentIndex]).text()[0];
						if (char == currentchar) {
							newIndex = currentIndex 
						} else {
							var item = jQuery("[value^='" + char + "']", originalElement);
							if (item.length) newIndex = item[0].index;
						}
						
						if (currentIndex == newIndex) {
							var nextEl = originalElement.options[newIndex + 1];
							var nextText = $(nextEl).text();
							if (nextText[0] == char) {
								newIndex = newIndex + 1;
							} else {
								var item = jQuery("[value^='" + char + "']", originalElement);
								if (item.length) newIndex = item[0].index;
							}
						}
						break;
					case ":next":
						newIndex = currentIndex + 1;
						if (newIndex > optionCountZeroBased)
						{
							newIndex = optionCountZeroBased;
						}
						break;
					
					case ":previous":
						newIndex = currentIndex - 1;
						if (newIndex < 0)
						{
							newIndex = 0;
						}

						break;
						
					case ":first":
						newIndex = 0;
						
						break;
						
					case ":last":
						newIndex = optionCountZeroBased;
						
						break;
				}

				originalElement.selectedIndex = newIndex;
				setDisplayValue();
			}
			

			function isDropDownVisible()
			{
				return _dropDownListJQuery.is(":visible");
			}
			

			_originalElementJQuery.combobox.updateSelection = 
				function()
				{
					setDisplayValue();
				};
				

			_originalElementJQuery.combobox.update =
				function()
				{
					buildDropDownList();
					updateDropDownListWidth();
					bindItemEvents();
					setDisplayValue();
				};
			
			
			function container_onClick()
			{
				if (_dropDownListJQuery.is(":hidden"))
				{
					toggleDropDownList(true);
				}
				else
				{
					toggleDropDownList(false);
				}
			}
			
			function dropDownListJQuery_onBlur()
			{
				if (_dropDownListJQuery.is(":visible"))
				{
					toggleDropDownList(false);
				}
			}
			
			function container_onItemClick(itemJQuery)
			{
				_originalElementJQuery.val(itemJQuery[0].dataValue);
				
				setDisplayValue();
				
				toggleDropDownList(false);
			}
			
			function container_onItemMouseOver(itemJQuery)
			{

				toggleItemHighlight(_lastItemSelectedJQuery, false);
				
				toggleItemHighlight(itemJQuery, true);
			}
			
			function container_onItemMouseOut(itemJQuery)
			{
				toggleItemHighlight(itemJQuery, false);
			}
			
			function container_onKeyDown(keyEvent)
			{
				
				switch (keyEvent.which)
				{
					case 33:
						//Page Up
					case 36:
						//Home
						selectValue(":first");
						break;
					
					case 34:
						//Page Down
					case 35:
						//End
						selectValue(":last");
						break;

					case 37:
						//Left
						selectValue(":previous");
						break;
						
					case 38:
						//Up
						if (keyEvent.altKey)
						{
							// alt-up
							// If DDL is hidden, then it is shown and vice-versa
							toggleDropDownList(!(isDropDownVisible()));
						}
						else
						{
							selectValue(":previous");
						}
						break;

					case 39:
						//Right
						selectValue(":next");
						break;
						
					case 40:
						//Down
						if (keyEvent.altKey)
						{
							// alt-down
							// If DDL is hidden, then it is shown and vice-versa
							toggleDropDownList(!(isDropDownVisible()));
						}
						else
						{
							selectValue(":next");
						}
						break;
						
					case 27:
					case 13:
						// Escape
						toggleDropDownList(false);
						break;

					case 9:

						_dropDownListJQuery.blur();
						
						// This is required in Internet Explorer as the blur() order is different
						$(window)[0].focus();
						
						break;
					default:
						if ((keyEvent.which > 64 && keyEvent.which < 91)) { // || keyEvent.which == 32 ||(keyEvent.which > 47 && keyEvent.which < 58)) {
						//console.dir(String.fromCharCode(keyEvent.which));
							selectValue(":char", String.fromCharCode(keyEvent.which));
						}
						break;
				}
				
				keyEvent.cancelBubble = true;
			}
			
			//#endregion private events
			
			initialiseControl();
		});
}


