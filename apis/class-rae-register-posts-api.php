<?php
class Rae_Register_Posts_Api 
{
	function __construct() 
	{
		add_action( 'rest_api_init', array( $this, 'rae_rest_posts_endpoints' ) );
	}

	function rae_rest_posts_endpoints() 
	{
		register_rest_route(
			'wp/v2/rae',
			'/post/create',
			array(
			'methods' => 'POST',
			'callback' => array( $this, 'rae_rest_create_post_endpoint_handler' ),
		));
	}

	function rae_rest_create_post_endpoint_handler( WP_REST_Request $request ) 
	{
		$response = array();
		$parameters = $request->get_params();
		$user_id = sanitize_text_field( $parameters['user_id'] );
		$content = sanitize_text_field( $parameters['content'] );
		$title = sanitize_text_field( $parameters['title'] );

		$error = new WP_Error();

		if ( empty( $user_id ) ) 
		{
			$error->add(400,
				__( "User ID field is required", 'rest-api-endpoints' ),
				array( 'status' => 400 ));

			return $error;
		}

		if (empty($title)) 
		{
			$error->add(400,
				__( "Title field is required", 'rest-api-endpoints' ),
				array( 'status' => 400 )
			);

			return $error;
		}

		if (empty($content)) 
		{
			$error->add(400,
				__( "Body field is required", 'rest-api-endpoints' ),
				array( 'status' => 400 )
			);

			return $error;
		}

		$user_can_publish_post = user_can( $user_id,'publish_posts' );
		if ( ! $user_can_publish_post ) 
		{
			$error->add(400,
				__( "You don't have previlige to publish a post", 'rest-api-endpoints' ),
				array( 'status' => 400 )
			);

			return $error;
		}

		$my_post = array(
			'post_type' => 'post',
			'post_author' => $user_id,
			'post_title'   => sanitize_text_field( $title ),
			'post_status'   => 'publish',
			'post_content'   => $content,
		);
		
		$post_id = wp_insert_post( $my_post );
		
		if ( !is_wp_error($post_id) ) 
		{
			$response['status'] = 200;
			$response['post_id'] = $post_id;
		} 
		else 
		{
			$error->add( 406, __( 'Post creating failed', 'rest-api-endpoints' ) );
			return $error;
		}

		return new WP_REST_Response( $response );
	}
}

new Rae_Register_Posts_Api();
