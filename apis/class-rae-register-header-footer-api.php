<?php
class Rae_Register_Header_Footer_Api 
{
	public function __construct() 
	{
		$this->route = '/header-footer';
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
		$response   = [];
		$parameters = $request->get_params();
		$header_menu_location_id   = ! empty( $parameters['header_location_id'] ) ? sanitize_text_field( $parameters['header_location_id'] ) : '';
		$footer_menu_location_id   = ! empty( $parameters['footer_location_id'] ) ? sanitize_text_field( $parameters['footer_location_id'] ) : '';

		$error = new WP_Error();

		$header_menu_items = $this->get_nav_menu_items( $header_menu_location_id );
		$footer_menu_items = $this->get_nav_menu_items( $footer_menu_location_id );

		if (!empty( $header_menu_items ) || !empty( $footer_menu_items )) 
		{
			$response['status']    = 200;
			$response['data'] = [
				'header' => [
					'siteLogoUrl' => $this->get_custom_logo_url( 'custom_logo' ),
					'siteTitle' => get_bloginfo( 'title' ),
					'siteDescription' => get_bloginfo( 'description' ),
					'favicon' => get_site_icon_url(),
					'headerMenuItems' => $header_menu_items,
				],
				'footer' => [
					'footerMenuItems' => $footer_menu_items,
					'socialLinks' => $this->get_social_icons(),
					'copyrightText' => $this->get_copyright_text(),
					'footerSidebarOne' => $this->get_sidebar( 'rwt-sidebar-1' ),
					'footerSidebarTwo' => $this->get_sidebar( 'rwt-sidebar-2' ),
				]
			];

		} 
		else 
		{
			$error->add( 406, __( 'Data not found', 'rest-api-endpoints' ) );
			return $error;
		}

		return new WP_REST_Response( $response );

	}
	
	public function get_custom_logo_url( $key )
	{
		$custom_logo_id = get_theme_mod( $key );
		$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
		return $image[0];
	}

	public function get_social_icons()
	{
		$social_icons = [];
		$social_icons_name = [ 'facebook', 'twitter', 'instagram', 'youtube' ];

		foreach ( $social_icons_name as $social_icon_name )
		{
			$social_link = get_theme_mod( sprintf( 'rae_%s_link', $social_icon_name ) );
			if ($social_link) 
			{
				array_push( $social_icons, [
					'iconName' =>esc_attr( $social_icon_name ),
					'iconUrl' => esc_url( $social_link )
				] );
			}
		}

		return $social_icons;

	}

	public function get_copyright_text() 
	{
		return get_theme_mod( 'rae_footer_text' );
	}

	function get_nav_menu_items( $location, $args = [] ) 
	{
		if (empty($location))
		{
			return '';
		}

		$locations = get_nav_menu_locations();
		$object = wp_get_nav_menu_object( $locations[ $location ] );
		$menu_data = wp_get_nav_menu_items( $object->name, $args );
		$menu_items = [];

		if ( !empty($menu_data) ) 
		{
			foreach ( $menu_data as $item ) 
			{
				if ( empty( $item->menu_item_parent ) ) 
				{
					$menu_item              = [];
					$menu_item['ID']        = $item->ID;
					$menu_item['title']     = $item->title;
					$menu_item['url']       = $item->url;
					$menu_item['children']  = [];
					$menu_item['pageSlug'] = get_post_field( 'post_name', $item->object_id );
					$menu_item['pageID']   = intval( $item->object_id );
					
					array_push( $menu_items, $menu_item );
				}
			}

			foreach ( $menu_data as $item ) 
			{
				if ( $item->menu_item_parent ) 
				{
					$submenu_item              = [];
					$submenu_item['ID']        = $item->ID;
					$submenu_item['url']       = $item->url;
					$submenu_item['title']     = $item->title;
					$submenu_item['pageSlug'] = get_post_field( 'post_name', $item->object_id );
					$submenu_item['pageID']   = intval( $item->object_id );

					foreach( $menu_items as $key => $parent_item ) 
					{
						if ( intval( $item->menu_item_parent ) === $parent_item['ID'] ) 
						{
							array_push( $menu_items[ $key ]['children'], $submenu_item );
						}
					}
				}
			}
		}
		
		$menu_items = ! empty( $menu_items ) ? $menu_items : '';
		return $menu_items;
	}

	public function get_sidebar( $sidebar_id )
	{
		ob_start();
		dynamic_sidebar(  );
		$output = ob_get_contents( $sidebar_id );
		ob_end_clean();

		return $output;
	}
}

new Rae_Register_Header_Footer_Api();
