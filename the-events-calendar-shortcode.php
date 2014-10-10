<?php
/***
 Plugin Name: The Events Calendar Shortcode
 Plugin URI: http://dandelionwebdesign.com/downloads/shortcode-modern-tribe/
 Description: An addon to add shortcode functionality for <a href="http://wordpress.org/plugins/the-events-calendar/">The Events Calendar Plugin (Free Version) by Modern Tribe</a>.
 Version: 1.0.0
 Author: Dandelion Web Design Inc.
 Author URI: http://dandelionwebdesign.com
 License: GPL2 or later
 License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Avoid direct calls to this file
if ( !defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Events calendar shortcode addon main class
 *
 * @package events-calendar-shortcode
 * @author Dandelion Web Design Inc.
 * @version 1.0.0
 */
class Events_Calendar_Shortcode
{
	/**
	 * Current version of the plugin.
	 *
	 * @since 1.0.0
	 */
	const VERSION = '1.0.0';

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see	 add_shortcode()
	 */
	public function __construct()
	{
		add_shortcode('ecs-list-events', array($this, 'ecs_fetch_events') ); // link new function to shortcode name
	} // END __construct()

	/**
	 * Fetch and return required events.
	 * @param  array $atts 	shortcode attributes
	 * @return string 	shortcode output
	 */
	public function ecs_fetch_events($atts)
	{
		/**
		 * Check if events calendar plugin method exists
		 */
		if ( !function_exists( 'tribe_get_events' ) ) {
			return;
		}

		global $wp_query, $post;
		$output='';
		$ecs_event_tax = '';

		extract( shortcode_atts( array(
			'cat' => '',
			'limit' => 5,
			'eventdetails' => true,
			'message' => 'There are no upcoming events at this time.',
			'order' => 'ASC',
			'viewall' => false,			
		), $atts, 'ecs-list-events' ), EXTR_PREFIX_ALL, 'ecs' );

		if ($ecs_cat) {
			$ecs_event_tax = array(
				array(
					'taxonomy' => 'tribe_events_cat',
					'field' => 'slug',
					'terms' => $ecs_cat
				)
			);
		}

		$posts = get_posts( array(
				'post_type' => 'tribe_events',
				'posts_per_page' => $ecs_limit,
				'tax_query'=> $ecs_event_tax,
				'meta_key' => '_EventEndDate',
				'orderby' => 'meta_value',
				'order' => $ecs_order,
				'meta_query' => array(
									array(
										'key' => '_EventEndDate',
										'value' => date('Y-m-d'),
										'compare' => '>=',
										'type' => 'DATETIME'
									)
								)
		) );

		if ($posts && !$no_upcoming_events) {

			$output .= '<ul class="ecs-event-list">';
			foreach( $posts as $post ) :
				setup_postdata( $post );
				$output .= '<li class="ecs-event">';
					$output .= '<h4 class="entry-title summary"><a href="' . tribe_get_event_link() . '" rel="bookmark">' . get_the_title() . '</a></h4>';
				if( $ecs_eventdetails !== 'false' ) {	
					$output .= '<span class="duration venue">' . tribe_events_event_schedule_details() . '</span>';
				}
				$output .= '</li>';
			endforeach;
			$output .= '</ul>';

			if( $ecs_viewall !== 'false' ) {
				$output .= '<button class="ecs-all-events"><a href="' . tribe_get_events_link() . '" rel="bookmark">' . translate( 'View All Events', 'tribe-events-calendar' ) . '</a></button>';
			}

		} else { //No Events were Found
			$output .= translate( $ecs_message, 'tribe-events-calendar' );
		} // endif

		wp_reset_query();

		return $output;
	}
}

/**
 * Instantiate the main class
 *
 * @since 1.0.0
 * @access public
 *
 * @var	object	$events_calendar_shortcode holds the instantiated class {@uses Events_Calendar_Shortcode}
 */
global $events_calendar_shortcode;
$events_calendar_shortcode = new Events_Calendar_Shortcode();
