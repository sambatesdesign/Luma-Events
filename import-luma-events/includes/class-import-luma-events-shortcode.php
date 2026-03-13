<?php
/**
 * Shortcode for displaying Luma events.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import_Luma_Events_Shortcode Class.
 */
class Import_Luma_Events_Shortcode {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'luma_events', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'posts_per_page' => 10,
				'category'       => '',
				'tag'            => '',
				'past_events'    => 'no',
				'order'          => 'ASC',
				'orderby'        => 'event_start_date',
				'columns'        => '3',
			),
			$atts,
			'luma_events'
		);

		// Build query args.
		$args = array(
			'post_type'      => 'luma_events',
			'posts_per_page' => intval( $atts['posts_per_page'] ),
			'orderby'        => 'meta_value',
			'meta_key'       => 'event_start_date',
			'order'          => strtoupper( $atts['order'] ),
		);

		// Filter by upcoming/past events.
		if ( 'no' === $atts['past_events'] ) {
			// Show only upcoming events.
			$args['meta_query'] = array(
				array(
					'key'     => 'start_ts',
					'value'   => current_time( 'timestamp' ),
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
			);
		} elseif ( 'yes' === $atts['past_events'] ) {
			// Show only past events.
			$args['meta_query'] = array(
				array(
					'key'     => 'start_ts',
					'value'   => current_time( 'timestamp' ),
					'compare' => '<',
					'type'    => 'NUMERIC',
				),
			);
		}

		// Filter by category.
		if ( ! empty( $atts['category'] ) ) {
			$categories = array_map( 'trim', explode( ',', $atts['category'] ) );
			$args['tax_query'][] = array(
				'taxonomy' => 'luma_category',
				'field'    => 'slug',
				'terms'    => $categories,
			);
		}

		// Filter by tag.
		if ( ! empty( $atts['tag'] ) ) {
			$tags = array_map( 'trim', explode( ',', $atts['tag'] ) );
			$args['tax_query'][] = array(
				'taxonomy' => 'luma_tag',
				'field'    => 'slug',
				'terms'    => $tags,
			);
		}

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No events found.', 'import-luma-events' ) . '</p>';
		}

		ob_start();

		$columns_class = 'ile-columns-' . intval( $atts['columns'] );

		echo '<div class="luma-events-grid ' . esc_attr( $columns_class ) . '">';

		while ( $query->have_posts() ) {
			$query->the_post();
			$this->render_event_item();
		}

		echo '</div>';

		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Render a single event item.
	 */
	private function render_event_item() {
		$post_id     = get_the_ID();
		$start_date  = get_post_meta( $post_id, 'event_start_date', true );
		$venue_name  = get_post_meta( $post_id, 'venue_name', true );
		$meeting_url = get_post_meta( $post_id, 'meeting_url', true );
		$luma_url    = get_post_meta( $post_id, 'luma_event_url', true );

		?>
		<div class="luma-event-item">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="event-image">
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'medium' ); ?>
					</a>
				</div>
			<?php endif; ?>

			<div class="event-content">
				<h3 class="event-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>

				<?php if ( $start_date ) : ?>
					<div class="event-date">
						<strong><?php esc_html_e( 'Date:', 'import-luma-events' ); ?></strong>
						<?php
						$start_datetime_obj = new DateTime( $start_date, wp_timezone() );
						echo esc_html( $start_datetime_obj->format( 'F j, Y @ g:i a' ) );
						?>
					</div>
				<?php endif; ?>

				<?php if ( $meeting_url ) : ?>
					<div class="event-type">
						💻 <?php esc_html_e( 'Virtual Event', 'import-luma-events' ); ?>
					</div>
				<?php elseif ( $venue_name ) : ?>
					<div class="event-location">
						📍 <?php echo esc_html( $venue_name ); ?>
					</div>
				<?php endif; ?>

				<div class="event-excerpt">
					<?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
				</div>

				<div class="event-actions">
					<a href="<?php the_permalink(); ?>" class="button">
						<?php esc_html_e( 'View Details', 'import-luma-events' ); ?>
					</a>
					<?php if ( $luma_url ) : ?>
						<a href="<?php echo esc_url( $luma_url ); ?>" class="button button-primary" target="_blank">
							<?php esc_html_e( 'Register on Luma', 'import-luma-events' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
