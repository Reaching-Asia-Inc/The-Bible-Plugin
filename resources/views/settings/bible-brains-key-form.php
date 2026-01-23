<?php
/**
 * @var string $nonce
 * @var string $fields
 * @var string $tab
 * @var string $error
 */

use function CodeZone\Bible\api_url;

$this->layout( 'layouts/settings', compact( 'tab' ) );
?>

<form method="post"
      x-data="br_bible_brains_key_form(<?php echo esc_attr(
          wp_json_encode(
              array_merge(
                  [
                      'fields'          => $fields,
                      'success_message' => __( 'Settings saved.', 'bible-plugin' ),
                      'url'             => esc_url( '/wp-admin/admin.php?page=bible-plugin&tab=advanced' ),
                      'action'          => esc_url( api_url( 'bible-brains/key' ) ),
                      'error'           => $error ?? '',
                      'refresh'         => true
                  ]
              )
          )
      ); ?>)"
>

    <fieldset>

        <?php $this->insert( 'partials/alerts' ); ?>

        <div class="tbp-form-group">
            <sp-field-group>
                <sp-field-label
                    for="bible_brains_key"><?php esc_html_e( 'Bible Brain API Key', 'bible-plugin' ); ?></sp-field-label>

                <div>
                    <sp-textfield id="bible_brains_key"
                        <?php if ($fields['bible_brains_key_readonly']):?> readonly <?php endif;?>
                                  name="bible_brains_key"
                                  :value="dirty_bible_brains_key"
                                  :valid="verified"
                                  @change="dirty_bible_brains_key = $event.target.value"
                                  placeholder="<?php esc_attr_e( 'Enter key...', 'bible-plugin' ); ?>"
                    ></sp-textfield>

                    <?php if (! $fields['bible_brains_key_readonly']):?>

                        <sp-button
                            x-show="!!dirty_bible_brains_key && !verified"
                            key="bible_brains_button_negative"
                            label="<?php esc_attr_e( 'Validate', 'bible-plugin' ); ?>"
                            variant="secondary"
                            @click="submit"
                            size="m">
                            <?php esc_html_e( 'Validate', 'bible-plugin' ); ?>
                            <sp-icon-key slot="icon"></sp-icon-key>
                        </sp-button>

                        <sp-button
                            x-show="dirty_bible_brains_key && verified"
                            key="bible_brains_button_positive"
                            variant="accent"
                            label="<?php esc_attr_e( 'Valid', 'bible-plugin' ); ?>"
                            @click="submit"
                            size="m">
                            <?php esc_html_e( 'Valid', 'bible-plugin' ); ?>
                            <sp-icon-key slot="icon"></sp-icon-key>
                        </sp-button>
                    <?php endif;?>

                </div>

                <sp-help-text size="s">
                    <span x-show="fields.bible_brains_key_instructions" x-text="fields.bible_brains_key_instructions"></span>
                    <span x-show="!fields.bible_brains_key_instructions"><?php esc_html_e( "You are not required to provide a key unless you want to use your own.", 'bible-plugin' ); ?></span>
                    <sp-link href="https://4.dbt.io/api_key/request" target="_blank">
                        <?php esc_html_e( "Request a key.", 'bible-plugin' ); ?>
                    </sp-link>
                </sp-help-text>
            </sp-field-group>
        </div>

        <div class="tbp-form-group">
            <sp-field-group>
                <sp-field-label
                        for="google_tag_id"><?php esc_html_e( 'Google Tag ID', 'bible-plugin' ); ?></sp-field-label>

                <div>
                    <sp-textfield id="google_tag_id"
                                  name="google_tag_id"
                                  :value="dirty_google_tag_id"
                                  @change="dirty_google_tag_id = $event.target.value"
                                  placeholder="<?php esc_attr_e( 'Enter Google Tag ID...', 'bible-plugin' ); ?>"
                    ></sp-textfield>

                    <sp-button
                            x-show="dirty_google_tag_id !== fields.google_tag_id"
                            key="google_tag_button"
                            label="<?php esc_attr_e( 'Save', 'bible-plugin' ); ?>"
                            variant="secondary"
                            @click="submit"
                            size="m">
                        <?php esc_html_e( 'Save', 'bible-plugin' ); ?>
                        <sp-icon-save slot="icon"></sp-icon-save>
                    </sp-button>
                </div>

                <sp-help-text size="s">
                    <?php esc_html_e( 'Enter your Google Tag ID (e.g., G-XXXXXXXXXX).', 'bible-plugin' ); ?>
                </sp-help-text>
            </sp-field-group>
        </div>
    </fieldset>
</form>

