<?php
namespace Concrete\Package\ThinkStory\Block\TSPrintPageToPdf;

use BlockType;
use CollectionAttributeKey;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Html\Service\Seo;
use Concrete\Core\Page\Feed;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Url\SeoCanonical;
use Database;
use Page;
use Core;
use PageList;
use PageType;
use Concrete\Core\Attribute\Key\CollectionKey;

use Express;

use \Concrete\Core\Tree\Type\Topic as TopicTree;
use \Concrete\Core\Tree\Node\Node as TreeNode;
use \Concrete\Core\Tree\Node\Type\Topic as TopicTreeNode;
use Concrete\Core\Attribute\Key\Category;

use Concrete\Core\Tree\Tree;
use Concrete\Core\Tree\Type\Topic;
use Concrete\Package\TSTest\TSPageList\TSPageList;

use Concrete\Package\ThinkStory\Entity\Attribute\Key\Settings\TimbreSettings;
use Concrete\Package\ThinkStory\Entity\Attribute\Value\Value\TimbreValue;
use ThinkStory\Attributes\Timbre;

use \Mpdf\Mpdf as Mpdf;

class Controller extends BlockController
{
    protected $btTable = 'btTSPrintPageToPdf';
    protected $btInterfaceWidth = 700;
    protected $btInterfaceHeight = 525;
    protected $btExportPageColumns = ['cParentID'];
    protected $btExportPageTypeColumns = ['ptID'];
    protected $btDefaultSet = 'think_story';
    protected $attributesToPrint;
    

    public function getBlockTypeDescription()
    {
        return t("Think Story Button that prints a page's selected attributes to a pdf.");
    }

    public function getBlockTypeName()
    {
        return t("Think Story Print to PDF");
    }

    public function requireAssets(){
        //$this->requireAsset('css', 'bootstrap');
        $this->requireAsset('css', 'font-awesome');
        //$this->requireAsset('javascript', 'bootstrap/*');
        //$this->requireAsset('javascript', 'jquery');
    }

    public function validate($data)
    {
        $e = Core::make('error');
        if(!$data['attributesHeaderIcon']){
            $e->add('NEED HEADER FILE ICON');
        }
        return $e;
    }

    public function save($data){
        $data['attributesToPrint'] = serialize($data['attributesToPrint']);
        $data['attributesTopics'] = serialize($data['attributesTopics']);
        $data['attributesContent'] = serialize($data['attributesContent']);
        parent::save($data);
    }

    public function add()
    {
        $c = Page::getCurrentPage();
        $this->loadKeys();
    }

    public function edit()
    {
        $b = $this->getBlockObject();
        $bCID = $b->getBlockCollectionID();
        $bID = $b->getBlockID();
        $this->set('bID', $bID);
        $c = Page::getCurrentPage();
        $this->loadKeys();
    }

    protected function loadKeys()
    {
        $attributeKeys = [];
        $attributeTopicKeys = [];
        $attributeTimbreKeys = [];
        $keys = CollectionKey::getList();

        //Add references to core page attributes
        $attributeKeys['rpv_pageName'] = t('Page Name');
        $attributeKeys['rpv_pageDescription'] = t('Page Description');
        $attributeKeys['rpv_pageDateCreated'] = t('Page Date Created');
        $attributeKeys['rpv_pageDatePublic'] = t('Page Date Published');
        $attributeKeys['rpv_pageDateLastModified'] = t('Page Date Modified');
        
        foreach ($keys as $ak) {
            if ($ak->getAttributeTypeHandle() !== 'timbre') {
                $attributeKeys[$ak->getAttributeKeyHandle()] = $ak->getAttributeKeyName();
            }
            //Topics
            if ($ak->getAttributeTypeHandle() == 'topics') {
                $attributeTopicKeys[$ak->getAttributeKeyHandle()] = $ak->getAttributeKeyName();
            }
            //Timbre
            if ($ak->getAttributeTypeHandle() == 'timbre') {
                $attributeTimbreKeys[$ak->getAttributeKeyHandle()] = $ak->getAttributeKeyName();
            }
        }

        $this->set('attributes', $attributeKeys);
        $this->set('attributesTopicsK', $attributeTopicKeys);
        $this->set('attributesTimbres', $attributeTimbreKeys);
        $this->set('chosen', unserialize($this->attributesToPrint));
        $this->set('chosenTimbre', $this->attributeTimbre);
        $this->set('chosenTopics', unserialize($this->attributesTopics));
        $this->set('chosenContent', unserialize($this->attributesContent));

        $chosenHeaderIcon = \File::getByID($this->attributesHeaderIcon);
        $this->set('chosenHeaderIcon', $chosenHeaderIcon);
        $this->set('attributesHeaderIcon', $this->attributesHeaderIcon);
    }

    public function view(){
        $this->set('customLabel', $this->label);
    }

    public function action_print_pdf(){
        $c = Page::getCurrentPage();
        //SET AUTO TOP MARGIN DEFAULTS TO FALSE
        //MEANING NO AUTO MARGIN IS APPLIED AFTER THE HEADER
        //NEED TO DEFINE stretch(min) OR pad (max)
        //$mpdf = new Mpdf(['format' => 'A4', 'setAutoTopMargin' => 'stretch', 'setAutoBottomMargin' => 'pad', 'tempDir' => __DIR__ . '/cache']);
        $mpdf = new Mpdf(['format' => 'A4', 'setAutoTopMargin' => 'stretch', 'setAutoBottomMargin' => 'pad']);
        $documentName = strval($c->getCollectionName());
        $mpdf->setTitle($documentName);
        $mpdf->showImageErrors = true;
        

        $mpdf->defaultheaderfontsize=10;
        $mpdf->defaultheaderfontstyle='Arial';
        $mpdf->defaultheaderline=1;
        $mpdf->defaultfooterfontsize=7;

        $footerUrl = is_null($this->attributesFooterURL) ? '' : $this->attributesFooterURL;
        $footerStyle = '';
        if(!is_null($c->getAttributeValue($this->attributeTimbre)) && $c->getAttributeValue($this->attributeTimbre)->getController()->getValidity()){
            $newSrc = $c->getAttributeValue($this->attributeTimbre)->getController()->getFileID()->getRelativePath();
            $htmlTmbr = "<img src=" . $newSrc . " alt='Timbre' style='width:60px;height:60px;text-align:right;'>";

            $headerIconFile = \File::getByID($this->attributesHeaderIcon)->getRelativePath();
            $htmlHeaderIcon = "<img src=" . $headerIconFile . " alt='Header Icon' style='height:60px;text-align:right;'>";

            //No more need for an <hr> block now that the header margins work!
            $mpdf->setheader(" <div style='text-align:right;'>". $htmlHeaderIcon ."</div> |  | <div style='text-align:right;'>". $htmlTmbr ."</div>");
            $mpdf->setFooter(' Page {PAGENO} | | ' . $footerUrl);
        } else {
            $headerIconFile = \File::getByID($this->attributesHeaderIcon)->getRelativePath();
            $htmlHeaderIcon = "<img src=" . $headerIconFile . " alt='Header Icon' style='height:60px;text-align:right;'>";
            $mpdf->setheader(" <div style='text-align:right;'>". $htmlHeaderIcon ."</div> |  | <div style='text-align:right;'>". $htmlTmbr ."</div>");
            $mpdf->setFooter(' Page {PAGENO} | | ' . $footerUrl);
        }

        ///Proc Methods
        //Themes
        $chTopics = unserialize($this->attributesTopics);
        if(is_array($chTopics)){
            foreach($chTopics as $topicName){
                $topic = $c->getAttribute($topicName);
                $topicTree = new TopicTree();
                if(!is_null($topic[0]) && !is_null($topicTree)){
                    $tree = $topicTree->getByID($topic[0]->getTreeID());
                    $mpdf->writeHTML("<div> <span style='font-weight: bold;display: inline'>" 
                        . tc('TreeName', $tree->getTreeName()) . ": </span> <span style=';display: inline'>"
                        . $c->getAttribute($topicName, 'display') 
                        . "</span></div>");
                }
                
            }
        }

        //Title
        $mpdf->writeHTML("<div style='font-size:20px;font-weight: bold;padding: 25px 0px;'>". $c->getCollectionName() . "</div>");

        //Content
        $chContent = unserialize($this->attributesContent);
        foreach($chContent as $content){
            if(is_object( $c->getAttribute($content, 'display') ) && method_exists( $c->getAttribute($content, 'display'), '__toString' )){
                $mpdf->WriteHTML($c->getAttribute($content, 'display'));
            }
            if(null !== $c->getAttribute($content, 'display')){
                $mpdf->WriteHTML($c->getAttribute($content, 'display'));
            } else {
                $mpdf->WriteHTML($content);
            }
        }
        
        $mpdf->Output();
        exit;
    }
}