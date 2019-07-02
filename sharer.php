<?php
namespace Grav\Plugin;
use Grav\Common\Plugin;
class SharerPlugin extends Plugin
{
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
        $this->enable([
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            'onTwigInitialized'   => ['onTwigInitialized', 0]
        ]);
    }
    /**
     * Add current directory to twig lookup paths.
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
        $config = $this->config->get('plugins.sharer');
        if ($config['built_in_css']) {
            $this->grav['assets']->addCss('plugin://sharer/css-compiled/sharer.css', -999);
        }

        $this->grav['assets']->addJs('plugin://sharer/js/sharer.min.js', -999);

        if($config['fontawesome_icons'] && $config['fontawesome_css']) {
            if($config['fontawesome_v4']) {
                $this->grav['assets']->addCss('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', -999);
            } else {
                $this->grav['assets']->addJs('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/js/all.min.js', -999);
            }
        }

        $twig = $this->grav['twig'];
        $twig->twig_vars['sharer_buttons'] = $config['buttons'];
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