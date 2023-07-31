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
			'OpenLab Activity Widget',
			array(
				'description' => 'Display list of BP activities related to the site group.',
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
		echo $args['before_widget'];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];

		$activity_args = array(
			'primary_id' => olab_get_group_id_by_blog_id( get_current_blog_id() ),
			'max'        => isset( $instance['num_items'] ) ? intval( $instance['num_items'] ) : 5,
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
		echo $args['after_widget'];
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

		$instance['title']         = wp_strip_all_tags( $new_instance['title'] );
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
	 */
	public function form( $instance ) {
		$group_type = olab_get_group_type_by_blog_id( get_current_blog_id() );

		$title      = isset( $instance['title'] ) ? $instance['title'] : ucfirst( $group_type ) . ' Activity';
		$num_items  = isset( $instance['num_items'] ) ? $instance['num_items'] : 5;
		$activities = isset( $instance['activities'] ) ? $instance['activities'] : array( '' );

		$source = isset( $instance['source'] ) ? $instance['source'] : 'this-group';

		$connections_enabled = defined( 'OPENLAB_CONNECTIONS_PLUGIN_URL' );

		$activity_options = array(
			''                     => 'All Activity',
			'created_announcement,created_announcement_reply' => 'Announcements',
			'new_blog_post'        => 'Posts',
			'new_blog_comment'     => 'Comments',
			'joined_group'         => 'Group Memberships',
			'added_group_document' => 'New Files',
			'bp_doc_created'       => 'New Docs',
			'bp_doc_edited'        => 'Doc Edits',
			'bp_doc_comment'       => 'Doc Comments',
			'bbp_topic_create'     => 'New Discussion Topics',
			'bbp_reply_create'     => 'Discussion Replies',
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title:</label><br />
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'num_items' ) ); ?>">How many items would you like to include?</label><br />
			<select name="<?php echo esc_attr( $this->get_field_name( 'num_items' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'num_items' ) ); ?>" class="widefat">
				<?php for ( $i = 1; $i < 11; $i++ ) { ?>
				<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $num_items, $i ); ?>><?php echo esc_html( $i ); ?></option>
				<?php } ?>
			</select>
		</p>

		<?php if ( $connections_enabled ) : ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'source' ) ); ?>">Activity Source:</label><br />
				<select name="<?php echo esc_attr( $this->get_field_name( 'source' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'source' ) ); ?>">
					<option value="this-group" <?php selected( $source, 'this-group' ); ?>>Current group only</option>
					<option value="connected-groups" <?php selected( $source, 'connected-groups' ); ?>>Connected groups</option>
					<option value="all" <?php selected( $source, 'all' ); ?>>Current + connected groups</option>
				</select>
			</p>
		<?php endif; ?>

		<p>
			<label>What types of activity would you like to include?</label><br />
			<?php foreach ( $activity_options as $key => $value ) { ?>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'activities' ) ); ?>[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $activities, true ) ); ?> />
				<?php echo esc_html( $value ); ?>
			</label>
			<br />
			<?php } ?>
		</p>
		<?php
	}
}
