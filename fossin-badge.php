<?php
/**
Plugin Name: Foss.in Badge
Plugin URI: http://sudarmuthu.com/wordpress/fossin-badge
Description: Helps you to display the <a href = "http://foss.in/promote">Foss.in Badge</a> in your WordPress website.
Author: Sudar
Version: 0.1
Author URI: http://sudarmuthu.com/
Text Domain: fossin-badge

=== RELEASE NOTES ===
2012-11-07 – v0.1 – Initial Release
*/

/**
 * To modify the list of badges, just change this array
 *
 * HTML copied from http://foss.in/promote
 */
$images_url = plugin_dir_url('fossin-badge/fossin-badge.php') ; 
$foss_in_badges = array(
    'attending_foss.in' => '<a href="http://foss.in"><img src="' . $images_url . 'images/attending_250px.png" alt="I am attending FOSS.IN" width="250" height="165" border="0" /></a>',
    'foss.in_logo' => '<a href="http://foss.in"><img src="' . $images_url . 'images/logo-button_250px.png" alt="FOSS.IN" width="250" height="90" border="0" /></a>',
    'foss.in_logo_small' => '<a href="http://foss.in"><img src="' . $images_url . 'images/banner-480.png" alt="FOSS.IN" width="480" height="84" border="0" /></a>',
    'foss_in_logo_large' => '<a href="http://foss.in"><img src="' . $images_url . 'images/banner-816.png" alt="FOSS.IN" width="816" height="144" border="0" /></a>',
    'show_me_the_code' => '<a href="http://foss.in"><img src="' . $images_url . 'images/show_me_the_code.png" alt="FOSS.IN: Show me the code" width="359" height="90" border="0" /></a>',
    'foss.in_details' => '<a href="http://foss.in"><img src="' . $images_url . 'images/foss.in.2012.png" alt="FOSS.IN" width="250" height="250" border="0" /></a>',
    'speaking_at_foss.in' => '<a href="http://foss.in"><img src="' . $images_url . 'images/speaking_250px.jpg" alt="FOSS.IN" width="250" height="250" border="0" /></a>'
);

/**
 * The main class
 *
 * @package default
 * @subpackage default
 * @author Sudar
 */
class FossInBadge {

    /**
     * Initalize the plugin by registering the hooks
     */
    function __construct() {

        // Load localization domain
        // Okay, I am too lazy to enable support for translation. Let me know if anyone really need it.
        // load_plugin_textdomain( 'fossin-badge', false, dirname(plugin_basename(__FILE__)) .  '/languages' );

        // Register hooks
        add_action('admin_head', array(&$this, 'add_script_config'));
    }

    /**
     * add script to admin page
     */
    function add_script_config() {
        global $foss_in_badges;

        // Add script only to Widgets page
        if (substr_compare($_SERVER['REQUEST_URI'], 'widgets.php', -11) == 0) {
?>

    <script type="text/javascript">
    var fossin_badges = new Array();

<?php
    foreach ($foss_in_badges as $key => $value) {
        echo "fossin_badges['$key'] = '$value';";
    }
?>    

    function fossin_show_badge(elm) {
        jQuery(elm).parent().nextAll('div.badge_preview').html(fossin_badges[jQuery(elm).val()]).show();
    }
    </script>
<?php
        }
    }

    // PHP4 compatibility
   function FossInBadge() {
        $this->__construct();
    }
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'FossInBadge' ); function FossInBadge() { global $fossInBadge; $fossInBadge = new FossInBadge(); }

// register FossInBadgeWidget widget
add_action('widgets_init', create_function('', 'return register_widget("FossInBadgeWidget");'));

/**
 * FossInBadgeWidget Class
 */
class FossInBadgeWidget extends WP_Widget {
    /** constructor */
    function FossInBadgeWidget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'FossInBadgeWidget', 'description' => __('Widget that shows Foss.in Badge', 'fossin-badge'));

		/* Widget control settings. */
		$control_ops = array('id_base' => 'fossin-badge' );

		/* Create the widget. */
		parent::WP_Widget( 'fossin-badge', __('Foss.in Badge', 'fossin-badge'), $widget_ops, $control_ops );
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        global $foss_in_badges;

        extract( $args );

        $title = $instance['title'];
        $badge_type = $instance['badge_type'];

        echo $before_widget;
        echo $before_title;
        echo $title;
        echo $after_title;

        display_foss_in_badge($badge_type);

        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
        // validate data
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['badge_type'] = strip_tags($new_instance['badge_type']);

        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {

        global $foss_in_badges;
		/* Set up some default widget settings. */
		$defaults = array( 'title' => '', 'badge_type' => reset($foss_in_badges));
		$instance = wp_parse_args( (array) $instance, $defaults );

        $title = esc_attr($instance['title']);
		$badge_type = $instance['badge_type'];
?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'fossin-badge'); ?>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('badge_type'); ?>"><?php _e('Badge Type:', 'fossin-badge'); ?></label>
            <select id="<?php echo $this->get_field_id('badge_type'); ?>" name="<?php echo $this->get_field_name('badge_type'); ?>" onchange="fossin_show_badge(this)" >
<?php
    foreach ($foss_in_badges as $key => $value) {
        echo "<option value='$key' " . selected($key, $badge_type) . " >" . ucwords(str_replace('_', ' ', $key)) . "</option>";
    }
?>
            </select>
        </p>

        <p><?php _e('Preview', 'fossin-badge'); ?></p>
        <div id="<?php $this->get_field_id('preview_div'); ?>" class="badge_preview">
            <?php echo $foss_in_badges[$badge_type]; ?>
        </div>
<?php
    }
} // class FossInBadgeWidget

/**
 * Template function to display the badge
 * 
 * @param string $badge_type 
 */
function display_foss_in_badge($badge_type) {
    global $foss_in_badges;
?>
    <div class="fossin_badge">
<?php
    echo $foss_in_badges[$badge_type];
?>
    </div>
<?php
}
?>
