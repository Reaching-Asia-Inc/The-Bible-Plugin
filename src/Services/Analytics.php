<?php

namespace CodeZone\Bible\Services;

use CodeZone\Bible\Settings\GoogleTagSetting;

/**
 * Class Analytics
 *
 * This class is responsible for outputting the Google Analytics tracking script.
 */
class Analytics
{
    /**
     * @var GoogleTagSetting
     */
    protected $setting;

    /**
     * Analytics constructor.
     *
     * @param GoogleTagSetting $setting
     */
    public function __construct( GoogleTagSetting $setting )
    {
        $this->setting = $setting;
        add_action( 'wp_head', [ $this, 'output_script' ] );
    }

    /**
     * Outputs the Google Analytics tracking script in the head.
     */
    public function output_script()
    {
        $tag_id = $this->setting->get();

        if ( empty( $tag_id ) ) {
            return;
        }

        $ids = preg_split( '/[\s,]+/', $tag_id, -1, PREG_SPLIT_NO_EMPTY );
        $valid_ids = [];
        foreach ( $ids as $id ) {
            if ( preg_match( '/^(G|GT|UA)-[A-Z0-9]+$/', strtoupper( $id ) ) ) {
                $valid_ids[] = strtoupper( $id );
            }
        }

        if ( empty( $valid_ids ) ) {
            return;
        }

        $main_id = $valid_ids[0];
        $config_params = [];
        if ( current_user_can( 'manage_options' ) ) {
            $config_params['debug_mode'] = true;
        }
        $params_json = ! empty( $config_params ) ? ', ' . wp_json_encode( $config_params ) : '';

        // phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript
        ?>
        <!-- Google tag (gtag.js) - Added by The Bible Plugin -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $main_id ); ?>"></script>
        <script>
        <?php
        // phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript
        ?>
            /**
             * The Bible Plugin - Google Analytics Configuration
             * This script enables tracking for Bible Reader events.
             */
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            <?php foreach ( $valid_ids as $id ) : ?>
            gtag('config', '<?php echo esc_js( $id ); ?>'<?php echo $params_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>);
            <?php endforeach; ?>
        </script>
        <?php
    }
}
