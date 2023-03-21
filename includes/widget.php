<?php

class Openlab_Activity_Block_Widget extends WP_Widget {

	public function __construct() {
        parent::__construct(
            'openlab_activity_block_widget',
            'OpenLab Activity Widget',
            array(
                'description'   => 'Display list of BP activities related to the site group.'
            )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];

        $activity_args = array(
            'primary_id'    => olab_get_group_id_by_blog_id( get_current_blog_id() ),
            'max'           => isset( $instance['num_items'] ) ? intval( $instance['num_items'] ) : 5,
            'action'        => isset( $instance['activities'] ) ? implode(',', $instance['activities']) : '',
            'scope'         => 'groups'
        );

        if( bp_has_activities( $activity_args ) ) :
            ?>
            <ul>
                <?php while( bp_activities() ) : bp_the_activity(); ?>
                <li>
                    <?php echo olab_get_activity_action( null, 'simple' ); ?>
                </li>
                <?php endwhile; ?>
            </ul>
            <?php
        else :
            _e( 'Sorry, there was no activity found. Please try a different filter.', 'buddypress' );
        endif;

        echo $args['after_widget'];
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['display_style'] = isset( $new_instance['display_style'] ) ? $new_instance['display_style'] : 'full';
        $instance['num_items'] = isset( $new_instance['num_items'] ) ? (int) $new_instance['num_items'] : 5;
        $instance['activities'] = isset( $new_instance['activities'] ) ? $new_instance['activities'] : array( '' );

        return $instance;
    }

    public function form( $instance ) {
        $group_type = olab_get_group_type_by_blog_id( get_current_blog_id() );

        $title = isset( $instance['title'] ) ? $instance['title'] : ucfirst($group_type) . ' Activity';
        $num_items = isset( $instance['num_items'] ) ? $instance['num_items'] : 5;
        $activities = isset( $instance['activities'] )? $instance['activities'] : array( '' );

        $activity_options = array(
            ''                                                  => 'All Activity',
            'created_announcement,created_announcement_reply'   => 'Announcements',
            'new_blog_post'                                     => 'Posts',
            'new_blog_comment'                                  => 'Comments',
            'joined_group'                                      => 'Group Memberships',
            'added_group_document'                              => 'New Files',
            'bp_doc_created'                                    => 'New Docs',
            'bp_doc_edited'                                     => 'Doc Edits',
            'bp_doc_comment'                                    => 'Doc Comments',
            'bbp_topic_create'                                  => 'New Discussion Topics',
            'bbp_reply_create'                                  => 'Discussion Replies'
        );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label><br />
            <input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'num_items' ); ?>">How many items would you like to include?</label><br />
            <select name="<?php echo $this->get_field_name( 'num_items' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat">
                <?php for( $i = 1; $i < 11; $i++ ) { ?>
                <option value="<?php echo $i; ?>" <?php selected( $num_items, $i ); ?>><?php echo $i; ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label>What types of activity would you like to include?</label><br />
            <?php foreach($activity_options as $key => $value) { ?>
            <label>
                <input type="checkbox" name="<?php echo $this->get_field_name( 'activities' ); ?>[]" value="<?php echo $key; ?>" 
                <?php checked( in_array( $key, $activities )); ?> /> 
                <?php echo $value; ?>
            </label>
            <br />
            <?php } ?>
        </p>
        <?php
    }

}
