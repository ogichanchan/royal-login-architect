<?php
/*
Plugin Name: Royal Login Architect
Plugin URI: https://github.com/ogichanchan/royal-login-architect
Description: A unique PHP-only WordPress utility. A royal style login plugin acting as a architect. Focused on simplicity and efficiency.
Version: 1.0.0
Author: ogichanchan
Author URI: https://github.com/ogichanchan
License: GPLv2 or later
Text Domain: royal-login-architect
*/

// Ensure WordPress is loaded directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Royal_Login_Architect class
 * Manages all plugin functionality.
 */
class Royal_Login_Architect {

    /**
     * Plugin version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Plugin slug (text domain).
     *
     * @var string
     */
    const SLUG = 'royal-login-architect';

    /**
     * Constructor.
     * Hooks into WordPress actions and filters.
     */
    public function __construct() {
        // Admin menu and settings
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Login page customizations
        add_action( 'login_enqueue_scripts', array( $this, 'login_styles' ) );
        add_filter( 'login_headerurl', array( $this, 'login_header_url' ) );
        add_filter( 'login_headertitle', array( $this, 'login_header_title' ) );
        add_filter( 'login_message', array( $this, 'login_message_content' ) );
    }

    /**
     * Static method to run on plugin activation.
     * Sets default options if they don't exist.
     */
    public static function activate() {
        // Only set default options if they don't exist to prevent overwriting user settings on re-activation.
        if ( false === get_option( 'rla_options' ) ) {
            $default_options = array(
                'logo_type'                 => 'image', // 'image' or 'text'
                'logo_text'                 => esc_html__( 'Royal Login', 'royal-login-architect' ),
                'logo_text_color'           => '#333333',
                'logo_image_base64_svg'     => self::get_default_crown_svg_base64(), // Base64 encoded SVG for default
                'login_background_color'    => '#f1f1f1',
                'login_form_background_color' => '#ffffff',
                'login_button_background_color' => '#0073aa',
                'login_button_text_color'   => '#ffffff',
                'custom_message'            => '',
            );
            add_option( 'rla_options', $default_options );
        }
    }

    /**
     * Static method to run on plugin deactivation.
     * Cleans up plugin options.
     */
    public static function deactivate() {
        // For simplicity and user preference, plugin options are not deleted on deactivation.
        // To delete options uncomment the line below:
        // delete_option( 'rla_options' );
    }

    /**
     * Gets plugin options, merging with defaults to ensure all keys are present.
     *
     * @return array Array of plugin options.
     */
    private function get_options() {
        $options = get_option( 'rla_options', array() );
        $default_options = array(
            'logo_type'                 => 'image',
            'logo_text'                 => esc_html__( 'Royal Login', 'royal-login-architect' ),
            'logo_text_color'           => '#333333',
            'logo_image_base64_svg'     => self::get_default_crown_svg_base64(),
            'login_background_color'    => '#f1f1f1',
            'login_form_background_color' => '#ffffff',
            'login_button_background_color' => '#0073aa',
            'login_button_text_color'   => '#ffffff',
            'custom_message'            => '',
        );
        return wp_parse_args( $options, $default_options );
    }

    /**
     * Adds the plugin's settings page to the admin menu under "Settings".
     */
    public function admin_menu() {
        add_options_page(
            esc_html__( 'Royal Login Architect Settings', 'royal-login-architect' ), // Page title
            esc_html__( 'Royal Login', 'royal-login-architect' ),                  // Menu title
            'manage_options',                                                       // Capability required
            self::SLUG,                                                             // Menu slug
            array( $this, 'settings_page_callback' )                                // Callback function
        );
    }

    /**
     * Registers plugin settings with WordPress's Settings API.
     */
    public function register_settings() {
        // Register a setting group and sanitize callback
        register_setting( 'rla_settings_group', 'rla_options', array( $this, 'sanitize_options' ) );

        // Add a settings section
        add_settings_section(
            'rla_general_section',                                                  // ID
            esc_html__( 'General Settings', 'royal-login-architect' ),              // Title
            array( $this, 'general_section_callback' ),                             // Callback
            self::SLUG                                                              // Page
        );

        // Add settings fields
        add_settings_field(
            'rla_logo_type',
            esc_html__( 'Login Logo Type', 'royal-login-architect' ),
            array( $this, 'logo_type_callback' ),
            self::SLUG,
            'rla_general_section'
        );

        add_settings_field(
            'rla_logo_text',
            esc_html__( 'Logo Text', 'royal-login-architect' ),
            array( $this, 'logo_text_callback' ),
            self::SLUG,
            'rla_general_section'
        );

        add_settings_field(
            'rla_logo_text_color',
            esc_html__( 'Logo Text Color', 'royal-login-architect' ),
            array( $this, 'logo_text_color_callback' ),
            self::SLUG,
            'rla_general_section'
        );

        add_settings_field(
            'rla_logo_image_base64_svg',
            esc_html__( 'Custom Logo SVG (Base64)', 'royal-login-architect' ),
            array( $this, 'logo_image_base64_svg_callback' ),
            self::SLUG,
            'rla_general_section'
        );

        add_settings_field(
            'rla_login_background_color',
            esc_html__( 'Login Page Background Color', 'royal-login-architect' ),
            array( $this, 'login_background_color_callback' ),
            self::SLUG,
            'rla_general_section'
        );

        add_settings_field(
            'rla_login_form_background_color',
            esc_html__( 'Login Form Background Color', 'royal-login-architect' ),
            array( $this, 'login_form_background_color_callback' ),
            self::SLUG,
            'rla_general_section'
        );

        add_settings_field(
            'rla_login_button_background_color',
            esc_html__( 'Login Button Background Color', 'royal-login-architect' ),
            array( $this, 'login_button_background_color_callback' ),
            self::SLUG,
            'rla_general_section'
        );

        add_settings_field(
            'rla_login_button_text_color',
            esc_html__( 'Login Button Text Color', 'royal-login-architect' ),
            array( $this, 'login_button_text_color_callback' ),
            self::SLUG,
            'rla_general_section'
        );

        add_settings_field(
            'rla_custom_message',
            esc_html__( 'Custom Login Message', 'royal-login-architect' ),
            array( $this, 'custom_message_callback' ),
            self::SLUG,
            'rla_general_section'
        );
    }

    /**
     * Sanitizes plugin options during saving.
     *
     * @param array $input The unsanitized options array from the form.
     * @return array The sanitized options array.
     */
    public function sanitize_options( $input ) {
        $sanitized_input = array();

        $sanitized_input['logo_type'] = isset( $input['logo_type'] ) && in_array( $input['logo_type'], array( 'image', 'text' ), true ) ? sanitize_text_field( $input['logo_type'] ) : 'image';
        $sanitized_input['logo_text'] = isset( $input['logo_text'] ) ? sanitize_text_field( $input['logo_text'] ) : '';
        $sanitized_input['logo_text_color'] = isset( $input['logo_text_color'] ) ? sanitize_hex_color( $input['logo_text_color'] ) : '';
        $sanitized_input['logo_image_base64_svg'] = isset( $input['logo_image_base64_svg'] ) ? sanitize_textarea_field( $input['logo_image_base64_svg'] ) : '';
        $sanitized_input['login_background_color'] = isset( $input['login_background_color'] ) ? sanitize_hex_color( $input['login_background_color'] ) : '';
        $sanitized_input['login_form_background_color'] = isset( $input['login_form_background_color'] ) ? sanitize_hex_color( $input['login_form_background_color'] ) : '';
        $sanitized_input['login_button_background_color'] = isset( $input['login_button_background_color'] ) ? sanitize_hex_color( $input['login_button_background_color'] ) : '';
        $sanitized_input['login_button_text_color'] = isset( $input['login_button_text_color'] ) ? sanitize_hex_color( $input['login_button_text_color'] ) : '';
        $sanitized_input['custom_message'] = isset( $input['custom_message'] ) ? wp_kses_post( $input['custom_message'] ) : '';

        return $sanitized_input;
    }

    /**
     * Callback for the general settings section description.
     */
    public function general_section_callback() {
        echo '<p>' . esc_html__( 'Configure the appearance and message of your WordPress login page.', 'royal-login-architect' ) . '</p>';
    }

    /**
     * Renders the logo type selection radio buttons.
     */
    public function logo_type_callback() {
        $options = $this->get_options();
        $logo_type = $options['logo_type'];
        ?>
        <label>
            <input type="radio" name="rla_options[logo_type]" value="image" <?php checked( $logo_type, 'image' ); ?> />
            <?php esc_html_e( 'Image Logo (SVG)', 'royal-login-architect' ); ?>
        </label>
        <br>
        <label>
            <input type="radio" name="rla_options[logo_type]" value="text" <?php checked( $logo_type, 'text' ); ?> />
            <?php esc_html_e( 'Text Logo', 'royal-login-architect' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'Choose whether to display an image (SVG) or text as the login logo.', 'royal-login-architect' ); ?></p>
        <?php
    }

    /**
     * Renders the logo text input field.
     */
    public function logo_text_callback() {
        $options = $this->get_options();
        ?>
        <input type="text" name="rla_options[logo_text]" value="<?php echo esc_attr( $options['logo_text'] ); ?>" class="regular-text" />
        <p class="description"><?php esc_html_e( 'This text will be used as the title/alt for the logo, and also as the displayed logo if "Text Logo" is selected.', 'royal-login-architect' ); ?></p>
        <?php
    }

    /**
     * Renders the logo text color input field.
     */
    public function logo_text_color_callback() {
        $options = $this->get_options();
        ?>
        <input type="text" name="rla_options[logo_text_color]" value="<?php echo esc_attr( $options['logo_text_color'] ); ?>" class="regular-text" placeholder="#333333" />
        <p class="description"><?php esc_html_e( 'Enter a hex color code (e.g., #RRGGBB) for the text logo. Only applies if "Text Logo" is selected.', 'royal-login-architect' ); ?></p>
        <?php
    }

    /**
     * Renders the custom logo SVG (Base64) textarea.
     */
    public function logo_image_base64_svg_callback() {
        $options = $this->get_options();
        ?>
        <textarea name="rla_options[logo_image_base64_svg]" rows="5" cols="50" class="large-text code"><?php echo esc_textarea( $options['logo_image_base64_svg'] ); ?></textarea>
        <p class="description"><?php esc_html_e( 'Enter your custom SVG as a base64 encoded string here (e.g., from an SVG to data URI converter). If left empty and "Image Logo" is selected, a default crown SVG will be used.', 'royal-login-architect' ); ?></p>
        <?php
    }

    /**
     * Renders the login page background color input field.
     */
    public function login_background_color_callback() {
        $options = $this->get_options();
        ?>
        <input type="text" name="rla_options[login_background_color]" value="<?php echo esc_attr( $options['login_background_color'] ); ?>" class="regular-text" placeholder="#f1f1f1" />
        <p class="description"><?php esc_html_e( 'Enter a hex color code (e.g., #RRGGBB) for the login page background.', 'royal-login-architect' ); ?></p>
        <?php
    }

    /**
     * Renders the login form background color input field.
     */
    public function login_form_background_color_callback() {
        $options = $this->get_options();
        ?>
        <input type="text" name="rla_options[login_form_background_color]" value="<?php echo esc_attr( $options['login_form_background_color'] ); ?>" class="regular-text" placeholder="#ffffff" />
        <p class="description"><?php esc_html_e( 'Enter a hex color code (e.g., #RRGGBB) for the login form background.', 'royal-login-architect' ); ?></p>
        <?php
    }

    /**
     * Renders the login button background color input field.
     */
    public function login_button_background_color_callback() {
        $options = $this->get_options();
        ?>
        <input type="text" name="rla_options[login_button_background_color]" value="<?php echo esc_attr( $options['login_button_background_color'] ); ?>" class="regular-text" placeholder="#0073aa" />
        <p class="description"><?php esc_html_e( 'Enter a hex color code (e.g., #RRGGBB) for the login button background.', 'royal-login-architect' ); ?></p>
        <?php
    }

    /**
     * Renders the login button text color input field.
     */
    public function login_button_text_color_callback() {
        $options = $this->get_options();
        ?>
        <input type="text" name="rla_options[login_button_text_color]" value="<?php echo esc_attr( $options['login_button_text_color'] ); ?>" class="regular-text" placeholder="#ffffff" />
        <p class="description"><?php esc_html_e( 'Enter a hex color code (e.g., #RRGGBB) for the login button text.', 'royal-login-architect' ); ?></p>
        <?php
    }

    /**
     * Renders the custom message textarea.
     */
    public function custom_message_callback() {
        $options = $this->get_options();
        ?>
        <textarea name="rla_options[custom_message]" rows="5" cols="50" class="large-text"><?php echo esc_textarea( $options['custom_message'] ); ?></textarea>
        <p class="description"><?php esc_html_e( 'Enter a custom message to display above the login form. Basic HTML tags are allowed.', 'royal-login-architect' ); ?></p>
        <?php
    }

    /**
     * Renders the plugin's settings page in the WordPress admin.
     */
    public function settings_page_callback() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Royal Login Architect Settings', 'royal-login-architect' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                // Output security fields and settings sections
                settings_fields( 'rla_settings_group' );
                do_settings_sections( self::SLUG );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Outputs inline CSS for the WordPress login page based on plugin settings.
     * This function is hooked into 'login_enqueue_scripts'.
     */
    public function login_styles() {
        $options = $this->get_options();
        $bg_color = esc_attr( $options['login_background_color'] );
        $form_bg_color = esc_attr( $options['login_form_background_color'] );
        $button_bg_color = esc_attr( $options['login_button_background_color'] );
        $button_text_color = esc_attr( $options['login_button_text_color'] );
        $logo_type = $options['logo_type'];
        $logo_text = esc_attr( $options['logo_text'] );
        $logo_text_color = esc_attr( $options['logo_text_color'] );
        $custom_svg_base64 = $options['logo_image_base64_svg'];

        // Use custom SVG if provided, otherwise fall back to default crown SVG.
        $logo_svg_base64 = ! empty( $custom_svg_base64 ) ? $custom_svg_base64 : self::get_default_crown_svg_base64();

        // Calculate a darker hover color for the button
        $hover_button_bg_color = $this->adjust_brightness( $button_bg_color, -20 ); // Darken by 20%
        ?>
        <style type="text/css">
            /* Royal Login Architect Custom Styles */
            body.login {
                background-color: <?php echo $bg_color; ?> !important;
            }
            #loginform {
                background: <?php echo $form_bg_color; ?> !important;
                border: 1px solid rgba(0,0,0,0.1); /* Subtle border for form */
                box-shadow: 0px 0px 20px rgba(0,0,0,0.05); /* Soft shadow */
                padding: 26px 24px 46px; /* Adjust padding */
                border-radius: 8px; /* Rounded corners */
            }
            .login form .input, .login input[type="text"] {
                border-radius: 4px;
                border-color: #ddd;
            }
            .login form input[type="checkbox"] {
                border-radius: 3px;
            }
            .login h1 a {
                width: 80px; /* Standard size for WP logo, adjust if needed */
                height: 80px; /* Standard size for WP logo, adjust if needed */
                background-size: contain; /* Ensure SVG fits */
                background-repeat: no-repeat;
                background-position: center center;
                margin: 0 auto 25px; /* Center the logo */
            }

            <?php if ( 'image' === $logo_type ) : ?>
                /* Image Logo (SVG) Styling */
                .login h1 a {
                    background-image: url('data:image/svg+xml;base64,<?php echo $logo_svg_base64; ?>') !important;
                    text-indent: -9999px; /* Hide default WordPress text */
                }
            <?php else : // Text logo ?>
                /* Text Logo Styling */
                .login h1 a {
                    background-image: none !important; /* Remove default WP logo image */
                    text-indent: 0 !important; /* Make text visible */
                    font-size: 32px; /* Make text logo prominent */
                    font-weight: bold;
                    color: <?php echo $logo_text_color; ?> !important; /* Apply text color */
                    text-decoration: none;
                    line-height: 1.2;
                    height: auto !important; /* Adjust height to content */
                    width: auto !important; /* Adjust width to content */
                    display: block; /* Ensure it takes full width */
                    text-align: center;
                }
                .login h1 a::after {
                    content: "<?php echo $logo_text; ?>"; /* Display custom text */
                }
            <?php endif; ?>

            .wp-core-ui .button-primary {
                background: <?php echo $button_bg_color; ?> !important;
                border-color: <?php echo $this->adjust_brightness( $button_bg_color, -10 ); ?> !important;
                box-shadow: 0 1px 0 <?php echo $this->adjust_brightness( $button_bg_color, -15 ); ?>;
                color: <?php echo $button_text_color; ?> !important;
                text-shadow: none;
                border-radius: 4px;
            }
            .wp-core-ui .button-primary:hover,
            .wp-core-ui .button-primary:focus {
                background: <?php echo $hover_button_bg_color; ?> !important;
                border-color: <?php echo $this->adjust_brightness( $hover_button_bg_color, -10 ); ?> !important;
                color: <?php echo $button_text_color; ?> !important;
            }
            .login #nav, .login #backtoblog {
                text-align: center;
            }
        </style>
        <?php
    }

    /**
     * Changes the URL the login logo links to.
     * Hooked into 'login_headerurl'.
     *
     * @return string The new URL (home URL of the site).
     */
    public function login_header_url() {
        return home_url();
    }

    /**
     * Changes the title attribute (tooltip) of the login logo link.
     * Hooked into 'login_headertitle'.
     *
     * @return string The new title.
     */
    public function login_header_title() {
        $options = $this->get_options();
        return esc_attr( $options['logo_text'] );
    }

    /**
     * Adds a custom message above the login form.
     * Hooked into 'login_message'.
     *
     * @param string $message Existing login message.
     * @return string Modified login message.
     */
    public function login_message_content( $message ) {
        $options = $this->get_options();
        $custom_message = $options['custom_message'];

        if ( ! empty( $custom_message ) ) {
            // Append the custom message, sanitized using wp_kses_post to allow safe HTML.
            $message .= '<p class="message">' . wp_kses_post( $custom_message ) . '</p>';
        }
        return $message;
    }

    /**
     * Helper function to get a default crown SVG as a base64 encoded string.
     * This SVG is used when no custom SVG is provided.
     *
     * @return string Base64 encoded SVG.
     */
    private static function get_default_crown_svg_base64() {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="#DAA520" d="M10 90h80v-5H10z"/><path fill="#DAA520" d="M50 10L30 50h40L50 10zM20 50l-5 15h10L20 50zm60 0l5 15h-10L80 50zM35 50l-5 15h10L35 50zm30 0l5 15h-10L65 50z"/><circle fill="#8B0000" cx="50" cy="15" r="5"/><circle fill="#8B0000" cx="20" cy="55" r="5"/><circle fill="#8B0000" cx="80" cy="55" r="5"/><circle fill="#8B0000" cx="35" cy="55" r="5"/><circle fill="#8B0000" cx="65" cy="55" r="5"/></svg>';
        return base64_encode( $svg );
    }

    /**
     * Adjusts the brightness of a hex color code.
     * Useful for generating hover states for buttons.
     *
     * @param string $hex The hex color code (e.g., "#RRGGBB" or "RRGGBB").
     * @param int    $steps The number of steps to adjust brightness (-255 to 255). Positive to lighten, negative to darken.
     * @return string The adjusted hex color code. Returns black for invalid input.
     */
    private function adjust_brightness( $hex, $steps ) {
        // Strip # if it exists
        $hex = str_replace( '#', '', $hex );

        // If it's a 3-digit hex, convert to 6-digit
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        // Validate hex color format
        if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
            return '#000000'; // Return black for invalid color
        }

        // Get RGB values
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );

        // Adjust brightness, clamping values between 0 and 255
        $r = max( 0, min( 255, $r + $steps ) );
        $g = max( 0, min( 255, $g + $steps ) );
        $b = max( 0, min( 255, $b + $steps ) );

        // Convert back to hex and ensure two digits
        $r_hex = str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT );
        $g_hex = str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT );
        $b_hex = str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );

        return '#' . $r_hex . $g_hex . $b_hex;
    }
}

// Register activation and deactivation hooks for the plugin.
register_activation_hook( __FILE__, array( 'Royal_Login_Architect', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Royal_Login_Architect', 'deactivate' ) );

// Initialize the plugin once WordPress has loaded all other plugins.
function run_royal_login_architect() {
    new Royal_Login_Architect();
}
add_action( 'plugins_loaded', 'run_royal_login_architect' );