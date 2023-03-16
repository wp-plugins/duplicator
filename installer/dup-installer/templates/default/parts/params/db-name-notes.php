<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<span class="s2-warning-emptydb">
    Warning: The selected 'Action' above will remove <u>all data</u> from this database!
</span>
<span class="s2-warning-renamedb">
    Notice: The selected 'Action' will rename <u>all existing tables</u> from the database name above with a prefix <?php echo $GLOBALS['DB_RENAME_PREFIX']; ?>
    The prefix is only applied to existing tables and not the new tables that will be installed.
</span>
<span class="s2-warning-manualdb">
    Notice: The 'Skip Database Extraction' action will prevent the SQL script (dup-database__[HASH].sql) in the archive from being executed.
    The database above should already be pre-populated with the data for the site.   The updates routines for updating the site URL and paths will be the
    only SQL database commands applied to the database.
</span>
