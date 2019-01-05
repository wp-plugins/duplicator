<?php

?>
<div class="hdr-main">
	Exception error
</div><br/>
<p>
    Message: <?php echo $exceptionError->getMessage(); ?>
</p>
Trace:
<pre class="exception-trace"><?php
echo $exceptionError->getTraceAsString();
?></pre>