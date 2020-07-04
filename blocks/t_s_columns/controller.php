<?php

namespace Concrete\Package\ThinkStory\Block\TSColumns;

use Concrete\Core\Block\BlockController;
use Core;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends BlockController
{
    protected $btTable = "btTSColumns";
    protected $btInterfaceWidth = "350";
    protected $btInterfaceHeight = "240";
    protected $btDefaultSet = 'think_story';

    public function getBlockTypeName()
    {
        return t('TS Column Adder');
    }

    public function getBlockTypeDescription()
    {
        return t('Block that ');
    }

    public function validate($data){
        $e = Core::make('error');
        if (!$data['column_count']) {
            $e->add(t('You must specify a number of columns.'));
        } else if ($data['count'] < 0){
            $e->add(t('You must specify a positive number of columns.'));
        }
        return $e;
    }

    public function save($data)
    {
        $data['column_count'] = intval($data['column_count']);
        parent::save($data);
    }
}