<?php
add_action( 'rest_api_init', function () {
	/* POST */
	register_rest_route( 'vue-posts-archive', '/load-posts/', array(
		'methods' => 'POST',
		'callback' => 'vue_posts_archive_api_load_posts',
	));

	register_rest_route( 'vue-posts-archive', '/load-popup-post/', array(
		'methods' => 'POST',
		'callback' => 'vue_posts_archive_api_load_popup_post',
	));
});

function vue_posts_archive_api_load_posts( $request ){
	$request_params = $request->get_params();
	$query_args = $request_params['query_args'];
	$paged = $request_params['paged'];
	$search_query = $request_params['search_query'];
	$current_cat_id = $request_params['current_cat_id'];
	$feat_post_ids = $request_params['feat_post_ids'];
	$posts_array = [];

	$status = 'fail';

	if( is_array( $query_args ) && !empty( $query_args ) ){
		if( $paged ){
			$query_args['paged'] = $paged;
		}
		
		$query_args['tax_query'] = is_array( $query_args['tax_query'] ) ? $query_args['tax_query'] : [];

		if( $current_cat_id ){
			$query_args['tax_query'][] = [
				'taxonomy' => 'category',
				'field' => 'term_id',
				'terms' => $current_cat_id,
			];
		}

		if( !empty( $search_query ) ){
			$query_args['s'] = $search_query;
		}

		if( $current_cat_id != 0 || !empty( $search_query ) ){
			$feat_post_ids = false;
		}

		$archive_data = get_posts_archive_data( $query_args, $feat_post_ids );
		$status = 'success';
	}

	$response = [
		'status' => $status,
		'feat_posts_array' => $archive_data['feat_posts_array'],
		'posts_array' => $archive_data['posts_array'],
		'max_num_pages' => $archive_data['max_num_pages'],
		'query_args' => $query_args
	];

	return $response;
}

function vue_posts_archive_api_load_popup_post( $request ){
	$request_params = $request->get_params();
	$post_id = $request_params['post_id'];

	if( $post_id ){
		$html = get_popup_post_html( $post_id );
	}

	$response = [
		'html' => $html,
	];

	return $response;
}