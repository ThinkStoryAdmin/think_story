<?php defined('C5_EXECUTE') or die(_("Access Denied.")) ?>
<script>
    $('#printerooo').click(function(){
        console.log("CLicked...")
        $.ajax({
            type        : 'POST',
            dataType    : 'json',
            data        : {a:1},
            url         : '<?php echo $this->action('print_pdf')?>',
            encode      : true,
            success: function(response){
                console.log("YAY");
                console.log(response)
                //$('.response').empty();
            },
            complete:function(data){
                //$("#loader").hide();
                //setTimeout(() => {  $("#loader").hide(); }, 1000);
            }
        });
    })
    
</script>

<div id="printerooo" class='ts-ptpdf'>
    <!--fa file is the best i can do -->
    <i class="fa fa-file ts-ptpdf cts-theme-icons"></i>
    <a class='ts-ptpdf cts-theme-tertiary-bar' href='<?php echo $this->action('print_pdf')?>' target="_blank"><?= t("PDF Download")?></a>
</div>
