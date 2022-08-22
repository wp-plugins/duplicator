<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $errorMessage string */
/* @var $restUrl string */
/* @var $isOk bool */
?>
<div class="sub-title">STATUS</div>
<?php if ($isOk) : ?>
    <p class="green" >
        <b>Successfully did a test REST API call to the WordPress backend.</b>
    </p>
<?php else : ?>
    <p class="red" >
        <b>REST API call failed with the following message:</b> <?php echo $errorMessage; ?>
    </p>
<?php endif; ?>


<div class="sub-title">DETAILS</div>
<p>
    This test makes sure the <a href="https://developer.wordpress.org/rest-api/" target="_blank">WordPress REST API</a> works properly, which is necessary to create new subsites.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<p>
    Some of the possible reasons why the WordPress REST API test might fail are the following:
</p>

<ul>
    <li>
        <b>The rest API is disabled on WordPress.</b> To test whether the REST API works properly please visit the <a href="<?php echo DUPX_U::esc_attr($restUrl) ?>" target="_blank">following address</a>
         and make sure you get a valid JSON output. In case you don't get a JSON output, please make sure that you have permalinks enabled. Under "Settings" > "Permalinks" the
        setting should not be set to "Plain".
    </li>
    <li>
        <b>SSL certificates are expired or invalid.</b> If this is the case please get in touch with your hosting provider to get everything working and up-to-date.
    </li>
    <li>
        <b>Basic Auth Authentication is enabled.</b> If you have basic auth enabled, please go to <i>Duplicator ❯ Settings ❯ Packages ❯ Advanced Settings</i> and
        set the "Basic Auth" option to enabled and enter the username and password. After saving the settings restart the import process.
    </li>
    <li>
        For more information on the topic please check out the <a href="https://developer.wordpress.org/rest-api/frequently-asked-questions/" target="_blank"><b>REST API FAQ</b></a>.
    </li>
</ul>