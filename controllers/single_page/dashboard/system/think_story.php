<?php
namespace Concrete\Package\ThinkStory\Controller\SinglePage\Dashboard\System;

use Concrete\Core\Page\Controller\DashboardPageController;

//TODO fix, doesn't redirect
//NOTE: this is only to redirect from the thinkstory main menu
class ThinkStory extends DashboardPageController    //Not DashboardSitePageController for some reason, look at the migration tool
{
    public function view()
    {
        $this->redirect('/dashboard/system/think_story/page_report');
    }
}