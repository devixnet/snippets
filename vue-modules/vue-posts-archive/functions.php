<?php
/*
*	Return terms array of specific taxonomy
*/
function get_terms_array( $taxonomy = 'category', $first_option = true ){
	$items = [];

	$terms = get_terms([
		'taxonomy' => $taxonomy,
		'hide_empty' => false,
	]);

	if( $first_option ){
		$items[0] = '— ' . __( 'Select', 'devix' ) . ' —';
	}

	if( is_array( $terms ) && !empty( $terms ) && !is_wp_error( $terms )  ){
		foreach( $terms as $term ){
			$items[$term->term_id] = $term->name;
		}
	}

	return $items;
}

function get_posts_archive_data( $query_args, $feat_post_ids = false ){
	global $post;

	$data = [];
	$feat_posts_array = [];
	$posts_array = [];

	$search_query = isset( $query_args['s'] ) ? $query_args['s'] : false;

	if( is_array( $feat_post_ids ) && !empty( $feat_post_ids ) ){
		$query_args['post__not_in'] = $feat_post_ids;

		foreach( $feat_post_ids as $post_id ){
			$feat_posts_array[] = [
				'id' => $post_id,
				'html' => get_archive_post_html( $post_id, $search_query )
			];
		}
	}
	
	$query = new \WP_Query( $query_args );

	if( $query->have_posts() ) :
		while( $query->have_posts() ) : $query->the_post();
			$posts_array[] = [
				'id' => $post->ID,
				'html' => get_archive_post_html( false, $search_query )
			];
		endwhile;
	endif;

	wp_reset_postdata();

	$data = [
		'feat_posts_array' => $feat_posts_array,
		'posts_array' => $posts_array,
		'found_posts' => $query->found_posts,
		'max_num_pages' => $query->max_num_pages,
	];

	return $data;
}

function get_posts_archive_html( $query_args ){
	global $post;

	$html = '';

	$query = new \WP_Query( $query_args );

	$search_query = isset( $query_args['s'] ) ? $query_args['s'] : false;

	if( $query->have_posts() ) :
		$html .= '<div class="posts-archive-wrap">';
			$html .= '<div class="flex h-align-start grid-pad-h-15 grid-pad-v-40">';
				while( $query->have_posts() ) : $query->the_post();
					$html .= '<div class="col-1-3 s-col-50 xs-col-100">';
						$html .= get_archive_post_html( false, $search_query );
					$html .= '</div>';
				endwhile;
			$html .= '</div>';
		$html .= '</div>';
	endif;

	wp_reset_postdata();

	return $html;
}

function get_post_tag_label( $post_id = false ){
	global $post;

	$post_id = $post_id ? $post_id : $post->ID;
	$tag_label = '';

	$custom_tag_label = get_field( 'custom_tag_label', $post_id );

	if( $custom_tag_label ){
		$tag_label = $custom_tag_label;
	}else{
		$post_categories = get_the_category( $post_id );

		if( is_array( $post_categories ) && !empty( $post_categories ) ){
			$post_cat = array_values( $post_categories )[0];
			$tag_label = $post_cat->name;
		}
	}

	return $tag_label;
}

function get_archive_post_html( $post_id = false, $highlight_keywords = false ){
	global $post;

	if( $post_id ){
		$post = get_post( $post_id );
		setup_postdata( $post );
	}

	$html = '';
	
	$home_url = home_url();
	$post_title = get_the_title();
	$post_link = get_permalink();
	$post_categories = get_the_category();
	$tag_label = get_post_tag_label();

	if( $highlight_keywords ){
		$post_title = highlight_keywords( $post_title, $highlight_keywords );
	}
	
	$post_link_target = ( strpos( $post_link, $home_url ) !== 0 ) ? '_blank' : false;
	$post_link_target_attr = $post_link_target ? ' target="_blank"' : '';

	if( is_array( $post_categories ) && !empty( $post_categories ) ){
		$post_cat = array_values( $post_categories )[0];
		$cat_name = $post_cat->name;
		$cat_slug = $post_cat->slug;
	}

	switch( $cat_slug ){
		case 'news':
			$link_title = sprintf( __('View %s', 'devix'), $cat_name );
			break;
		case 'knowledge-base':
			$link_title = sprintf( __('Read Definition', 'devix'), $cat_name );
			break;
		default:
			$link_title = __('View Article', 'devix');
			break;
	}

	$html .= '<div class="archive-post-wrap" data-id="' . $post->ID . '">';
		if( has_post_thumbnail() ){
			$post_thumbnail_url = get_the_post_thumbnail_url( null, 'large' );

			$html .= '<a href="' . $post_link . '"' . $post_link_target_attr . '>';
				$html .= '<div class="archive-post-image bg-center bg-cover" style="background-image: url(' . $post_thumbnail_url . ');">';
					$html .= $tag_label ? '<div class="archive-post-tag">' . $tag_label . '</div>' : '';
				$html .= '</div>';
			$html .= '</a>';
		}
		
		$html .= '<div class="archive-post-title">';
			$html .= '<a href="' . $post_link . '"' . $post_link_target_attr . '>';
				$html .= '<h3>' . $post_title . '</h3>';
			$html .= '</a>';
		$html .= '</div>';
		$html .= '<div class="archive-post-link">';
			$html .= '<a href="' . $post_link . '"' . $post_link_target_attr . ' class="button">';
				$html .= $post_title;
			$html .= '</a>';
		$html .= '</div>';
	$html .= '</div>';
	
	if( $post_id ){
		wp_reset_postdata();
	}

	return $html;
}

function get_popup_post_html( $post_id ){
	global $post;

	$post = get_post( $post_id );
	setup_postdata( $post );

	$html = '';
	
	$post_title = get_the_title();
	$post_content = get_the_content();
	$post_content = apply_filters( 'the_content', $post_content );

	$html .= '<div class="popup-post-wrap">';
		$html .= '<div class="popup-post-title">';
			$html .= get_title( $post_title, 'h1', 'h4' );
		$html .= '</div>';
		$html .= '<div class="popup-post-content">';
			$html .= $post_content;
		$html .= '</div>';
	$html .= '</div>';
	
	wp_reset_postdata();

	return $html;
}