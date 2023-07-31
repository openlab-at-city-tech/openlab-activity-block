<?php
/**
 * Server-side functions for rendering activity block.
 *
 * @package openlab-activity-block
 */

/**
 * Renders the activity block.
 *
 * @param mixed[] $atts Block attributes.
 * @return string
 */
function openlab_render_activity_block( $atts ) {
	ob_start();

	$args = array(
		'primary_id' => olab_get_group_id_by_blog_id( get_current_blog_id() ),
		'max'        => isset( $atts['numItems'] ) ? intval( $atts['numItems'] ) : 5,
		'action'     => isset( $atts['activities'] ) ? implode( ',', $atts['activities'] ) : '',
		'scope'      => 'groups',
	);

	$source = ! empty( $atts['source'] ) ? $atts['source'] : 'this-group';
	switch ( $source ) {
		case 'connected-groups' :
			$args['scope'] = 'connected-groups';
			break;

		case 'all' :
			$args['scope'] = 'this-group-and-connected-groups';
			break;
	}

	$display_style = isset( $atts['displayStyle'] ) ? $atts['displayStyle'] : 'full';
	?>
	<section>
		<?php if ( bp_has_activities( $args ) ) : ?>
			<div class="olab-activity-stream olab-activity-stream-<?php echo esc_attr( $display_stile ); ?>">
				<?php
				while ( bp_activities() ) :
					bp_the_activity();
					?>
					<div class="olab-activity-item">
						<div class="olab-activity-item-wrapper">
							<div class="olab-activity-entry-row">
								<?php if ( 'simple' !== $display_style ) : ?>
									<div class="olab-activity-entry-avatar">
										<div class="olab-activity-avatar">
											<a href="<?php bp_activity_user_link(); ?>">
												<?php
												bp_activity_avatar(
													array(
														'type'  => 'full',
														'class' => 'img-responsive',
													)
												);
												?>
											</a>
										</div>
									</div>
								<?php endif; ?>

								<div class="olab-activity-entry-data">
									<div class="olab-activity-entry-content">
										<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										<?php echo olab_get_activity_action( null, $display_style ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<div class="olab-info-message">
				<p><?php esc_html_e( 'Sorry, there was no activity found. Please try a different filter.', 'openlab-activity-block' ); ?></p>
			</div>
		<?php endif; ?>
	</section>
	<?php
	$html = ob_get_clean();

	return $html;
}
