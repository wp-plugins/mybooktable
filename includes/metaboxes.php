<?php

function mbt_metaboxes_init() {
	add_action('wp_ajax_mbt_buybuttons_metabox', 'mbt_buybuttons_metabox_ajax');
	add_action('wp_ajax_mbt_metadata_metabox', 'mbt_metadata_metabox_ajax');
	add_action('wp_ajax_mbt_isbn_preview', 'mbt_isbn_preview_ajax');
	add_action('admin_enqueue_scripts', 'mbt_enqueue_metabox_js');

	add_action('save_post', 'mbt_save_metadata_metabox');
	add_action('save_post', 'mbt_save_buybuttons_metabox');
	add_action('save_post', 'mbt_save_series_order_metabox');

	add_action('add_meta_boxes', 'mbt_add_metaboxes', 9);
}
add_action('mbt_init', 'mbt_metaboxes_init');

function mbt_add_metaboxes() {
	add_meta_box('mbt_blurb', __('Book Blurb', 'mybooktable'), 'mbt_book_blurb_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_metadata', __('Book Details', 'mybooktable'), 'mbt_metadata_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_buybuttons', __('Buy Buttons', 'mybooktable'), 'mbt_buybuttons_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_overview', __('Book Overview', 'mybooktable'), 'mbt_overview_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_series_order', __('Series Order', 'mybooktable'), 'mbt_series_order_metabox', 'mbt_book', 'side', 'default');
}

function mbt_enqueue_metabox_js() {
	if(!mbt_is_mbt_admin_page()) { return; }

	wp_enqueue_script('mbt-metaboxes', plugins_url('js/metaboxes.js', dirname(__FILE__)), array('jquery'), MBT_VERSION);
	wp_localize_script('mbt-metaboxes', 'mbt_metabox_i18n', array(
		'author_helptext' => '<p class="description"><a href="'.admin_url('edit-tags.php?taxonomy=mbt_author&post_type=mbt_book').'" target="_blank">'.__('Set the priority (order) of the authors.', 'mybooktable').'</a></p>'
	));
}



/*---------------------------------------------------------*/
/* Book Blurb Metabox                                      */
/*---------------------------------------------------------*/

function mbt_book_blurb_metabox($post) {
?>
	<label class="screen-reader-text" for="excerpt"><?php _e('Excerpt', 'mybooktable'); ?></label><textarea rows="1" cols="40" name="excerpt" id="excerpt"><?php echo($post->post_excerpt); ?></textarea>
	<p>
	<?php printf(__('Book Blurbs are hand-crafted summaries of your book. The goal of a book blurb is to convince strangers that they need buy your book in 100 words or less. Answer the question "why would I want to read this book?" <a href="%s" target="_blank">Learn more about writing your book blurb.</a>', 'mybooktable'), admin_url('admin.php?page=mbt_help&mbt_video_tutorial=book_blurbs')); ?>
	</p>
<?php
}



/*---------------------------------------------------------*/
/* Overview Metabox                                        */
/*---------------------------------------------------------*/

function mbt_overview_metabox($post) {
	wp_editor($post->post_content, 'content', array('dfw' => true, 'tabfocus_elements' => 'sample-permalink,post-preview', 'editor_height' => 360) );
	echo('<p>');
	_e('Book Overview is a longer description of your book. This typically includes all the text from the back cover of the book plus, endorsements and any other promotional materials from interior flaps or initial pages. This is also a good place to embed a book trailer if you have one.', 'mybooktable');
	echo('</p>');
}



/*---------------------------------------------------------*/
/* Metadata Metabox                                        */
/*---------------------------------------------------------*/

function mbt_metadata_metabox_ajax() {
	if(isset($_REQUEST['image_id'])) {
		$image = wp_get_attachment_image_src($_REQUEST['image_id'], 'mbt_book_image');
		list($src, $width, $height) = $image ? $image : mbt_get_placeholder_image_src();
		echo('<img src="'.$src.'" class="mbt-book-image">');
	}
	die();
}

function mbt_isbn_preview_ajax() {
	echo(mbt_isbn_preview_feedback($_REQUEST['data']));
	die();
}

function mbt_isbn_preview_feedback($isbn) {
	if(empty($isbn)) {
		if(mbt_get_setting('reviews_box') !== 'none') {
			$output = '<span class="mbt_admin_message_warning">'.__('You will not have reviews for this book. Please enter the book ISBN.', 'mybooktable').'</span>';
		} else {
			$output = '';
		}
	} else {
		$matches = array();
		preg_match("/^([0-9][0-9\-]{8,}[0-9Xx])$/", $isbn, $matches);
		if(!empty($matches[1])) {
			$filtered_isbn = preg_replace("/[^0-9Xx]/", "", $isbn);
			$output = '<span class="mbt_admin_message_success">'.__('Valid ISBN', 'mybooktable').' <a href="http://www.isbnsearch.org/isbn/'.$filtered_isbn.'" target="_blank">'.__('(verify book)', 'mybooktable').'</a></span>';
		} else {
			$output = '<span class="mbt_admin_message_failure">'.__('Invalid ISBN', 'mybooktable').'</span>';
		}
	}
	return $output;
}

function mbt_metadata_metabox($post) {
?>
	<table class="form-table mbt_metadata_metabox">
		<tr>
			<td rowspan="7">
				<h4 class="mbt-cover-image-title"><?php _e('Book Cover Image', 'mybooktable'); ?></h4>
				<?php mbt_the_book_image(); ?><br>
				<input type="hidden" id="mbt_book_image_id" name="mbt_book_image_id" value="<?php echo(get_post_meta($post->ID, "mbt_book_image_id", true)); ?>" />
				<input id="mbt_set_book_image_button" type="button" class="button" value="<?php _e('Set cover image', 'mybooktable'); ?>" />
			</td>
			<th><label for="mbt_unique_id">ISBN</label></th>
			<td>
				<div class="mbt_feedback mbt_api_key_feedback"><?php echo(mbt_isbn_preview_feedback(get_post_meta($post->ID, "mbt_unique_id", true))); ?></div>
				<input type="text" name="mbt_unique_id" id="mbt_unique_id" value="<?php echo(get_post_meta($post->ID, "mbt_unique_id", true)); ?>"  class="mbt_feedback_refresh" data-refresh-action="mbt_isbn_preview" data-element="mbt_unique_id"/>
				<p class="description"><?php _e('The ISBN number is needed if you want to enable GoodReads or Amazon reviews. (optional)', 'mybooktable'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_sample"><?php _e('Sample Chapter', 'mybooktable'); ?></label></th>
			<td>
				<input type="text" id="mbt_sample_url" name="mbt_sample_url" value="<?php echo(get_post_meta($post->ID, "mbt_sample_url", true)); ?>" />
				<input id="mbt_upload_sample_button" type="button" class="button" value="<?php _e('Upload', 'mybooktable'); ?>" />
				<p class="description"><?php _e('Upload a sample chapter from your book to give viewers a preview. We recommend using a .pdf format for the sample chapter. (optional)', 'mybooktable'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_book_length"><?php _e('Book Length', 'mybooktable'); ?></label></th>
			<td>
				<input type="text" name="mbt_book_length" id="mbt_book_length" value="<?php echo(get_post_meta($post->ID, "mbt_book_length", true)); ?>" />
				<p class="description"><?php _e('Is this book a short story, a complete novel, or an epic drama? (optional)', 'mybooktable'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_price"><?php _e('List Price', 'mybooktable'); ?></label></th>
			<td>
				<input type="text" name="mbt_price" id="mbt_price" value="<?php echo(get_post_meta($post->ID, "mbt_price", true)); ?>" />
				<p class="description"><?php _e('You can typically find the list price just above the ISBN barcode on the back cover of the book. (optional)', 'mybooktable'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_sale_price"><?php _e('Sale Price', 'mybooktable'); ?></label></th>
			<td>
				<input type="text" name="mbt_sale_price" id="mbt_sale_price" value="<?php echo(get_post_meta($post->ID, "mbt_sale_price", true)); ?>" />
				<p class="description"><?php _e('Setting a sale price will cross through the price field and show this price as well. (optional)', 'mybooktable'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_publisher_name"><?php _e('Publisher Name', 'mybooktable'); ?></label></th>
			<td>
				<input type="text" name="mbt_publisher_name" id="mbt_publisher_name" value="<?php echo(get_post_meta($post->ID, "mbt_publisher_name", true)); ?>" />
				<p class="description"><?php _e('(optional)', 'mybooktable'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_publisher_url"><?php _e('Publisher URL', 'mybooktable'); ?></label></th>
			<td>
				<input type="text" name="mbt_publisher_url" id="mbt_publisher_url" value="<?php echo(get_post_meta($post->ID, "mbt_publisher_url", true)); ?>" />
				<p class="description"><?php _e('Setting a publisher URL will turn the "Publisher Name" into a link to this address. (optional)', 'mybooktable'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_publication_year"><?php _e('Publication Year', 'mybooktable'); ?></label></th>
			<td>
				<input type="text" name="mbt_publication_year" id="mbt_publication_year" value="<?php echo(get_post_meta($post->ID, "mbt_publication_year", true)); ?>" />
				<p class="description"><?php _e('(optional)', 'mybooktable'); ?></p>
			</td>
		</tr>
	</table>
<?php
}

function mbt_save_metadata_metabox($post_id) {
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){return;}

	if(get_post_type($post_id) == "mbt_book") {
		if(isset($_REQUEST['mbt_book_image_id'])) { update_post_meta($post_id, "mbt_book_image_id", $_REQUEST['mbt_book_image_id']); }
		if(isset($_REQUEST['mbt_unique_id'])) { update_post_meta($post_id, "mbt_unique_id", preg_replace("/[^0-9Xx]/", "", $_REQUEST['mbt_unique_id'])); }
		if(isset($_REQUEST['mbt_sample_url'])) { update_post_meta($post_id, "mbt_sample_url", $_REQUEST['mbt_sample_url']); }
		if(isset($_REQUEST['mbt_book_length'])) { update_post_meta($post_id, "mbt_book_length", $_REQUEST['mbt_book_length']); }
		if(isset($_REQUEST['mbt_price'])) { update_post_meta($post_id, "mbt_price", $_REQUEST['mbt_price']); }
		if(isset($_REQUEST['mbt_sale_price'])) { update_post_meta($post_id, "mbt_sale_price", $_REQUEST['mbt_sale_price']); }
		if(isset($_REQUEST['mbt_publisher_name'])) { update_post_meta($post_id, "mbt_publisher_name", $_REQUEST['mbt_publisher_name']); }
		if(isset($_REQUEST['mbt_publisher_url'])) { update_post_meta($post_id, "mbt_publisher_url", $_REQUEST['mbt_publisher_url']); }
		if(isset($_REQUEST['mbt_publication_year'])) { update_post_meta($post_id, "mbt_publication_year", $_REQUEST['mbt_publication_year']); }
	}
}



/*---------------------------------------------------------*/
/* Buy Button Metabox                                      */
/*---------------------------------------------------------*/

function mbt_buybuttons_metabox_editor($data, $num, $store) {
	$output  = '<div class="mbt_buybutton_editor">';
	$output .= '<div class="mbt_buybutton_editor_header">';
	$output .= '<button class="mbt_buybutton_remover button">'.__('Remove').'</button>';
	$output .= '<h4 class="mbt_buybutton_title">'.$store['name'].'</h4>';
	$output .= '</div>';
	$output .= '<div class="mbt_buybutton_editor_content">';
	$output .= mbt_buybutton_editor($data, "mbt_buybutton".$num, $store);
	$output .= '</div>';
	$output .= '<div class="mbt_buybutton_editor_footer">';
	$output .= '<span class="mbt_buybutton_display_title">Display as:</span>';
	$display = (empty($data['display'])) ? 'button' : $data['display'];
	$output .= '<label class="mbt_buybutton_display"><input type="radio" name="mbt_buybutton'.$num.'[display]" value="button" '.checked($display, 'button', false).'>'.__('Button', 'mybooktable').'</label>';
	$output .= '<label class="mbt_buybutton_display"><input type="radio" name="mbt_buybutton'.$num.'[display]" value="text" '.checked($display, 'text', false).'>'.__('Text Bullet', 'mybooktable').'</label>';
	$output .= '</div>';
	$output .= '</div>';
	return $output;
}

function mbt_buybuttons_metabox_ajax() {
	$stores = mbt_get_stores();
	if(empty($stores[$_REQUEST['store']])) { die(); }
	echo(mbt_buybuttons_metabox_editor(array('store' => $_REQUEST['store']), 0, $stores[$_REQUEST['store']]));
	die();
}

function mbt_buybuttons_metabox($post) {
	wp_nonce_field(plugin_basename(__FILE__), 'mbt_nonce');

	if(!mbt_get_setting('enable_default_affiliates') and mbt_get_upgrade() === false) {
		echo('<a href="admin.php?page=mbt_settings&mbt_setup_default_affiliates=1">'.__('Activate Amazon and Barnes &amp; Noble Buttons').'</a>');
	}

	echo('<div class="mbt-buybuttons-note">'.mbt_get_upgrade_message(false, __('Want more options? Upgrade your MyBookTable and get the Universal Buy Button.', 'mybooktable')).'</div>');

	$stores = mbt_get_stores();
	uasort($stores, create_function('$a,$b', 'return strcasecmp($a["name"],$b["name"]);'));
	echo('Choose One:');
	echo('<select id="mbt_store_selector">');
	echo('<option value="">'.__('-- Choose One --').'</option>');
	foreach($stores as $slug => $store) {
		echo('<option value="'.$slug.'">'.$store['name'].'</option>');
	}
	echo('</select>');
	echo('<button id="mbt_buybutton_adder" class="button">'.__('Add').'</button>');

	echo('<div id="mbt_buybutton_editors">');
	$buybuttons = mbt_query_buybuttons($post->ID);
	if(!empty($buybuttons)) {
		for($i = 0; $i < count($buybuttons); $i++) {
			$buybutton = $buybuttons[$i];
			if(empty($stores[$buybutton['store']])) { continue; }
			echo(mbt_buybuttons_metabox_editor($buybutton, $i+1, $stores[$buybutton['store']]));
		}
	}
	echo('</div>');
}

function mbt_save_buybuttons_metabox($post_id) {
	if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !isset($_REQUEST['mbt_nonce']) || !wp_verify_nonce($_REQUEST['mbt_nonce'], plugin_basename(__FILE__))){return;}

	if(get_post_type($post_id) == "mbt_book") {
		$stores = mbt_get_stores();
		$buybuttons = array();
		for($i = 1; isset($_REQUEST['mbt_buybutton'.$i]); $i++) {
			$buybutton = $_REQUEST['mbt_buybutton'.$i];
			if(empty($stores[$buybutton['store']])) { continue; }
			$buybutton['url'] = preg_replace('/[\r\n]/', '', $buybutton['url']);
			$buybuttons[] = apply_filters('mbt_buybutton_save', $buybutton, $stores[$buybutton['store']]);
		}
		update_post_meta($post_id, "mbt_buybuttons", $buybuttons);
	}
}



/*---------------------------------------------------------*/
/* Series Order Metabox                                    */
/*---------------------------------------------------------*/

function mbt_series_order_metabox($post) {
?>
	<label for="mbt_series_order"><?php _e('Book Number', 'mybooktable'); ?>: </label><input name="mbt_series_order" type="text" size="4" id="mbt_series_order" value="<?php echo(esc_attr(get_post_meta($post->ID, "mbt_series_order", true))); ?>" />
	<p class="mbt-helper-description"><?php _e('Use this to order books within a series.', 'mybooktable'); ?></p>
<?php
}

function mbt_save_series_order_metabox($post_id) {
	if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !isset($_REQUEST['mbt_nonce']) || !wp_verify_nonce($_REQUEST['mbt_nonce'], plugin_basename(__FILE__))){return;}

	if(get_post_type($post_id) == "mbt_book") {
		update_post_meta($post_id, "mbt_series_order", $_REQUEST["mbt_series_order"]);
	}
}
