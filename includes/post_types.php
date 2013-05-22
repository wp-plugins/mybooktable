<?php

/*---------------------------------------------------------*/
/* Custom Post Types and Taxonomies                        */
/*---------------------------------------------------------*/

function mbt_create_post_types_and_taxonomies()
{
	register_post_type('mbt_book', array(
		'labels' => array(
			'name' => 'Books',
			'singular_name' => 'Book',
			'all_items' => 'Books',
			'add_new' => 'Add New Book',
			'add_new_item' => 'Add New Book',
			'new_item_name' => 'New Book',
			'edit_item' => 'Edit Book',
			'view_item' => 'View Book',
			'update_item' => 'Update Book',
			'search_items' => 'Search Books',
			'parent_item' => 'Parent Book',
			'parent_item_colon' => 'Parent Book:'
		),
		'public' => true,
		'show_ui' => true,
		'query_var' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 5,
		'exclude_from_search' => false,
		'has_archive' => true,
		'supports' => array('title', 'thumbnail'),
		'rewrite' => array('slug' => apply_filters('mbt_book_rewrite_name', 'books'))
	));

	register_taxonomy('mbt_author', 'mbt_book', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => 'Authors',
			'singular_name' => 'Author',
			'all_items' => 'All Authors',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Authors',
			'new_item_name' => 'New Authors',
			'edit_item' => 'Edit Authors',
			'view_item' => 'View Authors',
			'update_item' => 'Update Authors',
			'search_items' => 'Search Authors',
			'parent_item' => 'Parent Authors',
			'parent_item_colon' => 'Parent Authors:'
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => apply_filters('mbt_author_rewrite_name', 'authors'))
	));

	register_taxonomy('mbt_genre', 'mbt_book', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => 'Genres',
			'singular_name' => 'Genre',
			'all_items' => 'All Genres',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Genres',
			'new_item_name' => 'New Genres',
			'edit_item' => 'Edit Genres',
			'view_item' => 'View Genres',
			'update_item' => 'Update Genres',
			'search_items' => 'Search Genres',
			'parent_item' => 'Parent Genres',
			'parent_item_colon' => 'Parent Genres:'
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => apply_filters('mbt_genre_rewrite_name', 'genre'))
	));

	register_taxonomy('mbt_series', 'mbt_book', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => 'Series',
			'singular_name' => 'Series',
			'all_items' => 'All Series',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Series',
			'new_item_name' => 'New Series',
			'edit_item' => 'Edit Series',
			'view_item' => 'View Series',
			'update_item' => 'Update Series',
			'search_items' => 'Search Series',
			'parent_item' => 'Parent Series',
			'parent_item_colon' => 'Parent Series:'
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => apply_filters('mbt_series_rewrite_name', 'series'))
	));
}

function mbt_override_parent_files() {
	global $pagenow, $parent_file, $submenu_file;

	if($pagenow == "edit-tags.php" and ($_GET['taxonomy'] == "mbt_series" or $_GET['taxonomy'] == "mbt_genre" or $_GET['taxonomy'] == "mbt_author")) {
		$parent_file = "mbt_landing_page";
	}

	if(($pagenow == "post.php" or $pagenow == "post-new.php") and get_post_type() == "mbt_book") {
		$parent_file = "mbt_landing_page";
		$submenu_file = "edit.php?post_type=mbt_book";
	}

	return $parent_file;
}
add_filter('parent_file', 'mbt_override_parent_files');

function mbt_override_post_updated_messages($messages) {
	$post_id = isset($_GET['post']) ? intval($_GET['post']) : (isset($_POST['post_ID']) ? intval($_POST['post_ID']) : 0);
	if(get_post_type($post_id) == "mbt_book") {
		$messages['post'][1] = sprintf('Book updated. <a href="%s">View book</a>', esc_url(get_permalink($post_id)));
	}
	return $messages;
}
add_filter('post_updated_messages', 'mbt_override_post_updated_messages');