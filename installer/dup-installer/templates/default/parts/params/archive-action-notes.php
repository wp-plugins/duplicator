<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
/* Variables */
/* @var $currentAction string */
?>
<div 
    class="dynamic-sub-note dynamic-sub-note-<?php echo DUP_Extraction::ACTION_DO_NOTHING .
        ($currentAction == DUP_Extraction::ACTION_DO_NOTHING ? '' : ' no-display'); ?>"
>
    Note: <b>Files are extracted over existing files.</b> 
    After install, the destination folder will contain a combination of the old site files and the files extracted from the archive.
    This option is the most conservative option for those who want to make sure they do not want to lose data.
</div>
<div 
    class="dynamic-sub-note dynamic-sub-note-<?php echo DUP_Extraction::ACTION_REMOVE_ALL_FILES .
        ($currentAction == DUP_Extraction::ACTION_REMOVE_ALL_FILES ? '' : ' no-display'); ?>"
>
    Note: Before extracting the package files, <b>all files and folders in the installation folder will be removed</b> 
    except for folders that contain WordPress installations or Duplicator backup folders<br>
    This option is recommended for those who want to delete all files related to old installations or external applications.
</div>
<div 
    class="dynamic-sub-note dynamic-sub-note-<?php echo DUP_Extraction::ACTION_REMOVE_UPLOADS .
        ($currentAction == DUP_Extraction::ACTION_REMOVE_UPLOADS ? '' : ' no-display'); ?>"
>
    Note: Before extracting the package files, <b>all current media files will be removed</b> (wp-content/uploads)<br>
    This option is for those who want to avoid having old site media mixed with new but have other files/folders 
    in the home path that they don't want to delete.
</div>
<div 
    class="dynamic-sub-note dynamic-sub-note-<?php echo DUP_Extraction::ACTION_REMOVE_WP_FILES .
        ($currentAction == DUP_Extraction::ACTION_REMOVE_WP_FILES ? '' : ' no-display'); ?>"
>
    Note: Before extracting the package files, <b>all current WordPress core and content files and folders will be removed</b> (wp-include, wp-content ... )<br>
    This option is for those who want to avoid having old site media mixed with new but have other files/folders 
    in the home path that they don't want to delete.
</div>
