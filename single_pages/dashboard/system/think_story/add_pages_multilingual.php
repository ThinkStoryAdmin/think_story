<?php
use Concrete\Core\Attribute\Key\CollectionKey;
use \Concrete\Core\Tree\Type\Topic as TopicTree;
use \Concrete\Core\Tree\Node\Node as TreeNode;
use \Concrete\Core\Tree\Node\Type\Topic as TopicTreeNode;
use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Multilingual\Page\Section\Section as MultilingualSection;

use Concrete\Core\Attribute\Context\FrontendFormContext;
use Concrete\Core\Attribute\Form\Renderer;
use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Page\Page;

defined('C5_EXECUTE') or die("Access Denied.");

$nav = Loader::helper('navigation');

//Build tab identifiers
$tabs = array();
$counter = 0;
if(is_array($sections2)){
    foreach($sections2 as $sectionKey => $sectionValue){
        //$tabs['ccm-tab-content-language-tab-' . $sectionKey] = $sectionKey;
        $tabID = 'language-tab-' . $sectionKey;
        if($counter == 0){
            array_push($tabs, array($tabID, $sectionKey, true));
            $counter++;
        } else {
            array_push($tabs, array($tabID, $sectionKey));
        }
    }
} else {
    echo "Type of Sections2" . gettype($sections2);
}
print Core::make('helper/concrete/ui')->tabs($tabs);
?>
<div id="form-mother">
    <?php
    $counter = -1;
        if(is_array($tabs)){
            foreach($tabs as $tab){
                $counter++;
                ?>
                    <div id="ccm-tab-content-<?php echo $tab[0]?>" class="ccm-tab-content">
                        <div class="form-group">
                        <?php
                        //Whether or not we want to create a page in this location
                        $bCreatePageOrNoName = "rsvp-create-" . $counter;
                        echo $form->label($bCreatePageOrNoName, t("Create page in this locale")) . "  ";
                        //NOTE: putting the second 1 to 0 will make checkbox default to FALSE
                        // DO NOT DO THIS, some other elements take time to load and will not be properly hidden at the start!
                        echo $form->checkbox($bCreatePageOrNoName , 1, 1, array('style' => 'color: blue;', 'onchange' => 'onChangeCreate(this.id)'));

                        echo "<br/><br/>";

                        //Built-in Attributes (location, name, description, page type)
                        //$locationName = $tab[0] . "-rsvp-location";
                        $locationName ="rsvp-location";
                        echo $form->label($locationName, t("Page Location"));
                        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
                        //$defaultPage = MultilingualSection
                        //$sections2[$counter];

                        $tab[1]; // sectionKey
                        $sections3[$tab[1]];

                        //This defaultPage is pretty worthless unfortunately... the way C5 has the PageSelector work doesn't auto go to the language of the last page
                            //selected on this page, but probably uses cookies to get last selected root ACROSS ALL TABS OF A BROWSER
                        $defaultPage = $sections3[$tab[1]]->getSiteHomePageID();
                        echo $app->make('helper/form/page_selector')->selectPage($locationName, $defaultPage); //name, then default value. Default should be the root of the language site

                        echo "<br/>";
                        //$nameName = $tab[0] . "-rsvp-name";
                        $nameName = "rsvp-name";
                        echo $form->label($nameName, t("Page Name"));
                        echo $form->text($nameName);
                        echo "<br/>";

                        //$desciptionName = $tab[0] . "-rsvp-description";
                        $desciptionName = "rsvp-description";
                        echo $form->label($desciptionName, t("Page Description"));
                        echo $form->text($desciptionName);
                        echo "<br/>";

                        //Page Type Selector
                        ?><label class="control-label"><?= t('Page Type') ?></label>
                        <?php
                        $ctArray = PageType::getList(false, $siteType);

                        if (is_array($ctArray)) {
                            ?>
                            <!--<select class="form-control" name="<?=$tab[0]?>-rsvp-ptid" id="selectPTID">-->
                            <select class="form-control" name="rsvp-ptid" id="selectPTID">
                                <?php
                                foreach ($ctArray as $ct) {
                                    ?>
                                    <option
                                        value="<?= $ct->getPageTypeID() ?>" <?php if ("scenario_page_type" == $ct->getPageTypeHandle()) {
                                        ?>  selected <?php
                                    }
                                    ?>>
                                        <?= $ct->getPageTypeDisplayName() ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <?php
                        }
                        echo "<br/>";

                        //Page Template Selector
                        ?><label class="control-label"><?= t('Page Template (WARNING: MAKE SURE THE PAGE TEMPLATE YOU CHOOSE IS ASSOCIATED WITH THE PAGE TYPE)') ?></label>
                        <?php
                        $ptemplatesArray = PageTemplate::getList();
                        if (is_array($ptemplatesArray)) {
                            ?>
                            <!--<select class="form-control" name="<?=$tab[0]?>-rsvp-ptid" id="selectPTID">-->
                            <select class="form-control" name="rsvp-ptemplate" id="selectPTemplate">
                                <?php
                                foreach ($ptemplatesArray as $pt) {
                                    ?>
                                    <option
                                        value="<?= $pt->getPageTemplateHandle() ?>"
                                        <?php if ("page_scenario" == $pt->getPageTemplateHandle()) {
                                            ?> selected <?php
                                        }?>>
                                        <?= $pt->getPageTemplateName() ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <?php
                        }
                        echo "<br/>";

                        //Upgraded version : only show needed attributes :
                        //Simple configurable thing here, we pass a list of the attribute handles we want to be able to edit in the form
                        //Then we can just use the stuff from below!

                        $attribute_handles_to_address = ['ts_pattr_topic_theme', 'ts_pattr_topic_metier', 'ts_pattr_topic_type_donne', 'ts_pattr_timbre', 'ts_pattr_introduction', 'ts_pattr_declencheur', 'ts_pattr_peripetie', 'ts_pattr_resolution', 'ts_pattr_conclusion', 'ts_pattr_recommendations', 'ts_pattr_principes_de_base', 'ts_pattr_ressources'];

                        //Custom Attribute Forms
                        $keyslist = CollectionKey::getList('Collection');
                        $available_aks=array(''=>'(none)');

                        foreach($keyslist AS $key){
                            //$formElementName = $tab[0] . "-" . $key->getAttributeKeyHandle();
                            $formElementName = $key->getAttributeKeyHandle();
                            $typehandle = $key->getAttributeType()->getAttributeTypeHandle();

                            if(!in_array($formElementName, $attribute_handles_to_address)){
                                continue;
                            }

                            echo $form->label($key->getAttributeKeyName(), t($key->getAttributeKeyName()))  . "  ";
                            switch($typehandle){
                                case('text'):
                                    echo $form->text($formElementName);
                                    break;
                                case('textarea'):
                                    //If the text area's mode is rich text, create rich text editor, otherwise use basic textarea editor (no formatting)
                                    if($key->getAttributeKeySettings()->getMode() == 'rich_text'){
                                        $editor = Core::make('editor');
                                        echo $editor->outputStandardEditor($formElementName, $formElementName);
                                    } else {
                                        echo $form->textarea($formElementName);
                                    }
                                    break;
                                case('boolean'):
                                    echo $form->checkbox($formElementName , 1);
                                    break;
                                case('select'):
                                    echo 'select type';
                                    
                                    break;
                                case('topics'):
                                    //Get topic tree and its topics, then output topics to list and provide to selectMultiple form, as there is not a specific form element to select topics
                                    //if(is_object($key)){print_r(get_object_vars($key));}

                                    //getList appears to return a list of ALL topic tree objects (not the topic tree topics)
                                    //$tt = TopicTree::getByID($key->getAttributeKeySettings()->getTopicTreeID())->getList();
                                    $tt = TopicTree::getByID($key->getAttributeKeySettings()->getTopicTreeID());
                                    $topicTree = array();
                                    
                                    //Code taken from core
                                    $node = $tt->getRootTreeNodeObject();
                                    $node->populateChildren();
                                    if (is_object($node)) {
                                        //AMPERSAND IN FRONT OF VARIABLE IN USE MAKES IT LOOK OUTSIDE OF THE SCOPE OF THE FUNCTION
                                        $walk = function ($node) use (&$walk, &$view, &$topicTree) {
                                            foreach ($node->getChildNodes() as $topic) {
                                                if ($topic instanceof \Concrete\Core\Tree\Node\Type\Category) {

                                                } else {
                                                    //THIS WILL NOT PUSH TO THE GLOBAL ARRAY UNLESS THE $topicTree VARIABLE HAS AN AMPERSAND IN FRONT OF IT
                                                    //array_push($topicTree, array($topic->getTreeNodeID()=>$topic->getTreeNodeDisplayName()));
                                                    $topicTree[$topic->getTreeNodeID()] = $topic->getTreeNodeDisplayName();
                                                }
                                                if (count($topic->getChildNodes())) {
                                                    $walk($topic);
                                                }
                                            }
                                        };
                                        $walk($node);
                                    }
                                    
                                    //Check if can select multiple topics, if not output simple select form
                                    if($key->getAttributeKeySettings()->allowMultipleValues()){
                                        echo $form->selectMultiple($formElementName, $topicTree, null, null);
                                    } else {
                                        echo $form->select($formElementName, $topicTree, null, null);
                                    }
                                    break;
                                case('number'):
                                    echo $form->number($formElementName);
                                    break;
                                case('timbre'):
                                    echo "<br/>";
                                    $im = \Core::make('helper/image');
                                    $f = $key->getAttributeKeySettings()->getFileObject();
                                    $thumb = $im->getThumbnail(
                                        $f,
                                        75,
                                        75,
                                        true
                                    ); //<-- set these 2 numbers to max width and height of thumbnails
                                    //Taken from Concrete\Package\ThinkStory\Attribute\Timbre Controller
                                    echo "<img src=\"{$f->getRelativePath() }\" width=\"{$thumb->width}\" height=\"{$thumb->height}\" alt=\"\" />";

                                    echo '<p>Validate : </p>';
                                    //echo $form->checkbox($formElementName . "-valid" , 1);
                                    echo $form->checkbox($formElementName, 1);
                                    break;
                                case(''):
                                    break;
                                default:
                                    break;
                            }
                            echo "<br/><br/>";
                        }
                        ?></div>
                    </div>
                <?php
            }
        } else {
            echo "Type of Sections2" . gettype($sections2);
        }
    ?>
</div>
<div class="ccm-dashboard-form-actions-wrapper">
    <div class="ccm-dashboard-form-actions">
        <button id="qwop" class="pull-right btn btn-success" type="submit" ><?=t('Create Pages')?></button>
    </div>
</div>

<script>
    function enableButtons(){
        $('#qwop').attr('disabled', false);
    }

    function disableButtons(){
        $('#qwop').attr('disabled', true);
    }

    function onChangeCreate(callerID, again) {
        var parent = $("#" + callerID).parent();
        console.log(parent)

        //If is checked or unchecked
        if($("#" + callerID).is(':checked')){
            //Show all child elements
            parent.children().show();
        } else {
            //Hide all child elements, except for the rsvp-create checkbox
            parent.children().hide();
            $("#" + callerID).show() //Show the checkbox
            $("label[for^='rsvp-create-']").show() //$("label[for='rsvp-create*']").show() //Show the label
        }

        //Call again in case...
        if(again)
            setTimeout(onChangeCreate(callerID, !again),5000);
    }
    
    //When first load, hide ALL things
    /*$(document).ready(function(){
        console.log($("input[id^='rsvp-create-']"))
        $("input[id^='rsvp-create-']")each(function(index){
            //onChangeCreate(this.id);
        })
        //onChangeCreate($("input[id^='rsvp-create-']"));
    })*/

    //Script to run page addition
    $(function(){
        console.log($("input[id^='rsvp-create-']"))
        $("input[id^='rsvp-create-']").each(function(index){
            onChangeCreate(this.id);
        })
        $("#qwop").click(function(event){
            disableButtons();
            event.preventDefault(); //prevent default action 
            var request_method = "POST"
            var dataLanguages = []

            //Get data from all form elements
            $('div#form-mother').children().each(function(){
                var currID = $(this).attr('id');
                var currData = $('#' + currID + ' :input').serializeArray();
                var sanitizedData = []

                //Iterate to make sure we have no duplicate keys
                currData.forEach(function(item){
                    var existing = sanitizedData.filter(function(v,i){
                        return v.name == item.name;
                    })
                    if(existing.length){
                        var existingIndex = sanitizedData.indexOf(existing[0])
                        sanitizedData[existingIndex].value = sanitizedData[existingIndex].value.concat(item.value)
                    } else {
                        if(typeof item.value == 'string')
                            item.value = [item.value];
                        
                        sanitizedData.push(item)
                    }
                })

                dataLanguages.push(sanitizedData)
            })

            var form_data = {
                pageData    : dataLanguages
            }
            var url = "<?php echo $controller->action('action_createPages2')?>"

            console.log("Data: ")
            console.log(form_data)
            console.log("URL : " + url)
            
            $.ajax({
                url: url ,
                type: request_method,
                data : form_data
            }).done(function(response){
                console.log('Done')
                console.log(response)
                ConcreteAlert.notify({
                    title: <?php echo json_encode(t('Pages Successfully created')); ?>,
                    message: <?php echo json_encode(t('The pages have been created. You can now see them in the sitemap!')); ?>
                });
                enableButtons()
            }).error(function(error){
                console.log('There was an error')
                console.log(error)
                ConcreteAlert.dialog(
                    <?php echo json_encode(t('Error(s) creating pages!')); ?>,
                    error.responseText
                );
                enableButtons()
            });
        });
    });
    </script>
