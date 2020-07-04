<?php defined('C5_EXECUTE') or die("Access Denied.");?>
<div class="form-group">
    <?php
        $keys = array();
        foreach($attributeKeys as $attributeKey){
            $attributeController = $attributeKey->getController();
            $tID = $attributeController->getTopicTreeID();
            $keys[$attributeKey->getAttributeKeyHandle()] = $attributeKey->getAttributeKeyDisplayName();
        }
        echo $form->select('topic', $keys, $topic/*, array('style' => 'width: 125px;')*/);
    ?>
</div>

<!--<div class="form-group">
    <label class="control-label" for="expressColors"><?=t('Category Color Express Object Handle')?></label>
    <input type="text" class="form-control" name="expressColors" value="<?php echo $expressColors?>">
</div>
<div class="form-group">
    <label class="control-label" for="expressColorsColorsAttribute"><?=t('Category Colors Express Object, Color Attribute Handle')?></label>
    <input type="text" class="form-control" name="expressColorsColorsAttribute" value="<?php echo $expressColorsColorsAttribute?>">
</div>
<div class="form-group">
    <label class="control-label" for="expressColorsTopicsAttribute"><?=t('Category Colors Express Object, Topic Attribute Handle')?></label>
    <input type="text" class="form-control" name="expressColorsTopicsAttribute" value="<?php echo $expressColorsTopicsAttribute?>">
</div>-->

<div class="form-group" id='ccm-content-search'>
    <label class="control-label" for="expressColors"><?=t('Category Colors Express Object')?></label>
    <?php
        echo $form->select('expressColors', $entities, $expressColors, ['data-action'=>$view->action('load_entity_data')]);
    ?>

    <label class="control-label" for="expressColorsColorsAttribute"><?=t('Category Colors Express Object, Color Attribute')?></label><br>
    <select name='expressColorsColorsAttribute' data-container="attributes-list-select-color"></select><br>
    
    <label class="control-label" for="expressColorsTopicsAttribute"><?=t('Category Colors Express Object, Topic Attribute')?></label><br>
    <select name='expressColorsTopicsAttribute' data-container="attributes-list-select-topic"></select><br>
</div>

<script>
    $(function() {
        //Methods needed for the form drop downs (for the express topic color object)
        var $source = $('#ccm-content-search')
        _expressObjectAdvAttributesTemplate = _.template($('script[data-template=express-object-attributes-list-adv]').html())
        
        function fillTemplateColor(attributes, selected){
            var $container = $('#ccm-content-search select[data-container=attributes-list-select-color]')
            $container.html(_expressObjectAdvAttributesTemplate({attributes: attributes, selected:selected}))
        }

        function fillTemplateTopic(attributes, selected){
            var $container = $('#ccm-content-search select[data-container=attributes-list-select-topic]')
            $container.html(_expressObjectAdvAttributesTemplate({attributes: attributes, selected:selected}))
        }

        var expressColorsVar = '<?php echo $expressColors?>';
        console.log('Express Colors ; ' + expressColorsVar)
        var expressColorsColorsAttributeJSVar = '<?= $expressColorsColorsAttribute?>';
        console.log('expressColorsColorsAttributeJSVar ; ' + expressColorsColorsAttributeJSVar)
        var expressColorsTopicsAttributeJSVar = '<?= $expressColorsTopicsAttribute?>';
        console.log('expressColorsTopicsAttributeJSVar ; ' + expressColorsTopicsAttributeJSVar)

        if(!expressColorsVar.length == 0){
            $.concreteAjax({
                url: $source.find('select[name=expressColors]').attr('data-action'),
                //data: {'expressColorsSelc': $source.find('select[name=expressColors]').val()},
                data: {'expressColorsSelc': expressColorsVar},
                success: function(r) {
                    console.log(r)
                    fillTemplateColor(r.attributes,expressColorsColorsAttributeJSVar)
                    fillTemplateTopic(r.attributes,expressColorsTopicsAttributeJSVar)
                }
            });
        }

        $source.find('select[name=expressColors]').on('change', function() {
            console.log('Executing the thing!')
            var exEntityID = $(this).val();
            console.log(exEntityID)
            if (exEntityID) {
                $.concreteAjax({
                    url: $(this).attr('data-action'),
                    data: {'expressColorsSelc': $(this).val()},
                    success: function(r) {
                        console.log(r)
                        fillTemplateColor(r.attributes,expressColorsColorsAttributeJSVar)
                        fillTemplateTopic(r.attributes,expressColorsTopicsAttributeJSVar)
                    }
                });
            } else {
                fillTemplateColor(null,null)
                fillTemplateTopic(null,null)
            }
        });
    });
</script>

<!--Template for the form drop downs (for the express topic color object)-->
<script type="text/template" data-template="express-object-attributes-list-adv">
    <% _.each(attributes, function(attribute) { %>
        <option value="<%=attribute.akHandle%>"
            <% if(selected == attribute.akHandle){%>selected="selected"<%}%>
        >
        <%=attribute.akName%>
        </option>
    <% }); %>
</script>