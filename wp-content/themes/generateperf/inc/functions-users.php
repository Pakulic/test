<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//-----------------------------------------------------
// Affichage de l'avatar par défaut
//-----------------------------------------------------

function generateperf_default_avatar() {
    return get_stylesheet_directory_uri() . '/images/icons/avatar.svg';
}

//-----------------------------------------------------
// Champ de modification de l'avatar sur la page profile.php
//-----------------------------------------------------

add_action('show_user_profile', 'generateperf_custom_user_avatar', 10);
add_action('edit_user_profile', 'generateperf_custom_user_avatar', 10);
function generateperf_custom_user_avatar($user) {
    $user_avatar = get_avatar_url($user->ID);
    wp_nonce_field('custom_avatar_nonce', 'custom_avatar_nonce');
    echo '<h3>' . __('Custom avatar', 'generateperf') . '</h3>';
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th><label for="user_avatar">' . __('Profile picture', 'generateperf') . '</label></th>';
    echo '<td>';
    echo '<input type="button" data-id="user_avatar" data-src="user_avatar_src" class="button custom-avatar-button" value="' . __('Select an image', 'generateperf') . '"/>';
    echo '<input type="hidden" id="user_avatar" name="user_avatar" value="' . esc_url($user_avatar) . '"/>';
    echo '<p><img id="user_avatar_src" src="' . esc_url($user_avatar) . '" width="96" height="96" loading="lazy" decoding="async"></p>';
    echo '<input type="button" class="button delete-custom-avatar" value="' . __('Remove', 'generateperf') . '"/>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var frame;
            $('.custom-avatar-button').on('click', function(e) {
                e.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: '<?php echo esc_js(__('Select an image', 'generateperf')); ?>',
                    button: {
                        text: '<?php echo esc_js(__('Use as avatar', 'generateperf')); ?>'
                    },
                    multiple: false
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#user_avatar').val(attachment.url);
                    $('#user_avatar_src').attr('src', attachment.url).show();
                });
                frame.open();
            });
            $('.delete-custom-avatar').on('click', function(e) {
            e.preventDefault();
            $('#user_avatar').val('');
            $('#user_avatar_src').attr('src', '<?php echo generateperf_default_avatar(); ?>');
        });
        });
    </script>
    <?php
}

//-----------------------------------------------------
// Chargement de la couche WP.media
//-----------------------------------------------------

add_action('admin_enqueue_scripts', 'generateperf_avatar_media_scripts');
function generateperf_avatar_media_scripts($hook) {
    if ('profile.php' !== $hook && 'user-edit.php' !== $hook) return;
    wp_enqueue_media();
}

//-----------------------------------------------------
// Enregistrement des données à l'envoi du formulaire
//-----------------------------------------------------

add_action('personal_options_update', 'generateperf_save_custom_user_profile_fields');
add_action('edit_user_profile_update', 'generateperf_save_custom_user_profile_fields');
function generateperf_save_custom_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) return false;
    if (isset($_POST['user_avatar'])) {
        update_user_meta($user_id, 'user_avatar', $_POST['user_avatar']);
    }
}

//-----------------------------------------------------
// Chargement du script de sélection / upload des images
//-----------------------------------------------------

add_filter('get_avatar_url', 'generateperf_get_avatar_url', 10, 2);
function generateperf_get_avatar_url($url, $id_or_email) {
    $user = false;
    if (is_numeric($id_or_email)) {
        $id = (int) $id_or_email;
        $user = get_user_by('id', $id);
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by('id', $id);
        }
    } else {
        $user = get_user_by('email', $id_or_email);
    }
    if ($user && is_object($user)) {
        $user_avatar = get_user_meta($user->ID, 'user_avatar', true);
        if (!empty($user_avatar)) {
            return $user_avatar;
        }
    }
    // Avatar par défaut
    return generateperf_default_avatar();
}

//-----------------------------------------------------
// Création d'une colonne avatar dans l'administration
//-----------------------------------------------------

add_filter('manage_users_columns', 'generateperf_add_avatar_column');
function generateperf_add_avatar_column($columns) {
    $columns = array_slice($columns, 0, 1, true) + array('avatar' => __('Profile picture', 'generateperf')) + array_slice($columns, 1, NULL, true);
    return $columns;
}

//-----------------------------------------------------
// Affichage de la colonne dans users.php
//-----------------------------------------------------

add_filter('manage_users_custom_column', 'generateperf_display_avatar_in_column', 10, 3);
function generateperf_display_avatar_in_column($val, $column_name, $user_id) {
    switch ($column_name) {
        case 'avatar':
            return '<img src="' . esc_url(get_avatar_url($user_id)) . '" width="75" height="75" loading="lazy" decoding="async">';
            break;
        default:
            return $val;
    }
}

//-----------------------------------------------------
// Champ utilisateur téléphone
//-----------------------------------------------------

add_action( 'show_user_profile', 'generate_child_add_user_phone_support', 11 );
add_action( 'edit_user_profile', 'generate_child_add_user_phone_support', 11 );
function generate_child_add_user_phone_support( WP_User $user ) : void {
    echo '<h3>' . __('Custom fields', 'generateperf') . '</h3>';
	$html .= '<table class="form-table">';
		$html .= '<tr>';
			$html .= '<th>';
				$html .= '<label for="user-field-id">' . __( 'Phone', 'generateperf' ) . '</label>';
			$html .= '</th>';
			$html .= '<td>';
				$html .= '<input placeholder="+33600000000" type="tel" name="telephone" id="telephone" value="' . esc_attr( get_user_meta( $user->ID, 'telephone', true ) ) . '" class="regular-text"><br>';
				$html .= '<span class="description">' . __( 'Phone number with international indicator', 'generateperf' ) . '</span>';
			$html .= '</td>';
		$html .= '</tr>';
	$html .= '</table>';
	echo $html;
}