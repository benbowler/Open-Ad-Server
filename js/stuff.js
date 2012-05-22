$.fn.getNonColSpanIndex = function() {
    if(! $(this).is('td'))
        return -1;

    var allCells = this.parent('tr').children();
    var normalIndex = allCells.index(this);
    var nonColSpanIndex = 0;

    allCells.each(
        function(i, item)
        {
            if(i == normalIndex)
                return false;

            var colspan = $(this).attr('colspan');
            colspan = colspan ? parseInt(colspan) : 1;
            nonColSpanIndex += colspan;
        }
    );

    return nonColSpanIndex;
};


var fixCellIndexes = function(table) {
	var rows = table.rows;
	var len = rows.length;
	var matrix = [];
	for ( var i = 0; i < len; i++ )
	{
		var cells = rows[i].cells;
		var clen = cells.length;
		for ( var j = 0; j < clen; j++ )
		{
			var c = cells[j];
			var rowSpan = c.rowSpan || 1;
			var colSpan = c.colSpan || 1;
			var firstAvailCol = -1;
			if ( !matrix[i] )
			{ 
				matrix[i] = []; 
			}
			var m = matrix[i];
			// Find first available column in the first row
			while ( m[++firstAvailCol] ) {}
			c.realIndex = firstAvailCol;
			for ( var k = i; k < i + rowSpan; k++ )
			{
				if ( !matrix[k] )
				{ 
					matrix[k] = []; 
				}
				var matrixrow = matrix[k];
				for ( var l = firstAvailCol; l < firstAvailCol + colSpan; l++ )
				{
					matrixrow[l] = 1;
				}
			}
		}
	}
};


// Get URL Params
function gup(name) {
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var tmpURL = window.location.href;
	var results = regex.exec( tmpURL );
	if( results == null )
		return "";
	else
		return results[1];
}

function tableColumns(tbl,context) {
	changeSelectAllHandle('columns-container-' + tbl);
	$('input[type=checkbox]:gt(1)', '#columns-container-' + tbl).click(function(){
		changeSelectAllHandle('columns-container-' + tbl);
	});
	$('#columns-container-' + tbl + ' input.disabled')
		.parent().parent()
		.find('label').css('color','#888');
}


function TextMetrixWidth(el) {
	var Styles = Array('font-size','font-style', 'font-weight', 'font-family','line-height');
	for (var i=0; i<Styles.length; i++) {
		$('#textmetrixdiv').css(Styles[i], $(el).css(Styles[i]));
	}
	parent.document.getElementById('textmetrixdiv').innerHTML =  $(el).attr('value');
	return parent.document.getElementById('textmetrixdiv').offsetWidth;
}


$.fn.extend({
	guiDisable: function() {
		if ($(this).hasClass('guibutton')) $(this).attr('disabled', 'disabled').addClass('disabled');
	},
	guiEnable: function() {
		if ($(this).hasClass('guibutton')) $(this).attr('disabled', '').removeClass('disabled');
	},
	guiSetSelectedIndex: function(index){
		if ($(this).hasClass('guibutton'))  {
			// not implemented yet
		}
	},
	guiSetSelectedByValue: function(value){
		$('> u > i > a', this).removeClass('selected');
		$(this).each(function(){
			var selectedEl = $('> u > i > a[rel="'+ value +'"]', $(this));
			$('>b>b>b',this)[0]['oldOnClick'] = selectedEl[0].onclick;
			selectedEl.addClass('selected');
		})
	}
});

function bindOnClick() {
	return function(event) {
		 if($(this).closest('a,span').hasClass('disabled')) return false;
		 this['oldOnClick'].call(this, event || window.event);
		 return false;
	 };
}

function replaceFormButtons(context) {
	
	$('.guibutton[disabled]', context).addClass('disabled');
	$('.guibutton', context).each(function(i){
		if ($(this).hasClass('noremap') || $(this).hasClass('thickbox')) return false;
		var el = $('>b>b>b',this);
		if (typeof this.onclick == 'function') {
         if ('undefined' == typeof(el[0]['oldOnClick'])) {
			   el[0]['oldOnClick'] = this.onclick;
			   this.onclick = '';
			   el.bind('click', bindOnClick());
         }
		}
	});
	
	$('.guibutton', context)
		.hover(function(e){
			if ((e.target.tagName == 'SPAN' || e.target.tagName == 'A' || e.target.tagName == 'B') && (!$(this).hasClass('disabled')))
				$(this).addClass('over');
		}, function(){
			$(this).removeClass('over').removeClass('click');
		})
		.mouseup(function(){
			$(this).removeClass('click');
		})
		.mousedown(function(e){
			if (e.target.tagName == 'SPAN' || e.target.tagName == 'A' || e.target.tagName == 'B' )
				$(this).addClass('click');
		});
		

	$('.guibutton > u', context)
		.click(function(e){
			if ($('> i:visible', this).parents('.guibutton').length > 0) {
			} else {
				$('.guibutton > u > i').hide();	
				$('.guibutton > u').removeClass('click');
			}
			el = $('>i', this);
			el.css('margin-left', '-' + (el.width()-16) + 'px').toggle();
			if (el.length == 1) {
				$(this).addClass('click');	
			} else {
				$(this).removeClass('click');
			}
			e.stopPropagation();
		})
		.hover(function(e){
			$(this).addClass('over');
		}, function(e){
			$(this).removeClass('over');
		});
		
		$(document).click(function(e){
			$('.guibutton > u > i').hide();
			$('.guibutton > u').removeClass('click');
		});
}


function replaceErrors(context) {
	context = context || document;
	$('#error_message_div', context).each(function(){
		if ($('div.closeButton', this).length == 0) {
		var closeButton = document.createElement('div');
		$(closeButton)
			.addClass('closeButton ico ico-delete')
			.click(function(){
				$(this).parent().hide();
				$('.invalidfield', context).parent().find('p').hide();
				$('.invalidfield', context).removeClass('invalidfield');
			});
		$(this).prepend(closeButton);
		}
	});

	$('.invalidfield', context)
		.parent()
		.find('p')
		.each(function(){
			$(this).addClass('errorP');
			$(this).parent().append(this);
		});
	
	$('select.invalidfield', context).filter(':not([multiline])').each(function(){
		el = $(this);
	});
}


function parseTables(context) {
	if ($('.xTable', context).length == 0) return false;
	
	$('td.chkbox', context)
		.parent(':not(.th, .pagetotal, .alltotal)')
		.addClass('cursor-hand');
	
	$('td.chkbox:has(:checkbox:checked)', context)
		.parent().addClass('selected');
	
	$('.xTable>tbody>tr:not(.th)', context)
		.click(function(event) {
			if ($('>td.chkbox:has(:checkbox)', this).length > 0) {
				if ($(event.target.offsetParent).hasClass('xTable') || event.target.type == 'checkbox') {
					if (event.target.tagName !='TD' && (event.target.type != 'checkbox' || !($(event.target).parent().hasClass('chkbox')))) {
					} else {
						if (!$(event.target).parents().filter('tr').find('.chkbox :checkbox:disabled').length) {
							$(this).toggleClass('selected');
							if (event.target.type !== 'checkbox') {
								$(':checkbox:eq(0)', this).attr('checked', function() {
									return !this.checked;
								});
							}
						}
					}
				}
			}	
		});

	$('.filterBox', context).each(function(){
		$el = $(this);
		if ($('div.filterBoxMark', $el.parent()).length == 0) {
		    var mark = document.createElement('div');
		    $(mark).addClass('filterBoxMark');
		    $el.after($(mark));
		}
	});


	/* ***************** COLUMN SELECTION ****************** */

	var className = 'curColumn';
	
	$('.xTable > tbody > tr.th > td:not(.simpleTitle, .nohl)[onclick]', context).addClass('cursor-hand').hover(function() {
		var index = $.browser.safari ? $(this).parent().children().index(this) : this.cellIndex;
		var selector = '> tr:not(.th) > td:not(.nodata):nth-child('+ (index + 1) +')'; 
		$(selector , $(this).parent().parent()).addClass(className);
	},
	function() {
		var index = $.browser.safari ? $(this).parent().children().index(this) : this.cellIndex;
		var selector = '> tr:not(.th) > td:not(.nodata):nth-child('+ (index + 1) +')'; 
		$(selector , $(this).parent().parent()).removeClass(className);
	});

	
	
	$('.smplTable tr:odd').addClass('odd');

	$('.smplTable tr').hover(function(){
		if ($(this).hasClass('odd'))
			$(this).removeClass('odd').addClass('oddMarker');
		$(this).addClass('simpleTableOver');
	}, function(){
		if ($(this).hasClass('oddMarker'))
			$(this).removeClass('oddMarker').addClass('odd');
		$(this).removeClass('simpleTableOver');
	})

	$('.smplTable tr:last td').css('border-bottom', 'none');
	
	$('.xTable tr td.border_left').prev().css('border-right', 'none');
	
	$('.xTable>tbody>tr:first-child, .xTable>tbody>tr>td:first-child').addClass('first');
	$('.xTable>tbody>tr:last-child, .xTable>tbody>tr>td:last-child').addClass('last');
	
}

/* ***************** /COLUMN SELECTION ****************** */

function messageReaded(el){
	$(el).removeClass('unread');
	$(el).parent().parent().removeClass('new');
	$("td:nth-child(2)",$(el).parent().parent()).html("<div class=\"ico-center ico-readed\" title=\"Read message\"/>");
}

function jFrameGUI(id){
	context = $('#' + id).get(0);
	replaceFormButtons(context);
	replaceErrors(context);
	parseTables(context);
}

function browserCSS(){
	var body = $(document.body);
	if ($.browser.msie) $(body).addClass('msie');
	if ($.browser.safari) $(body).addClass('safari');
	if ($.browser.mozilla) $(body).addClass('mozilla');
	if ($.browser.opera) $(body).addClass('opera');
}

function limitation() {
	$('input,textarea').parent().find('.limitation').parent().find('input,textarea')
	
	.filter(':not([readonly])')
	.each(function(){
		var transerentKeyCodes = [34,33,35,36,37,38,39,40,46];
		var transerentKeys = [8,13];
		var padWidth = 40;
		var max = parseInt($(this).attr('maxlength'));
		if (max && max > 0) {
			var limCounter = document.createElement('span');
			var text = (this.type == 'text') ? $(this).val(): this.value;
			var length = text.length;


			$(this).after(limCounter);
			$(limCounter).addClass('limCounter').html(length + "<br>" + max);
			if (this.type == 'text') {
				$(limCounter).addClass('limInputBg');
				
			} else {
				$(limCounter).addClass('limTextareaBg');
				$(this).parent().find('.limitation').css('margin-top', '5px');
			}

		
			if(this.id == 'bgcolor'){
			   $(this).parent().find('.limCounter').html(7 + "<br>" + max);
			}
						
			
			$(this)
				.css('position','relative')
				.after(limCounter)
				.keyup(function(e){
					var text = (this.type == 'text') ? $(this).val(): this.value;
					var length = text.length;
					$(this).parent().find('.limCounter').html(length + "<br>" + max);
					if (length > max) {
						if (this.type == 'text') {
							$(this).val(text.substr(0,max));
						} else {
							this.value = text.substr(0,max);
						}
					}
				})
				.keypress(function(e) {
					var sel = getSelection(this);
					var selected = sel.end - sel.start;
					var key = e.which;
					var keyCode = e.keyCode;
					var length = (this.type == 'text') ? $(this).val().length : this.value.length;
					var text = (this.type == 'text') ? $(this).val(): this.value;
					
					if (!selected && length >= max && !(($.inArray(key,transerentKeys)!=-1) || ($.inArray(keyCode,transerentKeyCodes)!=-1))) {
						e.preventDefault();
					}
				})
			.focus(function() {
			
					$(this)
						.parent()
						.find('.limitation')
			        	.show();
				})
				.blur(function() {
					$(this)
						.parent()
						.find('.limitation')
						.hide();
				})
				
		}
	});
}


$(function(){
	// textmetric
	var x = parent.document.createElement('div');
	$(x).css('position','absolute').css('left','-10000px').css('top','-10000px').css('height','auto').css('width','auto').attr('id', 'textmetrixdiv');
	parent.document.body.appendChild(x);
	
	browserCSS();
	limitation();
	replaceFormButtons(document.body);
	replaceErrors(document.body);
	parseTables(document.body);
	
	// css issues for buttons
	
	$('.button')
		.hover(function(){
			$(this).addClass('buttonHover');
		}, function(){
			$(this).removeClass('buttonHover');
			$(this).removeClass('buttonPress');
		})
		.mousedown(function(){
			$(this).addClass('buttonPress');
		})
		.mouseup(function(){
			$(this).removeClass('buttonPress');
		})

	$('input[disabled], textarea[disabled]').each(function(){ 
		$(this).addClass('disabled');
	});

	$('input[readonly].text').addClass('readonly').removeClass('text');
	$('.alertDismiss').text('').hide().remove();
	$('.xTable a.unread').parent().parent().addClass('new');

	var currentFocus = null;
	$(':input').focus( function() {
		currentFocus = this;
	}).blur( function() {
		currentFocus = null;
	});

})


function go(url){
   top.location.href = url;
   return false;
}

$(function(){

	$('#help_link').click(function(){
      url = document.location.href.replace(base_url, '');
      url = base_url + 'helps/' + url;
      tb_show("Help", url + "?KeepThis=true&TB_iframe=true&height=400&width=500", false);
      return false;
	});

	jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }

};
});

function truncate(text, length, ellipsis) {
	var length = length || 30;
	var ellipsis = ellipsis || '...';
	if (text.length < length) return text;
	return text.substr(0, length) + ellipsis;
}

$(function() {

	$('span.truncate').each(function(){
		var title = $(this).attr('title');
		var max_len = $(this).attr('max_len');
		$(this).html(truncate(title,max_len));
	});
});

function getSelection(el){
	var e = el.jquery ? el[0] : el;
	return (

		/* mozilla / dom 3.0 */
		('selectionStart' in e && function() {
			var l = e.selectionEnd - e.selectionStart;
			return { start: e.selectionStart, end: e.selectionEnd, length: l, text: e.value.substr(e.selectionStart, l) };
		}) ||
	
		/* exploder */
		(document.selection && function() {
	
			e.focus();
	
			var r = document.selection.createRange();
			if (r == null) {
				return { start: 0, end: e.value.length, length: 0 }
			}
	
			var re = e.createTextRange();
			var rc = re.duplicate();
			re.moveToBookmark(r.getBookmark());
			rc.setEndPoint('EndToStart', re);
	
			return { start: rc.text.length, end: rc.text.length + r.text.length, length: r.text.length, text: r.text };
		}) ||
	
		/* browser not supported */
		function() {
			return { start: 0, end: e.value.length, length: 0 };
		}
	
	)();
}

function toggleContainer(id) {
	$('#' + id).toggle();
	$('#' + id).css('margin-top', '21px');
/*	$('#' + id).css('margin-left', '-' + ($('#' + id).width() - 150 + 24 ) + 'px');*/
	$('#' + id).css('right', '0');
	return false;
}

function toggleAdsBlock(id, mode) {
   /*var x = $('#bulk-editor-container:visible');
	if (x.length == 1) return false;
*/
   if ('show' == mode) {
	   
      $('#' + id).addClass('grayed');
      $('.ad-edit-handle', '#' + id).show();
   } else {
      $('#' + id).removeClass('grayed');
      $('.ad-edit-handle', '#' + id).hide();
   }
   return false;
}

function showBulkEditor(code) {
   // Start Loading
   startBulkLoading();
   // Show Container
   showBulkContainer(code);
   jQuery.ajaxSetup({
      async: false,
      cache: false
   });
   
    
   jQuery.get(site_url + 'index.php/advertiser/manage_ads_ads/get_ad_info/' + code, function(data) {
      if(!checkAjaxLogin(data)) {
         eval('data = ' + data);
         // Hide unnecessary fileds
         if ('text' == data.ad_type) {
            jQuery('.text_only', '#bulk-editor-container').show();
         } else {
            jQuery('.text_only', '#bulk-editor-container').hide();
         }
         // Fill fields
         jQuery('#bulk_ad_code', '#bulk-editor-container').val(code);
         jQuery('#bulk_ad_type', '#bulk-editor-container').val(data.ad_type);
         jQuery('#bulk_ad_title', '#bulk-editor-container').val(data.title);
         jQuery('#bulk_ad_description1', '#bulk-editor-container').val(data.description1);
         jQuery('#bulk_ad_description2', '#bulk-editor-container').val(data.description2);
         jQuery('#bulk_ad_display_url', '#bulk-editor-container').val(data.display_url);
         jQuery('#bulk_ad_destination_url', '#bulk-editor-container').val(data.destination_url);
         jQuery('#bulk_ad_destination_protocol', '#bulk-editor-container').val(data.destination_protocol);
         // Show preview
         UpdateTextPreview();
         // Stop Loading
         stopBulkLoading();
      }
   })
   jQuery.ajaxSetup({
      async: true,
      cache: true
   });
   return false;
}

function startBulkLoading() {
   jQuery('#error_message_div', '#bulk-editor-container').hide();
   jQuery('.errorP', '#bulk-editor-container').hide();
   jQuery('.invalidfield', '#bulk-editor-container').removeClass('invalidfield');
   jQuery('#bulk-editor-container .filterBox').addClass('loading-big').find('.table').css('visibility', 'hidden');
}

function stopBulkLoading() {
   jQuery('#bulk-editor-container .filterBox').removeClass('loading-big').find('.table').css('visibility', 'visible');
}

var lastcode = ''
function showBulkContainer(code) {
		
	if (code == lastcode) {
		$('#bulk-editor-container').show();
	} else {
		inp = $('input[value='+code+']').before($('#bulk-editor-container'));
		$('#bulk-editor-container').css('margin-left', '40px').css('margin-top', '-40px').css('text-align', 'left').show();
	}
	lastcode = code;
}

function hideBulkContainer() {
   jQuery('#bulk-editor-container').hide();
   jQuery('#error_message_div', '#bulk-editor-container').hide();
   jQuery('.errorP', '#bulk-editor-container').hide();
   jQuery('.invalidfield', '#bulk-editor-container').removeClass('invalidfield');
}

function reloadDinamicFrame(tab) {
   jQuery('#dinamic').loadJFrame(jQuery('#dinamic').attr('src'), function(){tabs_click(tab)});
}

function onDisplayUrlChange(prefix) {
   if($('#'+prefix+'_display_url').val() != '') {
      if($('#'+prefix+'_destination_url').val() == '') {
         $('#'+prefix+'_destination_url').val($('#'+prefix+'_display_url').val());
         $('#'+prefix+'_destination_url').trigger('onkeyup');
      }
   }
   
}

/**
* IE hacks
*/
if(!Array.indexOf) {
  Array.prototype.indexOf = function(obj) {
     for (var i = 0; i < this.length; i++) {
        if (this[i] == obj) {
           return i;
        }
     }
     return -1;
  }
}

Array.prototype.remove = function(from, to) {
   var rest = this.slice((to || from) + 1 || this.length);
   this.length = from < 0 ? this.length + from : from;
   return this.push.apply(this, rest);
};


function select_all(table, obj) {
	if (obj.checked) {
	   $('> tr[id] > td.chkbox > input:checkbox:enabled', $(obj).parent().parent().parent())
	   	.attr('checked', 'checked')
	   	.parent()
	   	.parent()
	   	.addClass('selected');
	   
   } else {
	   $('> tr[id] > td.chkbox > input:checkbox:enabled', $(obj).parent().parent().parent())
	   	.attr('checked', '')
	   	.parent()
	   	.parent()
	   	.removeClass('selected');
   }
   return true;
}

/**
 *  Popup Editors
 */

function hookDocumentClick(popup_id, caller_class, hide_effect) {
	$(document).click(function(e){
	    if (($(e.target).parents().filter('#'+popup_id+':visible').length != 1) && 
	    	  ($(e.target).parents().filter('#'+popup_id).length != 1)) {
	    	   if ($(e.target).hasClass(caller_class)) {
	    		    $(e.target).removeClass(caller_class);
	    		    return;
			   } else {
			    	  if ($('#'+popup_id+':visible').length > 0) {
			    		  hide_effect = hide_effect || undefined;
			    		   if (hide_effect) {    
			    		        $('#' + popup_id).fadeOut(hide_effect);
			    		    } else {
			    		       $('#' + popup_id).hide();
			    		    }
			        }
			   }
	    }
});	
}

function showContainer(popup_id, caller, effect) {


	effect = effect || undefined;
	$('#' + popup_id).hide();
	//get the position of the placeholder element  
	var pos = $(caller).offset();    
	
	var eWidth = $(caller).outerWidth();
	var mWidth = $('#'+popup_id).outerWidth();
	var left = (pos.left + eWidth - mWidth) + "px";
	var top = pos.top + "px";
    
	  //show the menu directly over the caller
	$('#' + popup_id).css({ 
		position: 'absolute',
		zIndex: 5000,
		left: left, 
		top: top
	});

	if (effect) {	  
		$('#' + popup_id).fadeIn(effect);
	} else {
		$('#' + popup_id).show();
	}
	return false;
}

function hideContainer(popup_id, effect) {
	effect = effect || undefined;
	if (effect) {    
        $('#' + popup_id).fadeOut(effect);
    } else {
       $('#' + popup_id).hide();
    }
    return false;
}

function createMail(code, role) {
   codeForm = '<form id="send_mail_form" method="post" action="' + site_url + 'admin/mail/index/' + role + '">';
   codeForm += '<input type="hidden" name="post_mail_targets" value="' + code + '" />';
   codeForm += '</form>';
   $('body').append(codeForm);
   $('#send_mail_form').submit();
   return false;
}



/* ===============================================ckfinder.js==============================================================*/
var CKFinder = function( basePath, width, height, selectFunction )
{
	this.BasePath = basePath || CKFinder.DEFAULT_BASEPATH ;
	this.Width	= width || '100%' ;
	this.Height	= height || 400 ;
	this.SelectFunction = selectFunction || null ;
	this.SelectFunctionData = null ;
	this.SelectThumbnailFunction = selectFunction || null ;
	this.SelectThumbnailFunctionData = null ;
	this.DisableThumbnailSelection = false ;
	this.ClassName = null || 'CKFinderFrame' ;
	this.StartupPath = null ;
	this.StartupFolderExpanded = false ;
	this.RememberLastFolder = true ;
	this.Id = null ;
	this.ConnectorLanguage = 'php' ;
}

CKFinder.DEFAULT_BASEPATH = '/ckfinder/' ;
CKFinder.prototype = {
	Create : function()
	{
		document.write( this.CreateHtml() ) ;
	},
	CreateHtml : function()
	{
		var className = this.ClassName ;
		if ( className && className.length > 0 )
			className = ' class="' + className + '"' ;

		var id = this.Id ;
		if ( id && id.length > 0 )
			id = ' id="' + id + '"' ;
			
		return '<iframe src="' + this._BuildUrl() + '" width="' + this.Width + '" ' +
			'height="' + this.Height + '"' + className + id + ' frameborder="0" scrolling="no"></iframe>' ;
	},
	Popup : function( width, height )
	{
		width = width || '80%' ;
		height = height || '70%' ;

		if ( typeof width == 'string' && width.length > 1 && width.substr( width.length - 1, 1 ) == '%' )
			width = parseInt( window.screen.width * parseInt( width ) / 100 ) ;

		if ( typeof height == 'string' && height.length > 1 && height.substr( height.length - 1, 1 ) == '%' )
			height = parseInt( window.screen.height * parseInt( height ) / 100 ) ;

		if ( width < 200 )
			width = 200 ;

		if ( height < 200 )
			height = 200 ;

		var top = parseInt( ( window.screen.height - height ) / 2 ) ;
		var left = parseInt( ( window.screen.width  - width ) / 2 ) ;

		var options = 'location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=yes,alwaysRaised=yes,resizable=yes' +
			',width='  + width +
			',height=' + height +
			',top='  + top +
			',left=' + left ;
		var popupWindow = window.open( '', 'CKFinderPopup', options, true ) ;
		if ( !popupWindow )
			return false ;
		var url = this._BuildUrl().replace(/&amp;/g, '&');
		try
		{
			popupWindow.moveTo( left, top ) ;
			popupWindow.resizeTo( width, height ) ;
			popupWindow.focus() ;
			popupWindow.location.href = url ;
		}
		catch (e)
		{
			popupWindow = window.open( url, 'CKFinderPopup', options, true ) ;
		}

		return true ;
	},

	_BuildUrl : function( url )
	{
		var url = url || this.BasePath ;
		var qs = "" ;

		if ( !url || url.length == 0 )
			url = CKFinder.DEFAULT_BASEPATH ;

		if ( url.substr( url.length - 1, 1 ) != '/' )
			url = url + '/' ;

		url += 'ckfinder.html' ;

		if ( this.SelectFunction )
		{
			var functionName = this.SelectFunction ;
			if ( typeof functionName == 'function' )
				functionName = functionName.toString().match( /function ([^(]+)/ )[1] ;

			qs += '?action=js&amp;func=' + functionName ;
		}

		if ( this.SelectFunctionData )
		{
			qs += qs ? '&amp;' : '?' ;
			qs += 'data=' + encodeURIComponent( this.SelectFunctionData ) ;
		}

		if ( this.DisableThumbnailSelection )
		{
			qs += qs ? "&amp;" : "?" ;
			qs += 'dts=1' ;
		}
		else if ( this.SelectThumbnailFunction || this.SelectFunction )
		{
			var functionName = this.SelectThumbnailFunction || this.SelectFunction ;
			if ( typeof functionName == 'function' )
				functionName = functionName.toString().match( /function ([^(]+)/ )[1] ;

			qs += qs ? "&amp;" : "?" ;
			qs += 'thumbFunc=' + functionName ;
			
			if ( this.SelectThumbnailFunctionData )
				qs += '&amp;tdata=' + encodeURIComponent( this.SelectThumbnailFunctionData ) ;
			else if ( !this.SelectThumbnailFunction && this.SelectFunctionData )
				qs += '&amp;tdata=' + encodeURIComponent( this.SelectFunctionData ) ;
		}

		if ( this.StartupPath )
		{
			qs += qs ? "&amp;" : "?" ;
			qs += "start=" + encodeURIComponent( this.StartupPath + ( this.StartupFolderExpanded ? ':1' : ':0' ) ) ;
		}

		if ( !this.RememberLastFolder )
		{
			qs += qs ? "&amp;" : "?" ;
			qs += "rlf=0" ;
		}

		if ( this.Id )
		{
			qs += qs ? "&amp;" : "?" ;
			qs += "id=" + encodeURIComponent( this.Id ) ;
		}
		
		return url + qs ;
	}

};
CKFinder.Create = function( basePath, width, height, selectFunction )
{
	var ckfinder ;
	
	if ( basePath != null && typeof( basePath ) == 'object' )
	{
		ckfinder = new CKFinder( ) ;
		for ( var _property in basePath )
			ckfinder[_property] = basePath[_property] ;
	}
	else
		ckfinder = new CKFinder( basePath, width, height, selectFunction ) ;

	ckfinder.Create() ;
}
CKFinder.Popup = function( basePath, width, height, selectFunction )
{
	var ckfinder ;
	
	if ( basePath != null && typeof( basePath ) == 'object' )
	{
		ckfinder = new CKFinder( ) ;
		for ( var _property in basePath )
			ckfinder[_property] = basePath[_property] ;
	}
	else
		ckfinder = new CKFinder( basePath, width, height, selectFunction ) ;

	ckfinder.Popup( width, height ) ;
}

CKFinder.SetupFCKeditor = function( editorObj, basePath, imageType, flashType )
{
	var ckfinder ;

	if ( basePath != null && typeof( basePath ) == 'object' )
	{
		ckfinder = new CKFinder( ) ;
		for ( var _property in basePath )
		{
			ckfinder[_property] = basePath[_property] ;
			
			if ( _property == 'Width' )
			{
				var width = ckfinder[_property] || 800 ;
				if ( typeof width == 'string' && width.length > 1 && width.substr( width.length - 1, 1 ) == '%' )
					width = parseInt( window.screen.width * parseInt( width ) / 100 ) ;

				editorObj.Config['LinkBrowserWindowWidth'] = width ;
				editorObj.Config['ImageBrowserWindowWidth'] = width ;
				editorObj.Config['FlashBrowserWindowWidth'] = width ;
			}
			else if ( _property == 'Height' )
			{
				var height = ckfinder[_property] || 600 ;
				if ( typeof height == 'string' && height.length > 1 && height.substr( height.length - 1, 1 ) == '%' )
					height = parseInt( window.screen.height * parseInt( height ) / 100 ) ;

				editorObj.Config['LinkBrowserWindowHeight'] = height ;
				editorObj.Config['ImageBrowserWindowHeight'] = height ;
				editorObj.Config['FlashBrowserWindowHeight'] = height ;
			}
		}
	}
	else
		ckfinder = new CKFinder( basePath ) ;

	var url = ckfinder.BasePath ;

	if ( url.substr( 0, 1 ) != '/' )
		url = document.location.pathname.substring( 0, document.location.pathname.lastIndexOf('/') + 1 ) + url ;

	url = ckfinder._BuildUrl( url ) ;
	var qs = ( url.indexOf( "?" ) !== -1 ) ? "&amp;" : "?" ;

	editorObj.Config['LinkBrowserURL'] = url ;
	editorObj.Config['ImageBrowserURL'] = url + qs + 'type=' + ( imageType || 'Images' ) ;
	editorObj.Config['FlashBrowserURL'] = url + qs + 'type=' + ( flashType || 'Flash' ) ;

	var dir = url.substring(0, 1 + url.lastIndexOf("/"));
	editorObj.Config['LinkUploadURL'] = dir + "core/connector/" + ckfinder.ConnectorLanguage + "/connector." 
		+ ckfinder.ConnectorLanguage + "?command=QuickUpload&type=Files" ;
	editorObj.Config['ImageUploadURL'] = dir + "core/connector/" + ckfinder.ConnectorLanguage + "/connector." 
		+ ckfinder.ConnectorLanguage + "?command=QuickUpload&type=" + ( imageType || 'Images' ) ;
	editorObj.Config['FlashUploadURL'] = dir + "core/connector/" + ckfinder.ConnectorLanguage + "/connector." 
		+ ckfinder.ConnectorLanguage + "?command=QuickUpload&type=" + ( flashType || 'Flash' ) ;
}
CKFinder.SetupCKEditor = function( editorObj, basePath, imageType, flashType )
{
	if ( editorObj === null )
	{
		for ( var editorName in CKEDITOR.instances )
			CKFinder.SetupCKEditor( CKEDITOR.instances[editorName], basePath, imageType, flashType ) ;
    		CKEDITOR.on( 'instanceCreated', function(e) {
			CKFinder.SetupCKEditor( e.editor, basePath, imageType, flashType ) ;
		});

		return;
	}

	var ckfinder ;

	if ( basePath != null && typeof( basePath ) == 'object' )
	{
		ckfinder = new CKFinder( ) ;
		for ( var _property in basePath )
		{
			ckfinder[_property] = basePath[_property] ;	

			if ( _property == 'Width' )
			{
				var width = ckfinder[_property] || 800 ;
				if ( typeof width == 'string' && width.length > 1 && width.substr( width.length - 1, 1 ) == '%' )
					width = parseInt( window.screen.width * parseInt( width ) / 100 ) ;

				editorObj.config.filebrowserWindowWidth = width ;
			}
			else if ( _property == 'Height' )
			{
				var height = ckfinder[_property] || 600 ;
				if ( typeof height == 'string' && height.length > 1 && height.substr( height.length - 1, 1 ) == '%' )
					height = parseInt( window.screen.height * parseInt( height ) / 100 ) ;

				editorObj.config.filebrowserWindowHeight = width ;
			}
		}
	}
	else
		ckfinder = new CKFinder( basePath ) ;

	var url = ckfinder.BasePath ;

	if ( url.substr( 0, 1 ) != '/' )
		url = document.location.pathname.substring( 0, document.location.pathname.lastIndexOf('/') + 1 ) + url ;

	url = ckfinder._BuildUrl( url ) ;
	var qs = ( url.indexOf( "?" ) !== -1 ) ? "&amp;" : "?" ;

	editorObj.config.filebrowserBrowseUrl = url ;
	editorObj.config.filebrowserImageBrowseUrl = url + qs + 'type=' + ( imageType || 'Images' ) ;
	editorObj.config.filebrowserFlashBrowseUrl = url + qs + 'type=' + ( flashType || 'Flash' ) ;

	var dir = url.substring(0, 1 + url.lastIndexOf("/"));
	editorObj.config.filebrowserUploadUrl = dir + "core/connector/" + ckfinder.ConnectorLanguage + "/connector." 
		+ ckfinder.ConnectorLanguage + "?command=QuickUpload&type=Files" ;
	editorObj.config.filebrowserImageUploadUrl = dir + "core/connector/" + ckfinder.ConnectorLanguage + "/connector." 
		+ ckfinder.ConnectorLanguage + "?command=QuickUpload&type=" + ( imageType || 'Images' ) ;
	editorObj.config.filebrowserFlashUploadUrl = dir + "core/connector/" + ckfinder.ConnectorLanguage + "/connector." 
		+ ckfinder.ConnectorLanguage + "?command=QuickUpload&type=" + ( flashType || 'Flash' ) ;
}

/*================================================== table.js =======================================================*/

function set_sort(table, field, sort_direction, sufix) {
   if ($('#' + table + '_sort_field'+sufix).val() == field) {
      if ($('#' + table + '_sort_direction'+sufix).val() == 'desc') {
         $('#' + table + '_sort_direction'+sufix).val('asc');
      } else {
         $('#' + table + '_sort_direction'+sufix).val('desc');
      }
   } else {
      $('#' + table + '_sort_field'+sufix).val(field);
      $('#' + table + '_sort_direction'+sufix).val(sort_direction);
   }
   $('#' + table + '_page').val(1);
   $('#' + table + '_action').val('');

   $('#' + table + '_form').submit();
   $('#' + table + '_loading').show();
   return false;
}

function set_page(table, page) {
   $('#' + table + '_page').val(page);
   $('#' + table + '_action').val('');
   $('#' + table + '_form').submit();
   $('#' + table + '_loading').show();
   return false;
}

function update_pagination(table) {
	$('#' + table + '_loading').show();
	var page = $('#' + table + '_page').val();
	var perPage = $('#' + table + '_per_page').val();
	var isInt = /^\d+$/;

	if (!isInt.test(page)) $('#' + table + '_page').val(1);
	if (!isInt.test(perPage)) $('#' + table + '_per_page').val(10);
	
	$('#' + table + '_action').val('');
	$('#' + table + '_form').submit();
	return false;
}

function post_form(table) {
   $('#' + table + '_form').submit();
   return false;
}

function go_link(table, action, confirmation) {
   if ('' != confirmation) {
      if (!confirm(confirmation)) {
         return false;
      }
   }
   $('#' + table + '_form').attr('action', action);
   $('#' + table + '_form').submit();
   return false;
}

function do_action(table, action, confirmation, records_reqirement) {
   
   records_reqirement = records_reqirement || '';
   confirmation = confirmation || '';
   var checked = $('.xTable:eq(0) > tbody:eq(0) > tr[id] > td > input:checkbox:checked', '#' + table + '_form');
   
   if ((checked.length < 1) && ('' != records_reqirement)) {
	   alert(records_reqirement);
	   return false;
   }
   if ('' != confirmation) {
      if (!confirm(confirmation)) {
         return false;
      }
   }
   $('#' + table + '_action').val(action);
   $('#' + table + '_form').submit();
   return false;
}
/* ----------------------------------------------------------------------------------------------- */

function act(val){
		$('#' + val).addClass('act');
		
		return false;
	}

function pas(val){
		$('#' + val).removeClass('act');
		return false;
	}

function checktr(chk, tr){
   /*
	if ($('#' + chk).attr('checked')){
      $('#' + tr).addClass('trAct');

   }
   else {
       $('#' + tr).removeClass('trAct');

   }*/
}

function trackFrameHeight(id) {
	$('#'+id).load(function() {
	// Set inline style to equal the body height of the iframed content.
		this.style.height = (this.contentWindow.document.body.offsetHeight) + 'px';
	});
}

function update_columns(table) {
   $('#' + table + '_form').submit();
   return false;
}

function selectAllColumns(id, checked) {
   jQuery('input[type=checkbox]:enabled', '#' + id).attr('checked', checked);
   return false;
}

function changeSelectAllHandle(id) {
   checked = true;
   jQuery('input[type=checkbox]:gt(1)', '#' + id).each(function(){
      if (!jQuery(this).attr('checked')) {
         checked = false;
      }
   });
   jQuery('input.select-all', '#' + id).attr('checked', checked);
   return false;
}
/*===============================================gridwizard.js=======================================================*/


$(function($) {
	$.fn.gridWizard = function(options) {
		var defaults = {
			'id' : 'gridWizard',
			'onRebuild' : function() {
			},
			
			'l' : {
				'split' : 'split',
				'px' : 'px',

				'slots' : 'slots',
				'type' : 'type',
				
				'typeI' : 'Image',
				'typeT' : 'Text',
				'typeR' : 'Rich Media',
				'typeIT' : 'Image and Text',
				'typeTR' : 'Text and Rich Media',
				'typeIR' : 'Image and Rich Media',
				'typeTIR' : 'Text, Image and Rich Media',
				'typeS' : 'Search channel',
				'typeITXT': 'In-Text',
				
				'joinright' : 'join with right cell',
				'joinup' : 'join with up cell',
				'joindown' : 'join with down cell',
				'joinleft' : 'join with left cell',

				'channel' : 'channel'
			},

			'onChannelClick' : function() {
			},

			'rebuild' : function() {
				init(this);
			},
			'width' : 800,
			'height' : 600,
			'channels' : '',
			'foreignChannels' : '',
			'resizeCells' : true,
			'formatTable' : true,
			'showSizes' : true,
			'showChannels' : false,
			'manageChannels' : false,
			'callbackChannels' : true,
			'channelsContainerId' : 'channels_list',
			'scaleRatio' : 1,
			'scaledWidth' : 300,
			'scaledHeight' : 300,
			'obj' : this,
			'maxCol' : 3,
			'maxRow' : 3,
			'matrix' : new Array(),
			'zones' : [{
						'id' : '0',
						'colspan' : 3,
						'rowspan' : 3
					}]
		};
		options.zones = (options.zones && options.zones.length) ? options.zones : defaults.zones;  
		options.width = parseInt(options.width) || defaults.width;
		options.height = parseInt(options.height) || defaults.height;
		var options = $.extend(defaults, options);
		options.obj = this;		

		var _top = 0;
		var _left = 0;
		var _height = 0;
		var _width = 0;

		var init = function(opts) {

			try {
				if ((opts.scaledWidth / opts.scaledHeight) >= (opts.width / opts.height)) {
					_height = opts.scaledHeight;
					_width = parseInt(_height * (opts.width / opts.height));
	
					opts.scaleRatio = _height / opts.height;
				} else {
					_width = opts.scaledWidth;
					_height = parseInt(_width * (opts.height / opts.width));
	
					opts.scaleRatio = _width / opts.width;
				}
	
				var defaultCellW = Math.round(opts.width / opts.maxCol);
				var defaultCellH = Math.round(opts.height / opts.maxRow);
	
				opts.sizesW = opts.sizesW || new Array();
				opts.sizesH = opts.sizesH || new Array();
				
				for (var i = 0; i < opts.maxCol; i++) {
					opts.sizesH[i] = parseInt(opts.sizesH[i]) || defaultCellH;  
				}
	
				for (var i = 0; i < opts.maxRow; i++) {
					opts.sizesW[i] = parseInt(opts.sizesW[i]) || defaultCellW;  
				}
				
				$(opts.obj).css('position', 'absolute').html('');
	
				_top = parseInt($(opts.obj).css('top'));
				_left = parseInt($(opts.obj).css('left'));
	
				opts.sizesW = normalizeSizes(opts.sizesW, opts.width);
				opts.sizesH = normalizeSizes(opts.sizesH, opts.height);
				opts.sizesW = convertPercents(opts.sizesW, opts.width);
				opts.sizesH = convertPercents(opts.sizesH, opts.height);
	
				$('#' + opts.channelsContainerId).empty();
				var container = containerBuilder(opts);
	
				if (opts.resizeCells)
					$(container).append(dragBuilder(opts))
	
				var table = tableBuilder(opts);
	
				if (opts.showSizes) {
					opts.table = table[2];
				} else {
					opts.table = table[0];
				}
	
				$(container).append(table);
	
				if (opts.showChannels) {
					var channels = channelsBuilder(opts);
					$(container).append(channels);
				}
	
				$(opts.obj).append(container);
				if ($.browser.msie) {
					$(container).hide().show();
				}
				opts.onRebuild.call(opts);			
			} catch (err) {
				
			}
			

		}

		var spreadPixels = function(arr, pixels) {
			for (var i = 0; i < arr.length; i++) {
				if (pixels == 0)
					return true;
				if (pixels > 0) {
					arr[i] = arr[i] - 1;
					pixels = pixels - 1;
				} else {
					arr[i] = arr[i] + 1;
					pixels = pixels + 1;
				}
			}
			if (pixels != 0)
				spreadPixels(arr, pixels);
		}

		var arraySum = function(arr) {
			var sum = 0;
			$(arr).each(function() {
				if (!(/\%/.test(this))) {
					sum = sum + parseInt(this);
				} else {
					return false;
				}
			});
			return sum;
		}

		var convertPercents = function(arr, aspect) {
			var impressionSum = 0;
			for (var i = 0; i < arr.length; i++) {
				if (typeof(arr[i]) == 'string') {
					if (/\%/.test(arr[i])) {
						perc = parseFloat(arr[i]);
						arr[i] = Math.round((aspect / 100) * perc)
						impressionSum = impressionSum
								+ Math.round((aspect / 100) * perc)
								- ((aspect / 100) * perc);
					}
				}
			}

			spreadPixels(arr, Math.round(impressionSum));
			return arr;
		}

		var normalizeSizes = function(sizes, aspect) {
			var real = arraySum(sizes);
			if (real && real != aspect) {
				for (var i = 0; i < sizes.length; i++) {
					sizes[i] = (100 / (real / sizes[i])) + '%';
				}
			}
			return sizes;

		}
		
		var simplifyObj = function(o) {
			return o;
		}

		var resizeContainer = function(container, top, left, _width, _height) {
			$(container)
				.css('top', top + 'px')
				.css('left', left + 'px')
				.css('width', _width + 'px')
				.css('height', _height + 'px');
		}


		var reserveMatrix = function(width, height, x, y, limCols, opts) {
			var sizeY = 0;
			for (var j = y; j <= y + height - 1; j++) {
				var sizeX = 0;
				for (var i = x; i <= x + width - 1; i++) {
					opts.matrix[j * limCols + i] = false;
					sizeX = sizeX + Math.round(opts.sizesW[i] * opts.scaleRatio);
				}
				sizeY = sizeY + Math.round(opts.sizesH[j] * opts.scaleRatio);
			}
			return {
				'x' : sizeX,
				'y' : sizeY
			};
		}

		var recalculateSizes = function(draglets, opts) {
			var dv1 = draglets[0];
			var dv2 = draglets[1];
			var dh1 = draglets[2];
			var dh2 = draglets[3];

			opts.sizesW[0] = Math.round(parseInt($(dv1).css('left')) / opts.scaleRatio);
			opts.sizesW[1] = Math.round((parseInt($(dv2).css('left')) - parseInt($(dv1).css('left')))/ opts.scaleRatio);
			opts.sizesW[2] = opts.width - Math.round(parseInt($(dv2).css('left')) / opts.scaleRatio);
			opts.sizesH[0] = Math.round(parseInt($(dh1).css('top')) / opts.scaleRatio);
			opts.sizesH[1] = Math.round((parseInt($(dh2).css('top')) - parseInt($(dh1).css('top')))/ opts.scaleRatio);
			opts.sizesH[2] = opts.height - Math.round(parseInt($(dh2).css('top')) / opts.scaleRatio);

			init(opts);
		}

		var containerBuilder = function(opts) {
			var container = document.createElement('div');
			resizeContainer(container, 0, 0, _width, _height);
			$(container).addClass('wiz-container').attr('id', 'wiz-' + opts.id);

			opts.container = container;
			return container;
		}

		var dragBuilder = function(opts) {
			var dragContainer = document.createElement('div');
			$(dragContainer)
					.attr('id', 'ui-layout-draglets')
					.css('height', _height)
					.css('width', _width)
					.css('z-index', 50)
					.css('position', 'absolute');

			var dragletVertical1 = document.createElement('div');
			var dragletVertical2 = document.createElement('div');
			var dragletHorizontal1 = document.createElement('div');
			var dragletHorizontal2 = document.createElement('div');

			$([dragletVertical1, dragletVertical2])
					.addClass('ui-vertical-draglets')
					.css('height', _height)
					.css('z-index', 120)
					.css('position', 'absolute');

			$([dragletHorizontal1, dragletHorizontal2])
					.addClass('ui-horizontal-draglets')
					.css('width', _width)
					.css('z-index', 120)
					.css('position', 'absolute');

			$(dragletVertical1).css('left', parseInt(opts.sizesW[0]* opts.scaleRatio) + 'px').attr('rel', 1);
			$(dragletVertical2).css('left', parseInt(opts.sizesW[0]* opts.scaleRatio + opts.sizesW[1] * opts.scaleRatio) + 'px').attr('rel', 2);
			$(dragletHorizontal1).css('top', parseInt(opts.sizesH[0]* opts.scaleRatio)	+ 'px').attr('rel', 1);
			$(dragletHorizontal2).css('top', parseInt(opts.sizesH[0]	* opts.scaleRatio + opts.sizesH[1] * opts.scaleRatio) + 'px').attr('rel', 2);

			$([dragletHorizontal1, dragletHorizontal2]).mousedown(function() {
				$(dragContainer).addClass('ui-container-drag')
				$(this).addClass('ui-draglet-inuse');
				if ($(this).attr('rel') == '1') {
					resizeContainer(dragContainer, 0, 0, _width, parseInt(opts.sizesH[0]
							* opts.scaleRatio
							+ opts.sizesH[1]
							* opts.scaleRatio));
				} else {
					resizeContainer(dragContainer, parseInt(opts.sizesH[0]
							* opts.scaleRatio), 0, _width, parseInt(opts.sizesH[1]
							* opts.scaleRatio
							+ opts.sizesH[2]
							* opts.scaleRatio));
				}
			})
					.mouseup(function() {
						resizeContainer(dragContainer, 0, 0, _width, _height);
						$(dragContainer).removeClass('ui-container-drag');
						$(this).removeClass('ui-draglet-inuse');
					})
					.draggable({
						axis : 'y',
						containment : $(dragContainer),
						scroll : false,
						start : function(e, ui) {
							$(opts.table).hide();
						},
						stop : function(e, ui) {
							resizeContainer(dragContainer, 0, 0, _width, _height);
							$(dragContainer).removeClass('ui-container-drag');
							recalculateSizes([dragletVertical1,
									dragletVertical2, dragletHorizontal1,
									dragletHorizontal2], opts);
						}
					});

			$([dragletVertical1, dragletVertical2]).mousedown(function() {
				$(dragContainer).addClass('ui-container-drag');
				$(this).addClass('ui-draglet-inuse');
				if ($(this).attr('rel') == '1') {
					resizeContainer(dragContainer, 0, 0, parseInt(opts.sizesW[0]
							* opts.scaleRatio
							+ opts.sizesW[1]
							* opts.scaleRatio), _height);
				} else {
					resizeContainer(dragContainer, 0, parseInt(opts.sizesW[0]
							* opts.scaleRatio), parseInt(opts.sizesW[1]
							* opts.scaleRatio + opts.sizesW[2]
							* opts.scaleRatio), _height);
				}
			})
					.mouseup(function() {
						resizeContainer(dragContainer, 0, 0, _width, _height);
						$(dragContainer).removeClass('ui-container-drag');
						$(this).removeClass('ui-draglet-inuse');
					})
					.draggable({
						axis : 'x',
						containment : $(dragContainer),
						scroll : false,
						start : function(e, ui) {
							$(opts.table).hide();
						},
						stop : function(e, ui) {
							resizeContainer(dragContainer, 0, 0, _width, _height);
							$(dragContainer).removeClass('ui-container-drag');
							recalculateSizes([dragletVertical1,
									dragletVertical2, dragletHorizontal1,
									dragletHorizontal2], opts);
						}
					});

			return [dragContainer, dragletVertical1, dragletVertical2,
					dragletHorizontal1, dragletHorizontal2];
		}

		var channelsBuilder = function(opts) {		
			var newChannels = new Array();
			  for (var i = 0; i < opts.channels.length; i++) {
				if ((parseInt(opts.channels[i].x) + parseInt(opts.channels[i].width)) > parseInt(opts.width) || (parseInt(opts.channels[i].y) + parseInt(opts.channels[i].height)) > parseInt(opts.height)) {
					opts.foreignChannels[opts.foreignChannels.length] = opts.channels[i];
					continue;
				} 
				newChannels[newChannels.length] = opts.channels[i];
			}			
			opts.channels = newChannels;			
			var channels = new Array();
			
			for (var i = 0; i < opts.channels.length; i++) {
				var newChannel = document.createElement('div');
				if (opts.manageChannels) {
					var closeButton = document.createElement('div');
					$(closeButton)
							.addClass('ui-channel-closeBtn floatr center')
							.append('x')
							.attr('title', 'Remove channel from the Layout')
							.bind('click', {
								'options' : opts,
								'removeId' : parseInt(opts.channels[i].id)
							}, function(e) {

								opts = e.data.options;
								var newChannels = new Array();
								var newForeignChannel = {};

								for (var i = 0; i < opts.channels.length; i++) {
									if (parseInt(opts.channels[i].id) != e.data.removeId) {
										newChannels[newChannels.length] = opts.channels[i];
									} else {
										newForeignChannel = e.data.options.channels[i];
									}
								}
								opts.channels = newChannels;
								opts.foreignChannels[opts.foreignChannels.length] = newForeignChannel;
								opts.rebuild(opts);
							});
				}
				
				if (opts.channels[i].ad_type == 'search') {
					$(newChannel)
					.append('<div class="n">' + opts.channels[i].ad_type + '</div>')
					.addClass('search-channel');
				} else if (opts.channels[i].ad_type == 'intext') {
					$(newChannel)
					.append('<div class="n">' + opts.channels[i].ad_type + '</div>')
					.addClass('intext-channel');
				}  
				
				 else {
					$(newChannel)
					.append('<div class="n">'
						+ opts.channels[i].width + ' x '
						+ opts.channels[i].height + '</div>');
				}
				
				
				$(newChannel)
						.css('position', 'absolute')
						.css('z-index', '2002')
						.attr('id', 'channel' + opts.channels[i].id)
						.attr('title', opts.channels[i].title + (opts.channels[i].ad_type!='search' ? (' ('
								+ opts.channels[i].width + 'x'
								+ opts.channels[i].height + ')') : ''))
						.css('width', Math.round(opts.channels[i].width
								* opts.scaleRatio)
								+ 'px')
						.css('height', Math.round(opts.channels[i].height
								* opts.scaleRatio)
								+ 'px')
						.css('top', Math.round(opts.channels[i].y
								* opts.scaleRatio)
								+ 'px')
						.css('left', Math.round(opts.channels[i].x
								* opts.scaleRatio)
								+ 'px')
						.addClass('ovh b f9px ui-channel iu-ready-to-drop');

				if (opts.manageChannels) {
					$(newChannel)
						.prepend(closeButton)
						.hover(function(){
							$(this).css('z-index', '9999');
						}, function(){
							$(this).css('z-index', '1000');
						})
						.draggable({
							revert: 'invalid',
							cursorAt : { top : -1, left : -1}
						})
						
						.bind('dragstart', {o : opts, i : i}, function(e, ui) {
							var d = e.data;
							$(this)
								.removeClass('ui-channel')
								.addClass('ui-dragChannel')
								.css('width', Math.round(d.o.channels[d.i].width * d.o.scaleRatio) + 'px')
								.css('height', Math.round(d.o.channels[d.i].height* d.o.scaleRatio) + 'px');
						})
						.bind('dragstop', opts, function(e, ui){
								$(this)
									.removeClass('ui-dragChannel')
									.addClass('ui-channel');																
						});
				}
				
				
				if (opts.callbackChannels)
					$(newChannel).css('cursor', 'pointer').bind('click', {
						options : opts,
						el : opts.channels[i]
					}, function(e) {
						
						e.data.options.onChannelClick.call(e.data.el);
					});

				channels[channels.length] = newChannel;
			}

			if (opts.manageChannels) {
				for (var i = 0; i < opts.foreignChannels.length; i++) {
					newForeignChannel = document.createElement('div');
					var ChType = '';
					
					if (opts.foreignChannels[i].ad_type == 'search') {
						ChType = opts.l.typeS;
					} else if (opts.foreignChannels[i].ad_type == 'intext') {
						ChType = opts.l.typeITXT;
					} else {
						var adTypes = opts.foreignChannels[i].ad_type.split(',');
						var textAllowed = (-1 != adTypes.indexOf('text')) ? true : false;
						var imgAllowed =  (-1 != adTypes.indexOf('image')) ? true : false;
						var richAllowed = (-1 != adTypes.indexOf('richmedia')) ? true : false;
						
						if (textAllowed) {
							if (imgAllowed && richAllowed) {
								ChType = opts.l.typeTIR;
							} else if (imgAllowed && !richAllowed) {
								ChType = opts.l.typeIT;
							} else if (!imgAllowed && richAllowed) {
								ChType = opts.l.typeTR;
							} else {
								ChType = opts.l.typeT;
							}
						} else if (imgAllowed) {
							if (richAllowed) {
								ChType = opts.l.typeIR;
							} else {
								ChType = opts.l.typeI;
							}
						} else if (richAllowed) {
							ChType = opts.l.typeR;
						} else {
							ChType = 'unknown';
						}
					}
					
					if (opts.foreignChannels[i].ad_type == 'search') {
						opts.foreignChannels[i].width = 300;
						opts.foreignChannels[i].height = 500;
					}
					
					
					if (opts.foreignChannels[i].ad_type == 'intext') {
						opts.foreignChannels[i].width = 270;
						opts.foreignChannels[i].height = 160;
					}
					
					if (opts.foreignChannels[i].ad_type != 'search' && opts.foreignChannels[i].ad_type != 'intext') {
						$(newForeignChannel)
							.append('' 
								+ '<div class="slot"><img src=' + site_url + '"/images/slots_preview/slots_' + opts.foreignChannels[i].id_dimension + ((opts.foreignChannels[i].ad_type=='text') ? '' : '_1') + '.gif"></div>' 
								+ '<div class="title">'	+ opts.foreignChannels[i].title + '</div>'
								+ '<div class="size">' + opts.foreignChannels[i].width + ' x ' + opts.foreignChannels[i].height + '</div>' 
								+ '<div class="slot2">slots: ' + opts.foreignChannels[i].max_slots_count + '</div>' 
								+ '<div class="slot2">type: ' + ChType + '</div>' 
								+ '');
					} else {
						$(newForeignChannel)
							.append('' 
								+ '<div class="title">'	+ opts.foreignChannels[i].title + '</div>'
								+ '<div class="slot2">type: ' + ChType + '</div>' 
								+ '');
					}
					
					$(newForeignChannel)
							.attr('id', 'channel' + opts.foreignChannels[i].id)
							.attr('title', opts.foreignChannels[i].title)
							.addClass('ui-ready-to-drop')
							.addClass('ovh b ui-foreignChannel')
							.draggable({
								revert : 'invalid',
								//containment: $(opts),
								//opacity : 0.7,
								// cursor: 'move',
								cursorAt : {
									top : -1,
									left : -1
								}
							})
							.bind('dragstart', {o : opts, i : i}, function(e, ui) {
								var d = e.data;
								$(this)
									.removeClass('ui-foreignChannel')
									.addClass('ui-dragChannel')
									.css('width', Math.round(d.o.foreignChannels[d.i].width * d.o.scaleRatio) + 'px')
									.css('height', Math.round(d.o.foreignChannels[d.i].height* d.o.scaleRatio) + 'px');
							})
							.bind('dragstop', {o : opts, index : i}, function(e, ui) {
								var d = e.data;
								$(ui.helper.context)
									.removeClass('ui-dragChannel')
									.addClass('ui-foreignChannel')
									.css('width', '')
									.css('height', '');
								d.o.rebuild(d.o);
							})
							.bind('mouseup', function(e) {
								$(this)
									.removeClass('ui-dragChannel')
									.addClass('ui-foreignChannel')
									.css('width', '')
									.css('height', '');
							});

					$('#' + opts.channelsContainerId).append(newForeignChannel);
				}
			}
			return channels;
		}

		var getChannelInfo = function(el, pos, o) {
			var channel = { id : '', width : '', title : '', height : '', x : '', y : ''};
			channel.id = parseInt(($(el).attr('id').substring(7, $(el).attr('id').length)));
			
			var foundForeign = false;
			for (var i = 0; i < o.foreignChannels.length; i++) {
				if (parseInt(o.foreignChannels[i].id) == channel.id) {
					foundForeign = true;
					fCh = o.foreignChannels[i];
					channel.title = fCh.title;
					channel.id_dimension = fCh.id_dimension;
					channel.ad_type = fCh.ad_type;
					channel.max_slots_count = fCh.max_slots_count;
					channel.width = parseInt(fCh.width);
					channel.height = parseInt(fCh.height);
				}
			}
			
			if (!foundForeign) {
				for (var i = 0; i < o.channels.length; i++) {
					if (parseInt(o.channels[i].id) == channel.id) {
						fCh = o.channels[i];
						channel.title = fCh.title;
						channel.id_dimension = fCh.id_dimension;
						channel.ad_type = fCh.ad_type;
						channel.max_slots_count = fCh.max_slots_count;
						channel.width = parseInt(fCh.width);
						channel.height = parseInt(fCh.height);
					}
				}
			}

			channel.x = Math.round((pos.left - $(o.obj).offset().left) / o.scaleRatio);
			channel.y = Math.round((pos.top - $(o.obj).offset().top) / o.scaleRatio); // Math.round((pos.top
			return channel;
		}

		var tableBuilder = function(opts) {
			var limitCol = opts.maxCol;
			var limitRow = opts.maxRow;
			for (j = 0; j <= (limitRow * limitCol) - 1; j++) {
				opts.matrix[j] = true;
			}


			if (opts.showSizes) {
				var WSizesTBL = document.createElement('table');
				var HSizesTBL = document.createElement('table');

				$(WSizesTBL)
						.addClass('ui-tableWsizes')
						.css('position', 'absolute')
						.css('width', _width + 'px')
						.css('top', (_height + 10) + 'px');

				$(HSizesTBL)
						.addClass('ui-tableHsizes')
						.css('position', 'absolute')
						.css('height', _height + 'px')
						.css('left', (_width + 10) + 'px');

				for (var i = 0; i < opts.sizesW.length; i++) {
					var Wtd = document.createElement('td');
					$(Wtd).css('width', parseInt(opts.sizesW[i]
							* opts.scaleRatio)
							+ 'px').css('text-align', 'center').html('<div>'
							+ opts.sizesW[i] + 'px</div>');
					$(WSizesTBL).append(Wtd);
				}
				var top = 0;
				for (var i = 0; i < opts.sizesH.length; i++) {
					var Htr = document.createElement('tr');
					var Htd = document.createElement('td');

					$(Htd)
							.css('height', parseInt(opts.sizesH[i]
									* opts.scaleRatio)
									+ 'px')
							.css('text-align', 'center')
							.html('<div style="left:'
									+ (($.browser.msie || $.browser.opera || $.browser.safari  ) ? 0 : (_width + 10))
									+ 'px; top:'
									+ (parseInt(top) + 2)
									+ 'px;position:absolute;height:'
									+ Math.round(opts.sizesH[i] * opts.scaleRatio)
									+ 'px"></div>' + opts.sizesH[i]  + 'px');
					$(Htr).append(Htd);
					top = top + parseInt(opts.sizesH[i] * opts.scaleRatio);
					$(HSizesTBL).append(Htr);
				}
			}

			var currentTBL = document.createElement('table');
			var currentTBLBody = document.createElement('tbody');
			$(currentTBL)
					.attr('width', _width)
					.attr('height', _height)
					.attr('id', 'wiz-' + opts.id)
					.attr('cellspacing', 0)
					.attr('cellpadding', 0)
					.attr('border', 0)
					.css('position', 'absolute')
					.css('z-index', 100)
					.addClass('wiz-grid');

			var currZoneIndex = 0;
			for (var y = 0; y < limitRow; y++) {
				var currentTR = document.createElement('tr');
				$(currentTR).attr('valign', 'top');
				var currentWidth = 0;
				for (var x = 0; x < limitCol; x++) {
					if (!opts.matrix[y * limitCol + x]) continue;
					var currZone = opts.zones[currZoneIndex];
					var cellSizes = reserveMatrix(parseInt(currZone.colspan), parseInt(currZone.rowspan), x, y, limitCol, opts);
					opts.matrix[y * limitCol + x] = true;
					currZoneIndex++;

					currentTD = document.createElement('td');
					$(currentTD)
							.attr('id', 'zone' + currZone.id)
							.attr('width', cellSizes.x)
							.attr('height', cellSizes.y)
							.css('width', cellSizes.x + 'px')
							.css('height', cellSizes.y + 'px')
							.addClass('ui-zone');
					$(currentTR).append(currentTD);
					
					currentTD.colSpan = currZone.colspan;
					currentTD.rowSpan = currZone.rowspan;
					
				}
				$(currentTBLBody).append(currentTR);
			}
			
			for (i = 0; i < limitCol; i++) {
				var currentCol = document.createElement('col');
				$(currentCol).css('width', Math.round(opts.sizesW[i] * opts.scaleRatio) + 'px');
				$(currentTBL).append(currentCol);
			}
			
			$(currentTBL).append(currentTBLBody);
			
			$('td', currentTBL).each(function(){
				var $this = $(this);
				var newDiv = document.createElement('div');
				$(newDiv)
					.css('position', 'absolute')
					.css('width', (parseInt($this.attr('width'))-2) + 'px')
					.css('height', (parseInt($this.attr('height'))-2) + 'px')
					.addClass('ui-zone-fill');
				$this.append(newDiv);
			});
			
			if (opts.manageChannels) {
				$('td', currentTBL).droppable({
					tolerance : 'fit',
					//revert : 'invalid',
					activeClass : 'ui-dropZone'
				}).bind('drop', {
					o : opts
				}, function(e, ui) {
					
					var d = e.data;

					var channel = getChannelInfo(ui.helper.context, ui.absolutePosition, d.o);
					channel.zone = parseInt($(e.currentTarget).attr('id').substring(4, $(e.currentTarget).attr('id').length));
					var newIndex = d.o.channels.length;
					for (var i = 0; i < d.o.channels.length; i++) {
						if (d.o.channels[i].id == channel.id) {
							newIndex = i;
							break;
						}
					}

					var newForeignChannels = new Array();
					for (var i = 0; i < d.o.foreignChannels.length; i++) {
						if (d.o.foreignChannels[i].id != channel.id) {
							newForeignChannels[newForeignChannels.length] = d.o.foreignChannels[i];
						}
					}

					d.o.channels[newIndex] = channel;
					$(ui.helper.context)
						.draggable('disable')
						.removeClass('ui-ready-to-drop');
					d.o.foreignChannels = newForeignChannels;
					d.o.rebuild(d.o);
				});

			}
			
			

			if (opts.formatTable) {

				var tds = $('td', currentTBL);

				var currZoneIndex = 0;
				
				
				var newMatrix = new Array();
				var currentIndex = 0;
				for (var y = 0; y < opts.maxRow ; y++) {
					for (var x = 0; x < opts.maxCol; x++) {
						var newNode = {};
						newNode.state = opts.matrix[y * opts.maxCol + x];
						if (opts.matrix[y * opts.maxCol + x]) {
							newNode.colspan = parseInt(opts.zones[currentIndex].colspan);
							newNode.rowspan = parseInt(opts.zones[currentIndex].rowspan);
							newNode.index = currentIndex;
							currentIndex++;
						}
						newMatrix[y * opts.maxCol + x] = $.extend(newMatrix[y * opts.maxCol + x], newNode);
					}
				}

				var currZoneIndex = 0;
				
				$(tds).each(function() {
					
					if (parseInt($(this).attr('rowspan')) > 1 || parseInt($(this).attr('colspan')) > 1) {
						$(this).prepend(createButton(this, 'split', opts, currZoneIndex));
					}
					
					var cZoneIndex = 0;
					for (var y = 0; y < opts.maxRow; y++) {
						for (var x = 0; x < opts.maxCol; x++) {
							
							var current = newMatrix[y * opts.maxCol + x];
							if (!current.state) continue;
			
							if (cZoneIndex == currZoneIndex) {
								
								var top = newMatrix[(y-1) * opts.maxCol + x];
								if (!(x + current.colspan >= opts.maxCol)) var right =  newMatrix[y * opts.maxCol + (x + parseInt(current.colspan))];
								if (!(y + current.rowspan >= opts.maxRow)) var bottom = newMatrix[(y+parseInt(current.rowspan)) * opts.maxCol + x];
								
								if (x > 0) {
									var left = newMatrix[y * opts.maxCol + (x - 1)];
								}
								
								
								// top
								if ((y > 0) && typeof(top)!='undefined' && top.state && (top.colspan == current.colspan)) {
									targetZoneIndex = top.index;
									$(this).prepend(createButton(this, 'top', opts, currZoneIndex, targetZoneIndex));
								}
								// right
								if ((x < opts.maxCol-1) && typeof(right)!='undefined' && right.state && (right.rowspan == current.rowspan)) {
									targetZoneIndex = right.index;
									$(this).prepend(createButton(this, 'right', opts, currZoneIndex, targetZoneIndex));
								}
								// left
								if ((x > 0) && typeof(left)!='undefined' && left.state && (left.rowspan == current.rowspan)) {
									targetZoneIndex = left.index;
									$(this).prepend(createButton(this, 'left', opts, currZoneIndex, targetZoneIndex));
								}
								// bottom
								if ((y < opts.maxRow-1) && typeof(bottom)!='undefined' && bottom.state && (bottom.colspan == current.colspan)) {
									targetZoneIndex = bottom.index;
									$(this).prepend(createButton(this, 'bottom', opts, currZoneIndex, targetZoneIndex));
								}
							}
							
							cZoneIndex++;
							
						}
					}

					currZoneIndex++;

				})
				.click(function() {
					$('td', currentTBL).removeClass('selected');
					$(this).addClass('selected');
				})
				.hover(function() {
					$('td', currentTBL).removeClass('selected');
					$(this).addClass('selected');
				}, function(){
				})
				;
			}

			if (opts.showSizes) {
				return [HSizesTBL, WSizesTBL, currentTBL];
			} else {
				return [currentTBL];
			}

		}


		var createButton = function(el, dir, opts, index, targetIndex) {
			var button = document.createElement('div');
			$(button)
					.addClass('ui-zone-button')
					.addClass(dir)
					.css('position', 'absolute')
					.css('z-index', '1000')
					.hover(function() {
						$(this).addClass('ui-button-hover');
					}, function() {
						$(this).removeClass('ui-button-hover');
					});

			switch (dir) {
				case 'top' :
					$(button)
						.css('margin-left', Math.round(parseInt($(el).attr('width')) / 2) - 10 + 'px')
						.append("&uarr;")
						.bind('click', opts, function(e){
							rebuildZones(e.data, index, targetIndex, 'top');
						});
					break;
				case 'right' :
					$(button)
						.css('margin-left', Math.round(parseInt($(el).attr('width')) ) - 20 + 'px')
						.css('margin-top', Math.round(parseInt($(el).attr('height')) / 2) - 10 + 'px')
						.append("&rarr;")
						.bind('click', opts, function(e){
							rebuildZones(e.data, index, targetIndex, 'right');
						});
					break;
				case 'bottom' :
					$(button)
						.css('margin-left', Math.round(parseInt($(el).attr('width')) / 2) - 10 + 'px')
						.css('margin-top', Math.round(parseInt($(el).attr('height'))) - 20  + 'px')
						.append("&darr;")
						.bind('click', opts, function(e){
							rebuildZones(e.data, index, targetIndex, 'bottom');
						});
					break;
				case 'left' :
					$(button)
						.css('margin-top', Math.round(parseInt($(el).attr('height')) / 2) - 10 + 'px')
						.append("&larr;")
						.bind('click', opts, function(e){
							rebuildZones(e.data, index, targetIndex, 'left');
						});
					break;
				case 'split' :
					$(button)
						.css('margin-left', Math.round(parseInt($(el).attr('width'))/ 2) - 15 + 'px')
						.css('margin-top', Math.round(parseInt($(el).attr('height')) / 2) - 10	+ 'px')
						.text(opts.l.split)
						.bind('click', opts, function(e) {
							var currentIndex = 0;
							var newZones = new Array();
							for (var y = 0; y <= e.data.maxRow - 1; y++) {
								for (var x = 0; x <= e.data.maxCol - 1; x++) {
									if (e.data.matrix[y * e.data.maxCol + x] == false)
										continue;
	
									var currZone = e.data.zones[currentIndex];
									if (currentIndex == index) {
										for (var j = y; j <= y + parseInt(currZone.rowspan) - 1; j++) {
											for (var i = x; i <= x + parseInt(currZone.colspan) - 1; i++) {
												e.data.matrix[j * e.data.maxCol + i] = 'reserved';
											}
										}
										e.data.zones[currentIndex].colspan = 1;
										e.data.zones[currentIndex].rowspan = 1;
										e.data.matrix[y * e.data.maxCol + x] = true;
									}
									currentIndex++;
								}
							}
	
							currentIndex = 0;
							var newIndex = 0;
	
							for (var y = 0; y <= e.data.maxRow - 1; y++) {
								for (var x = 0; x <= e.data.maxCol - 1; x++) {
									var currZone = e.data.zones[currentIndex];
									if (e.data.matrix[y * e.data.maxCol + x] == true) {
										newZones[newIndex] = currZone;
										currentIndex++;
										newIndex++;
										continue;
									};
	
									if (e.data.matrix[y * e.data.maxCol + x] == 'reserved') {
										newZones[newIndex] = {
											'colspan' : 1,
											'rowspan' : 1
										};
										e.data.matrix[y * e.data.maxCol + x] = true;
										newIndex++;
										continue;
									};
								}
							}
	
							e.data.zones = newZones;
							e.data.rebuild(e.data);
	
						});
					break;
				default :
					break;
			}

			return button;
		}
		
		var deleteZone = function(arr, index) {
			var newArr = new Array();
			for(var i = 0; i < arr.length; i++) {
				if (i != index) newArr[newArr.length] = arr[i];
			}
			return newArr;
		}
		
		var rebuildZones = function(o, index, targetIndex, direction) {
			var newZones = new Array();
			switch (direction) {
				case 'bottom' :
						o.zones[index].rowspan = parseInt(o.zones[index].rowspan) + parseInt(o.zones[targetIndex].rowspan);
						o.zones = deleteZone(o.zones, targetIndex); 
					break;
				case 'top' : 
						o.zones[targetIndex].rowspan = parseInt(o.zones[index].rowspan) + parseInt(o.zones[targetIndex].rowspan);
						o.zones[targetIndex].id = o.zones[index].id;
						o.zones = deleteZone(o.zones, index);
					break;
				case 'left' :
						o.zones[targetIndex].colspan = parseInt(o.zones[index].colspan) + parseInt(o.zones[targetIndex].colspan);
						o.zones[targetIndex].id = o.zones[index].id;
						o.zones = deleteZone(o.zones, index);
					break;
				case 'right' : 
						o.zones[index].colspan = parseInt(o.zones[index].colspan) + parseInt(o.zones[targetIndex].colspan);
						o.zones = deleteZone(o.zones, targetIndex);
					break;
			}
			o.rebuild(o);
		}

		return this.each(function() {
			init(options);
		});
	};
});

/* ==================================================date.js==================================================================*/

Date.dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
Date.abbrDayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
Date.monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
Date.abbrMonthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
Date.firstDayOfWeek = 1;
Date.format = 'mm/dd/yyyy';
Date.fullYearStart = '20';

(function() {

	function add(name, method) {
		if( !Date.prototype[name] ) {
			Date.prototype[name] = method;
		}
	};
	add("isLeapYear", function() {
		var y = this.getFullYear();
		return (y%4==0 && y%100!=0) || y%400==0;
	});

	add("isWeekend", function() {
		return this.getDay()==0 || this.getDay()==6;
	});

	add("isWeekDay", function() {
		return !this.isWeekend();
	});

	add("getDaysInMonth", function() {
		return [31,(this.isLeapYear() ? 29:28),31,30,31,30,31,31,30,31,30,31][this.getMonth()];
	});

	add("getDayName", function(abbreviated) {
		return abbreviated ? Date.abbrDayNames[this.getDay()] : Date.dayNames[this.getDay()];
	});

	add("getMonthName", function(abbreviated) {
		return abbreviated ? Date.abbrMonthNames[this.getMonth()] : Date.monthNames[this.getMonth()];
	});

	add("getDayOfYear", function() {
		var tmpdtm = new Date("1/1/" + this.getFullYear());
		return Math.floor((this.getTime() - tmpdtm.getTime()) / 86400000);
	});

	add("getWeekOfYear", function() {
		return Math.ceil(this.getDayOfYear() / 7);
	});


	add("setDayOfYear", function(day) {
		this.setMonth(0);
		this.setDate(day);
		return this;
	});
	
	add("addYears", function(num) {
		this.setFullYear(this.getFullYear() + num);
		return this;
	});
	
	add("addMonths", function(num) {
		var tmpdtm = this.getDate();
		
		this.setMonth(this.getMonth() + num);
		
		if (tmpdtm > this.getDate())
			this.addDays(-this.getDate());
		
		return this;
	});
		
	add("addDays", function(num) {
		this.setDate(this.getDate() + num);
		return this;
	});
		
	add("addHours", function(num) {
		this.setHours(this.getHours() + num);
		return this;
	});
	
	add("addMinutes", function(num) {
		this.setMinutes(this.getMinutes() + num);
		return this;
	});
		
	add("addSeconds", function(num) {
		this.setSeconds(this.getSeconds() + num);
		return this;
	});
		
	add("zeroTime", function() {
		this.setMilliseconds(0);
		this.setSeconds(0);
		this.setMinutes(0);
		this.setHours(0);
		return this;
	});
		
	add("asString", function() {
		var r = Date.format;

		return r.split('yyyy').join(this.getFullYear())
			.split('mm').join(_zeroPad(this.getMonth()+1))
			.split('dd').join(_zeroPad(this.getDate()));
		
	});

	Date.fromString = function(s)
	{
		var f = Date.format;
		var d = new Date('01/01/1977');
		var iY = f.indexOf('yyyy');
		if (iY > -1) {
			d.setFullYear(Number(s.substr(iY, 4)));
		} else {
			// TODO - this doesn't work very well - are there any rules for what is meant by a two digit year?
			d.setFullYear(Number(Date.fullYearStart + s.substr(f.indexOf('yy'), 2)));
		}
		var iM = f.indexOf('mmm');
		if (iM > -1) {
			var mStr = s.substr(iM, 3);
			for (var i=0; i<Date.abbrMonthNames.length; i++) {
				if (Date.abbrMonthNames[i] == mStr) break;
			}
			d.setMonth(i);
		} else {
			d.setMonth(Number(s.substr(f.indexOf('mm'), 2)) - 1);
		}
		d.setDate(Number(s.substr(f.indexOf('dd'), 2)));
		if (isNaN(d.getTime())) {
			return false;
		}
		return d;
	};
	
	// utility method
	var _zeroPad = function(num) {
		var s = '0'+num;
		return s.substring(s.length-2)
		//return ('0'+num).substring(-2); // doesn't work on IE :(
	};
	
})();
/*=========================================================excanvas.min.js==========================================================*/

if(!document.createElement("canvas").getContext){(function(){var S=Math;var T=S.round;var P=S.sin;var c=S.cos;var K=S.abs;var b=S.sqrt;var A=10;var L=A/2;function H(){return this.context_||(this.context_=new N(this))}var R=Array.prototype.slice;function d(e,g,h){var Z=R.call(arguments,2);return function(){return e.apply(g,Z.concat(R.call(arguments)))}}var I={init:function(Z){if(/MSIE/.test(navigator.userAgent)&&!window.opera){var e=Z||document;e.createElement("canvas");e.attachEvent("onreadystatechange",d(this.init_,this,e))}},init_:function(g){if(!g.namespaces.g_vml_){g.namespaces.add("g_vml_","urn:schemas-microsoft-com:vml","#default#VML")}if(!g.namespaces.g_o_){g.namespaces.add("g_o_","urn:schemas-microsoft-com:office:office","#default#VML")}if(!g.styleSheets.ex_canvas_){var f=g.createStyleSheet();f.owningElement.id="ex_canvas_";f.cssText="canvas{display:inline-block;overflow:hidden;text-align:left;width:300px;height:150px}g_vml_\\:*{behavior:url(#default#VML)}g_o_\\:*{behavior:url(#default#VML)}"}var e=g.getElementsByTagName("canvas");for(var Z=0;Z<e.length;Z++){this.initElement(e[Z])}},initElement:function(e){if(!e.getContext){e.getContext=H;e.innerHTML="";e.attachEvent("onpropertychange",a);e.attachEvent("onresize",B);var Z=e.attributes;if(Z.width&&Z.width.specified){e.style.width=Z.width.nodeValue+"px"}else{e.width=e.clientWidth}if(Z.height&&Z.height.specified){e.style.height=Z.height.nodeValue+"px"}else{e.height=e.clientHeight}}return e}};function a(f){var Z=f.srcElement;switch(f.propertyName){case"width":Z.style.width=Z.attributes.width.nodeValue+"px";Z.getContext().clearRect();break;case"height":Z.style.height=Z.attributes.height.nodeValue+"px";Z.getContext().clearRect();break}}function B(f){var Z=f.srcElement;if(Z.firstChild){Z.firstChild.style.width=Z.clientWidth+"px";Z.firstChild.style.height=Z.clientHeight+"px"}}I.init();var E=[];for(var W=0;W<16;W++){for(var V=0;V<16;V++){E[W*16+V]=W.toString(16)+V.toString(16)}}function O(){return[[1,0,0],[0,1,0],[0,0,1]]}function D(g,f){var e=O();for(var Z=0;Z<3;Z++){for(var j=0;j<3;j++){var h=0;for(var i=0;i<3;i++){h+=g[Z][i]*f[i][j]}e[Z][j]=h}}return e}function U(e,Z){Z.fillStyle=e.fillStyle;Z.lineCap=e.lineCap;Z.lineJoin=e.lineJoin;Z.lineWidth=e.lineWidth;Z.miterLimit=e.miterLimit;Z.shadowBlur=e.shadowBlur;Z.shadowColor=e.shadowColor;Z.shadowOffsetX=e.shadowOffsetX;Z.shadowOffsetY=e.shadowOffsetY;Z.strokeStyle=e.strokeStyle;Z.globalAlpha=e.globalAlpha;Z.arcScaleX_=e.arcScaleX_;Z.arcScaleY_=e.arcScaleY_;Z.lineScale_=e.lineScale_}function C(e){var h,g=1;e=String(e);if(e.substring(0,3)=="rgb"){var k=e.indexOf("(",3);var Z=e.indexOf(")",k+1);var j=e.substring(k+1,Z).split(",");h="#";for(var f=0;f<3;f++){h+=E[Number(j[f])]}if(j.length==4&&e.substr(3,1)=="a"){g=j[3]}}else{h=e}return{color:h,alpha:g}}function Q(Z){switch(Z){case"butt":return"flat";case"round":return"round";case"square":default:return"square"}}function N(e){this.m_=O();this.mStack_=[];this.aStack_=[];this.currentPath_=[];this.strokeStyle="#000";this.fillStyle="#000";this.lineWidth=1;this.lineJoin="miter";this.lineCap="butt";this.miterLimit=A*1;this.globalAlpha=1;this.canvas=e;var Z=e.ownerDocument.createElement("div");Z.style.width=e.clientWidth+"px";Z.style.height=e.clientHeight+"px";Z.style.overflow="hidden";Z.style.position="absolute";e.appendChild(Z);this.element_=Z;this.arcScaleX_=1;this.arcScaleY_=1;this.lineScale_=1}var J=N.prototype;J.clearRect=function(){this.element_.innerHTML=""};J.beginPath=function(){this.currentPath_=[]};J.moveTo=function(e,Z){var f=this.getCoords_(e,Z);this.currentPath_.push({type:"moveTo",x:f.x,y:f.y});this.currentX_=f.x;this.currentY_=f.y};J.lineTo=function(e,Z){var f=this.getCoords_(e,Z);this.currentPath_.push({type:"lineTo",x:f.x,y:f.y});this.currentX_=f.x;this.currentY_=f.y};J.bezierCurveTo=function(f,e,l,k,j,h){var Z=this.getCoords_(j,h);var i=this.getCoords_(f,e);var g=this.getCoords_(l,k);M(this,i,g,Z)};function M(Z,g,f,e){Z.currentPath_.push({type:"bezierCurveTo",cp1x:g.x,cp1y:g.y,cp2x:f.x,cp2y:f.y,x:e.x,y:e.y});Z.currentX_=e.x;Z.currentY_=e.y}J.quadraticCurveTo=function(j,f,e,Z){var i=this.getCoords_(j,f);var h=this.getCoords_(e,Z);var k={x:this.currentX_+2/3*(i.x-this.currentX_),y:this.currentY_+2/3*(i.y-this.currentY_)};var g={x:k.x+(h.x-this.currentX_)/3,y:k.y+(h.y-this.currentY_)/3};M(this,k,g,h)};J.arc=function(m,k,l,h,e,f){l*=A;var r=f?"at":"wa";var n=m+c(h)*l-L;var q=k+P(h)*l-L;var Z=m+c(e)*l-L;var o=k+P(e)*l-L;if(n==Z&&!f){n+=0.125}var g=this.getCoords_(m,k);var j=this.getCoords_(n,q);var i=this.getCoords_(Z,o);this.currentPath_.push({type:r,x:g.x,y:g.y,radius:l,xStart:j.x,yStart:j.y,xEnd:i.x,yEnd:i.y})};J.rect=function(f,e,Z,g){this.moveTo(f,e);this.lineTo(f+Z,e);this.lineTo(f+Z,e+g);this.lineTo(f,e+g);this.closePath()};J.strokeRect=function(f,e,Z,g){var h=this.currentPath_;this.beginPath();this.moveTo(f,e);this.lineTo(f+Z,e);this.lineTo(f+Z,e+g);this.lineTo(f,e+g);this.closePath();this.stroke();this.currentPath_=h};J.fillRect=function(f,e,Z,g){var h=this.currentPath_;this.beginPath();this.moveTo(f,e);this.lineTo(f+Z,e);this.lineTo(f+Z,e+g);this.lineTo(f,e+g);this.closePath();this.fill();this.currentPath_=h};J.createLinearGradient=function(e,g,Z,f){var h=new X("gradient");h.x0_=e;h.y0_=g;h.x1_=Z;h.y1_=f;return h};J.createRadialGradient=function(g,i,f,e,h,Z){var j=new X("gradientradial");j.x0_=g;j.y0_=i;j.r0_=f;j.x1_=e;j.y1_=h;j.r1_=Z;return j};J.drawImage=function(t,f){var m,k,o,AB,r,p,v,AD;var n=t.runtimeStyle.width;var s=t.runtimeStyle.height;t.runtimeStyle.width="auto";t.runtimeStyle.height="auto";var l=t.width;var z=t.height;t.runtimeStyle.width=n;t.runtimeStyle.height=s;if(arguments.length==3){m=arguments[1];k=arguments[2];r=p=0;v=o=l;AD=AB=z}else{if(arguments.length==5){m=arguments[1];k=arguments[2];o=arguments[3];AB=arguments[4];r=p=0;v=l;AD=z}else{if(arguments.length==9){r=arguments[1];p=arguments[2];v=arguments[3];AD=arguments[4];m=arguments[5];k=arguments[6];o=arguments[7];AB=arguments[8]}else{throw Error("Invalid number of arguments")}}}var AC=this.getCoords_(m,k);var g=v/2;var e=AD/2;var AA=[];var Z=10;var j=10;AA.push(" <g_vml_:group",' coordsize="',A*Z,",",A*j,'"',' coordorigin="0,0"',' style="width:',Z,"px;height:",j,"px;position:absolute;");if(this.m_[0][0]!=1||this.m_[0][1]){var i=[];i.push("M11=",this.m_[0][0],",","M12=",this.m_[1][0],",","M21=",this.m_[0][1],",","M22=",this.m_[1][1],",","Dx=",T(AC.x/A),",","Dy=",T(AC.y/A),"");var y=AC;var x=this.getCoords_(m+o,k);var u=this.getCoords_(m,k+AB);var q=this.getCoords_(m+o,k+AB);y.x=S.max(y.x,x.x,u.x,q.x);y.y=S.max(y.y,x.y,u.y,q.y);AA.push("padding:0 ",T(y.x/A),"px ",T(y.y/A),"px 0;filter:progid:DXImageTransform.Microsoft.Matrix(",i.join(""),", sizingmethod='clip');")}else{AA.push("top:",T(AC.y/A),"px;left:",T(AC.x/A),"px;")}AA.push(' ">','<g_vml_:image src="',t.src,'"',' style="width:',A*o,"px;"," height:",A*AB,'px;"',' cropleft="',r/l,'"',' croptop="',p/z,'"',' cropright="',(l-r-v)/l,'"',' cropbottom="',(z-p-AD)/z,'"'," />","</g_vml_:group>");this.element_.insertAdjacentHTML("BeforeEnd",AA.join(""))};J.stroke=function(AF){var k=[];var l=false;var AQ=C(AF?this.fillStyle:this.strokeStyle);var AB=AQ.color;var AL=AQ.alpha*this.globalAlpha;var g=10;var n=10;k.push("<g_vml_:shape",' filled="',!!AF,'"',' style="position:absolute;width:',g,"px;height:",n,'px;"',' coordorigin="0 0" coordsize="',A*g," ",A*n,'"',' stroked="',!AF,'"',' path="');var m=false;var AP={x:null,y:null};var x={x:null,y:null};for(var AK=0;AK<this.currentPath_.length;AK++){var AJ=this.currentPath_[AK];var AO;switch(AJ.type){case"moveTo":AO=AJ;k.push(" m ",T(AJ.x),",",T(AJ.y));break;case"lineTo":k.push(" l ",T(AJ.x),",",T(AJ.y));break;case"close":k.push(" x ");AJ=null;break;case"bezierCurveTo":k.push(" c ",T(AJ.cp1x),",",T(AJ.cp1y),",",T(AJ.cp2x),",",T(AJ.cp2y),",",T(AJ.x),",",T(AJ.y));break;case"at":case"wa":k.push(" ",AJ.type," ",T(AJ.x-this.arcScaleX_*AJ.radius),",",T(AJ.y-this.arcScaleY_*AJ.radius)," ",T(AJ.x+this.arcScaleX_*AJ.radius),",",T(AJ.y+this.arcScaleY_*AJ.radius)," ",T(AJ.xStart),",",T(AJ.yStart)," ",T(AJ.xEnd),",",T(AJ.yEnd));break}if(AJ){if(AP.x==null||AJ.x<AP.x){AP.x=AJ.x}if(x.x==null||AJ.x>x.x){x.x=AJ.x}if(AP.y==null||AJ.y<AP.y){AP.y=AJ.y}if(x.y==null||AJ.y>x.y){x.y=AJ.y}}}k.push(' ">');if(!AF){var w=this.lineScale_*this.lineWidth;if(w<1){AL*=w}k.push("<g_vml_:stroke",' opacity="',AL,'"',' joinstyle="',this.lineJoin,'"',' miterlimit="',this.miterLimit,'"',' endcap="',Q(this.lineCap),'"',' weight="',w,'px"',' color="',AB,'" />')}else{if(typeof this.fillStyle=="object"){var o=this.fillStyle;var u=0;var AI={x:0,y:0};var AC=0;var s=1;if(o.type_=="gradient"){var r=o.x0_/this.arcScaleX_;var e=o.y0_/this.arcScaleY_;var q=o.x1_/this.arcScaleX_;var AR=o.y1_/this.arcScaleY_;var AN=this.getCoords_(r,e);var AM=this.getCoords_(q,AR);var j=AM.x-AN.x;var h=AM.y-AN.y;u=Math.atan2(j,h)*180/Math.PI;if(u<0){u+=360}if(u<0.000001){u=0}}else{var AN=this.getCoords_(o.x0_,o.y0_);var Z=x.x-AP.x;var f=x.y-AP.y;AI={x:(AN.x-AP.x)/Z,y:(AN.y-AP.y)/f};Z/=this.arcScaleX_*A;f/=this.arcScaleY_*A;var AH=S.max(Z,f);AC=2*o.r0_/AH;s=2*o.r1_/AH-AC}var AA=o.colors_;AA.sort(function(p,i){return p.offset-i.offset});var v=AA.length;var z=AA[0].color;var y=AA[v-1].color;var AE=AA[0].alpha*this.globalAlpha;var AD=AA[v-1].alpha*this.globalAlpha;var AG=[];for(var AK=0;AK<v;AK++){var t=AA[AK];AG.push(t.offset*s+AC+" "+t.color)}k.push('<g_vml_:fill type="',o.type_,'"',' method="none" focus="100%"',' color="',z,'"',' color2="',y,'"',' colors="',AG.join(","),'"',' opacity="',AD,'"',' g_o_:opacity2="',AE,'"',' angle="',u,'"',' focusposition="',AI.x,",",AI.y,'" />')}else{k.push('<g_vml_:fill color="',AB,'" opacity="',AL,'" />')}}k.push("</g_vml_:shape>");this.element_.insertAdjacentHTML("beforeEnd",k.join(""))};J.fill=function(){this.stroke(true)};J.closePath=function(){this.currentPath_.push({type:"close"})};J.getCoords_=function(f,e){var Z=this.m_;return{x:A*(f*Z[0][0]+e*Z[1][0]+Z[2][0])-L,y:A*(f*Z[0][1]+e*Z[1][1]+Z[2][1])-L}};J.save=function(){var Z={};U(this,Z);this.aStack_.push(Z);this.mStack_.push(this.m_);this.m_=D(O(),this.m_)};J.restore=function(){U(this.aStack_.pop(),this);this.m_=this.mStack_.pop()};function G(Z){for(var f=0;f<3;f++){for(var e=0;e<2;e++){if(!isFinite(Z[f][e])||isNaN(Z[f][e])){return false}}}return true}function Y(e,Z,f){if(!G(Z)){return }e.m_=Z;if(f){var g=Z[0][0]*Z[1][1]-Z[0][1]*Z[1][0];e.lineScale_=b(K(g))}}J.translate=function(f,e){var Z=[[1,0,0],[0,1,0],[f,e,1]];Y(this,D(Z,this.m_),false)};J.rotate=function(e){var g=c(e);var f=P(e);var Z=[[g,f,0],[-f,g,0],[0,0,1]];Y(this,D(Z,this.m_),false)};J.scale=function(f,e){this.arcScaleX_*=f;this.arcScaleY_*=e;var Z=[[f,0,0],[0,e,0],[0,0,1]];Y(this,D(Z,this.m_),true)};J.transform=function(h,g,j,i,e,Z){var f=[[h,g,0],[j,i,0],[e,Z,1]];Y(this,D(f,this.m_),true)};J.setTransform=function(h,g,j,i,f,e){var Z=[[h,g,0],[j,i,0],[f,e,1]];Y(this,Z,true)};J.clip=function(){};J.arcTo=function(){};J.createPattern=function(){return new F};function X(Z){this.type_=Z;this.x0_=0;this.y0_=0;this.r0_=0;this.x1_=0;this.y1_=0;this.r1_=0;this.colors_=[]}X.prototype.addColorStop=function(e,Z){Z=C(Z);this.colors_.push({offset:e,color:Z.color,alpha:Z.alpha})};function F(){}G_vmlCanvasManager=I;CanvasRenderingContext2D=N;CanvasGradient=X;CanvasPattern=F})()};

/*=========================================================keyword-tool.js==========================================================*/
Sppc = {};

Sppc.KeywordTool$Class = {
	old_tab: new Array(),
	controller: '',
	nextStep: '',
	selectedKeywords: null,
	aviableBids: new Array(),
	defaultMatchType: 'broad',
	lastEditedKeyword: null,
	
	constructor: function(){
		jQuery.extend(true, this, Sppc.KeywordTool$Class);
	},
	initialize: function(config){
		this.controller = config.controller;
		this.aviableBids = config.aviableBids;
		this.nextStep = config.nextStep;
		
		this.initializeBidsEditor();
		
		this.old_tab['tab'] = '';
		this.switchTab('word', 'tab');
		
		jQuery('#word_filterSwitcher').click(function(){
			if (jQuery('#word_filter').hasClass('hide')) {
				jQuery('#word_filter').removeClass('hide');
			} else {
				jQuery('#word_filter').addClass('hide');
			}
			return false;
		});
		
		jQuery('#website_filterSwitcher').click(function(){
			if (jQuery('#website_filter').hasClass('hide')) {
				jQuery('#website_filter').removeClass('hide');
			} else {
				jQuery('#website_filter').addClass('hide');
			}
			
			return false;
		});
		
		jQuery('#website_textBlockSwitcher').click(function(){
			if (jQuery('#website_textBlock').hasClass('hide')) {
				jQuery('#website_textBlock').removeClass('hide');
				jQuery('#website_URL').attr('disabled', 'disabled');
			} else {
				jQuery('#website_textBlock').addClass('hide');
				jQuery('#website_URL').attr('disabled', '');
			}
			
			return false;
		});
		
		jQuery('#addOwnKeywordsTabSwitcher').click(function(){
			if (jQuery('#addOwnKeywordsTab').hasClass('hide')) {
				jQuery('#addOwnKeywordsTab').removeClass('hide');
			} else {
				jQuery('#addOwnKeywordsTab').addClass('hide');
			}
			
			return false;
		});
		
		this.loadKeywordIdeasTable();
		this.updateSelectedKeywordsTable();
	},
	addOwnKeywords: function(){
		var selectedKeywords = jQuery('#ownKeywords').val();
		if (selectedKeywords) {
			jQuery.post(
				site_url + this.controller + '/add_own_keywords', 
				{'selectedKeywords': selectedKeywords},
				function(response){
					if(!checkAjaxLogin(response)) {
						try {
							response = JSON.parse(response);
						} catch(error) {
							return ;
						}
						
						if (response.status == 'ok') {
							jQuery('#ownKeywords').val('');
							Sppc.KeywordTool.updateSelectedKeywordsTable();
						} else {
							alert(response.error);
						}
					}
				}
			);
		}
	},
	deleteSelectedKeyword: function(keywordId) {
		jQuery.post(
			site_url + this.controller + '/delete_keyword',
			{'keyword_id': keywordId},
			function(response) {
				if (!checkAjaxLogin(response)) {
					try {
						response = JSON.parse(response);
					} catch (error) {
						return ;
					}
					
					if (response.status == 'ok') {
						Sppc.KeywordTool.updateSelectedKeywordsTable();
					} else {
						alert(response.error);
					}
				}
			}
		);
	},
	clearOwnKeywords: function() {
		jQuery('#ownKeywords').val('');
	},
	updateSelectedKeywordsTable: function() {
		$('#selected_keywords_table_process').show();
		$("#selected_keywords_container").hide().loadJFrame(site_url + this.controller + '/get_selected_keywords');
	}, 
	switchTab: function(tab, set) {
		if (this.old_tab[set] != '') {
			$('#'+set+'_div_'+this.old_tab[set]).hide();     
			$('#'+set+'_'+this.old_tab[set]).removeClass('sel');               
			$('#'+set+'_'+this.old_tab[set]).parent().removeClass('sel');
		}
		
	    xcontainer = $('#x-container');
		$('#'+set+'_div_'+tab+' .x-container').append(xcontainer);
		$('#'+set+'_div_'+tab).show();
		$('#'+set+'_'+tab).addClass('sel');
		$('#'+set+'_'+tab).parent().addClass('sel');             
		this.old_tab[set] = tab;
	},
	onSelectedKeywordsTableLoaded: function(){
		var container = $('#selected_keywords_container');
		
		if(!checkAjaxLogin(container.html())) {
			replaceFormButtons(container.document); 
			parseTables(container);
			$('#selected_keywords_table_process').hide();
			container.show();
			this.reloadKeywordIdeasTable();
			this.lastEditedKeyword = null;
		}
	},
	initializeBidsEditor: function() {
		jQuery(this.aviableBids).each(function(){
			var currentBid = this;
			jQuery('#useCustomBidFor_'+this).change(function(){
				if (jQuery(this).attr('checked')) {
					jQuery('#CustomBidFor_'+currentBid).attr('disabled', '');
					jQuery('#CustomBidFor_'+currentBid).focus();
				} else {
					jQuery('#CustomBidFor_'+currentBid).attr('disabled', 'disabled');
				}
				jQuery('#CustomBidFor_'+this).css('border', '1px solid #DAD6D2');
			});
			
			jQuery('#CustomBidFor_'+this).keyup(function(event){
				switch(event.keyCode) {
					case 13:
						jQuery(this).blur();
						Sppc.KeywordTool.saveKeywordBids();
						break;
					case 27:
						Sppc.KeywordTool.closeBidEditor();
						break;
				}
			});
			
			jQuery('#CustomBidFor_'+this).blur(function(){
				jQuery(this).css('border', '1px solid #DAD6D2');
			});
			
			jQuery('#useCustomBidFor_'+this).attr('checked', false);
			jQuery('#CustomBidFor_'+this).attr('disabled', 'disabled');
		});
	},
	closeBidEditor: function() {
		jQuery(this.aviableBids).each(function(){
			jQuery('#useCustomBidFor_'+this).attr('checked', false);
			jQuery('#CustomBidFor_'+this).attr('disabled', 'disabled');
			jQuery('#CustomBidFor_'+this).val("");
		});
		hideContainer('keywordBidsEditorDialog'); 
	},
	saveKeywordBids: function() {
		if (!this.checkKeywordBidsEditorFields()) {
			var data = {
				keyword: jQuery('#keywordBidsEditorKeywordId').val()
			};
			
			jQuery(this.aviableBids).each(function(){
				var currentBid = this;
				if (jQuery('#useCustomBidFor_' + currentBid).attr('checked')) {
					data[currentBid] = jQuery('#CustomBidFor_'+currentBid).val();
				}
			});
			
			jQuery.post(site_url+this.controller+'/set_keyword_bids', data, function(response){
				if(!checkAjaxLogin(response)) {
					Sppc.KeywordTool.closeBidEditor();
					Sppc.KeywordTool.updateSelectedKeywordsTable();
				}
			});
		}
	},
	checkKeywordBidsEditorFields: function() {
		var hasErrors = false;
		
		jQuery(this.aviableBids).each(function(){
			var currentBid = this;
			if (jQuery('#useCustomBidFor_' + currentBid).attr('checked')) {
				var bidValue = parseFloat(jQuery('#CustomBidFor_'+currentBid).val());
				if (isNaN(bidValue)) {
					hasErrors = true;
					jQuery('#CustomBidFor_'+currentBid).css('border', '1px solid red');
					
					if (hasErrors == false) {
						jQuery('#CustomBidFor_'+currentBid).focus();
					}
				}
			}
			
		});
		
		return hasErrors;
	},
	showKeywordBidsEditor: function(keyword, sender, bid) {
		jQuery('#keywordBidsEditorKeywordId').val(keyword);
		showContainer('keywordBidsEditorDialog', sender);
		
		if ((!this.lastEditedKeyword) || (this.lastEditedKeyword.id != keyword)) {
			this.lastEditedKeyword = null;
			jQuery('#keywordBidsEditorFields').hide();
			jQuery('#bids_editor_process').show();
			
			jQuery.ajax({
				async: false,
				cache: false,
				data: {'keyword': keyword},
				type: 'post',
				url: site_url + this.controller + '/get_keyword_bid_info',
				success: function(response) {
					if(!checkAjaxLogin(response)) {
						jQuery('#bids_editor_process').hide();
						jQuery('#keywordBidsEditorFields').show();
						
						try {
							response = JSON.parse(response);
						} catch(error) {
							Sppc.KeywordTool.closeBidEditor();
						}
						
						if (response.status == 'ok') {
							Sppc.KeywordTool.lastEditedKeyword = response.keywordInfo;
						} else {
							alert(response.error);
						}
					}
				}
			});
		}
		
		if (!this.lastEditedKeyword) {
			this.closeBidEditor();
			return ;
		}
		
		var thisObj = this;
		jQuery(this.aviableBids).each(function(){
			var currentBid = this;
			jQuery('#CustomBidFor_'+currentBid).val(thisObj.lastEditedKeyword.bids[currentBid].value);
			
			if (thisObj.lastEditedKeyword.bids[currentBid].use_custom_bid) {
				jQuery('#CustomBidFor_'+currentBid).attr('disabled', '');
				jQuery('#useCustomBidFor_'+currentBid).attr('checked', true);
			} else {
				jQuery('#CustomBidFor_'+currentBid).attr('disabled', 'disabled');
				jQuery('#useCustomBidFor_'+currentBid).attr('checked', false);
			}
		});
		
		if (bid) {
			jQuery('#useCustomBidFor_'+bid).attr('checked', true);
			jQuery('#CustomBidFor_'+bid).attr('disabled', '');
			jQuery('#CustomBidFor_'+bid).focus();
		}
	},
	checkGroupKeywords: function() {
		jQuery.post(site_url + this.controller + '/check_group_keywords', null, function(response){
			if(!checkAjaxLogin(response)) {
				try {
					response = JSON.parse(response);
				} catch(error) {
					return ;
				}
				
				if (response.status == 'ok') {
					Sppc.KeywordTool.gotoNextStep();
				} else {
					jQuery('#errorMessage').html(response.error);
					jQuery('.errorBox').show();
					jQuery(document).scrollTop(0);
				}
			}
		});
	},
	gotoNextStep: function() {
		location.href = site_url+this.nextStep;
	},
	updateDefaultBids: function() {
		var defaultBids = {};
		for(var i = 0; i < this.aviableBids.length; i++) {
			defaultBids[this.aviableBids[i]] = jQuery('#default_'+this.aviableBids[i]+'_bid').val();
		}
		jQuery.post(site_url+this.controller+'/set_default_bids/', defaultBids, function(response){
			if(!checkAjaxLogin(response)) {
				try {
					response = JSON.parse(response);					
				} catch(error) {
					return ;
				}
				
				if (response.status == 'ok') {
					Sppc.KeywordTool.updateSelectedKeywordsTable();
				} else {
					alert(response.error);
					
				}
			}
		});
	},
	loadKeywordIdeasTable: function() {
		jQuery('#keywords_ideas_container').loadJFrame(site_url + this.controller + '/get_keyword_ideas');
	},
	reloadKeywordIdeasTable: function() {
		if (this.old_tab['tab'] == 'word') {
			this.getKeywordIdeasFromPhrase();
		} else {
			this.getKeywordIdeasFromSite();
		}
	},
	getKeywordIdeasFromSite: function() {
		var type;
		var context;
		if (jQuery('#website_URL').attr('disabled')) {
			type = "text";
			context = jQuery('#website_text').val();
		} else {
			type = "site";
			context = jQuery('#website_URL').val();
		}
		
		if (context) {
			jQuery('#keywordIdeasFormType').val(type);
			jQuery('#keywordIdeasFormContext').val(context);
			jQuery('#keywordIdeasFormFilterKeywords').val(jQuery('#website_filterContainKeywords').val());
			var alreadySelectedFilter = (jQuery('#website_filterKeywordsInGroup').attr('checked')) ? 1 : 0;
			jQuery('#keywordIdeasFormFilterDontShowAlreadySelected').val(alreadySelectedFilter);
			
			jQuery('#keywords_ideas_container').hide();
			jQuery('#keywords_ideas_table_process').show();
			
			jQuery('#keyword_ideas_form').submit();
		}
	},
	getKeywordIdeasFromPhrase: function() {
		if (jQuery('#word_phrase').val()) {
			jQuery('#keywordIdeasFormType').val('phrase');
			jQuery('#keywordIdeasFormContext').val(jQuery('#word_phrase').val());
			jQuery('#keywordIdeasFormFilterKeywords').val(jQuery('#word_filterContainKeywords').val());
			var alreadySelectedFilter = (jQuery('#word_filterKeywordsInGroup').attr('checked')) ? 1 : 0;
			jQuery('#keywordIdeasFormFilterDontShowAlreadySelected').val(alreadySelectedFilter);
			
			jQuery('#keywords_ideas_container').hide();
			jQuery('#keywords_ideas_table_process').show();
			
			jQuery('#keyword_ideas_form').submit();
		}
	},
	onKeywordIdeasTableLoaded: function() {
		var container = $('#keywords_ideas_container');
		
		if(!checkAjaxLogin(container.html())) {
			replaceFormButtons(container.document); 
			parseTables(container);
			$('#keywords_ideas_table_process').hide();
			
			Sppc.KeywordTool.setDefaultMatchType(Sppc.KeywordTool.defaultMatchType);
			jQuery('#globalMatchType').val(Sppc.KeywordTool.defaultMatchType);
			
			jQuery('#globalMatchType').change(function(){
				Sppc.KeywordTool.setDefaultMatchType(jQuery(this).val());
			});
			
			jQuery('.keyword-idea-add-menu', container).change(function(){
				var elId = jQuery(this).attr('id');
				var keywordId = elId.substr(19);
				var matchType = jQuery(this).val();
				
				Sppc.KeywordTool.addKeywordIdea(keywordId, matchType);
			});
			container.show();
		}
	},
	setDefaultMatchType: function(matchType) {
		jQuery('span.guibutton', jQuery('#keywordIdeasTable')).guiSetSelectedByValue(matchType);
		this.defaultMatchType = matchType;
	},
	addKeywordIdea: function(keywordId, matchType) {
		if (!matchType) {
			matchType = this.defaultMatchType;
		}
		
		jQuery.post(site_url+this.controller+'/add_keyword_idea',
			{'keyword': keywordId, 'match_type': matchType},
			function(response) {
				if(!checkAjaxLogin(response)) {
					try {
						response = JSON.parse(response);
					} catch(error) {
						return ;
					}
					
					if (response.status == 'ok') {
						Sppc.KeywordTool.updateSelectedKeywordsTable();
					} else {
						alert(response.error);
					}
				}
			}
		);
	}
};
Sppc.KeywordTool = new Sppc.KeywordTool$Class.constructor();
/*=========================================================slimbox2.js==========================================================*/


(function($) {

	// Global variables, accessible to Slimbox only
	var win = $(window), options, images, activeImage = -1, activeURL, prevImage, nextImage, compatibleOverlay, middle, centerWidth, centerHeight, ie6 = !window.XMLHttpRequest,
		operaFix = window.opera && (document.compatMode == "CSS1Compat") && ($.browser.version >= 9.3), documentElement = document.documentElement,

	// Preload images
	preload = {}, preloadPrev = new Image(), preloadNext = new Image(),

	// DOM elements
	overlay, center, image, sizer, prevLink, nextLink, bottomContainer, bottom, caption, number;

	/*
		Initialization
	*/

	$(function() {
		// Append the Slimbox HTML code at the bottom of the document
		$("body").append(
			$([ 
				overlay = $('<div id="lbOverlay" />')[0],
				bottomContainer = $('<div id="lbBottomContainer" />')[0],
				center = $('<div id="lbCenter" />')[0]
				
			]).css("display", "none")
		);

		image = $('<div id="lbImage" />').appendTo(center).append(
			sizer = $('<div style="position: relative;" />').append([
				prevLink = $('<a id="lbPrevLink" href="#" />').click(previous)[0],
				nextLink = $('<a id="lbNextLink" href="#" />').click(next)[0]
			])[0]
		)[0];

		bottom = $('<div id="lbBottom" />').appendTo(bottomContainer).append([
			$('<a id="lbCloseLink" href="#" />').add(overlay).click(close)[0],
			caption = $('<div id="lbCaption" />')[0],
			number = $('<div id="lbNumber" />')[0],
			$('<div style="clear: both;" />')[0]
		])[0];
	});


	/*
		API
	*/

	// Open Slimbox with the specified parameters
	$.slimbox = function(_images, startImage, _options) {
		options = $.extend({
			loop: false,				// Allows to navigate between first and last images
			overlayOpacity: 0.8,			// 1 is opaque, 0 is completely transparent (change the color in the CSS file)
			overlayFadeDuration: 400,		// Duration of the overlay fade-in and fade-out animations (in milliseconds)
			resizeDuration: 400,			// Duration of each of the box resize animations (in milliseconds)
			resizeEasing: "swing",			// "swing" is jQuery's default easing
			initialWidth: 250,			// Initial width of the box (in pixels)
			initialHeight: 250,			// Initial height of the box (in pixels)
			imageFadeDuration: 400,			// Duration of the image fade-in animation (in milliseconds)
			captionAnimationDuration: 400,		// Duration of the caption animation (in milliseconds)
			counterText: "Image {x} of {y}",	// Translate or change as you wish, or set it to false to disable counter text for image groups
			closeKeys: [27, 88, 67],		// Array of keycodes to close Slimbox, default: Esc (27), 'x' (88), 'c' (67)
			previousKeys: [37, 80],			// Array of keycodes to navigate to the previous image, default: Left arrow (37), 'p' (80)
			nextKeys: [39, 78]			// Array of keycodes to navigate to the next image, default: Right arrow (39), 'n' (78)
		}, _options);

		// The function is called for a single image, with URL and Title as first two arguments
		if (typeof _images == "string") {
			_images = [[_images, startImage]];
			startImage = 0;
		}

		middle = win.scrollTop() + ((operaFix ? documentElement.clientHeight : win.height()) / 2);
		centerWidth = options.initialWidth;
		centerHeight = options.initialHeight;
		$(bottomContainer).css({width: centerWidth});
		var top = Math.max(0, middle - (centerHeight / 2)) + $(bottomContainer).height();
		$(center).css({top: top, width: centerWidth, height: centerHeight, marginLeft: -centerWidth/2}).show();
		compatibleOverlay = ie6 || (overlay.currentStyle && (overlay.currentStyle.position != "fixed"));
		if (compatibleOverlay) overlay.style.position = "absolute";
		$(overlay).css("opacity", options.overlayOpacity).fadeIn(options.overlayFadeDuration);
		position();
		setup(1);

		images = _images;
		options.loop = options.loop && (images.length > 1);
		return changeImage(startImage);
	};

	$.fn.slimbox = function(_options, linkMapper, linksFilter) {
		linkMapper = linkMapper || function(el) {
			return [el.href, el.title];
		};

		linksFilter = linksFilter || function() {
			return true;
		};

		var links = this;

		return links.unbind("click").click(function() {
			// Build the list of images that will be displayed
			var link = this, startIndex = 0, filteredLinks, i = 0, length;
			filteredLinks = $.grep(links, function(el, i) {
				return linksFilter.call(link, el, i);
			});

			// We cannot use jQuery.map() because it flattens the returned array
			for (length = filteredLinks.length; i < length; ++i) {
				if (filteredLinks[i] == link) startIndex = i;
				filteredLinks[i] = linkMapper(filteredLinks[i], i);
			}

			return $.slimbox(filteredLinks, startIndex, _options);
		});
	};


	/*
		Internal functions
	*/

	function position() {
		var l = win.scrollLeft(), w = operaFix ? documentElement.clientWidth : win.width();
		$([center, bottomContainer]).css("left", l + (w / 2));
		if (compatibleOverlay) $(overlay).css({left: l, top: win.scrollTop(), width: w, height: win.height()});
	}

	function setup(open) {
		$("object").add(ie6 ? "select" : "embed").each(function(index, el) {
			if (open) $.data(el, "slimbox", el.style.visibility);
			el.style.visibility = open ? "hidden" : $.data(el, "slimbox");
		});
		var fn = open ? "bind" : "unbind";
		win[fn]("scroll resize", position);
		$(document)[fn]("keydown", keyDown);
	}

	function keyDown(event) {
		var code = event.keyCode, fn = $.inArray;
		// Prevent default keyboard action (like navigating inside the page)
		return (fn(code, options.closeKeys) >= 0) ? close()
			: (fn(code, options.nextKeys) >= 0) ? next()
			: (fn(code, options.previousKeys) >= 0) ? previous()
			: false;
	}

	function previous() {
		return changeImage(prevImage);
	}

	function next() {
		return changeImage(nextImage);
	}

	function changeImage(imageIndex) {
		if (imageIndex >= 0) {
			activeImage = imageIndex;
			activeURL = images[activeImage][0];
			prevImage = (activeImage || (options.loop ? images.length : 0)) - 1;
			nextImage = ((activeImage + 1) % images.length) || (options.loop ? 0 : -1);

			stop();
			center.className = "lbLoading";

			preload = new Image();
			preload.onload = animateBox;
			preload.src = activeURL;
		}

		return false;
	}

	function animateBox() {
		center.className = "";
		$(image).css({backgroundImage: "url(" + activeURL + ")", visibility: "hidden", display: ""});
		$(sizer).width(preload.width);
		$([sizer, prevLink, nextLink]).height(preload.height);

		$(caption).html(images[activeImage][1] || "");
		$(number).html((((images.length > 1) && options.counterText) || "").replace(/{x}/, activeImage + 1).replace(/{y}/, images.length));

		if (prevImage >= 0) preloadPrev.src = images[prevImage][0];
		if (nextImage >= 0) preloadNext.src = images[nextImage][0];

		centerWidth = image.offsetWidth;
		centerHeight = image.offsetHeight;
		var top = Math.max(0, middle - (centerHeight / 2));
		$(bottomContainer).css({width: centerWidth});
		top = top + $(bottomContainer).height();
		if (center.offsetHeight != centerHeight) {
			$(center).animate({height: centerHeight, top: top}, options.resizeDuration, options.resizeEasing);
		}
		if (center.offsetWidth != centerWidth) {
			$(center).animate({width: centerWidth, marginLeft: -centerWidth/2}, options.resizeDuration, options.resizeEasing);
		}
		top = top - $(bottomContainer).height();
		$(center).queue(function() {
			$(bottomContainer).css({top: top, marginLeft: -centerWidth/2, visibility: "hidden", display: ""});
			$(image).css({display: "none", visibility: "", opacity: ""}).fadeIn(options.imageFadeDuration, animateCaption);
		});
	}

	function animateCaption() {
		if (prevImage >= 0) $(prevLink).show();
		if (nextImage >= 0) $(nextLink).show();
		$(bottom).css("marginTop", bottom.offsetHeight).animate({marginTop: 5}, options.captionAnimationDuration);
		bottomContainer.style.visibility = "";
		
	}

	function stop() {
		preload.onload = null;
		preload.src = preloadPrev.src = preloadNext.src = activeURL;
		$([center, image, bottom]).stop(true);
		$([prevLink, nextLink, image, bottomContainer]).hide();
	}

	function close() {
		if (activeImage >= 0) {
			stop();
			activeImage = prevImage = nextImage = -1;
			$(center).hide();
			$(overlay).stop().fadeOut(options.overlayFadeDuration, setup);
		}

		return false;
	}

})(jQuery);

/*====================================================json2.js========================================================*/


"use strict";

if (!this.JSON) {
    this.JSON = {};
}

(function () {

    function f(n) {
        return n < 10 ? '0' + n : n;
    }

    if (typeof Date.prototype.toJSON !== 'function') {

        Date.prototype.toJSON = function (key) {

            return isFinite(this.valueOf()) ?
                   this.getUTCFullYear()   + '-' +
                 f(this.getUTCMonth() + 1) + '-' +
                 f(this.getUTCDate())      + 'T' +
                 f(this.getUTCHours())     + ':' +
                 f(this.getUTCMinutes())   + ':' +
                 f(this.getUTCSeconds())   + 'Z' : null;
        };

        String.prototype.toJSON =
        Number.prototype.toJSON =
        Boolean.prototype.toJSON = function (key) {
            return this.valueOf();
        };
    }

    var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        gap,
        indent,
        meta = { 
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        rep;


    function quote(string) {
        escapable.lastIndex = 0;
        return escapable.test(string) ?
            '"' + string.replace(escapable, function (a) {
                var c = meta[a];
                return typeof c === 'string' ? c :
                    '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
            }) + '"' :
            '"' + string + '"';
    }


    function str(key, holder) {
        var i,          
            k,          
            v,          
            length,
            mind = gap,
            partial,
            value = holder[key];

        if (value && typeof value === 'object' &&
                typeof value.toJSON === 'function') {
            value = value.toJSON(key);
        }


        if (typeof rep === 'function') {
            value = rep.call(holder, key, value);
        }


        switch (typeof value) {
        case 'string':
            return quote(value);

        case 'number':

            return isFinite(value) ? String(value) : 'null';

        case 'boolean':
        case 'null':

            return String(value);


        case 'object':


            if (!value) {
                return 'null';
            }


            gap += indent;
            partial = [];

            if (Object.prototype.toString.apply(value) === '[object Array]') {

                length = value.length;
                for (i = 0; i < length; i += 1) {
                    partial[i] = str(i, value) || 'null';
                }

                v = partial.length === 0 ? '[]' :
                    gap ? '[\n' + gap +
                            partial.join(',\n' + gap) + '\n' +
                                mind + ']' :
                          '[' + partial.join(',') + ']';
                gap = mind;
                return v;
            }

            if (rep && typeof rep === 'object') {
                length = rep.length;
                for (i = 0; i < length; i += 1) {
                    k = rep[i];
                    if (typeof k === 'string') {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            } else {


                for (k in value) {
                    if (Object.hasOwnProperty.call(value, k)) {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            }


            v = partial.length === 0 ? '{}' :
                gap ? '{\n' + gap + partial.join(',\n' + gap) + '\n' +
                        mind + '}' : '{' + partial.join(',') + '}';
            gap = mind;
            return v;
        }
    }


    if (typeof JSON.stringify !== 'function') {
        JSON.stringify = function (value, replacer, space) {

            var i;
            gap = '';
            indent = '';

            if (typeof space === 'number') {
                for (i = 0; i < space; i += 1) {
                    indent += ' ';
                }

            } else if (typeof space === 'string') {
                indent = space;
            }

            rep = replacer;
            if (replacer && typeof replacer !== 'function' &&
                    (typeof replacer !== 'object' ||
                     typeof replacer.length !== 'number')) {
                throw new Error('JSON.stringify');
            }

            return str('', {'': value});
        };
    }


    if (typeof JSON.parse !== 'function') {
        JSON.parse = function (text, reviver) {

            var j;

            function walk(holder, key) {

                var k, v, value = holder[key];
                if (value && typeof value === 'object') {
                    for (k in value) {
                        if (Object.hasOwnProperty.call(value, k)) {
                            v = walk(value, k);
                            if (v !== undefined) {
                                value[k] = v;
                            } else {
                                delete value[k];
                            }
                        }
                    }
                }
                return reviver.call(holder, key, value);
            }

            cx.lastIndex = 0;
            if (cx.test(text)) {
                text = text.replace(cx, function (a) {
                    return '\\u' +
                        ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
                });
            }
            if (/^[\],:{}\s]*$/.
test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').
replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {


                j = eval('(' + text + ')');


                return typeof reviver === 'function' ?
                    walk({'': j}, '') : j;
            }

            throw new SyntaxError('JSON.parse');
        };
    }
}());


/*==========================================selectbox.js==========================================*/


jQuery.fn.extend({
	selectbox: function(options) {
		return this.each(function() {
			new jQuery.SelectBox(this, options);
		});
	}
});

jQuery.SelectBox = function(selectobj, options) {
	
	var opt = options || {};
	opt.inputClass = opt.inputClass || "selectbox";
	opt.containerClass = opt.containerClass || "selectbox-wrapper";
	opt.hoverClass = opt.hoverClass || "selected";
	opt.debug = opt.debug || false;
	
	var elm_id = selectobj.id;
	var active = -1;
	var inFocus = false;
	var hasfocus = 0;
	//jquery object for select element
	var $select = $(selectobj);
	
	var oldClass = selectobj.className;
	//console.dir(oldClass);
	// jquery container object
	var $container = setupContainer(opt);
	//jquery input object 
	var $input = setupInput(opt);
	// hide select and append newly created elements
	$select.hide().before($input).before($container);
	
	
	init();
	
	$input
	.click(function(){
        if (!inFocus) {
		  $container.toggle();
		}
	})
	.focus(function(){
	   if ($container.not(':visible')) {
	       inFocus = true;
	       $container.show();
	   }
	})
	.keydown(function(event) {	   
		switch(event.keyCode) {
			case 38: // up
				event.preventDefault();
				moveSelect(-1);
				break;
			case 40: // down
				event.preventDefault();
				moveSelect(1);
				break;
			//case 9:  // tab 
			case 13: // return
				event.preventDefault(); // seems not working in mac !
				setCurrent();
				hideMe();
				break;
		}
	})
	.blur(function() {
		if ($container.is(':visible') && hasfocus > 0 ) {
			//if(opt.debug) console.log('container visible and has focus')
		} else {
			hideMe();	
		}
	});


	function hideMe() { 
		hasfocus = 0;
		$container.hide(); 
	}
	
	function init() {
		$container.append(getSelectOptions()).hide();
		var InpWidth = $input.width();
		//console.dir(InpWidth);
		if (InpWidth) $container.width(InpWidth);
    }
	
	function setupContainer(options) {
		var container = document.createElement("div");
		$container = $(container);
		$container.attr('id', elm_id+'_container');
		$container.addClass(options.containerClass);
		$container.addClass(oldClass);
		
		return $container;
	}
	
	function setupInput(options) {
		var input = document.createElement("input");
		var $input = $(input);
		$input.attr("id", elm_id+"_input");
		$input.attr("type", "text");
		$input.addClass(options.inputClass);
		$input.attr("autocomplete", "off");
		$input.attr("readonly", "readonly");
		$input.attr("tabIndex", $select.attr("tabindex")); // "I" capital is important for ie
		
		$input.addClass(oldClass);
		return $input;	
	}
	
	function moveSelect(step) {
		var lis = $("li", $container);
		if (!lis) return;

		active += step;

		if (active < 0) {
			active = 0;
		} else if (active >= lis.size()) {
			active = lis.size() - 1;
		}

		lis.removeClass(opt.hoverClass);

		$(lis[active]).addClass(opt.hoverClass);
	}
	
	function setCurrent() {	
		var li = $("li."+opt.hoverClass, $container).get(0);
		var el = li.id
		
		$select.val(el.substring(elm_id.length));
		
		$select.change();
		$input.val($(li).html());
		return true;
	}
	
	// select value
	function getCurrentSelected() {
		return $select.val();
	}
	
	// input value
	function getCurrentValue() {
		return $input.val();
	}
	
	function getSelectOptions() {
		var select_options = new Array();
		var ul = document.createElement('ul');
		$select.children('option').each(function() {
			var li = document.createElement('li');
			li.setAttribute('id', elm_id + $(this).val());
			li.innerHTML = $(this).html();
			if ($(this).is(':selected')) {
				$input.val($(this).html());
				$(li).addClass(opt.hoverClass);
			}
			ul.appendChild(li);
			$(li)
			.mouseover(function(event) {
				hasfocus = 1;
				jQuery(event.target, $container).addClass(opt.hoverClass);
			})
			.mouseout(function(event) {
				hasfocus = -1;
				jQuery(event.target, $container).removeClass(opt.hoverClass);
			})
			.click(function(event) {
				$(this).addClass(opt.hoverClass);
				setCurrent();
				hideMe();
			})
			.data('value', $(this).val());
		});
		return ul;
	}
	
};