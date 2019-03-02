<?php
/**
 * The template for displaying event archive
 *
 * @package Wordi
 * @since 1.0.0
 */
get_header();
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">

		<?php
		if ( have_posts() ) :
			?>

			<header class="page-header">
				<?php
					the_archive_title( '<h1 class="page-title">', '</h1>' );
				?>
			</header><!-- .page-header -->

			<?php
			// Start the Loop.
			$args = [
				'post_status'    => 'publish',
				'post_type'      => 'wordi',
				'posts_per_page' => 100,
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_type'      => 'DATE',
				'meta_key'       => 'wordi_startdate',
			];

			$posts         = new WP_Query( $args );
			$current_year  = '';
			$current_month = '';
			while ( $posts->have_posts() ) {
			?>
			<article id="event-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content">
				<?php
				$posts->the_post();
				$post_id = get_the_id();

				$startdate  = strtotime( get_post_meta( $post_id, 'wordi_startdate', true ) );
				$enddate    = strtotime( get_post_meta( $post_id, 'wordi_enddate', true ) );
				$dateformat = get_option( 'date_format' );

				$last_year    = $current_year;
				$last_month   = $current_month;
				$current_year = date_i18n( 'Y', $startdate );
				if ( $last_year != $current_year ) {
					$last_month = '';
				}
				$current_month = date_i18n( 'F', $startdate );

				if ( $last_year != $current_year ) {
					echo '<h2>' . $current_year . '</h2>';
				}

				if ( $last_month != $current_month ) {
					echo '<h3>' . $current_month . '</h3>';
				}

				$post_id = get_the_ID();
				if ( is_sticky() && is_home() && ! is_paged() ) {
					printf( '<span class="sticky-post">%s</span>', _x( 'Featured', 'post', 'wordi' ) );
				}
				echo '<a href="' . esc_url( get_permalink() ) . '" class="event-details-link">' . get_the_title() . '</a>';
				echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;' . get_the_excerpt();

				// Date and times


				echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;' . date_i18n( $dateformat, $startdate );
				if ( $startdate !== $enddate ) {
					echo ' - ' . date_i18n( $dateformat, $enddate );
				}

				?>
				</div>
			</article>
			<?php
			}
			wp_reset_postdata();

			// If no content, include the "No posts found" template.
		else :
			?>
			<section class="no-results not-found">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'No words yet!', 'wordi' ); ?></h1>
				</header><!-- .page-header -->

				<div class="page-content">
					<?php
					if ( current_user_can( 'publish_posts' ) ) :

						printf(
							'<p>' . wp_kses(
								/* translators: 1: link to WP admin new post page. */
								__( 'Ready to publish your first event? <a href="%1$s">Get started here</a>.', 'wordi' ),
								array(
									'a' => array(
										'href' => array(),
									),
								)
							) . '</p>',
							esc_url( admin_url( 'post-new.php?post_type=wordi' ) )
						);
					else :
						?>

						<p><?php _e( 'It seems we can&rsquo;t find any words.', 'wordi' ); ?></p>
						<?php

					endif;
					?>
				</div><!-- .page-content -->
			</section><!-- .no-results -->
			<?php
		endif;
		?>
		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();
