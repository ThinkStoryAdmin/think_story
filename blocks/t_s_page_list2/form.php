<?php 
    defined('C5_EXECUTE') or die("Access Denied.");
    $c = Page::getCurrentPage();    //TODO remove
?>
<div class="form-group">
    <label class="control-label"><?= t('Page Type') ?></label>
    <?php
        $ctArray = PageType::getList(false, $siteType);
        if (is_array($ctArray)) {
            $pageTypesArray = [];
            $pageTypesArray[0] = '** ' . t('All') . ' **';
            foreach ($ctArray as $ct) {
                $pageTypesArray[$ct->getPageTypeID()] = $ct->getPageTypeDisplayName();
            }
            echo $form->select('ptID', $pageTypesArray, $ptID, ['data-action'=>'doSomethingQ']);
        }
    ?>
</div>

<div class="form-group">    <!-- Sort method (filter or sort) --> 
    <label class="control-label"><?=t('Sort Type')?></label>
    <?php
        echo $form->select('sortType', [0 => t('Sort'), 1 => t('Filter')], $sortType);
    ?>
</div>

<div class="form-group">
    <label class="control-label"><?=t('Choose topics attribute')?></label>
    <?php
        echo $form->selectMultiple('topics', $attributeKeysTopics, $chosenTopics/*, array('style' => 'width: 125px;')*/);
    ?>
</div>

<div class="form-group">
    <label class="control-label" for="pageTopicColors"><?=t('Page Topic Attribute, where Topic is linked to a Category Color')?></label>
    <?php
        echo $form->select('pageTopicColors', $attributeKeysTopicLinkedColor, $pageTopicColors);
    ?>
</div>

<!-- TODO remove ?
<div class="form-group">
    <label class="control-label"><?=t('Sort')?></label>
    <select name="orderBy" class="form-control">
        <option value="display_most_recent" <?= ($orderBy == 'display_most_recent' ? 'selected="selected"' : '');?>>
        <?= t('Most recent') ?>
        </option>
        <option value="display_most_popular" <?= ($orderBy == 'display_most_popular' ? 'selected="selected"' : '');?>>
            <?= t('Most popular') ?>
        </option>
        <option value="display_random" <?= ($orderBy == 'display_random' ? 'selected="selected"' : '');?>>
            <?= t('Random') ?>
        </option>
    </select>
    <?php
        echo $form->select('orderBy', ['display_most_recent' => t('Most recent'), 'display_most_popular' => t('Most popular'), 'display_random' => t('Random')], $orderBy);
    ?>
</div>-->

<!--View Counter Forms stuff-->
<div class="form-group">
    <label class="control-label"><?= t('Page View Count Page Attribute (needed if you sort by popularity)') ?></label>
    <?php
        echo $form->select('viewCountAttribute', $attributeKeysVC, $viewCountAttribute);
    ?>
</div>

<!-- Choose Redirect method & page -->
<div class='form-group'>
    <label for='title' class="control-label"><?=t('Results Page : how to redirect results')?></label>
    <?php
        echo $form->select('iRedirectMethod', [ 0 => t("Don't redirect"), 1 => t('Redirect to a specific page'), 2 => t('Redirect a number of pages up the site tree') ], $iRedirectMethod, ['data-action'=>'redirectMethodListener']);
    ?><br>
    <div id="ccm-redirect-method-choice">
        <div id="ccm-search-block-external-target-page">
            <?php
                echo Loader::helper('form/page_selector')->selectPage('cParentID', $cParentID);
            ?>
        </div>
        <div id="ccm-number-up-target-page">
            <?php
                echo $form->number('numberUpRedirect', $numberUpRedirect, ['placeholder' => 'Number of pages up the site tree to redirect by']);
            ?>
        </div>
    </div>
</div>

<!-- Topic Color Express Object-->
<div class="form-group" id='ccm-content-search'>
    <label class="control-label" for="expressColors"><?=t('Category Colors Express Object')?></label>
    <?php
        echo $form->select('expressColors', $entities, $expressColors, ['data-action'=>$view->action('load_entity_data')]);
    ?><br>
    <!-- TODO fix! Colors defines topics, and Topics defines colors! (https://github.com/ThinkStoryAdmin/think_story/issues/7)-->
    <label for="expressColorsColorsAttribute"><?=t('Category Colors Express Object, Topic Attribute')?></label>
    <select name='expressColorsColorsAttribute' data-container="attributes-list-select-color"></select><br><br>
    
    <label for="expressColorsTopicsAttribute"><?=t('Category Colors Express Object, Color Attribute')?></label>
    <select name='expressColorsTopicsAttribute' data-container="attributes-list-select-topic"></select><br>
</div>

<!-- Default topic color -->
<div class="form-group">
    <label class="control-label"><?= t('Default Color (used if no color define or if no color found)') ?></label><br>
    <?php
        $color =  Core::make('helper/form/color');
        echo $color->output('defaultColor', $defaultColor ? $defaultColor : '#000000');
    ?>
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

        //Set topic color menus
        if(!expressColorsVar.length == 0){
            $.concreteAjax({
                url: $source.find('select[name=expressColors]').attr('data-action'),
                data: {'expressColorsSelc': expressColorsVar/*$source.find('select[name=expressColors]').val()*/},
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

        //Redirect options set visibility based on existing choices
        $('#ccm-search-block-external-target-page').hide();
        $('#ccm-number-up-target-page').hide();
        switch(<?= $iRedirectMethod ?>){
            case 1:
                $('#ccm-search-block-external-target-page').show();
                $('#ccm-number-up-target-page').hide();
                break;
            case 2:
                $('#ccm-search-block-external-target-page').hide();
                $('#ccm-number-up-target-page').show();
                break;
            case 0:
            default:
                $('#ccm-search-block-external-target-page').hide();
                $('#ccm-number-up-target-page').hide();
                break;
        }

        //Redirect options listeners
        $('[data-action=redirectMethodListener]').on('change', () => {
            if($('#' + event.target.id).val() == 1){
                $('#ccm-search-block-external-target-page').show();
                $('#ccm-number-up-target-page').hide();
            } else if($('#' + event.target.id).val() == 2) {
                $('#ccm-search-block-external-target-page').hide();
                $('#ccm-number-up-target-page').show()
            } else {
                $('#ccm-search-block-external-target-page').hide();
                $('#ccm-number-up-target-page').hide()
            }
        })
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
