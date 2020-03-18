<?php
// getting updated options so I can show them below.
	$options = get_option('qp_saved_templates');
	$selected = get_option('qp_selected_template');
//	delete_option('qp_saved_templates');
//	delete_option('qp_selected_template');

	// if a button was clicked to "delete" a template...
	if (isset($_POST['qpt_deleted'])) {
		// delete the array with the corresponding template name
		unset($options[$_POST['qpt_deleted']]);
		// then update the optio
		update_option( 'qp_saved_templates', $options );
		delete_option('qp_selected_template');
		// now get the new options...so that you can display them on the page.
		$options = get_option('qp_saved_templates');
		$selected = get_option('qp_selected_template');
	}
	// checking to see if the button was clicked.
	if (isset($_POST['goForth'])) {
		// checking to see if the options setting for this is empty, if it is, create a new array $mastervar
		if (empty($options)) {
			$mastervar = array();	
		// if it's not, set the var mastervar to be the same as the option
		} else {
			$mastervar = $options;
		}
		// if the template has been given a name, execute this stuff:
		if (!empty($_POST['template_name'])) {
			$tname = $_POST['template_name'];
			// mastervar array template_name => new array()
			$mastervar[$tname] = array();
			// suspicion is that I rewrite this to $mastervar[] = array(); in order to write [0] -> this value...
			// set the mastervar[$tname] variable to that array for later use.
			$mastervar[$tname] = $mastervar[$tname];
//			echo 'mastervar[$tname] =';
//			print_r($mastervar[$tname]);
			if ($_POST['post_title']) {
				$mastervar[$tname]['title'] = true;
			}
			if ($_POST['ext_url']) {
				$mastervar[$tname]['ext_url'] = true;
			}
			if ($_POST['post_content']) {
				$mastervar[$tname]['content'] = true;
			}
			if ($_POST['post_date']) {
				$mastervar[$tname]['date'] = true;
			}
			if ($_POST['tags']) {
				$mastervar[$tname]['tags'] = true;
			}
			if ($_POST['cf_key']) {
				// creating a new array for the fields
				$mastervar[$tname]['cfields'] = array();
				// building arrays from the cf key and title fields
				$cf_titlearr = $_POST['cf_title'];
				$cf_keyarr = $_POST['cf_key'];			
				for ($i = 0; $i < count($cf_keyarr); $i++) {
//					echo 'adding' .$cf_key;
					$cf_key = $cf_keyarr[$i];
					$cf_title = $cf_titlearr[$i];
					$mastervar[$tname]['cfields'][$cf_key] = $cf_title;
				}
				echo 'custom fields - check';
			}
//			$mastervar = $qp_vars;
		} // end check for template_name

//		unset($mastervar);
		update_option( 'qp_saved_templates', $mastervar ); 
		// getting the updated options so i can show them below
		$options = get_option('qp_saved_templates');
	}
	
	// checking to see which form was marked as "selected" when the form was submitted.
	if (isset($_POST['selected'])) {
		// if there is no selected template, and there is an option:
			update_option( 'qp_selected_template', $_POST['selected'] );
	} else {
		if (empty($selected) && !empty($options)) {
			$c = 0;
			foreach ($options as $key => $value) {
			// get the first template and set it as the selected template
				if ($c < 1) {
					update_option('qp_selected_template', $key);
				}
			$c++;	
			}
		}
	} // end check to see if a button has been clicked as "selected"
	
	echo 'Selected: '.$selected .'<br />';
	echo 'Options: '; var_dump($options);
?>
		
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
	.selected .button {
	background: rgb(255, 246, 182);
	}
	.selected .delete:hover {
		color: #cc0000;
	}	
</style>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#addbutton').click( function() {
		var tVal = jQuery('#selector');
		var newForm = jQuery('.newform-inner');
		var newInput = '';
		switch(tVal.val()) {
		case 'ext_url':
			newInput = '<p><label for="ext_url">External URL</label><input type="text"  name="ext_url" value="External URL"></p>';
			jQuery('#exturl-option').remove();
			break;
		case 'title':
			newInput = '<p><label for="post_title">Post Title</label><input type="text"  name="post_title" value="Post Title"></p>';
			jQuery('#title-option').remove();
			break;
		case 'content':
			newInput = '<p><label for="Tags">Post Content</label><input type="text" name="post_content" value="Post Content"></p>';
			jQuery('#content-option').remove();			
			break;
		case 'post_date':
			newInput = '<p><input type="text" name="post_date" value="Post Date"></p>';
			jQuery('#date-option').remove();
			break;
		case 'tags':
			newInput = '<p><label for="Tags">Post Tags</label><input type="hidden" name="tags" value="true"></p>';
			jQuery('#tags-option').remove();
			break;			
		case 'custom_field':
			newInput = '<p><fieldset><input type="text" name="cf_title[]" placeholder="Title" value=""><input type="text" name="cf_key[]" placeholder="custom field key" value=""></fieldset></p>';
			break;
		}
		newForm.append(newInput);
	});	
});
</script>

<div class="wrap">
	<div class="icon32" id="icon-edit"><br></div>
	<h2>Quick Post Templates</h2>
<div class="metabox">
	<form action="" method="POST">
		<p>Select which template you'd like to use to <a href="admin.php?page=add-quick-post">add Quick Posts.</a></p>
		<p>
		<?php
		if (!empty($options)) {
		// getting all the templates and showing them with buttons
		foreach ($options as $key => $value): ?>
		<span class="button-group<?php if ($key == $selected): ?> selected<?php endif; ?>">
			<button class="button" name="selected" value="<?php echo $key; ?>"><?php echo $key; ?></button>
			<button class="button delete" name="qpt_deleted" value="<?php echo $key; ?>">X</button>
		</span>
		<?php endforeach;
		} // end check for options?>
		</p>
	</form>
</div>
<div class="metabox">
	<div class="selector">
		<p>
		<select name="fields" id="selector" id="#" size="1">
			<option value="">Select your options here</option>
			<option id="exturl-option" value="ext_url">External URL</option>
			<option id="title-option" value="title">Title</option>
			<option id="content-option" value="content">Content</option>		
			<option id="tags-option" value="tags">Tags</option>
			<option id="date-option" value="post_date">Post Date</option>
			<option value="custom_field">Custom Field</option>
		</select>
		<input type="button" name="add" value="+ Add" id="addbutton" class="button">
		</p>
	</div>
	<div class="fields">

		<?php wp_nonce_field('quick-posts-template'); ?>
		<form class="newform" method="post" action="">
			<h2>Template Name: <br>
				<input type="text" name="template_name" value="" id="template_name"></h2>
			<div class="newform-inner">
			</div>
			<input type="submit" name="goForth" class="button-primary" value="Create Template" id="goForth">
		</form>
	</div>
</div><!--END metabox-->
</div><!--END wrao-->