<?php
/**
 * Bible Brains Setup Form
 *
 * @var $action string
 * @var $nonce string
 * @var $tab string
 * @var $language_options array
 * @var $bible_options array
 * @var $media_type_options array
 * @var $fields array
 * @var $key_action string
 * @var $language_options_endpoint string
 * @var $bible_options_endpoint string
 * @var $search_label string
 */

use function CodeZone\Bible\api_url;

$this->layout( 'layouts/settings', compact( 'tab' ) );
?>

<form method="post"
      id="bible-brains-form"
      @submit="submit"
      x-data="br_form(<?php echo esc_attr(
	      wp_json_encode(
		      [
			      'fields' => $fields,
			      'action' => esc_url( api_url( "bible-brains" ) ),
		      ]
        )
    ); ?>)"
>

    <sp-divider size="s"></sp-divider>

    <tbp-languages-field
            :value="fields.languages"
            @change="fields.languages = $event.target.value"
            media-type-options='<?php echo esc_attr( wp_json_encode( array_values( $media_type_options ) ) ); ?>'
    ></tbp-languages-field>

	<?php $this->insert( 'partials/alerts' ); ?>

    <sp-button-group>

        <sp-button
                active="false"
                type="submit"
                variant="accent"
                label="<?php esc_attr_e( 'Save', 'bible-plugin' ); ?>"
        >
			<?php esc_html_e( 'Save', 'bible-plugin' ); ?>
        </sp-button>
    </sp-button-group>
</form>
