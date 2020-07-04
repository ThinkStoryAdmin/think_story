<?php   defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<div class="alert alert-danger">
   <?php  echo t('<strong>Attention!</strong> Clearing your site\'s content prior to installing this theme is highly recommended.')?>
   <input type="checkbox" name="installTopics" value="1" /> Install topics.
   <input type="checkbox" name="installSampleContent" value="1" /> Install express objects, stacks, etc.
</div>
