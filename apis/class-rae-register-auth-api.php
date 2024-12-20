<?php 
class Rae_Register_Auth_API {
	public function __construct()
	{
		add_action( 'rest_api_init', array( $this, 'rae_rest_user_endpoints' ) );
	}

	function rae_rest_user_endpoints() 
	{
		register_rest_route(
			'wp/v2/rae',
			'/user/login',
			array(
			'methods' => 'POST',
			'callback' => array( $this, 'rae_rest_user_login_endpoint_handler' ),
		));
	}

	function rae_rest_user_login_endpoint_handler( WP_REST_Request $request ) {
		$response = array();
		$parameters = $request->get_params();
		
		$username = sanitize_text_field( $parameters['username'] );
		$password = sanitize_text_field( $parameters['password'] );
		$error = new WP_Error();

		if ( empty( $username ) ) 
		{
			$error->add(400,
				__( "Username field is required", 'rest-api-endpoints' ),
				array( 'status' => 400 ));

			return $error;
		}

		if ( empty( $password ) ) 
		{
			$error->add(
				400,
				__( "Password field is required", 'rest-api-endpoints' ),
				array( 'status' => 400 )
			);

			return $error;
		}

		$user = wp_authenticate( $username, $password  );

		if ( ! is_wp_error( $user ) ) 
		{
			$response['status'] = 200;
			$response['user'] = $user;
		} 
		else 
		{
			$error->add( 406, __( 'User not found. Check credentials', 'rest-api-endpoints' ) );
			return $error;
		}
		return new WP_REST_Response( $response );
	}
}

new Rae_Register_Auth_API();

