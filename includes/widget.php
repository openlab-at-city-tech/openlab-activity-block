<?php
/**
 * Widget.
 *
 * @package openlab-activity-block
 */

/**
 * Activity block widget.
 */
class Openlab_Activity_Block_Widget extends WP_Widget {
	/**
	 * Widget constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct(
			'openlab_activity_block_widget',
			__( 'OpenLab Activity Widget', 'openlab-activity-block' ),
			array(
				'description' => __( 'Display list of activity items related to the site group.', 'openlab-activity-block' ),
			)
		);
	}

	/**
	 * Generates the widget markup.
	 *
	 * @param mixed[] $args     Widget configuration.
	 * @param mixed[] $instance Saved widget settings.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget']; // @phpstan-ignore-line

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title']; // @phpstan-ignore-line

		$activity_args = array(
			'primary_id' => olab_get_group_id_by_blog_id( get_current_blog_id() ),
			'max'        => isset( $instance['num_items'] ) ? intval( $instance['num_items'] ) : 5, // @phpstan-ignore-line
			'action'     => isset( $instance['activities'] ) ? implode( ',', $instance['activities'] ) : '',
			'source'     => 'this-group',
			'scope'      => 'groups',
		);

		$source = ! empty( $instance['source'] ) ? $instance['source'] : 'this-group';
		switch ( $source ) {
			case 'connected-groups' :
				$activity_args['scope'] = 'connected-groups';
				break;

			case 'all' :
				$activity_args['scope'] = 'this-group-and-connected-groups';
				break;
		}

		if ( bp_has_activities( $activity_args ) ) :
			?>
			<ul>
				<?php
				while ( bp_activities() ) :
					bp_the_activity();
					?>
				<li>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo olab_get_activity_action( null, 'simple' ); ?>
				</li>
				<?php endwhile; ?>
			</ul>
			<?php
		else :
			esc_html_e( 'Sorry, there was no activity found. Please try a different filter.', 'openlab-activity-block' );
		endif;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget']; // @phpstan-ignore-line
	}

	/**
	 * Save routine for widget settings.
	 *
	 * @param mixed[] $new_instance New settings.
	 * @param mixed[] $old_instance Old settings.
	 * @return mixed[]
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$new_title = isset( $new_instance['title'] ) && is_string( $new_instance['title'] ) ? $new_instance['title'] : '';

		$instance['title']         = wp_strip_all_tags( $new_title );
		$instance['display_style'] = isset( $new_instance['display_style'] ) ? $new_instance['display_style'] : 'full';
		$instance['num_items']     = isset( $new_instance['num_items'] ) ? (int) $new_instance['num_items'] : 5;
		$instance['activities']    = isset( $new_instance['activities'] ) ? $new_instance['activities'] : array( '' );
		$instance['source']        = isset( $new_instance['source'] ) ? $new_instance['source'] : 'this-group';

		return $instance;
	}

	/**
	 * Generates the markup for the widget settings form.
	 *
	 * @param mixed[] $instance Saved widget settings.
	 * @return string
	 */
	public function form( $instance ) {
		$group_type = olab_get_group_type_by_blog_id( get_current_blog_id() );

		if ( isset( $instance['title'] ) && is_string( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			// translators: Group type.
			$title = sprintf( '%s Activity', ucfirst( $group_type ) );
		}

		$num_items  = isset( $instance['num_items'] ) ? $instance['num_items'] : 5;
		$activities = isset( $instance['activities'] ) ? $instance['activities'] : array( '' );

		$source = isset( $instance['source'] ) ? $instance['source'] : 'this-group';

		$connections_enabled = openlab_activity_block_show_activity_source_for_site( get_current_blog_id() );

		$activity_options = array(
			''                     => __( 'All Activity', 'openlab-activity-block' ),
			'created_announcement,created_announcement_reply' => __( 'Announcements', 'openlab-activity-block' ),
			'new_blog_post'        => __( 'Posts', 'openlab-activity-block' ),
			'new_blog_comment'     => __( 'Comments', 'openlab-activity-block' ),
			'joined_group'         => __( 'Group Memberships', 'openlab-activity-block' ),
			'added_group_document' => __( 'New Files', 'openlab-activity-block' ),
			'bp_doc_created'       => __( 'New Docs', 'openlab-activity-block' ),
			'bp_doc_edited'        => __( 'Doc Edits', 'openlab-activity-block' ),
			'bp_doc_comment'       => __( 'Doc Comments', 'openlab-activity-block' ),
			'bbp_topic_create'     => __( 'New Discussion Topics', 'openlab-activity-block' ),
			'bbp_reply_create'     => __( 'Discussion Replies', 'openlab-activity-block' ),
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'openlab-activity-block' ); ?></label><br />
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'num_items' ) ); ?>"><?php esc_html_e( 'How many items would you like to include?', 'openlab-activity-block' ); ?></label><br />
			<select name="<?php echo esc_attr( $this->get_field_name( 'num_items' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'num_items' ) ); ?>" class="widefat">
				<?php for ( $i = 1; $i < 11; $i++ ) { ?>
				<option value="<?php echo esc_attr( (string) $i ); ?>" <?php selected( $num_items, $i ); ?>><?php echo esc_html( (string) $i ); ?></option>
				<?php } ?>
			</select>
		</p>

		<?php if ( $connections_enabled ) : ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'source' ) ); ?>"><?php esc_html_e( 'Activity Source:', 'openlab-activity-block' ); ?></label><br />
				<select name="<?php echo esc_attr( $this->get_field_name( 'source' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'source' ) ); ?>">
					<option value="this-group" <?php selected( $source, 'this-group' ); ?>><?php esc_html_e( 'Current group only', 'openlab-activity-block' ); ?></option>
					<option value="connected-groups" <?php selected( $source, 'connected-groups' ); ?>><?php esc_html_e( 'Connected groups', 'openlab-activity-block' ); ?></option>
					<option value="all" <?php selected( $source, 'all' ); ?>><?php esc_html_e( 'Current + connected groups', 'openlab-activity-block' ); ?></option>
				</select>
			</p>
		<?php endif; ?>

		<p>
			<label><?php esc_html_e( 'What types of activity would you like to include?', 'openlab-activity-block' ); ?></label><br />
			<?php foreach ( $activity_options as $key => $value ) { ?>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'activities' ) ); ?>[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $activities, true ) ); ?> />
				<?php echo esc_html( $value ); ?>
			</label>
			<br />
			<?php } ?>
		</p>
		<?php

		return '';
	}
}
