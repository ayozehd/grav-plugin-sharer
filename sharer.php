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
            'onAssetsInitialized' => ['onAssetsInitialized', 0],
            'onTwigInitialized'   => ['onTwigInitialized', 0]
        ]);
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
        $buttons = $this->config->get('plugins.sharer.buttons');

        // Sorting buttons by priority value
        uasort($buttons, function($a, $b) {
			return $a['priority'] < $b['priority'] ? -1 : $a['priority'] == $b['priority'] ? 0 : -1;
        });

        $this->grav['twig']->twig_vars['sharer_buttons'] = $buttons;
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

        return $this->grav['twig']->processTemplate('partials/sharer.html.twig', [
            'page' => $this->grav['page']   // current page
        ]);

    }
}