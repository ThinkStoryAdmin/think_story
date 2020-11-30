<?php 
    defined('C5_EXECUTE') or die("Access Denied.");
    $c = Page::getCurrentPage();
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
            echo $form->select('ptID', $pageTypesArray, $ptID);
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
    <label for='title' class="control-label"><?=t('Results Page')?>:</label>
    <div class="checkbox">
        <label for="ccm-search-block-external-target">
            <input id="ccm-search-block-external-target" <?php if (intval($cParentID) > 0) {
                ?>checked<?php 
            } ?> name="externalTarget" type="checkbox" value="1" />
            <?=t('Post Results to a Different Page')?>
        </label>
    </div>
    <div id="ccm-redirect-method">
        <label><?= t('How to redirect') ?></label>
        <fieldset id="group1">
            <input type="radio" value="value1" name="group1" id="type1"><label><?= t('Choose a specific page') ?></label><br>
            <input type="radio" value="value2" name="group1" id="type2"><label><?= t('Choose number of pages to go up') ?></label>
        </fieldset>

        <div id="ccm-redirect-method-choice">
            <div id="ccm-search-block-external-target-page">
                <?php
                    echo Loader::helper('form/page_selector')->selectPage('cParentID', $cParentID);
                ?>
            </div>
            <div id="ccm-number-up-target-page">
                <?php
                    echo $form->number('numberUpRedirect', $numberUpRedirect, ['placeholder' => 'Number of pages up to redirect by']);
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Topic Color Express Object-->
<div class="form-group" id='ccm-content-search'>
    <label class="control-label" for="expressColors"><?=t('Category Colors Express Object')?></label>
    <?php
        echo $form->select('expressColors', $entities, $expressColors, ['data-action'=>$view->action('load_entity_data')]);
    ?>
    <!-- TODO fix! Colors defines topics, and Topics defines colors! (https://github.com/ThinkStoryAdmin/think_story/issues/7)-->
    <label class="control-label" for="expressColorsColorsAttribute"><?=t('Category Colors Express Object, Topic Attribute')?></label><br>
    <select name='expressColorsColorsAttribute' data-container="attributes-list-select-color"></select><br>
    
    <label class="control-label" for="expressColorsTopicsAttribute"><?=t('Category Colors Express Object, Color Attribute')?></label><br>
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
        $('#ccm-search-block-external-target-page').hide();
        //Methods needed for the form drop downs (for the express topic color object)
        var $source = $('#ccm-content-search')
        console.log("Source:")
        console.log($source)
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

        /*$("input[name=externalTarget]").on('change', function() {
            if ($(this).is(":checked")) {
                $('#ccm-search-block-external-target-page').show();
            } else {
                $('#ccm-search-block-external-target-page').hide();
            }
        }).trigger('change');*/

        //Redirect set defaults (VALUES are set, but menus aren't unhidden -> use C5's built in select instead of a radio group ?)
        
        //Redirect options listeners
        $("input[name=externalTarget]").on('change', function() {
            if ($(this).is(":checked")) {
                $('#ccm-redirect-method').show();
                $('#ccm-search-block-external-target-page').hide();
            } else {
                $('#ccm-redirect-method').hide();
            }
        }).trigger('change');

        $("input[id=type1]").on('change', function() {
            if ($(this).is(":checked")) {
                $('#ccm-search-block-external-target-page').show();
                $('#ccm-number-up-target-page').hide();
            } else {
                $('#ccm-search-block-external-target-page').hide();
                $('#ccm-number-up-target-page').show()
            }
        }).trigger('change');

        $("input[id=type2]").on('change', function() {
            if ($(this).is(":checked")) {
                $('#ccm-number-up-target-page').show();
                $('#ccm-search-block-external-target-page').hide();
            } else {
                $('#ccm-number-up-target-page').hide();
                $('#ccm-search-block-external-target-page').show();
            }
        }).trigger('change');
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
