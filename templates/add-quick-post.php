<style type="text/css" media="screen">
	.form-table th {
		width:48px;
	}
	.selector {
		float: left;
	}
	.fields {
		float: right;
		width: 600px;
	}
</style>
<div class="wrap">
	<div class="icon32" id="icon-edit"><br></div>
	<h2>Insert Posts/Pages</h2>
    <?php if (isset($_POST['post_type'])) { ?>
		<div class="updated below-h2"><p>Post/Pages added successfully.</p></div>
    <?php } ?>
<?php $options = get_option('qp_saved_templates');
$selected = get_option('qp_selected_template'); ?>
<form action="" method="post">

<?php wp_nonce_field('add-quick-post'); ?>

<div class="metabox-holder has-right-sidebar" id="poststuff">

<div class="inner-sidebar" id="side-info-column">
<div class="meta-box-sortables ui-sortable" id="side-sortables"><div class="postbox " id="linksubmitdiv">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><span>Customize</span></h3>
<div class="inside">
<div id="submitlink" class="submitbox">

<div id="minor-publishing">

<div style="display: none;">
<input type="submit" value="Save" name="save">
</div>

<div id="minor-publishing-actions">
<div id="preview-action">
</div>
<div class="clear"></div>
</div>

<div id="misc-publishing-actions">

	<div class="general-options">
		<div class="misc-pub-section">
			<label for="post_type">Type:</label>
			<select style="text-transform: capitalize;" id="post_type" name="post_type">
				<?php $args=array(
				  'public'   => true,
				); 
				$output = 'names'; // names or objects, note names is the default
				$post_types=get_post_types($args,$output);
				foreach ($post_types as $key => $value) { ?>
					<?php if ($value != 'attachment'): ?>
						<option value="<?php echo $value; ?>"><?php echo $value; ?></option>			
					<?php endif; ?>
				<?php }
				?>
			</select>
		</div>
	
		<div class="misc-pub-section">
			<label for="post_status">Status:</label>
			<select id="post_status" name="post_status">

				<option value="publish" selected="selected">Published</option>
				<option value="pending">Pending Review</option>
				<option value="draft" >Draft</option>
			</select>
		</div>
		<div class="misc-pub-section misc-pub-section">
			<label for="post_parent">Author:</label>
			<?php
				$args = array(
					'orderby'          => 'display_name',
					'order'            => 'ASC',
					'multi'            => 0,
					'show'             => 'display_name',
					'echo'             => 1,
					'selected'         => 0,
					'name'             => 'user',
					'who'				=> 'authors'
				);
				wp_dropdown_users( $args );
			?>
		</div>
	</div>
	<div id="post-options">
		<div class="misc-pub-section misc-pub-section-categories for-post">
			<label for="cat">Category:</label>
			<?php
				$args = array(
					'hide_empty' => 0
				); 
				wp_dropdown_categories( $args );
			?>
		</div>
	</div>
	<div id="page-options">
		<div class="misc-pub-section misc-pub-section-template for-page">
			<?if ( 0 != count( get_page_templates() ) ) {
					$template = !empty($post->page_template) ? $post->page_template : false;
					?>
			<label for="page_template"><?php _e('Page Template') ?></label>
			<select name="page_template" id="page_template">
			<option value='default'><?php _e('Default Template'); ?></option>
			<?php page_template_dropdown($template); ?>
			</select>
			<?php
				} ?>
		</div>
	
		<div class="misc-pub-section misc-pub-section-parent for-page">
			<label for="post_parent">Parent:</label>
			<?php
				$arr = array(
					'option_none_value' => 0,
					'show_option_none' => 'none'
				); 
			
				wp_dropdown_pages($arr);
			?>
		</div>
	</div>
	
</div>

</div>

<div id="major-publishing-actions">

<div id="publishing-action">
	<input type="submit" value="Insert Posts" accesskey="p" id="publish_quick_posts" class="button-primary" name="publish_quick_posts">
</div>
<div class="clear"></div>
</div>
<div class="clear"></div>
</div>
</div>
</div>
</div></div>

<div id="post-body">
<div id="post-body-content">


<div class="meta-box-sortables ui-sortable" id="normal-sortables">

<div class="postbox " id="linkadvanceddiv">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><span><?php echo $selected; ?></span></h3>
<div class="inside">
	<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
		<tbody>
	<?php 

//		creating new option to loop through mroe easily...since i'm so dumb
// This pulls in the "selected" template
if (empty($options[$selected])) {
	echo '<div class="updated below-h2"><p>It doesn\'t look like you\'ve created a post template. Jump over to <a href="admin.php?page=qp-templates">the template creator and make one now.</a></div>';
}
	$newoptions[] = $options[$selected];
	if (condition) {
		# code...
	}
	for($i = 0; $i < count($newoptions); ++$i) {
		$option = $newoptions[$i];
		if ($option['title']) { ?>
			<tr class="form-field">
					<th valign="top" scope="row"><label>Title</label></th>
					<td><input type="text" style="width: 95%;" value="" size="50" class="code" name="title[]"></td>
			</tr>				
<?php 	}
		if ($option['content']) { ?>
			<tr class="form-field">
				<th valign="top" scope="row"><label>Content</label></th>
				<td><textarea style="width: 95%;" rows="5" cols="50" name="content[]"></textarea></td>
			</tr>
<?php 	}
		if ($option['ext_url']) { ?>
			<tr class="form-field">
				<th valign="top" scope="row"><label>URL:</label></th>
				<td><input type="text" style="width: 95%;" class="code" name="ext_url[]"></td>
			</tr>
			
<?php	}
		if ($option['date']) { ?>
			<tr class="form-field">
				<th valign="top" scope="row"><label>Date</label></th>
				<td><input type="text" style="width: 95%;" value="" size="50" class="code" name="date[]"><br>
					<small>(Accepts logical dates)</small></td>
			</tr>
	<?php	}
			// if we have tags, spit out that form:
			if ($option['tags']) { ?>
				<tr class="form-field">
					<th valign="top" scope="row"><label>Tags</label></th>
					<td><input type="text" style="width: 95%;" value="" size="50" class="code" name="tags[]">,
						<small>(comma separated)</small>
					</td>
				</tr>
<?php 	} if ($option['cfields']) {
	foreach ($option['cfields'] as $key => $value) { ?>
		<tr class="form-field">
			<th valign="top" scope="row"><label><?php echo $value; ?></label></th>
			<td><input type="text" style="width: 95%;" value="" size="50" class="code" name="<?php echo $key.'[]'; ?>">,
			</td>
		</tr>
<?	}
}
}
?>
	<tr>
		<td colspan="2" align="right">
			<div style="margin-right:24px;">
				<input type="button" value="+ Add More" accesskey="p" class="button-primary add_more">
				<input type="button" value="- Remove This" accesskey="p" class="button-primary remove_this">
			</div>
		</td>
	</tr>
</tbody></table>
</div>
</div>
</div>

</div>
</div>
</div>

</form>
</div>