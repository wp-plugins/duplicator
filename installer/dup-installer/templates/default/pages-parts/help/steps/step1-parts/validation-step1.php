<?php

use Duplicator\Installer\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>


<!-- ===================================
VALIDATION-->
<h3>Validation</h3>
The system validation checks help to make sure the system is ready for install.  During installation the website will be in maintenance mode and not
accessible to users.   The series of checks will alert if there are any items that need attention.   An overview of the different status codes can all
be found online in the FAQ titled
<a 
    href="<?php echo LinkManager::getDocUrl('how-to-fix-installer-validation-checks', 'install', 'validation fixes'); ?>" 
    target="_blank"
>
    How to fix installer validation checks? 
</a>
<br/><br/>

The validation process requires a connection to the database before starting.   Enter in all the Database Connection fields and click the "Validate" button
to start the validation process.   If the database connection is not successful, details about how to solve the issue will be provided.   If the database
connection is successful then additional system checks will be performed to help users identify any potential issues that might arise during the install
process.

