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
						// All setup on the right table -- these will not change
						$post_type = $_POST['post_type'];
						$post_status = $_POST['post_status'];
						$post_author = $_POST['user'];
						$post_parent = $_POST['page_id'];
						$page_template = $_POST['page_template'];
						$post_category = $_POST['cat'];
						$post_topic = (int)$_POST['topic'];
						$external_url = $_POST['ext_url'];
						// pulling in the "post options" options.
						foreach ($_POST as $key => $value) {
							switch ($key) {
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
							// looping through each URL that is given in the array to get the proper values
							foreach ($url_arr as $key => $value) {
								$response = wp_remote_get($value);
								$body = wp_remote_retrieve_body($response);

								$html = new simple_html_dom();

								// Load HTML from a string
								$html->load($body);


								$uri = $value;
								$title = $html->find('title', 0)->plaintext;

								foreach($html->find('meta') as $element) {
									// getting the open graph image source
									switch ($element->property) {
										case 'og:image':
										$imgsrc = $element->content;

											break;
										case 'og:site_name':
										$site_name = $element->content;

											break;

										case 'og:description':
//										echo '<strong>'.$element->content.'</strong>';
										$pcontent = urldecode($element->content);
											break;
										case 'og:video':
											$videostr = $element->content;
											if (strpos($videostr,'vimeo') or strpos($videostr,'youtube') ) {
												$video = $videostr;
											}
											break;
// open graph title is here.
											//	case 'og:title' :
										//	$title = $element->content;
										//	break;
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
											//echo '<img src="'.$element->content.'">';
												break;

											case 'cre':
											$site_name = $element->content;
												break;
										}
								}
								// removing any @ signs from the site name if they're in there.
								$site_name = str_replace('@', '', $site_name);
								//setting the post title, and stripping the tags.
								$post_title = strip_tags($title);
								//var_dump($post_title);
								//die;
								$post_content = $pcontent;
								$pub_name = $site_name;
								$taxomony = 'post_tag';
								if (!empty($pub_name)) {
									// Check if the pub name exists & load variable with the ID
									$publication_name = term_exists( $pub_name, $taxonomy, 0 );
									
									// Create pub name if it doesn't exist
									if ( !$publication_name ) {
										$publication_name = wp_insert_term( $pub_name, $taxomony, array( 'parent' => 0 ) );
										$termID = $publication_name['term_taxonomy_id'];
									} else {
										$termID = (int)$publication_name;
									}
									//echo $termID;
	
									$custom_tax = array(
										'post_tag' => array($termID),
										'topic' => $post_topic
									);
								}
								// getting the dates array:
								if (!empty($date_arr[$i])) {
									$hasdate = true;
									$post_dates = $date_arr[$i];
									// converting the human style string to a unix timestamp
									$post_date_format = strtotime($post_dates);
									// converting the unix timestamp to the right format for WordPress to set the date.
									$data['post_date'] = date('Y-m-d H:i:s', $post_date_format);
								} elseif($date) {
									$post_date_format = strtotime($date);
									// converting the unix timestamp to the right format for WordPress to set the date.
									$data['post_date'] = date('Y-m-d H:i:s', $post_date_format);
								}
								$post_tags = $tags_arr[$i];
								$pre_content = $content_arr[$i];
								$data['post_title'] = $post_title;
								if (!empty($pre_content)) {
									$data['post_content'] = $pre_content;
								} else {
									$data['post_content'] = $post_content;
								}
								$data['tax_input'] = $custom_tax;
								$data['tags_input'] = $post_tags;
//								print_r($data);
//								echo '<hr />';
								// adding the post to the site
								// once that happens, also update the postmeta for the "link"
								if($eID = wp_insert_post( $data )) {
									update_post_meta($eID, 'link', $uri);
									if (strlen($video) > 0) {
										update_post_meta($eID, 'post_video', $video);
									}
									$item = $this->set_post_thumbnail_from_url($imgsrc, $eID);
									echo 'Added: <strong>'.get_the_title($eID).'</strong><br />';
								}

								$i++;
//								echo $eID . ' - '. $item;
								// clearing out all the variables, so that one item doesn't bump another.
								$imgsrc = '';
								$eID = '';
								$site_name = '';
								$date = '';
								$site_name = '';
								$pcontent = '';
								$title = '';
								$uri = '';
								$post_tags = '';
								$pre_content = '';
								$termID = '';
								$publication_name = '';
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
								$data['post_title'] = $post_title;
								$data['post_content'] = $post_content;
								$data['tags_input'] = $post_tags;

								if($eID = wp_insert_post( $data )) {
									foreach ($_POST as $key => $value) {
										// if the $value of the post object is an array...it's the custom fields, so let's move on with it.
										if (is_array($value) && ($key != 'title' && $key != 'content' && $key != 'date' && $key != 'tags') ) {
											update_post_meta($eID, $key, $value[$i]);
										}
									}
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
