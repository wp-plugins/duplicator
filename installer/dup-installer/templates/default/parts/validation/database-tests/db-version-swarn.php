<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $hostDBVersion string */
/* @var $sourceDBVersion string */
/* @var $dbsOfSameType string */
/* @var $hostDBEngine string */
/* @var $sourceDBEngine string */
?>
<div class="sub-title">STATUS</div>
<p>
    <?php if (!$dbsOfSameType) { ?>
        <i class='red'>
            The current database engine is <b>[<?php echo htmlentities($hostDBEngine . ' ' . $hostDBVersion); ?>]</b> while the host database engine was
            <b>[<?php echo htmlentities($sourceDBEngine . ' ' . $sourceDBVersion); ?>]</b>.
        </i>
    <?php } else { ?>
        <i class='red'>
            The current database version is <b>[<?php echo htmlentities($hostDBVersion); ?>]</b> which is below the source database version of
            <b>[<?php echo htmlentities($sourceDBVersion); ?>]</b>.
        </i>
    <?php } ?>
    In some cases this might cause problems with the migration.
</p>

<div class="sub-title">DETAILS</div>
<p>
    <?php if (!$dbsOfSameType) { ?>
        Some versions of different database engines are not compatible with each other, which might cause problems with the database import. 
        We suggest continuing the install as usual.
        In case there are problems with the database install please consult the links in the 
        troubleshoot section to find out which features are not compatible.
    <?php } else { ?>
        The source site used a newer version of <?php echo htmlentities($hostDBEngine); ?>, 
        which may result in problems during the installation if there were changes
        made which are not backward-compatible. We suggest continuing with the install as usual. 
        In case of problems with the database try contacting your hosting provider and asking for a database version upgrade.
    <?php } ?>
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li><a href="https://www.percona.com/software/mysql-database/percona-server" target="_blank">Percona official website</a></li>
    <li><a href="https://mariadb.com/kb/en/mariadb/mariadb-vs-mysql-compatibility/" target="_blank">MariaDB vs MySQL compatibility chart</a></li>
    <li>
        <a href="<?php echo DUPX_U::esc_attr(DUPX_Constants::FAQ_URL); ?>how-to-fix-database-connection-issues/" target="_help"
           title="I'm running into issues with the Database what can I do?">
            [Additional FAQ Help]
        </a>
    </li>
</ul>

