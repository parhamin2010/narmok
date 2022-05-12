<?php 
if (!defined('UBA_ONE_DOLLOR_RUN')) exit;

require_once("class-notification.php");

class OD_INITIALIZER{
    public function __construct()
    {
        new OD_NOTIFICATION();
    }
}

new OD_INITIALIZER();

?>