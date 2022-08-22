<?php

/**
 *
 * @package Duplicator/Installer
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

?>
<p>
    <b>Overwrite:</b> Overwrites users is the classic mode,  users from the source site will be installed and those from the target site will be discarded.
</p>
<p>
    <b>Keep:</b> Keeps all users of the target site by discarding users of the source site.
    All content on the source site will be assigned to the <u>content author</u> selected user.
</p>
<p>
    <b>Merge:</b> Merges users from the target site with users from the source site.
    The target site users will be unchanged and the source site users will be added by remapping ids and logins if duplicated.
</p>