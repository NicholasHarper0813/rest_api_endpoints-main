<?php 
function rae_custom_new_menu()
{
	register_nav_menus( [
		'rwt-menu-header' => esc_html__( 'RWT Header Menu', 'rest-api-endpoints' ),
		'rwt-menu-footer' => esc_html__( 'RWT Footer Menu', 'rest-api-endpoints' ),
	] );
}
add_action( 'init', 'rae_custom_new_menu' );

function rae_sidebar_registration() 
{
	$shared_args = [
		'before_title'  => '<h2 class="widget-title subheading heading-size-3">',
		'after_title'   => '</h2>',
		'before_widget' => '<div class="widget %2$s"><div class="widget-content">',
		'after_widget'  => '</div></div>',
	];

	register_sidebar(
		array_merge(
			$shared_args,
			[
				'name'        => __( 'RWT Footer #1', 'rest-api-endpoints' ),
				'id'          => 'rwt-sidebar-1',
				'description' => __( 'Widgets in this area will be displayed in the first column in the footer.', 'rest-api-endpoints' ),
			]
		)
	);

	register_sidebar(
		array_merge(
			$shared_args,
			[
				'name'        => __( 'RWT Footer #2', 'rest-api-endpoints' ),
				'id'          => 'rwt-sidebar-2',
				'description' => __( 'Widgets in this area will be displayed in the second column in the footer.', 'rest-api-endpoints' ),
			]
		)
	);
}

add_action( 'widgets_init', 'rae_sidebar_registration' );

if ( function_exists( 'register_sidebar' ) )
{
	register_sidebar();
}

