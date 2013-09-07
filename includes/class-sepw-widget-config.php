<?php

/**
 * Best Answers Widget 
 */
class SEPW_Widget_Config extends WP_Widget
{
	private $se_sites;
	private $order;	
	private $sort;
	
	/**
	 * Widget setup.
	 */
	function SEPW_Widget_Config()
	{
		require_once 'config-stackphp.php';
		require_once 'class-sepw-widget-print.php';
		$this->se_sites = SEPW_Widget_Print::get_sites();
		$this->order = array(
			'asc' => __( 'Ascending', 'sepw' ),
			'desc' => __( 'Descending', 'sepw' )
		);
		$this->sort = array( 
			'votes' => __( 'Votes', 'sepw' ), 
			'activity' => __( 'Activity', 'sepw' ), 
			'creation' => __( 'Creation', 'sepw' ) 
		);
		/* Widget settings. */
		$widget_ops = array(
			'classname'		 => 'se_best_answers',
			'description'	 => __( 'Display posts by User in any Stack Exchange site. Or show the recent activity.', 'sepw' ) 
		);
		/* Widget control settings. */
		$control_ops = array(
			'width'		 => 300,
			'height'	 => 350,
			'id_base'	 => 'se_best_answers-widget' 
		);

		/* Create the widget. */
		$this->WP_Widget( 
			'se_best_answers-widget',
			__( 'Stack Exchange Posts', 'sepw' ), 
			$widget_ops, 
			$control_ops 
		);
		
		# Conditional Widget enqueue
		add_action( 'wp_enqueue_scripts', array( $this, 'widget_style' ) );
		
	}

	
	/**
	 * Load Widget style if it is not disabled in the plugin options  
	 */
	function widget_style()
	{
		if( !is_active_widget( false, false, $this->id_base, true ) )
				return;
		$opts = get_option( SEPW_Widget_Init::$option_name );
		if( !isset( $opts['css'] ) )
			wp_enqueue_style( 'se-widget', SEPW_Widget_Init::get_instance()->plugin_url . 'css/sepw.css' );
	}
	
	
	/**
	 * Front end widget display
	 *
	 * Cache: http://wordpress.stackexchange.com/a/95095
	 */
	function widget( $args, $instance )
	{
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$stack_site = ( isset( $instance['random'] ) ) ? array_rand( $this->se_sites, 1 ) : $instance['stack_site'];
		$q_or_a = isset( $instance['q_or_a'] ) ? 'questions' : 'answers';
		$subtitle = isset( $instance['subtitle'] ) ? $instance['subtitle'] : false;
		
		// Get posts
		$get_se = SEPW_Widget_Print::get_se( 
				$instance['user'], 
				$instance['page_size'], 
				$stack_site, 
				$q_or_a, 
				$instance['sort'], 
				$instance['order'] 
		);
		
		if( !$get_se ) 
		{
			//echo 'no widget';
			return;
		}
		
		$build_widget = $before_widget;
		
		if( !empty( $instance['user'] ) )
			$build_widget .= SEPW_Widget_Print::print_user( 
					$build_widget, 
					$get_se, 
					$instance['user'], 
					'',//$before_title, 
					'',//$after_title, 
					$title, 
					$stack_site, 
					$q_or_a, 
					$subtitle, 
					$this->se_sites 
			);
		else
			$build_widget .= SEPW_Widget_Print::print_site( 
					$build_widget, 
					$get_se, 
					'',//$before_title, 
					'',//$after_title, 
					$title, 
					$stack_site, 
					$q_or_a, 
					$subtitle, 
					$this->se_sites 
			);

		echo $build_widget . $after_widget;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 */
	function form( $instance )
	{
		$text_fields = array(
			'title'		 => __( 'Title', 'sepw' ),
			'user'		 => sprintf(
					'%s<br /><small>%s</small>',
					__( "User ID", 'sepw' ),
					__( "Leave empty to list global Q&A's.", 'sepw' )
					),
			'page_size'	 => __( 'Posts per page', 'sepw' ),
		);		
		$checkbox_fields = array(
			'subtitle'		 => sprintf(
					'%s<br /><small>%s</small>',
					__( "Show subtitle?", 'sepw' ),
					__( "User/Site information bellow the title.", 'sepw' )
					),
			'random'		 => sprintf(
					'%s<br /><small>%s</small>',
					__( "Randomize site?", 'sepw' ),
					__( "Only works when User not selected.", 'sepw' )
					),
			'q_or_a'		 => sprintf(
					'%s<br /><small>%s</small>',
					__( "Show Questions?", 'sepw' ),
					__( "Leave unchecked to show Answers.", 'sepw' )
					),
		);
		$defaults = array(
			'title'		 => __( 'Stack Exchange Posts', 'sepw' ),
			'user'		 => '',
			'page_size'	 => '3',
			'stack_site' => 'wordpress',
			'q_or_a'	 => false,
			'subtitle'	 => false,
			'random'	 => false,
			'sort'		 => 'votes',
			'order'		 => 'desc'
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		// Print Text Input
		foreach( $text_fields as $key => $value )
		{
			SEPW_Widget_Print::the_text( 
					$key, 
					$value, 
					$instance,
					$this->get_field_id( $key ),
					$this->get_field_name( $key )
			);
			// Print this checkbox just after Title
			if( 'title' == $key )
				SEPW_Widget_Print::the_checkbox( 
						$instance, 
						'subtitle', 
						$checkbox_fields['subtitle'],
						$this->get_field_id( 'subtitle' ),
						$this->get_field_name( 'subtitle' )
				);
		}		
		SEPW_Widget_Print::the_sites( 
				$instance, 
				$this->get_field_id( 'stack_site' ), 
				$this->get_field_name( 'stack_site' ), 
				$this->se_sites
		);
		# UPDATE IMAGE DROPDOWN
		$opts = get_option( SEPW_Widget_Init::$option_name );
		if( !isset( $opts['fancy_dropdown'] ) ):
		?>
		<script>
			jQuery(document).ready(function($) {
				try {
					$("#<?php echo $this->get_field_id( 'stack_site' ); ?>").msDropDown({visibleRows:"10"});//{childWidth:"250px",outerWidth:"250px"}
				} catch($) {
					console.log('Error creating images dropdown: ' + $.message);
				}
			});
		</script>
		<?php
		endif;

		// Print final checkboxes
		SEPW_Widget_Print::the_checkbox( 
				$instance, 
				'random', 
				$checkbox_fields['random'],
				$this->get_field_id( 'random' ),
				$this->get_field_name( 'random' )
		);
		SEPW_Widget_Print::the_checkbox( 
				$instance, 
				'q_or_a', 
				$checkbox_fields['q_or_a'],
				$this->get_field_id( 'q_or_a' ),
				$this->get_field_name( 'q_or_a' )
		);
		// Print Order dropdown
		SEPW_Widget_Print::the_simple_select( 
				__('Order', 'sepw' ), 
				'order', 
				$instance, 
				$this->order,
				$this->get_field_id( 'order' ),
				$this->get_field_name( 'order' )
		);
		SEPW_Widget_Print::the_simple_select( 
				__( 'Sort', 'sepw' ), 
				'sort', 
				$instance, 
				$this->sort,
				$this->get_field_id( 'sort' ),
				$this->get_field_name( 'sort' )
		);
	}

	
	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;

		$instance['title'] = esc_attr( $new_instance['title'] );
		$instance['user'] = esc_attr( $new_instance['user'] );
		$page_size = empty($new_instance['page_size'] ) ? '10' : esc_attr( $new_instance['page_size'] );
		$instance['page_size'] = ( is_numeric( $page_size ) && $page_size < 101 ) ? $page_size : $old_instance['page_size'];
		$instance['stack_site'] = $new_instance['stack_site'];
		$instance['q_or_a'] = $new_instance['q_or_a'];
		$instance['random'] = $new_instance['random'];
		$instance['subtitle'] = $new_instance['subtitle'];
		$instance['sort'] = $new_instance['sort'];
		$instance['order'] = $new_instance['order'];
		
		return $instance;
	}
}