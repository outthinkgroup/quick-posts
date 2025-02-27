(function($) {
	
var quick_post_fields = [
'<div class="postbox qp_post" id="linkadvanceddiv">'                                                                    ,
'<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><span>Quick Post Content</span></h3>',
'<div class="inside">'                                                                                           ,
'<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">'                                ,
'	<tbody><tr class="form-field">'                                                                              ,
'		<th valign="top" scope="row"><label>Title</label></th>'                                                  ,
'		<td><input type="text" style="width: 95%;" value="" size="50" class="code" name="title[]"></td>'         ,
'	</tr>'                                                                                                       ,
'	<tr class="form-field">'                                                                                     ,
'		<th valign="top" scope="row"><label>Content</label></th>'                                                ,
'		<td><textarea style="width: 95%;" rows="5" cols="50" name="content[]"></textarea></td>'                  ,
'	</tr>'                                                                                                       ,
'	<tr class="form-field">'                                                                                     ,
'		<th valign="top" scope="row"><label>Date</label></th>'                                                   ,
'		<td><input type="text" style="width: 95%;" value="" size="50" class="code" name="date[]"><br>'           ,
'			<small>(Accepts logical dates)</small></td>'                                                         ,
'	</tr>'                                                                                                       ,
'	<tr class="form-field">'                                                                                     ,
'		<th valign="top" scope="row"><label>URL to post</label></th>'                                            ,
'		<td><input type="text" style="width: 95%;" value="" size="50" class="code" name="post_URL[]"><br>'       ,
'			<small>(<strong>include http://</strong>)</small></td>'                                              ,
'	</tr>'                                                                                                       ,
'	<tr class="form-field">'                                                                                     ,
'		<th valign="top" scope="row"><label>Tags</label></th>'                                                   ,
'		<td><input type="text" style="width: 95%;" value="" size="50" class="code" name="tags[]">,'              ,
'			<small>(comma separated)</small>'                                                                    ,
'		</td>'                                                                                                   ,
'	</tr>'                                                                                                       ,
'	'                                                                                                            ,
'	<tr>'                                                                                                        ,
'		<td colspan="2" align="right">'                                                                          ,
'			<div style="margin-right:24px;">'                                                                    ,
'				<input type="button" value="+ Add More" accesskey="p" class="button-primary add_more">'          ,
'				<input type="button" value="- Remove This" accesskey="p" class="button-primary remove_this">'    ,
'			</div>'                                                                                              ,
'		</td>'                                                                                                   ,
'	</tr>'                                                                                                       ,
'</tbody></table>'                                                                                               ,
'</div>'                                                                                                         ,
'</div>'                                                                                                         
];
	
	$(function() {
		$(document).on('click', '.handlediv', function() {
			$(this).parent().find('.inside').slideToggle('fast');
		});
		
		$(document).on('click', 'input.add_more', function() {
			var tcontent = $(this).parents('.postbox');
			tcontent.clone().appendTo($('#normal-sortables')).find('input[type=text]').val('');
		});
		
		$(document).on('click', 'input.remove_this', function() {
			// Count total number of postboxes
			var totalBoxes = $('.qp_post').length;
			
			// Only remove if there's more than one box
			if (totalBoxes > 1) {
				$(this).parents('.postbox').remove();
			} else {
				alert('Cannot remove the last form. At least one must remain.');
				// Or you could hide/disable the remove button instead of showing an alert
			}
		});
		$(document).ready(function() {
			pageopts = $('#page-options').remove();
			postopts = $('#post-options').remove();
			if ($('#post_type').val() == 'post') {
				$('.general-options').after(postopts);
			} else if ($('#post_type').val() == 'page') {
				$('.general-options').after(pageopts);
			}
			
		});
		$('#post_type').change( function() {
			if ($(this).val() == 'post') {
				$('#page-options').remove();
				$('.general-options').after(postopts);
			} else if ($(this).val() == 'page') {
				$('#post-options').remove();
				$('.general-options').after(pageopts);
			}
		});
		
	});

})(jQuery);