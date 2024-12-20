<?php
class Rae_Register_Get_Posts_Api {
	public function __construct() {
		$this->post_type     = 'post';
		$this->route         = '/posts';
		$this->post_per_page = 9;

		add_action( 'rest_api_init', [ $this, 'rest_posts_endpoints' ] );
	}
	
	public function rest_posts_endpoints()
	{
		register_rest_route(
			'rae/v1',
			$this->route,
			[
				'method'   => 'GET',
				'callback' => [ $this, 'rest_endpoint_handler' ],
			]
		);
	}

	public function rest_endpoint_handler( WP_REST_Request $request )
	{
		$response      = [];
		$parameters    = $request->get_params();
		$posts_page_no = ! empty( $parameters['page_no'] ) ? intval( sanitize_text_field( $parameters['page_no'] ) ) : '';
		$error = new WP_Error();
		$posts_data = $this->get_posts( $posts_page_no );
		
		if ( ! empty( $posts_data['posts_data'] ) ) 
		{
			$response['status']      = 200;
			$response['posts_data']  = $posts_data['posts_data'];
			$response['found_posts'] = $posts_data['found_posts'];
			$response['page_count']  = $posts_data['page_count'];
		} 
		else 
		{
			$error->add( 406, __( 'Posts not found', 'rest-api-endpoints' ) );
			return $error;
		}

		return new WP_REST_Response( $response );

	}
	
	public function calculate_page_count( $total_found_posts, $post_per_page ) 
	{
		return ( (int) ( $total_found_posts / $post_per_page ) + ( ( $total_found_posts % $post_per_page ) ? 1 : 0 ) );
	}

	public function get_posts( $page_no = 1 )
	{
		$args = [
			'post_type'              => $this->post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => $this->post_per_page,
			'fields'                 => 'ids',
			'orderby'                => 'date',
			'paged'                  => $page_no,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];

		$latest_post_ids = new WP_Query( $args );
		$post_result = $this->get_required_posts_data( $latest_post_ids->posts );
		$found_posts = $latest_post_ids->found_posts;
		$page_count  = $this->calculate_page_count( $found_posts, $this->post_per_page );

		return [
			'posts_data'  => $post_result,
			'found_posts' => $found_posts,
			'page_count'  => $page_count,
		];
	}

	public function get_required_posts_data($post_IDs) 
	{
		$post_result = [];
		if (empty( $post_IDs ) && ! is_array( $post_IDs )) 
		{
			return $post_result;
		}

		foreach ( $post_IDs as $post_ID ) 
		{

			$author_id     = get_post_field( 'post_author', $post_ID );
			$attachment_id = get_post_thumbnail_id( $post_ID );
			$post_data                     = [];
			$post_data['id']               = $post_ID;
			$post_data['title']            = get_the_title( $post_ID );
			$post_data['excerpt']          = get_the_excerpt( $post_ID );
			$post_data['date']             = get_the_date( '', $post_ID );
			$post_data['attachment_image'] = [
				'img_sizes'  => wp_get_attachment_image_sizes( $attachment_id ),
				'img_src'    => wp_get_attachment_image_src( $attachment_id, 'full' ),
				'img_srcset' => wp_get_attachment_image_srcset( $attachment_id ),
			];
			$post_data['categories']       = get_the_category( $post_ID );
			$post_data['meta']             = [
				'author_id'   => $author_id,
				'author_name' => get_the_author_meta( 'display_name', $author_id )
			];
			array_push( $post_result, $post_data );
		}
		
		return $post_result;
	}
}

new Rae_Register_Get_Posts_Api();
