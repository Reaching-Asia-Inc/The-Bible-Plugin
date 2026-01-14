<?php
/**
 * Shortcode: Scripture
 *
 * @var array $content
 * @var array $attributes
 * @var string $reference
 * @var string $fileset_type
 */

$this->layout( 'layouts/shortcode', [ 'error' => $error ?? false ] );
?>

<tbp-bible
        reference="<?php echo $attributes['reference']; ?>"
></tbp-bible>
<script>
(function(){
  // Fire GA event for bible shortcode load if GA is present
  var data = <?php echo wp_json_encode( [
      'reference' => $attributes['reference'] ?? '',
  ] ); ?>;
  function fire() {
    try {
      if (typeof window.gtag === 'function') {
        window.gtag('event', 'tbp_bible_shortcode_load', data);
        return;
      }
      if (Array.isArray(window.dataLayer)) {
        window.dataLayer.push(Object.assign({event:'tbp_bible_shortcode_load'}, data));
        return;
      }
      if (typeof window.ga === 'function') {
        window.ga('send','event','BiblePlugin','tbp_bible_shortcode_load', JSON.stringify(data));
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
