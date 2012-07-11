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
