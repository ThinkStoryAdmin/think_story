<?php   defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<div class="alert alert-danger">
   <?php echo t('<strong>Attention!</strong> Clearing your site\'s content prior to installing this theme is highly recommended.')?>
   <?php echo 'Some express objects & attribute types will be installed ';?>
   <br><input type="checkbox" name="installSampleContent" value="1" /> Install sample content (pages, topics, express objects, stacks, etc).
</div>
