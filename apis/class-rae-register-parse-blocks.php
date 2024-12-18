<?php

class Rae_Register_Parse_Block {
	public function __construct() 
	{
		$this->post_type     = 'post';
		$this->route         = '/parse-block';

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
		$post_id = ! empty( $parameters['post_id'] ) ? intval( sanitize_text_field( $parameters['post_id'] ) ) : '';
		$error = new WP_Error();
		$parsed_block = $this->get_parsed_block_content( $post_id );

		if (!empty( $parsed_block )) 
		{
			$response['status']      = 200;
			$response['parsed_block']  = $parsed_block;
		} 
		else 
		{
			$error->add( 406, __( 'Post not found', 'rest-api-endpoints' ) );
			return $error;
		}

		return new WP_REST_Response( $response );

	}

	public function get_parsed_block_content( $post_ID ) 
	{
		$parsed_content = [];
		if (empty($post_ID) && !is_array($post_ID)) 
		{
			return $parsed_content;
		}

		$post_result = get_post( $post_ID );
		$parsed_content = parse_blocks( $post_result->post_content );
		
		return $parsed_content;
	}
}

new Rae_Register_Parse_Block();
