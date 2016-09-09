<?php

/*
 * This file is part of the BtoB4Rewards package.
 * 
 * (c) www.btob4rewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace asdfstudio\admin\models\menu;

use Yii;

/**
 * Menu principal izquierdo (admin.builder.menu_sidebar)
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class MenuSidebarBuilder {

    /**
     *
     * @var \Knp\Menu\MenuFactory
     */
    private $factory;
    private $menu;

    public function __construct() {
        $this->factory = new \Knp\Menu\MenuFactory();
        $this->menu = $this->factory->createItem("sidebar", [
            "childrenAttributes" => [
                "class" => "nav metismenu",
            ],
        ]);
        $this->menu->setAttribute("no_ul", true);
    }

    public function addItem($child, array $options = array()) {
        $this->menu->addChild($child, $options);
    }

    public function render() {
        $matcher = new \Knp\Menu\Matcher\Matcher();
        $voter = new \Knp\Menu\Matcher\Voter\UriVoter(Yii::$app->getRequest()->url);
        $matcher->addVoter($voter);
        $environment = Yii::$container->get("twig");
        $template = "menu_sidebar.twig";
        $pathTemplate = Yii::getAlias("@admin-module/views/layouts");
        if (!$environment->getLoader()->exists($template)) {
            $environment->getLoader()->addPath($pathTemplate);
        }
        if (!$environment->getLoader()->exists("knp_menu_base.html.twig")) {
            $environment->getLoader()->addPath(Yii::getAlias("@vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views/"));
        }
        $menu = $this->menu;
        $menu->addChild('Home', array('uri' => '/admin/admin/index'))->setAttribute("icon", "fa fa-th-large");
        $menu->addChild('Comments', array('uri' => '#comments'));
        $menu->addChild('Symfony2', array('uri' => 'http://symfony-reloaded.org/'));
        $subMenu = $menu->addChild('sub Comments', array('uri' => '#comments',
            "childrenAttributes" => [
                "class" => "nav nav-second-level",
            ],));
        $subMenu->addChild('Symfony2', array('uri' => 'http://symfony-reloaded.org/'));

        $renderer = new \Knp\Menu\Renderer\TwigRenderer($environment, $template, $matcher, [
            "currentClass" => "active"
        ]);
//        var_dump(file_exists("/Users/inhack20/www/freelance/btobrewards/vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views/knp_menu.html.twig"));
//        die;
        return $renderer->render($this->menu);
    }

}
