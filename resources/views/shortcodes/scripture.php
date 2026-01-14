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
<script>
(function(){
  // Fire GA event for scripture shortcode load if GA is present
  var data = <?php echo wp_json_encode( [
      'reference' => $reference,
      'language' => $attributes['language'] ?? '',
      'media_type' => $fileset_type,
      'direction' => $direction,
  ] ); ?>;
  function fire() {
    try {
      if (typeof window.gtag === 'function') {
        window.gtag('event', 'tbp_scripture_shortcode_load', data);
        return;
      }
      if (Array.isArray(window.dataLayer)) {
        window.dataLayer.push(Object.assign({event:'tbp_scripture_shortcode_load'}, data));
        return;
      }
      if (typeof window.ga === 'function') {
        window.ga('send','event','BiblePlugin','tbp_scripture_shortcode_load', JSON.stringify(data));
      }
    } catch (e) {}
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fire);
  } else {
    fire();
  }
})();
</script>
