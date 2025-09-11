<?php
/**
 * @var string $tab
 * @var string $nonce
 * @var array  $local_bibles
 * @var string|null $success_message
 */
$action_base = admin_url( 'admin.php?page=bible-plugin' );
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

$this->layout( 'layouts/settings', compact( 'tab' ) );
?>
<div class="wrap" x-data="br_custom_bibles_form(<?php echo esc_attr( wp_json_encode( [
        'bibles'     => $local_bibles ?? [],
        'nonce'      => $nonce,
        'actionBase' => $action_base,
    ] ) ); ?>)" x-init="init()">
    <div id="local-ajax-notices" x-show="notice.message" :class="'notice is-dismissible notice-' + notice.type" style="display:none;">
        <p x-text="notice.message"></p>
        <button type="button" class="notice-dismiss" @click="notice.message=''" aria-label="<?php echo esc_attr__( 'Dismiss this notice.', 'bible-plugin' ); ?>">
            <span class="screen-reader-text"><?php echo esc_html__( 'Dismiss this notice.', 'bible-plugin' ); ?></span>
        </button>
    </div>
    <?php
    // Server-side notice rendering (non-AJAX flow)
    if ( !empty( $success_message ) ) {
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html( $success_message )
        );
    } elseif ( isset( $_GET['upload-success'], $_GET['message'] ) ) {
        // Backward-compatible fallback using query parameters
        $message = rawurldecode( wp_unslash( $_GET['message'] ) );
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html( $message )
        );
    }
    ?>
    <h1><?php echo esc_html__( 'Custom Paratext Bibles', 'bible-plugin' ); ?></h1>
    <p><?php echo esc_html__( 'Upload Paratext Bible zip files, associate a language, and manage your custom bibles.', 'bible-plugin' ); ?></p>

    <hr />

    <h2><?php echo esc_html__( 'Add Paratext Bible', 'bible-plugin' ); ?></h2>
    <form id="local-bible-upload-form" x-ref="uploadForm" @submit.prevent="uploadBible" enctype="multipart/form-data" method="post" action="<?php echo esc_url( \CodeZone\Bible\api_url( 'local-bibles/upload' ) ); ?>">
        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>" />
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row"><sp-field-label for="local-zip"><?php echo esc_html__( 'Paratext Zip', 'bible-plugin' ); ?></sp-field-label></th>
                <td><input type="file" id="local-zip" name="file" accept=".zip" required /></td>
            </tr>
            <tr>
                <th scope="row"><sp-field-label for="language-id"><?php echo esc_html__( 'Language ID or ISO code', 'bible-plugin' ); ?></sp-field-label></th>
                <td>
                    <sp-textfield
                        id="language-id"
                        placeholder="<?php echo esc_attr__( 'e.g., eng or eng_lc', 'bible-plugin' ); ?>"
                        @input="$refs.languageIdHidden.value = $event.target.value"
                    ></sp-textfield>
                    <input type="hidden" x-ref="languageIdHidden" name="language_id" />
                    <p class="description"><?php echo esc_html__( 'If left blank, the ISO code from Paratext is used.', 'bible-plugin' ); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
        <p>
            <sp-button variant="primary" @click="uploadBible"><?php echo esc_html__( 'Add Paratext Bible', 'bible-plugin' ); ?></sp-button>
        </p>
    </form>

    <hr />

    <h2><?php echo esc_html__( 'Custom Bibles', 'bible-plugin' ); ?></h2>
    <table class="widefat fixed striped">
        <thead>
        <tr>
            <th><?php echo esc_html__( 'ID', 'bible-plugin' ); ?></th>
            <th><?php echo esc_html__( 'Name', 'bible-plugin' ); ?></th>
            <th><?php echo esc_html__( 'ISO', 'bible-plugin' ); ?></th>
            <th><?php echo esc_html__( 'Language', 'bible-plugin' ); ?></th>
            <th><?php echo esc_html__( 'Language ID', 'bible-plugin' ); ?></th>
            <th><?php echo esc_html__( 'Path', 'bible-plugin' ); ?></th>
            <th><?php echo esc_html__( 'Actions', 'bible-plugin' ); ?></th>
        </tr>
        </thead>
        <tbody id="local-bibles-table-body">
        <template x-if="!bibles || Object.keys(bibles).length === 0">
            <tr><td colspan="7"><?php echo esc_html__( 'No custom bibles yet.', 'bible-plugin' ); ?></td></tr>
        </template>
        <template x-for="([id, b], idx) in Object.entries(bibles)" :key="id">
            <tr :data-id="id">
                <td><code x-text="id"></code></td>
                <td x-text="b?.name || ''"></td>
                <td x-text="b?.iso || ''"></td>
                <td x-text="b?.language || ''"></td>
                <td>
                    <sp-textfield
                        class="language-id-input"
                        :value="bibles[id].language_id || ''"
                        placeholder="<?php echo esc_attr__( 'e.g., eng', 'bible-plugin' ); ?>"
                        @input="bibles[id].language_id = $event.target.value"
                    ></sp-textfield>
                </td>
                <td style="max-width: 300px; word-break: break-all;">
                    <small x-text="b?.path || ''"></small>
                </td>
                <td>
                    <sp-button variant="secondary" class="local-bible-save" @click="saveBible(id)"><?php echo esc_html__( 'Save', 'bible-plugin' ); ?></sp-button>
                    <sp-button variant="negative" class="local-bible-delete" @click="deleteBible(id)"><?php echo esc_html__( 'Delete', 'bible-plugin' ); ?></sp-button>
                </td>
            </tr>
        </template>
        </tbody>
    </table>
    <!-- Custom Bibles settings Alpine component is now loaded from the admin bundle -->
</div>
