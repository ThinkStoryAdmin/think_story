<?php
namespace Concrete\Package\ThinkStory\Help;
use Concrete\Core\Foundation\Service\Provider;

class HelpServiceProvider extends Provider
{
    public function register()
    {
        /*$this->app['help/block_type']->registerMessageString('plain_text_box', 'Plain Text Box Help');
        $this->app['help/block_type']->registerMessageString('dummy_block', 'Other help text.');
        $this->app['help/dashboard']->registerMessageString('/dashboard/acme\_widgets/add',
            t('Add a Widget.')
        );*/
        $this->app['help/single_page']->registerMessageString('/dashboard/system/think_story/add_translation_text', 'Help message');
    }

    public function on_start()
    {
        $app = Core::make('app');
        $provider = new \Concrete\Package\Calendar\Help\HelpServiceProvider($app);
        $provider->register();
    }
}