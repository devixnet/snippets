<?php
/*
*	Return posts archive HTML
*/
function vue_get_posts_archive( $settings = [] ){
	$params = '';

	// echo '<pre>';
	// var_dump( $settings );
	// echo '</pre>';

	if( is_array( $settings['options'] ) && !empty( $settings['options'] ) ){
		$params .= " :options='" . json_encode( $settings['options'] ) . "'";
	}

	if( is_array( $settings['query_args'] ) && !empty( $settings['query_args'] ) ){
		$params .= " :query_args='" . json_encode( $settings['query_args'] ) . "'";
	}

	if( is_array( $settings['query_data'] ) && !empty( $settings['query_data'] ) ){
		$params .= " :query_data='" . json_encode( $settings['query_data'] ) . "'";
	}

	if( is_array( $settings['feat_posts_array'] ) && !empty( $settings['feat_posts_array'] ) ){
		$params .= " :feat_posts_array='" . json_encode( $settings['feat_posts_array'] ) . "'";
	}

	if( is_array( $settings['posts_array'] ) && !empty( $settings['posts_array'] ) ){
		$params .= " :posts_array='" . json_encode( $settings['posts_array'] ) . "'";
	}

	if( is_array( $settings['feat_post_ids'] ) && !empty( $settings['feat_post_ids'] ) ){
		$params .= " :feat_post_ids='" . json_encode( $settings['feat_post_ids'] ) . "'";
	}

	return '<au-vue-posts-archive' . $params . '></au-vue-posts-archive>';
}