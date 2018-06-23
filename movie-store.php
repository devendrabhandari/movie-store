<?php
/*
 Plugin Name: Movie Store
 Description: This is a plugin to manage movies and its details, and ratings of each movie.
 Author: Devendra Bhandari
 Version: 1.0
 Author URI: https://www.linkedin.com/in/devendrasinghbhandari/
 Text Domain: movie_store_domain
 */

// Register Custom Post Type i.e. movies
 function movie_post_type() {

 	$labels = array(
 		'name'                  => _x( 'Movies', 'Post Type General Name', 'movie_store_domain' ),
 		'singular_name'         => _x( 'Movie', 'Post Type Singular Name', 'movie_store_domain' ),
 		'menu_name'             => __( 'Movies', 'movie_store_domain' ),
 		'name_admin_bar'        => __( 'Movie', 'movie_store_domain' ),
 		'archives'              => __( 'Movie Archives', 'movie_store_domain' ),
 		'attributes'            => __( 'Movie Attributes', 'movie_store_domain' ),
 		'parent_item_colon'     => __( 'Parent Movie:', 'movie_store_domain' ),
 		'all_items'             => __( 'All Movies', 'movie_store_domain' ),
 		'add_new_item'          => __( 'Add New Movie', 'movie_store_domain' ),
 		'add_new'               => __( 'Add New', 'movie_store_domain' ),
 		'new_item'              => __( 'New Movie', 'movie_store_domain' ),
 		'edit_item'             => __( 'Edit Movie', 'movie_store_domain' ),
 		'update_item'           => __( 'Update Movie', 'movie_store_domain' ),
 		'view_item'             => __( 'View Movie', 'movie_store_domain' ),
 		'view_items'            => __( 'View Movies', 'movie_store_domain' ),
 		'search_items'          => __( 'Search Movie', 'movie_store_domain' ),
 		'not_found'             => __( 'Not found', 'movie_store_domain' ),
 		'not_found_in_trash'    => __( 'Not found in Trash', 'movie_store_domain' ),
 		'featured_image'        => __( 'Featured Image', 'movie_store_domain' ),
 		'set_featured_image'    => __( 'Set featured image', 'movie_store_domain' ),
 		'remove_featured_image' => __( 'Remove featured image', 'movie_store_domain' ),
 		'use_featured_image'    => __( 'Use as featured image', 'movie_store_domain' ),
 		'insert_into_item'      => __( 'Insert into movie', 'movie_store_domain' ),
 		'uploaded_to_this_item' => __( 'Uploaded to this movie', 'movie_store_domain' ),
 		'items_list'            => __( 'Movies list', 'movie_store_domain' ),
 		'items_list_navigation' => __( 'Movies list navigation', 'movie_store_domain' ),
 		'filter_items_list'     => __( 'Filter items list', 'movie_store_domain' ),
 		);
 	$args = array(
 		'label'                 => __( 'Movie', 'movie_store_domain' ),
 		'description'           => __( 'Manage movies', 'movie_store_domain' ),
 		'labels'                => $labels,
 		'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
 		'hierarchical'          => false,
 		'public'                => true,
 		'show_ui'               => true,
 		'show_in_menu'          => true,
 		'menu_position'         => 5,
 		'show_in_admin_bar'     => true,
 		'show_in_nav_menus'     => true,
 		'can_export'            => true,
 		'has_archive'           => true,
 		'exclude_from_search'   => false,
 		'publicly_queryable'    => true,
 		'capability_type'       => 'post',
 		);
 	register_post_type( 'movies', $args );

 }
 add_action( 'init', 'movie_post_type', 0 );

/**
 * Register meta box to show custom field i.e. rating
 */
function wp_register_custom_meta_box() {
	add_meta_box( 'meta-box-movie-rating', __( 'Movie Rating', 'movie_store_domain' ), 'wp_movie_rating_callback', 'movies' );
}
add_action( 'add_meta_boxes', 'wp_register_custom_meta_box' );

/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function wp_movie_rating_callback( $post ) {
	$rating = get_post_meta( $post->ID, 'rating', true );
	$html = '<table><tr><td width="50">' . esc_html__('Rating', 'movie_store_domain') . ':</td><td align="left">' . (!empty($rating) ? $rating: 0) . '</td></tr></table>';
	echo $html;
}

// Remove default `wp_no_robots` from single movie post and add custom robots for the same
function custom_wp_no_robots() {
	if ( is_singular( 'movies' ) ) {
		remove_action( 'wp_head', 'noindex', 1 );
		remove_action( 'wp_head', 'follow', 1 );
		echo "<meta name='robots' content='noindex, nofollow' />\n";
	}
}
// Need to add the action with a priority less than 1 as noindex is added with a priority of 1
add_action( 'wp_head', 'custom_wp_no_robots', -1 );

// Enqueue custom css & js files
function custom_movies_scripts() {
	if ( is_singular( 'movies' ) ) {
		wp_localize_script( 'jquery', 'rating_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_style( 'rateyo-rating-style', plugins_url( '/rateyo.css', __FILE__ ), array() );
		wp_enqueue_script( 'rateyo-rating-script', plugins_url( '/rateyo.js', __FILE__ ), array(), true, true );
		wp_enqueue_script( 'rateyo-rating-custom-script', plugins_url( '/custom.js', __FILE__ ), array(), true, true );
	}
}
add_action( 'wp_enqueue_scripts', 'custom_movies_scripts' );

/**
 * Append rating widget after post content.
 *
 * @param $content Current post content.
 */
function rating_after_post_content($content) {
	global $post;
	if ( is_singular( 'movies' ) ) {
		$rating = get_post_meta( $post->ID, 'rating', true );
		$content .= '<div id="rateYo" data-rateyo-rating="' . (!empty($rating) ? $rating : 0) . '" data-post-id="' . $post->ID. '" data-rateyo-read-only="' . (!empty($rating) ? 'true' : 'false') . '"></div>';
	}
	return $content;
}
add_filter( "the_content", "rating_after_post_content" );

// Set movie rating
function save_movie_rating() {
	$post_id = isset($_POST['post_id']) ? $_POST['post_id'] : 0;
	$rating =  isset($_POST['rating']) ? $_POST['rating'] : 0;

	if ( 'publish' == get_post_status ( $post_id ) && ! empty($rating) ) {
		update_post_meta( $post_id, 'rating', $rating );
		wp_send_json(['status'=>'success']);
	} else {
		wp_send_json(['status'=>'failure']);
	}
}
add_action('wp_ajax_rate', 'save_movie_rating');
add_action('wp_ajax_nopriv_rate', 'save_movie_rating');
