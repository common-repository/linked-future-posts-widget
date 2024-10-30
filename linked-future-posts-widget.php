<?php
/**
 * Plugin Name: Linked Future Posts Widget
 * Plugin URI: http://www.indianpeakswebdesign.com/
 * Description: A widget that displays a list of scheduled posts with links to each post. 
 * Version: 1.0.0
 * Author: Rick Accountius
 * Author URI: http://www.indianpeakswebdesign.com/
 */

/**
 * Linked_Future_Posts widget class
 */
class WP_Widget_Linked_Future_Posts extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'widget_linked_future_entries', 'description' => __( "List scheduled/linked future posts", 'linked_future_posts_widget') );
		parent::__construct( 'linked-future-posts', __( 'Linked Future Posts', 'linked_future_posts_widget' ), $widget_ops );

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	public function widget($args, $instance) {
		$cache = wp_cache_get('widget_linked_future_posts', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) )
			return $cache[$args['widget_id']];

		ob_start();
		extract($args);

		$title = empty($instance['title']) ? __('Linked Future Posts', 'linked_future_posts_widget') : apply_filters('widget_title', $instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

		$queryArgs = array(
			'showposts'           => $number,
			'what_to_show'        => 'posts',
			'nopaging'            => 0,
			'post_status'         => 'future',
			'ignore_sticky_posts' => 1,
			'order'               => 'ASC'
		);

		$r = new WP_Query($queryArgs);
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php echo $before_title . $title . $after_title; ?>


		<ul>
		<?php
			// Get the posts from the query
			$posts = $r->get_posts();

			// Loop through the posts
			foreach( $posts as $post ) {
			    //echo $post->post_name.'<br />'; 
		?>
			    <li><a href="<?php echo site_url(); ?>/<?php echo $post->post_name; ?>"> <?php echo $post->post_title; ?> </a></li>
		<?php
			}
		?>
		</ul>
		
		<?php echo $after_widget; ?>
<?php
			wp_reset_query();  // Restore global post data stomped by the_post().
		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_add('widget_linked_future_posts', $cache, 'widget');
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_linked_future_entries']) )
			delete_option('widget_linked_future_entries');

		return $instance;
	}

	private function flush_widget_cache() {
		wp_cache_delete('widget_linked_future_posts', 'widget');
	}

	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'number' => 5 ) );

		$title = esc_attr($instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">
		<?php _e('Title:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>">
		<?php _e('Number of posts to show:'); ?>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" /></label>
		<br /><small><?php _e('(at most 15)'); ?></small></p>
<?php
	}
}
function registerLinkedFuturePostsWidget() {
	register_widget('WP_Widget_Linked_Future_Posts');
}
add_action('widgets_init', 'registerLinkedFuturePostsWidget');
