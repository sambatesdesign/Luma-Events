<?php
/**
 * Custom Post Type and Taxonomies.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import_Luma_Events_CPT Class.
 */
class Import_Luma_Events_CPT {

	/**
	 * The post type name.
	 *
	 * @var string
	 */
	public $post_type = 'luma_events';

	/**
	 * The post type slug.
	 *
	 * @var string
	 */
	public $post_type_slug = 'luma-event';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_filter( 'manage_luma_events_posts_columns', array( $this, 'add_admin_columns' ) );
		add_action( 'manage_luma_events_posts_custom_column', array( $this, 'render_admin_columns' ), 10, 2 );
		add_filter( 'manage_edit-luma_events_sortable_columns', array( $this, 'sortable_columns' ) );
		add_filter( 'post_thumbnail_html', array( $this, 'add_placeholder_thumbnail' ), 10, 5 );
	}

	/**
	 * Register the custom post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Luma Events', 'Post Type General Name', 'import-luma-events' ),
			'singular_name'         => _x( 'Luma Event', 'Post Type Singular Name', 'import-luma-events' ),
			'menu_name'             => __( 'Luma Events', 'import-luma-events' ),
			'name_admin_bar'        => __( 'Luma Event', 'import-luma-events' ),
			'archives'              => __( 'Event Archives', 'import-luma-events' ),
			'attributes'            => __( 'Event Attributes', 'import-luma-events' ),
			'parent_item_colon'     => __( 'Parent Event:', 'import-luma-events' ),
			'all_items'             => __( 'All Events', 'import-luma-events' ),
			'add_new_item'          => __( 'Add New Event', 'import-luma-events' ),
			'add_new'               => __( 'Add New', 'import-luma-events' ),
			'new_item'              => __( 'New Event', 'import-luma-events' ),
			'edit_item'             => __( 'Edit Event', 'import-luma-events' ),
			'update_item'           => __( 'Update Event', 'import-luma-events' ),
			'view_item'             => __( 'View Event', 'import-luma-events' ),
			'view_items'            => __( 'View Events', 'import-luma-events' ),
			'search_items'          => __( 'Search Event', 'import-luma-events' ),
			'not_found'             => __( 'Not found', 'import-luma-events' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'import-luma-events' ),
			'featured_image'        => __( 'Event Image', 'import-luma-events' ),
			'set_featured_image'    => __( 'Set event image', 'import-luma-events' ),
			'remove_featured_image' => __( 'Remove event image', 'import-luma-events' ),
			'use_featured_image'    => __( 'Use as event image', 'import-luma-events' ),
			'insert_into_item'      => __( 'Insert into event', 'import-luma-events' ),
			'uploaded_to_this_item' => __( 'Uploaded to this event', 'import-luma-events' ),
			'items_list'            => __( 'Events list', 'import-luma-events' ),
			'items_list_navigation' => __( 'Events list navigation', 'import-luma-events' ),
			'filter_items_list'     => __( 'Filter events list', 'import-luma-events' ),
		);

		$args = array(
			'label'               => __( 'Luma Event', 'import-luma-events' ),
			'description'         => __( 'Events imported from Luma', 'import-luma-events' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'taxonomies'          => array( 'luma_category', 'luma_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-calendar-alt',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rewrite'             => array( 'slug' => $this->post_type_slug ),
		);

		register_post_type( $this->post_type, $args );
	}

	/**
	 * Register taxonomies.
	 */
	public function register_taxonomies() {
		// Category taxonomy.
		$category_labels = array(
			'name'              => _x( 'Event Categories', 'taxonomy general name', 'import-luma-events' ),
			'singular_name'     => _x( 'Event Category', 'taxonomy singular name', 'import-luma-events' ),
			'search_items'      => __( 'Search Categories', 'import-luma-events' ),
			'all_items'         => __( 'All Categories', 'import-luma-events' ),
			'parent_item'       => __( 'Parent Category', 'import-luma-events' ),
			'parent_item_colon' => __( 'Parent Category:', 'import-luma-events' ),
			'edit_item'         => __( 'Edit Category', 'import-luma-events' ),
			'update_item'       => __( 'Update Category', 'import-luma-events' ),
			'add_new_item'      => __( 'Add New Category', 'import-luma-events' ),
			'new_item_name'     => __( 'New Category Name', 'import-luma-events' ),
			'menu_name'         => __( 'Categories', 'import-luma-events' ),
		);

		$category_args = array(
			'hierarchical'      => true,
			'labels'            => $category_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'luma-category' ),
		);

		register_taxonomy( 'luma_category', array( $this->post_type ), $category_args );

		// Tag taxonomy.
		$tag_labels = array(
			'name'                       => _x( 'Event Tags', 'taxonomy general name', 'import-luma-events' ),
			'singular_name'              => _x( 'Event Tag', 'taxonomy singular name', 'import-luma-events' ),
			'search_items'               => __( 'Search Tags', 'import-luma-events' ),
			'popular_items'              => __( 'Popular Tags', 'import-luma-events' ),
			'all_items'                  => __( 'All Tags', 'import-luma-events' ),
			'edit_item'                  => __( 'Edit Tag', 'import-luma-events' ),
			'update_item'                => __( 'Update Tag', 'import-luma-events' ),
			'add_new_item'               => __( 'Add New Tag', 'import-luma-events' ),
			'new_item_name'              => __( 'New Tag Name', 'import-luma-events' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'import-luma-events' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'import-luma-events' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags', 'import-luma-events' ),
			'not_found'                  => __( 'No tags found.', 'import-luma-events' ),
			'menu_name'                  => __( 'Tags', 'import-luma-events' ),
		);

		$tag_args = array(
			'hierarchical'      => false,
			'labels'            => $tag_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'luma-tag' ),
		);

		register_taxonomy( 'luma_tag', array( $this->post_type ), $tag_args );
	}

	/**
	 * Add custom admin columns.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;

			// Add event date after title.
			if ( 'title' === $key ) {
				$new_columns['event_date']   = __( 'Event Date', 'import-luma-events' );
				$new_columns['event_status'] = __( 'Status', 'import-luma-events' );
				$new_columns['event_type']   = __( 'Type', 'import-luma-events' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom admin columns.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_admin_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'event_date':
				$start_date = get_post_meta( $post_id, 'event_start_date', true );
				if ( $start_date ) {
					$timezone = get_post_meta( $post_id, 'event_timezone', true );
					echo esc_html( date_i18n( 'M j, Y g:i a', strtotime( $start_date ) ) );
					if ( $timezone ) {
						echo '<br><small>' . esc_html( $timezone ) . '</small>';
					}
				} else {
					echo '—';
				}
				break;

			case 'event_status':
				$start_ts = get_post_meta( $post_id, 'start_ts', true );
				if ( $start_ts ) {
					$now = current_time( 'timestamp' );
					if ( $start_ts > $now ) {
						echo '<span style="color: green;">⬤</span> Upcoming';
					} else {
						echo '<span style="color: gray;">⬤</span> Past';
					}
				}
				break;

			case 'event_type':
				$meeting_url = get_post_meta( $post_id, 'meeting_url', true );
				$venue_name  = get_post_meta( $post_id, 'venue_name', true );

				if ( $meeting_url && $venue_name ) {
					echo '🌐 Hybrid';
				} elseif ( $meeting_url ) {
					echo '💻 Virtual';
				} elseif ( $venue_name ) {
					echo '📍 In-Person';
				} else {
					echo '—';
				}
				break;
		}
	}

	/**
	 * Make columns sortable.
	 *
	 * @param array $columns Existing sortable columns.
	 * @return array Modified sortable columns.
	 */
	public function sortable_columns( $columns ) {
		$columns['event_date'] = 'event_start_date';
		return $columns;
	}

	/**
	 * Add placeholder thumbnail for events without featured images.
	 *
	 * @param string       $html              The post thumbnail HTML.
	 * @param int          $post_id           The post ID.
	 * @param int          $post_thumbnail_id The post thumbnail ID.
	 * @param string|array $size              The post thumbnail size.
	 * @param string       $attr              Query string of attributes.
	 * @return string Modified HTML with placeholder if needed.
	 */
	public function add_placeholder_thumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		// Only add placeholder for luma_events and eventbrite_events post types.
		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, array( 'luma_events', 'eventbrite_events' ), true ) ) {
			return $html;
		}

		// If there's already a thumbnail, return it.
		if ( ! empty( $html ) ) {
			return $html;
		}

		// Get size attributes.
		$size_class = 'attachment-' . ( is_array( $size ) ? implode( 'x', $size ) : $size );
		$default_attr = is_array( $attr ) ? $attr : array();

		// Extract class from attributes if it exists.
		$class = isset( $default_attr['class'] ) ? $default_attr['class'] : '';
		if ( empty( $class ) && is_string( $attr ) && strpos( $attr, 'class=' ) !== false ) {
			preg_match( '/class=["\']([^"\']*)["\']/', $attr, $matches );
			$class = isset( $matches[1] ) ? $matches[1] : '';
		}

		// Build placeholder image HTML using local plugin image.
		$placeholder_url = IMPORT_LUMA_EVENTS_PLUGIN_URL . 'image-placeholder.png';
		$placeholder_html = sprintf(
			'<img src="%s" class="%s %s" alt="%s" style="width: 100%%; height: auto;">',
			esc_url( $placeholder_url ),
			esc_attr( $size_class ),
			esc_attr( $class ),
			esc_attr( get_the_title( $post_id ) )
		);

		return $placeholder_html;
	}
}
