<?php
/**
 * @var string $nonce
 * @var string $fields
 * @var string $tab
 * @var string $color_scheme_options
 * @var string $error
 * @var string $translation_options
 */

use function CodeZone\Bible\api_url;

$this->layout( 'layouts/settings', compact( 'tab' ) );
?>
<form x-data="br_form(<?php echo esc_attr(
	wp_json_encode(
		array_merge(
			[
				'refresh'              => true,
				'fields'               => $fields,
				'nonce'                => $nonce,
				'action'               => esc_url( api_url( "customization" ) ),
				'error'                => $error ?? '',
				'color_scheme_options' => $color_scheme_options
			]
		)
	)
); ?>)"
      @submit="submit"
>
    <fieldset>

        <h2><?php esc_html_e( "Theme", 'bible-plugin' ); ?></h2>

		<?php $this->insert( 'partials/alerts' ); ?>

        <div class="tbp-form-group">
            <sp-picker
                    id="color_scheme"
                    name="color_scheme"
                    label="<?php esc_attr_e( 'Choose', 'bible-plugin' ); ?>..."
                    :value="fields.color_scheme"
                    autocomplete
                    grows
                    required
                    @change="fields.color_scheme = $event.target.value"
            >
                <template x-for="({itemText, value}) in color_scheme_options"
                          :key="value"
                >
                    <sp-menu-item
                            :value="value">
                        <span x-text="itemText"></span>
                    </sp-menu-item>
                </template>
            </sp-picker>

            <sp-help-text size="s">
				<?php esc_html_e( "Switch between light mode or dark mode to match your theme.", 'bible-plugin' ); ?>
            </sp-help-text>
        </div>

        <div class="tbp-form-group">

            <sp-field-group>
                <sp-field-label
                        required
                        for="colors_accent"><?php esc_html_e( 'Accent Color', 'bible-plugin' ); ?></sp-field-label>

                <tbp-color-slider
                        id="colors_accent"
                        name="colors_accent"
                        :value="fields.colors.accent"
                        @change="fields.colors.accent = $event.target.value"
                ></tbp-color-slider>

                <sp-help-text size="s">
					<?php esc_html_e( "The primary accent color used in the bible reader.", 'bible-plugin' ); ?>
                </sp-help-text>
            </sp-field-group>

            <sp-field-group>
                <sp-field-label
                        required
                        for="colors_accent"><?php esc_html_e( 'Accent Color Steps', 'bible-plugin' ); ?></sp-field-label>

                <tbp-color-steps
                        id="colors_accent_steps"
                        name="colors_accent_steps"
                        :color="fields.colors.accent"
                        :value="fields.colors.accent_steps"
                        range="<?php echo esc_attr( wp_json_encode( range( 100, 1500, 100 ) ) ); ?>"
                        @change="fields.colors.accent_steps = $event.target.value"
                ></tbp-color-steps>


                <sp-help-text size="s">
					<?php esc_html_e( "This range of colors could be displayed in the bible reader. The accent color you chose will be used most often.", 'bible-plugin' ); ?>
                </sp-help-text>
            </sp-field-group>
        </div>
    </fieldset>

    <fieldset>
        <h2><?php esc_html_e( "Translations", 'bible-plugin' ); ?></h2>
        <div class="tbp-form-group">
            <template x-for="(value, string) in fields.translations">
                <sp-field-group>
                    <sp-field-label
                            :for="`translation-`+string"
                            x-text="string"></sp-field-label>
                    <sp-textfield
                            :id="`translation-`+string"
                            placeholder="<?php esc_attr_e( 'Enter a translation...', 'bible-plugin' ); ?>"
                            :name="`translations[`+string+`]`"
                            :value="fields.translations[string]"
                            @input="console.log('stgring'); fields.translations[string] = $event.target.value"
                </sp-field-group>
            </template>
    </fieldset>

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
