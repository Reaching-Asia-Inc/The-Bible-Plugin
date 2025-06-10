<?php
/**
 * Shortcode: Scripture
 *
 * @var array $content
 * @var array $attributes
 * @var string $reference
 * @var string $fileset_type
 * @var string $direction
 */

$this->layout( 'layouts/shortcode', [ 'error' => $error ?? false ] );
?>

<tbp-content content='<?php echo esc_attr( wp_json_encode( $content ) ); ?>'
             reference='<?php echo esc_attr( wp_json_encode( $reference ) ); ?>'
             language="<?php echo esc_attr( $attributes["language"] ); ?>"
             type="<?php echo esc_attr( $fileset_type ); ?>"
             dir="<?php echo esc_attr( $direction ); ?>"
             heading_text="<?php if ( is_string( $attributes["heading_text"] ) ): ?><?php echo esc_attr( $attributes["heading_text"] ); ?><?php endif; ?>"
             <?php if ( $attributes["heading"] ): ?>heading<?php endif; ?>

>
</tbp-content>
