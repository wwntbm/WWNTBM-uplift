<?php

/**
 * Enqueue subscription form JS
 */
function uplift_enqueue_js() {
    wp_enqueue_script( 'subscribe', get_stylesheet_directory_uri() . '/js/subscribe.js', array( 'jquery' ), NULL, true );
}
add_action( 'wp_enqueue_scripts', 'uplift_enqueue_js' );

/**
 * Make podcasts private by default
 */
function default_post_visibility() {
    global $post;

    if ( 'publish' == $post->post_status ) {
        $visibility = 'public';
        $visibility_trans = __( 'Public' );
    } elseif ( !empty( $post->post_password ) ) {
        $visibility = 'password';
        $visibility_trans = __( 'Password protected' );
    } elseif ( $post->post_type == 'podcast' && is_sticky( $post->ID ) ) {
        $visibility = 'public';
        $visibility_trans = __( 'Public, Sticky' );
    } else {
        $post->post_password = '';
        $visibility = 'private';
        $visibility_trans = __( 'Private' );
    } ?>

    <script type='text/javascript'>
        (function($) {
            try {
                $('#post-visibility-display').text('<?php echo $visibility_trans; ?>');
                $('#hidden-post-visibility').val('<?php echo $visibility; ?>');
                $('#visibility-radio-<?php echo $visibility; ?>').attr('checked', true);
            } catch(e) {}
        })(jQuery);
    </script>
    <?php
}
add_action( 'post_submitbox_misc_actions' , 'default_post_visibility' );

/**
 * Show podcast info only to logged-in users
 * @return string HTML content
 */
function uplift_login_shortcode() {
    ob_start();

    if ( ! is_user_logged_in() ) {
        $login_args = array(
            'redirect'  => get_home_url() . '/podcast/',
        );
        echo '<h2>Log In</h2>
        <p>Please log in to access these podcasts.</p>';
        wp_login_form( $login_args );
    } else {
        echo '<h2>Podcasts</h2>
        <p><a href="' . get_home_url() . '/podcast/" class="button">Access the podcasts here</a>.</p>';
    }

    return ob_get_clean();
}
add_shortcode( 'conditional_login_form', 'uplift_login_shortcode' );

/**
 * Force HTTPS feed stylsheet URL
 * @param  string $feed_url RSS feed stylesheet URL
 * @return string modified RSS feed stylesheet URL
 */
function uplift_rss_stylesheet( $feed_style_url ) {
    return str_replace( 'http://', 'https://', $feed_style_url );
}
add_filter( 'ssp_rss_stylesheet', 'uplift_rss_stylesheet' );

/**
 * Add placeholder attributes to Mailchimp signup form
 * @param  array $fields form fields
 * @return array modified form fields
 */
function uplift_mc_placeholders( $fields ) {
    $fields[0]['default'] = 'john.doe@example.com';
    $fields[1]['default'] = 'John';
    $fields[2]['default'] = 'Doe';
    return $fields;
}
add_filter( 'mailchimp_dev_mode_fields', 'uplift_mc_placeholders' );

/**
 * Force SSL/TLS
 *
 * Workaround for a bug where MU domain mapping did not work when the domain included https://
 *
 * @param  string $string template directory, WP ajax URL, etc.
 * @return string HTTPS string
 */
function uplift_force_tls( $string ) {
    return str_replace( 'http://', 'https://', $string );
}
add_filter( 'home_url', 'uplift_force_tls' );
add_filter( 'template_directory_uri', 'uplift_force_tls' );
add_filter( 'stylsheet_directory_uri', 'uplift_force_tls' );
