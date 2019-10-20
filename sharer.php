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

    public function onAssetsInitialized()
    {
        $config = $this->config->get('plugins.sharer');

        if ($config['built_in_css']) {
            $this->grav['assets']->addCss('plugin://sharer/assets/css-compiled/sharer.css');
        }

        if($config['fontawesome_icons'] && $config['fontawesome_css']) {
            if($config['fontawesome_v4']) {
                $this->grav['assets']->addCss('plugin://sharer/assets/css/font-awesome-4.7.0.min.css', 99);
            } else {
                $this->grav['assets']->addCss('plugin://sharer/assets/css/fontawesome-5.11.2.min.css', 99);
                $this->grav['assets']->addCss('plugin://sharer/assets/css/all.min.css', 99);
            }
        }

        $this->grav['assets']->addJs('plugin://sharer/assets/js/sharer.min.js', 100);
    }

    /**
     * Add simple `sharer()` Twig function and `|sharer_sort_buttons` filter
     */
    public function onTwigInitialized()
    {
        $this->grav['twig']->twig()->addFunction(
            new \Twig_SimpleFunction('sharer', [$this, 'renderTemplate'])
        );

        $this->grav['twig']->twig()->addFilter(
            new \Twig_SimpleFilter('sharer_sort_buttons', [$this, 'sortButtonsByPriority'])
        );
    }

    public function renderTemplate() {

        return $this->grav['twig']->processTemplate('partials/sharer.html.twig', [
            'page' => $this->grav['page']
        ]);

    }

    // Sort buttons by priority parameter
    public function sortButtonsByPriority($items = [])
    {
        uasort($items, function($a, $b) {
			return $a['priority'] <=> $b['priority'];
        });

        return $items;
    }
}