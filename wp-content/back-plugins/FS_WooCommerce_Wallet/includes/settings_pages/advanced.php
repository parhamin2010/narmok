<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

?>

<form action="<?php echo admin_url('options.php') ?>" method="post">

    <?php

    settings_fields('fsww_advanced_options_group');
    do_settings_sections('fsww_advanced_options_group');

    ?>

    <input type="hidden" name="fsww_db_version" value="0">


    <?php submit_button(__('Run database update script', 'fslm')); ?>

</form>

