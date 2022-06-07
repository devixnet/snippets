<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
*	Elementor Posts Archive Widget.
*/
class Posts_Archive_Widget extends Widget_Base{
	public function get_name() {
		return 'posts-archive';
	}

	public function get_title() {
		return __( 'Posts Archive', 'devix' );
	}

	public function get_icon() {
		return 'fa fa-shield';
	}

	public function get_categories() {
		return [ 'widgets' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'slider_settings',
			[
				'label' => __('Posts Archive', 'devix'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		// $this->add_control(
		// 	'archive_title',
		// 	[
		// 		'label' => __( 'Archive Title', 'devix' ),
		// 		'type' => Controls_Manager::TEXT,
		// 		'label_block' => true,
		// 	]
		// );

		$this->add_control(
			'load_more_title',
			[
				'label' => __( '"Load More" Button Title', 'devix' ),
				'type' => Controls_Manager::TEXT,
				'default' => $this->get_default_load_more_title(),
			]
		);

		$this->add_control(
			'show_search_box',
			[
				'label' => __( 'Show Search Box', 'devix' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'devix' ),
				'label_off' => __( 'Hide', 'devix' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'search_box_label',
			[
				'label' => __( 'Search Box Label', 'devix' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'condition' => [
					'show_search_box' => 'yes'
				]
			]
		);

		$this->add_control(
			'show_cat_filter',
			[
				'label' => __( 'Show Category Filter', 'devix' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'devix' ),
				'label_off' => __( 'Hide', 'devix' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'cat_filter_all_title',
			[
				'label' => __( 'All Categories Title', 'devix' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => $this->get_default_filter_all_title(),
				'condition' => [
					'show_cat_filter' => 'yes'
				]
			]
		);

		$cat_ids = get_terms_array('category');
		$cat_ids['current'] = __('CURRENT CATEGORY', 'devix');

		$this->add_control(
			'cat_ids',
			[
				'label' => __( 'Categories', 'devix' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $cat_ids,
			]
		);

		$this->add_control(
			'is_knowledge_base',
			[
				'label' => __( 'Is Knowledge Base Archive', 'devix' ),
				'type' => Controls_Manager::SELECT,
				'multiple' => true,
				'options' => [
					'yes' => __('Yes', 'devix'),
					'no' => __('No', 'devix')
				],
				'default' => 'no'
			]
		);

		$this->add_control(
			'feat_post_ids',
			[
				'label' => __( 'Featured Posts', 'devix' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => get_items_array('post'),
				'condition' => [
					'is_knowledge_base' => 'no'
				]
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => __( 'Posts Per Page', 'devix' ),
				'type' => Controls_Manager::NUMBER,
				'min' => -1,
				'max' => 30,
				'step' => 1,
				'default' => get_option('posts_per_page'),
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order By', 'devix' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date' => __('Date', 'devix'),
					'title' => __('Title', 'devix'),
					'menu_order' => __('Menu Order', 'devix'),
					'rand' => __('Random', 'devix'),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'devix' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc' => __('Ascending', 'devix'),
					'desc' => __('Descending', 'devix')
				],
			]
		);

		$this->add_control(
			'no_posts_message',
			[
				'label' => __( '"No Posts" Message', 'devix' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => $this->get_default_no_posts_message(),
			]
		);

		$this->end_controls_section();
	}
	
	protected function get_default_search_box_label() {
		return __('Search', 'devix');
	}
	
	protected function get_default_filter_all_title() {
		return __('All', 'devix');
	}
	
	protected function get_default_load_more_title() {
		return __('Load More', 'devix');
	}
	
	protected function get_default_no_posts_message() {
		return __('No posts found.', 'devix');
	}

	protected function render() {
		$elem_id = $this->get_id();
		$settings = $this->get_settings_for_display();
		// $archive_title = $settings['archive_title'];
		$load_more_title = $settings['load_more_title'];
		$show_search_box = $settings['show_search_box'];
		$search_box_label = $settings['search_box_label'] ? $settings['search_box_label'] : __('Search', 'devix');
		$show_cat_filter = $settings['show_cat_filter'];
		$cat_filter_all_title = $settings['cat_filter_all_title'];
		$no_posts_message = $settings['no_posts_message'];
		
		$vue_categories = [];
		$cat_ids = $settings['cat_ids'];
		$is_knowledge_base = ( $settings['is_knowledge_base'] == 'yes' ) ? true : false;
		$feat_post_ids = !$is_knowledge_base ? $settings['feat_post_ids'] : 'hide';
		$posts_per_page = $settings['posts_per_page'];
		$orderby = $settings['orderby'] ? $settings['orderby'] : 'date';
		$order = $settings['order'] ? $settings['order'] : 'desc';

		if( is_array( $cat_ids ) && !empty( $cat_ids ) ){
			foreach( $cat_ids as $key => $cat_id ){
				if( $cat_id == 'current' ){
					if( $current_cat_id = get_query_var('cat') ){
						$cat_ids[$key] = $current_cat_id;
					}else{
						unset( $cat_ids[$key] );
					}
				}
			}

			foreach( $cat_ids as $cat_id ){
				$vue_categories[] = [
					'term_id' => $cat_id,
					'name' => get_term( $cat_id )->name
				];
			}
		}

		$options = [
			// 'archive_title' => $archive_title ? get_title( $archive_title, 'h1', 'supertext' ) : false,
			'is_knowledge_base' => $is_knowledge_base,
			'load_more_title' => $load_more_title ? $load_more_title : $this->get_default_load_more_title(),
			'show_search_box' => ( $show_search_box == 'yes' ) ? true : false,
			'search_box_label' => $search_box_label ? $search_box_label : $this->get_default_search_box_label(),
			'show_cat_filter' => ( $show_cat_filter == 'yes' ) ? true : false,
			'cat_filter_all_title' => $cat_filter_all_title,
			'categories' => $vue_categories,
			'no_posts_message' => $no_posts_message ? $no_posts_message : $this->get_default_no_posts_message(),
		];
		
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

		$query_args = [
			'post_type' => 'post',
			'posts_per_page' => $posts_per_page,
			'paged' => $paged,
			'orderby' => $orderby,
			'order' => $order,
		];

		if( is_array( $cat_ids ) && !empty( $cat_ids ) ){
			$query_args['tax_query'] = [[
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => $cat_ids,
			]];
		}

		// echo '<pre>';
		// var_dump( $query_args );
		// echo '</pre>';

		if( $feat_post_ids != 'hide' ){
			$feat_posts_num = ( is_array( $feat_post_ids ) && !empty( $feat_post_ids ) ) ? count( $feat_post_ids ) : 0;

			if( $feat_posts_num > 2 ){
				$feat_post_ids = array_slice( $feat_post_ids, 0, 2 );
			}else if( $feat_posts_num < 2 ){
				$feat_query_args = $query_args;
				$feat_query_args['posts_per_page'] = (2 - $feat_posts_num);
				$feat_query_args['fields'] = 'ids';

				$feat_query = new \WP_Query( $feat_query_args );
				
				if( is_array( $feat_query->posts ) && !empty( $feat_query->posts ) ){
					$feat_post_ids = $feat_query->posts;
				}

				wp_reset_postdata();
			}
		}

		$archive_data = get_posts_archive_data( $query_args, $feat_post_ids );

		// echo '<pre>';
		// var_dump( $archive_data );
		// echo '</pre>';

		$query_data = [
			'found_posts' => $archive_data['found_posts'],
			'max_num_pages' => $archive_data['max_num_pages'],
		];

		$settings = [
			'options' => $options,
			'query_args' => $query_args,
			'query_data' => $query_data,
			'feat_posts_array' => $archive_data['feat_posts_array'],
			'posts_array' => $archive_data['posts_array'],
			'feat_post_ids' => $feat_post_ids,
		];

		echo '<div id="vue-module-' . $elem_id . '" class="vue-module  posts-archive-widget">';
			echo vue_get_posts_archive( $settings );
		echo '</div>';

	}
}

Plugin::instance()->widgets_manager->register_widget_type( new Posts_Archive_Widget() );