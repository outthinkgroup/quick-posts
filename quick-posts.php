<?php
/*
Plugin Name: Quick Posts [do not upgrade]
Plugin URI: http://geekoutwith.me/quick-posts
Description: Add multiple pages or posts quickly and easily, apply page templates and parents to pages and categories and tags to posts. You can also set post status and author.
Version: 1.3
Author: Joseph Hinson
Author URI: http://geekoutwith.me/
License: GPL2
*/
include_once('inc/dom_scraping_functions.php');
if (!class_exists('QuickPosts'))
{
	class QuickPosts
	{
		public $plugin_url;

		public function __construct()
		{
			$this->plugin_url = trailingslashit(WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)));

			add_action('admin_menu', array(&$this, 'admin_menu'));
			add_action('admin_print_scripts', array(&$this, 'print_scripts'));
		}

		public function install()
		{

		}
		public function set_post_thumbnail_from_url($uri, $postid) {
//			echo $uri;
			$attachment = '';
			$attach_data = '';
			$title = '';
			if(strpos($uri,'?')) {
				$uri = preg_replace('/\?.*/', '', $uri);
			} // endif
			$info = pathinfo($uri);
			if($info['extension'] != "jpg" && $info['extension'] != "jpeg" && $info['extension'] != "gif" && $info['extension'] != "png")
			{
				return false;
			} else {
				/*
				$args = array(
					'headers' => array(
				        'user-agent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2)'
				      ),
				    'timeout'     => 10,
				    'filename'    => null
				);
				$data = wp_remote_get($uri, $args);
				$file = wp_remote_retrieve_body($data);
				*/
//				echo '<img src="'.$uri.'"">';
				$ch = curl_init($uri);
				$uploads_dir = wp_upload_dir();
				$title = get_the_title($postid);
				$filename = $uploads_dir['path'].'/'.$postid.'-'.$info['basename'];
				$fh = fopen($filename, "w");
				curl_setopt($ch, CURLOPT_FILE, $fh);
				curl_setopt($ch, CURLOPT_TIMEOUT, 500);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_exec($ch);
				curl_close($ch);
				$wp_filetype = wp_check_filetype(basename($filename), null);
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title' => $title,
					'post_content' => $title,
					'post_status' => 'inherit'
				);

				$attach_id = wp_insert_attachment($attachment, $filename, $postid);

				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id,  $attach_data );
				add_post_meta($postid, '_thumbnail_id', $attach_id);
//				echo $attach_id;
				return $attach_id;
			}
		}
		public function admin_menu()
		{
			add_menu_page('Add quick Post(s) or Page(s)', 'Quick Posts', 'administrator', 'add-quick-post', array(&$this, 'display_form'), plugin_dir_url( __FILE__ ).'/img/icon.png');
			//add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
			add_submenu_page( 'add-quick-post', 'Template Builder', 'Template Builder', 'manage_options', 'qp-templates', array(&$this, 'qp_template_editor') );

		}
		public function qp_template_editor() {
			include 'templates/qp-templates.php';
		}
		public function display_form()
		{
			global $wpdb;

			$current_page = $_GET['page'];
			$publish_quick_posts = isset($_POST['publish_quick_posts']) ? true : false;

			if ($publish_quick_posts)
			{
				check_admin_referer('add-quick-post');
			}

			switch ($current_page)
			{
				case 'add-quick-post':

					if ($publish_quick_posts)
					{
						//print_r($_POST);
						// All setup on the right table -- these will not change
						$post_type = $_POST['post_type'];
						$post_status = $_POST['post_status'];
						$post_author = $_POST['user'];
						$post_parent = $_POST['page_id'];
						$page_template = $_POST['page_template'];
						$post_category = $_POST['cat'];
						$post_topic = (int)$_POST['topic'];
						$post_publication = (int)$_POST['publication'];
						$external_url = $_POST['ext_url'];
						// pulling in the "post options" options.
						// the key / value pairs are field name => value
						foreach ($_POST as $name => $value) {
							switch ($name) {
								case 'ext_url':
									$url_arr = $value;
									break;
								case 'title':
									$title_arr = $value;
									break;
								case 'content':
									$content_arr = $value;
									break;
								case 'date':
									$date_arr = $value;
									break;
								case 'tags':
									$tags_arr = $value;
									break;
								default:
									break;
							}
						}

						$data = array();
						$data['post_status'] = $post_status;
						$data['post_parent'] = $post_parent;
						$data['post_type'] = $post_type;
						$data['post_author'] = $post_author;
						$data['post_category'] = array($post_category);						
						if ($url_arr) {
							$i = 0;
							foreach ($url_arr as $key => $value) {
								// Initialize default values from form data first
								$post_title = !empty($title_arr[$i]) ? $title_arr[$i] : '';
								$post_content = !empty($content_arr[$i]) ? $content_arr[$i] : '';
								$post_tags = !empty($tags_arr[$i]) ? $tags_arr[$i] : '';
								
								// Only scrape URL if the form fields are empty
								$response = wp_remote_get($value);
								$body = wp_remote_retrieve_body($response);
								
								$html = new simple_html_dom();
								$html->load($body);
								
								// Initialize variables
								$imgsrc = '';
								$site_name = '';
								$pcontent = '';
								$video = '';
								$date = '';
								
								foreach($html->find('meta') as $element) {
									switch ($element->property) {
										case 'og:image':
											$imgsrc = $element->content;
											break;
										case 'og:site_name':
											$site_name = $element->content;
											break;
										case 'og:description':
											$pcontent = urldecode($element->content);
											break;
										case 'og:video':
											$videostr = $element->content;
											if (strpos($videostr,'vimeo') or strpos($videostr,'youtube')) {
												$video = $videostr;
											}
											break;
										case 'article:published_time':
											$date = $element->content;
											break;
									}
									switch ($element->name) {
										case 'article:published':
											$date = $element->content;
											break;
										case 'twitter:image':
											$imgsrc = $element->content;
											break;
										case 'cre':
											$site_name = $element->content;
											break;
									}
								}
								// getting the dates array:
								if (empty($date) && !empty($date_arr[$i]) ) {
									$hasdate = true;
									$post_dates = $date_arr[$i];
									// converting the human style string to a unix timestamp
									$post_date_format = strtotime($post_dates);
									// converting the unix timestamp to the right format for WordPress to set the date.
									$data['post_date'] = date('Y-m-d H:i:s', $post_date_format);
								}
								// Remove @ signs from site name
								$site_name = str_replace('@', '', $site_name);
								
								// If title is empty, get it from scraping
								if (empty($post_title)) {
									$scraped_title = $html->find('title', 0)->plaintext;
									$post_title = strip_tags($scraped_title);
								}
								
								// If content is empty, get it from scraping
								if (empty($post_content)) {
									$post_content = $pcontent;
								}

								// Set up post data
								$data['post_title'] = $post_title;
								$data['post_content'] = $post_content;
								
								// Create the post
								if($eID = wp_insert_post($data)) {
									update_post_meta($eID, 'press_url', $value);
									
									// Store publisher as post meta
									if (!empty($site_name)) {
										update_post_meta($eID, 'publisher', $site_name);
									}
									
									if (!empty($video)) {
										update_post_meta($eID, 'post_video', $video);
									}
									if (!empty($imgsrc)) {
										$item = $this->set_post_thumbnail_from_url($imgsrc, $eID);
									}
									
									foreach ($_POST as $key => $value) {
										// if the $value of the post object is an array...it's the custom fields so let's process them
										// Todo: this should be cleaned up to be more organized.
										if (is_array($value) && ($key != 'title' && $key != 'content' && $key != 'date' && $key != 'tags' && !empty($value)) ) {
											update_post_meta($eID, $key, $value[$i]);
										}
									}
									
									if ($page_template != 'default' ) {
										update_post_meta($eID, '_wp_page_template', $page_template);
									}

								}
								
								$i++;
								
								// Clear variables for next iteration
								$imgsrc = '';
								$eID = '';
								$site_name = '';
								$date = '';
								$pcontent = '';
								$title = '';
								$uri = '';
								$post_tags = '';
								$pre_content = '';
							}
						} else {
							for ($i = 0; $i < count($title_arr); $i++) {
								// looping through the arrays set above...to pull out the specific value for each post

								$post_title = $title_arr[$i];
								$post_content = $content_arr[$i];
								$post_tags = $tags_arr[$i];

								// getting the dates array:
								if (!empty($date_arr[$i])) {
									$hasdate = true;
									$post_dates = $date_arr[$i];
									// converting the human style string to a unix timestamp
									$post_date_format = strtotime($post_dates);
									// converting the unix timestamp to the right format for WordPress to set the date.
									$data['post_date'] = date('Y-m-d H:i:s', $post_date_format);
								}
								$custom_tax = array(
									'topic' => array($post_topic),
									'publication' => array($post_publication)
								);
								$data['post_title'] = $post_title;
								$data['post_content'] = $post_content;
								$data['tags_input'] = $post_tags;
								//$data['tax_input'] = $custom_tax;
								

								if($eID = wp_insert_post( $data )) {
									foreach ($_POST as $key => $value) {
										// if the $value of the post object is an array...it's the custom fields, so let's move on with it.
										if (is_array($value) && ($key != 'title' && $key != 'content' && $key != 'date' && $key != 'tags') ) {
											update_post_meta($eID, $key, $value[$i]);
										}
									}
									wp_set_object_terms($eID, $post_topic, 'topic');
									wp_set_object_terms($eID, $post_publication, 'publication');
									if ($page_template != 'default' ) {
										update_post_meta($eID, '_wp_page_template', $page_template);
									}
								}
							}
						}
					}

					include 'templates/add-quick-post.php';
					break;
			}
		}

		public function print_scripts()
		{
			wp_enqueue_script('jquery');
			wp_enqueue_script('quick-post', $this->plugin_url . 'js/quick-posts.js', array('jquery'));
		}
	}
}

if (class_exists('QuickPosts'))
{
	$quickposts = new QuickPosts();
	register_activation_hook(__FILE__, array(&$quickposts, 'install'));
}

?>
