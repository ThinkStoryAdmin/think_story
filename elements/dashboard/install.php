<?php   defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<div class="alert alert-danger">
   <?php echo t('<strong>Attention!</strong> Clearing your site\'s content prior to installing this theme is highly recommended.')?>
   <?php echo 'Some express objects & attribute types will be installed ';?>
   <br><input type="checkbox" name="installSampleContent" value="1" /> Install sample content (pages, topics, express objects, stacks, etc).
   <!--<br>
   <label>
      <input type="radio" name="installContentLevel" value="none" required>
      No sample content (only theme, blocks and attribute types, make sure you know what you're doing!)
   </label><br>

   <label>
      <input type="radio" name="installContentLevel" value="basic">
      Basic (same as none, but also includes some initial setup)
   </label><br>

   <label>
      <input type="radio" name="installContentLevel" value="full">
      Full (same as basic, but also includes pages, topics, colors, etc.)
   </label><br>-->
</div>
