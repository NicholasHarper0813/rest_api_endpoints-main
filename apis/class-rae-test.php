<?php
use Firebase\JWT\JWT;

class Rae_Test 
{
	public function __construct()
	{
		$cookie_name = "user";
		$cookie_value = "Nicholas Harper";
		setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/" );

		$this->post_type     = 'post';
		$this->route         = '/mytest';

		add_action( 'rest_api_init', array( $this, 'rae_rest_posts_endpoints' ) );
	}

	public function rae_rest_posts_endpoints() 
	{
		register_rest_route(
			'rae/v1',
			$this->route,
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'rae_rest_endpoint_handler' ),
			) );
	}

	public function get_all_cookies( $cookies_as_string ) 
	{
		$headerCookies = explode('; ', $cookies_as_string );
		$cookies = array();
		foreach( $headerCookies as $itm )
		{
			list( $key, $val ) = explode( '=', $itm,2 );
			$cookies[ $key ] = $val;
		}
		return $cookies ;
	}

	public function rae_rest_endpoint_handler( WP_REST_Request $request ) 
	{
		$response      = [];
		$parameters    = $request->get_params();
		$posts_page_no = ! empty( $parameters['page_no'] ) ? intval( sanitize_text_field( $parameters['page_no'] ) ) : 1;
		$cookies = $this->get_all_cookies( $header['cookie'][0] );
		$result = $this->validate_token( $cookies['authToken'] );

		return new WP_REST_Response( $result );

		$error = new WP_Error();
		$cases_data = [];

		if ( !is_wp_error( $cases_data['cases_posts'] ) && ! empty( $cases_data['cases_posts'] ) ) 
		{
			$response['status']      = 200;
			$response['cases_posts'] = $cases_data['cases_posts'];
			$response['found_posts'] = $cases_data['found_posts'];
			$total_found_posts      = intval( $cases_data['found_posts'] );
			$response['page_count'] = $this->calculate_page_count( $total_found_posts, 9 );
		} 
		else 
		{
			$error->add( 406, __( 'Media Releases Posts not found', 'rest-api-endpoints' ) );
			return $error;
		}

		return new WP_REST_Response( $response );
	}

	public function validate_token( $token, $output = true)
	{
		$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
		if (!$secret_key) 
		{
			return new WP_Error(
				'jwt_auth_bad_config',
				'JWT is not configurated properly, please contact the admin',
				array(
					'status' => 403,
				)
			);
		}

		try {
			$token = JWT::decode($token, $secret_key, array('HS256'));
			if ($token->iss != get_bloginfo('url'))
			{
				return new WP_Error(
					'jwt_auth_bad_iss',
					'The iss do not match with this server',
					array(
						'status' => 403,
					)
				);
			}
			
			if (!isset($token->data->user->id)) 
			{
				return new WP_Error(
					'jwt_auth_bad_request',
					'User ID not found in the token',
					array(
						'status' => 403,
					)
				);
			}
			
			if (!$output) 
			{
				return $token;
			}
			
			return array(
				'code' => 'jwt_auth_valid_token',
				'data' => array(
					'status' => 200,
				),
			);
		} 
		catch (Exception $e) 
		{
			return new WP_Error(
				'jwt_auth_invalid_token',
				$e->getMessage(),
				array(
					'status' => 403,
				)
			);
		}
	}


}

new Rae_Test();
