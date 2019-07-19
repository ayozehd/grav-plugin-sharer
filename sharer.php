<?php
namespace Grav\Plugin;
use Grav\Common\Plugin;
class SharerPlugin extends Plugin
{
    protected $sharer_cache_id;

    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }

        $this->initSetup();

        $this->enable([
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            'onAssetsInitialized' => ['onAssetsInitialized', 0],
            'onTwigInitialized'   => ['onTwigInitialized', 0]
        ]);
    }

    private function initSetup()
    {
        $this->sharer_cache_id = md5('sharer-plugin'.$this->grav['cache']->getKey());
    }

    /**
     * Add plugin templates.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Add CSS and JS to page header
     */
    public function onTwigSiteVariables()
    {
        $config = $this->config->get('plugins.lastfm');

        $cache = $this->grav['cache'];

        $data = $cache->fetch($this->sharer_cache_id);

        if(!$data) {

            $this->grav['debugger']->addMessage('sharer cache miss.');

            try {
                
                $data = $this->config->get('plugins.sharer.buttons');

            } catch(Exception $e) {

                $this->grav['log']->error('plugin.sharer: '. $e->getMessage());
            }

            $cache->save($this->sharer_cache_id, $data);

        } else {
            $this->grav['debugger']->addMessage('sharer cache hit.');
        }

        $twig = $this->grav['twig'];
        $twig->twig_vars['sharer_buttons'] = $data;
    }

    public function onAssetsInitialized()
    {
        $config = $this->config->get('plugins.sharer');
        if ($config['built_in_css']) {
            $this->grav['assets']->addCss('plugin://sharer/assets/css-compiled/sharer.css');
        }

        if($config['fontawesome_icons'] && $config['fontawesome_css']) {
            if($config['fontawesome_v4']) {
                $this->grav['assets']->addCss('plugin://sharer/assets/css/font-awesome-4.7.0.min.css');
            } else {
                $this->grav['assets']->addJs('plugin://sharer/assets/js/fontawesome-all.min.js');
            }
        }

        $this->grav['assets']->addJs('plugin://sharer/assets/js/sharer.min.js', 110);
    }

    /**
     * Add simple `sharer()` Twig function
     */
    public function onTwigInitialized()
    {
        $this->grav['twig']->twig()->addFunction(
            new \Twig_SimpleFunction('sharer', [$this, 'renderTemplate'])
        );
    }

    public function renderTemplate() {
        $page = $this->grav['page'];
        return $this->grav['twig']->processTemplate('partials/sharer.html.twig', [
            'page' => $page
        ]);
    }
}