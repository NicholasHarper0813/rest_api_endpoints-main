<?php 
class Rae_Register_Get_Post_Api {
	public function __construct() {
		$this->post_type     = 'post';
		$this->route         = '/post';
		add_action( 'rest_api_init', [ $this, 'rest_posts_endpoints' ] );
	}
	
	public function rest_posts_endpoints() {
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
		$post_id = ! empty( $parameters['post_id'] ) ? intval( sanitize_text_field( $parameters['post_id'] ) ) : '';
		$error = new WP_Error();
		$post_data = $this->get_required_post_data( $post_id );

		if ( ! empty( $post_data ) ) 
		{

			$response['status']      = 200;
			$response['post_data']  = $post_data;

		} 
		else 
		{
			$error->add( 406, __( 'Post not found', 'rest-api-endpoints' ) );
			return $error;
		}

		return new WP_REST_Response( $response );
	}

	public function get_required_post_data( $post_ID )
	{

		$post_data = [];

		if ( empty( $post_ID ) && ! is_array( $post_ID ) ) 
		{
			return $post_data;
		}

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


		return $post_data;
	}
}

new Rae_Register_Get_Post_Api();
