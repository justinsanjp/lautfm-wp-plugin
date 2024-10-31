<?php
/**
 * Plugin Name: Laut.fm Radio Player
 * Description: Ein Custom Radio Player für laut.fm mit Live/Playlist Mode und schwebendem Player
 * Version: 1.3
 * Author: justinsanjp
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

// Default settings
function lautfm_radio_default_settings() {
    return array(
        'mode' => 'live',
        'station' => 'justplay',
        'floating_player' => 'disabled',
        'custom_css' => ''
    );
}

// Enqueue necessary scripts and styles
function lautfm_radio_enqueue_scripts() {
    wp_enqueue_style('lautfm-radio-style', plugins_url('css/style.css', __FILE__));
    wp_enqueue_script('lautfm-radio-script', plugins_url('js/player.js', __FILE__), array('jquery'), '1.1', true);
    
    $settings = get_option('lautfm_radio_settings', lautfm_radio_default_settings());
    
    // Add custom CSS if exists
    if (!empty($settings['custom_css'])) {
        wp_add_inline_style('lautfm-radio-style', $settings['custom_css']);
    }
    
    // Pass variables to JavaScript
    wp_localize_script('lautfm-radio-script', 'lautfmData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'stream_url' => 'https://stream.laut.fm/' . esc_attr($settings['station']),
        'station' => esc_attr($settings['station']),
        'floating_player' => $settings['floating_player']
    ));
}
add_action('wp_enqueue_scripts', 'lautfm_radio_enqueue_scripts');

// Add menu item to WordPress admin
function lautfm_radio_admin_menu() {
    add_menu_page(
        'Laut.fm Radio Settings',
        'Radio Player',
        'manage_options',
        'lautfm-radio-settings',
        'lautfm_radio_settings_page',
        'dashicons-radio'
    );
}
add_action('admin_menu', 'lautfm_radio_admin_menu');

// Create the settings page
function lautfm_radio_settings_page() {
    if (isset($_POST['submit'])) {
        $settings = array(
            'mode' => sanitize_text_field($_POST['lautfm_radio_mode']),
            'station' => sanitize_text_field($_POST['lautfm_radio_station']),
            'floating_player' => sanitize_text_field($_POST['lautfm_floating_player']),
            'custom_css' => sanitize_textarea_field($_POST['lautfm_custom_css']),
            'display_type' => sanitize_text_field($_POST['lautfm_display_type']),
            'iframe_code' => sanitize_textarea_field($_POST['lautfm_iframe_code'])
        );
        update_option('lautfm_radio_settings', $settings);
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }
    
    $settings = get_option('lautfm_radio_settings', lautfm_radio_default_settings());
    ?>
    <div class="wrap">
        <h2>Laut.fm Radio Player Settings</h2>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">Display Type</th>
                    <td>
                        <select name="lautfm_display_type" id="lautfm_display_type" onchange="toggleSettings()">
                            <option value="embedded" <?php selected($settings['display_type'], 'embedded'); ?>>Embedded</option>
                            <option value="widget" <?php selected($settings['display_type'], 'widget'); ?>>Widget</option>
                        </select>
                        <p class="description">Wähle zwischen Embedded-Player oder Widget.</p>
                    </td>
                </tr>
            </table>

            <div id="embedded_settings" style="display: <?php echo $settings['display_type'] === 'embedded' ? 'block' : 'none'; ?>;">
                <h3>Embedded Settings</h3>
                <div style="background-color: #ffdddd; border-left: 6px solid #f44336; padding: 10px; margin-bottom: 15px;">
                    <strong>Warnung:</strong> Embedded-Streams sind von laut.fm verboten. Die Nutzung erfolgt auf eigene Gefahr. Wenn Sie sicher sein möchten, verwenden Sie bitte das Widget.
                </div>
                <table class="form-table">
                    <tr>
                        <th scope="row">Station Name</th>
                        <td>
                            <input type="text" name="lautfm_radio_station" 
                                   value="<?php echo esc_attr($settings['station']); ?>"
                                   placeholder="justplay">
                            <p class="description">Der Name deiner laut.fm Station</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Player Mode</th>
                        <td>
                            <select name="lautfm_radio_mode">
                                <option value="live" <?php selected($settings['mode'], 'live'); ?>>Live Mode</option>
                                <option value="playlist" <?php selected($settings['mode'], 'playlist'); ?>>Playlist Mode</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Floating Player</th>
                        <td>
                            <select name="lautfm_floating_player">
                                <option value="disabled" <?php selected($settings['floating_player'], 'disabled'); ?>>Deaktiviert</option>
                                <option value="enabled" <?php selected($settings['floating_player'], 'enabled'); ?>>Aktiviert</option>
                            </select>
                            <p class="description">Aktiviert einen schwebenden Player am unteren Bildschirmrand</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Custom CSS</th>
                        <td>
                            <textarea name="lautfm_custom_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                            <p class="description">Füge hier dein eigenes CSS hinzu, um das Aussehen des Players anzupassen</p>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="widget_settings" style="display: <?php echo $settings['display_type'] === 'widget' ? 'block' : 'none'; ?>;">
                <h3>Widget Configurator</h3>
                <p>Hier kannst du dir dein Widget zusammen bauen: <a href="https://laut.fm/widgets/configurator/player_for/justplay" target="_blank">Configurator</a></p>
                <table class="form-table">
                    <tr>
                        <th scope="row">Iframe Code</th>
                        <td>
                            <textarea name="lautfm_iframe_code" rows="5" cols="50" class="large-text code"><?php echo esc_textarea($settings['iframe_code']); ?></textarea>
                            <p class="description">Füge hier den Iframe-Code für dein Widget ein.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>

    <script type="text/javascript">
        function toggleSettings() {
            var displayType = document.getElementById("lautfm_display_type").value;
            document.getElementById("embedded_settings").style.display = displayType === "embedded" ? "block" : "none";
            document.getElementById("widget_settings").style.display = displayType === "widget" ? "block" : "none";
        }
        document.addEventListener('DOMContentLoaded', toggleSettings);
    </script>
    <?php
}

// Shortcode to display the player
function lautfm_radio_player_shortcode() {
    $settings = get_option('lautfm_radio_settings', lautfm_radio_default_settings());
    $mode = $settings['mode'];
    $badge_class = $mode === 'live' ? 'on-air' : 'playlist-mode';
    $badge_text = $mode === 'live' ? 'ON AIR' : 'Playlist Mode is Active';
    
    ob_start();
    ?>
    <div class="lautfm-radio-player">
        <div class="player-badge <?php echo esc_attr($badge_class); ?>">
            <span class="badge-text"><?php echo esc_html($badge_text); ?></span>
        </div>
        <div class="player-info">
            <div class="now-playing">
                <span class="artist"></span> - <span class="song"></span>
            </div>
        </div>
        <div class="player-controls">
            <button class="play-pause">Play</button>
            <input type="range" class="volume-slider" min="0" max="100" value="80">
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('lautfm_radio', 'lautfm_radio_player_shortcode');

// Add floating player to footer if enabled
function lautfm_add_floating_player() {
    $settings = get_option('lautfm_radio_settings', lautfm_radio_default_settings());
    if ($settings['floating_player'] === 'enabled') {
        ?>
        <div class="lautfm-floating-player">
            <div class="now-playing">
                <span class="artist"></span> - <span class="song"></span>
            </div>
            <div class="player-controls">
                <button class="play-pause">Play</button>
                <input type="range" class="volume-slider" min="0" max="100" value="80">
            </div>
        </div>
        <?php
    }
}
add_action('wp_footer', 'lautfm_add_floating_player');