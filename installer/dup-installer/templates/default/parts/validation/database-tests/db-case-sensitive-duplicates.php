<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var int $lowerCaseTableNames
 * @var array<string[]> $duplicateTableNames
 * @var string[] $reduntantTableNames
 */

?>
<div class="sub-title">STATUS</div>
<p class="red">
    The following tables have the same name but different casing. Underlined tables are going to be excluded from the database extraction.
</p>
<ul>
<?php foreach ($duplicateTableNames as $tableName => $tableNames) { ?>
    <li>
        <?php
        foreach ($tableNames as $index => $name) {
            if (in_array($name, $reduntantTableNames)) { ?>
                <u class="red"><b><?php echo $name; ?></b></u>
                <?php
            } else {
                echo $name;
            }

            if ($index < (count($tableNames) - 1)) {
                echo ', ';
            }
        }
        ?>
    </li>
<?php } ?>
</ul>

<div class="sub-title">DETAILS</div>
<p>
    The database setting <b>lower_case_table_names</b> is set to <b>[<?php echo $lowerCaseTableNames; ?>]</b> which doesn't allow case sensitive table names.
    This will cause issues trying to create tables with the same case insensitive table name. To change the filtered tables switch to "Advanced" and 
    mode and choose the tables to extract in Step 2.
</p>
