<?php
$abn_confirm_my_name_button_color = get_option('abn_confirm_my_name_button_color', '#ffd7c3');
$abn_validate_my_personalization_button_color = get_option('abn_validate_my_personalization_button_color', '#ffd7c3');

echo    '<style id="abn-plugin-dynamic-style">
            #abnConfirmNameBtn {
                background-color: '. $abn_confirm_my_name_button_color .';
            }

            #abnValidateNameBtn {
                background-color: '. $abn_validate_my_personalization_button_color .';
            }
        </style>';
?>