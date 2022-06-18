Vue.component('vue-posts-archive', {
	data: function () {
		return {
			paged: 1,
			max_num_pages: 1,
			current_cat_id: 0,
			search_query: '',
			feat_posts: [],
			posts: [],
			search_timeout: null,
			loading: false,
			popup_post_id: false,
			localize: localize
		}
	},
	created: function(){
		this._setup_data();
	},
	mounted: function(){
		this._setup_event_listeners();
	},
	computed: {
		next_page: function () {
			return this.paged + 1;
		},
		is_knowledge_base: function () {
			if( this.options.is_knowledge_base ){
				return true;
			}
			return false;
		},
		show_search_box: function () {
			if( this.options.show_search_box ){
				return true;
			}
			return false;
		},
		show_archive_filter: function () {
			if( this.options.show_cat_filter && this.options.categories.length ){
				return true;
			}
			return false;
		},
		show_archive_header: function () {
			if( this.show_search_box || this.show_archive_filter ){
				return true;
			}
			return false;
		},
	},
	// watch: {
	// 	popup_post_id: function ( new_value ) {
	// 		if( new_value ){
	// 			this._open_post_popup();
	// 		}else{
	// 			this._close_post_popup();
	// 		}
	// 	},
	// },
	props: {
		options: {
			type: Object,
			default: function(){
				return {};
			}
		},
		query_args: {
			type: Object,
			default: function(){
				return {};
			}
		},
		query_data: {
			type: Object,
			default: function(){
				return {};
			}
		},
		feat_posts_array: {
			type: Array,
			default: function(){
				return [];
			}
		},
		posts_array: {
			type: Array,
			default: function(){
				return [];
			}
		},
		feat_post_ids: {
			type: Array,
			default: function(){
				return [];
			}
		},
	},
	methods: {
		_setup_data(){
			if( typeof this.query_args.paged != 'undefined' ){
				this.paged = this.query_args.paged;
			}

			if( typeof this.query_data.max_num_pages != 'undefined' ){
				this.max_num_pages = this.query_data.max_num_pages;
			}

			if( typeof this.feat_posts_array != 'undefined' ){
				this.feat_posts = this.feat_posts_array;
			}

			if( typeof this.posts_array != 'undefined' ){
				this.posts = this.posts_array;
			}
		},
		_setup_event_listeners(){
			// return;

			if( this.is_knowledge_base ){
				var triggers = document.querySelectorAll('.archive-post-wrap a');
	
				triggers.forEach( trigger => {
					trigger.addEventListener('click', event => {
						event.preventDefault();

						var target_element = event.target;
						var wrapper = target_element.closest('.archive-post-wrap');

						if( wrapper ){
							var post_id = wrapper.getAttribute('data-id');

							if( post_id ){
								this.popup_post_id = parseInt( post_id );
							}
							// console.log( 'post_id', wrapper.getAttribute('data-id') );
						}
					})
				});
			}
		},
		_load_more_posts(){
			this.paged += 1;
			this._load_posts( true );
		},
		_filter_category( cat_id ){
			this.current_cat_id = cat_id;
			this._filter_posts();
		},
		_filter_posts( delay = 0 ){
			this.paged = 1;

			if( delay > 0 ){
				clearTimeout( this.search_timeout );

				this.search_timeout = setTimeout(() => {
					this._load_posts();
				}, delay);
			}else{
				this._load_posts();
			}
		},
		_load_posts( append = false ){
			if( this.loading ){
				return false;
			}

			this.loading = true;

			axios.post( localize.api_url + '/vue-posts-archive/load-posts', {
				query_args: this.query_args,
				feat_post_ids: this.feat_post_ids,
				paged: this.paged,
				search_query: this.search_query,
				current_cat_id: this.current_cat_id,
			}).then( response => {
				if( response.data.status == 'success' ){
					if( response.data.max_num_pages ){
						this.max_num_pages = response.data.max_num_pages
					}

					this.feat_posts = response.data.feat_posts_array;

					if( append ){
						this.posts = this.posts.concat( response.data.posts_array );
					}else{
						this.posts = response.data.posts_array;
					}

					setTimeout( ()=> {
						this._setup_event_listeners();
					}, 200);
				}
				
				this.loading = false;

			}).catch( e => {
				console.log( e );
				this.loading = false;
			})
		},
	},
	template: `<div class="posts-archive-wrap">
		<div v-if="show_archive_header" class="posts-archive-header">
			<div class="flex v-align-middle grid-pad-h-10 grid-pad-v-10">
				<div v-if="show_archive_filter" class="col-auto col-fill xs-col-100">
					<div class="posts-archive-filter">
						<div class="flex h-align-start grid-pad-h-30 s-grid-pad-h-20 xs-h-align-center">
							<div 
								v-if="options.cat_filter_all_title" 
								class="col-auto"
							>
								<a 
									href="#"
									:class="{selected: (current_cat_id == 0)}"
									@click.prevent="_filter_category(0)"
									v-html="options.cat_filter_all_title"
								></a>
							</div>
							<div 
								class="col-auto"
								v-for="cat in options.categories" 
								:key="cat.term_id"
							>
								<a 
									href="#"
									:class="{selected: (current_cat_id == cat.term_id)}"
									@click.prevent="_filter_category(cat.term_id)"
									v-html="cat.name"
								></a>
							</div>
						</div>
					</div>
				</div>
				<div v-if="show_search_box" class="col-auto xs-col-100">
					<div class="posts-archive-search-box">
						<input 
							type="text" 
							class="input-search"
							:placeholder="options.search_box_label" 
							v-model="search_query" 
							@input="_filter_posts(1500)"
						/>
					</div>
				</div>
			</div>
		</div>
		<div v-if="feat_posts.length || posts.length" class="posts-archive" :class="{ loading: loading }">
			<div class="posts-archive-inner">
				<div class="flex h-align-start grid-pad-h-15 grid-pad-v-40">
					<div v-for="post in feat_posts" class="col-50 xs-col-100" :key="post.id" v-html="post.html"></div>
					<div v-for="post in posts" class="col-1-3 s-col-50 xs-col-100" :key="post.id" v-html="post.html"></div>
				</div>
			</div>
			<div v-if="next_page <= max_num_pages" class="flex h-align-center posts-archive-load-more">
				<a href="#" class="button button-style-1" @click.prevent="_load_more_posts()">
					<span class="button-title" v-html="options.load_more_title"></span>
					<span class="button-arrow down"></span>
				</a>
			</div>
		</div>

		<div v-else class="posts-archive-no-posts"><p v-html="options.no_posts_message"></p></div>
		
		<vue-post-popup 
			:post_id="popup_post_id"
			:key="popup_post_id"
			@reset_post_id="popup_post_id = false"
		></vue-post-popup>
	</div>`
});


Vue.component('vue-post-popup', {
	data: function () {
		return {
			loading: false,
			show_popup: false,
			post_html: '',
			localize: localize
		}
	},
	created: function(){
		this._load_post();
	},
	props: {
		post_id: false,
	},
	methods: {
		_load_post(){
			if( !this.post_id ){
				return false;
			}

			this.loading = true;

			axios.post( localize.api_url + '/vue-posts-archive/load-popup-post', {
				post_id: this.post_id,
			}).then( response => {
				if( response.data.html ){
					this.post_html = response.data.html;
					this.show_popup = true;
				}
				
				this.loading = false;

			}).catch( e => {
				console.log( e );
				this.loading = false;
			});
		},
		_close_popup(){
			this.show_popup = false;

			setTimeout( ()=> {
				this.post_html = '';
				this.$emit('reset_post_id');
			}, 700 );
		},
	},
	template: `<div class="post-popup-holder" :class="{ loading: loading }">
		<transition name="fade">
			<div 
				v-if="show_popup" 
				class="post-popup-wrap"
			>
				<div 
					class="post-popup-overlay"
					@click.prevent="_close_popup"
				></div>

				<div class="post-popup">
					<a 
						href="#" 
						class="post-popup-close"
						@click.prevent="_close_popup"
					></a>
					
					<div 
						class="post-popup-inner"
						v-html="post_html"
					></div>
				</div>
			</div>
		</transition>
	</div>`
});