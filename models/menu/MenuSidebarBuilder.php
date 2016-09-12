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

use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Matcher\Voter\UriVoter;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\TwigRenderer;
use Yii;
use yii\helpers\Url;

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
    /**
     * @var \Knp\Menu\MenuItem
     */
    private $rootMenu;

    public function __construct() {
        $this->factory = new MenuFactory();
        $this->rootMenu = $this->factory->createItem("sidebar", [
            "childrenAttributes" => [
                "class" => "nav metismenu",
            ],
        ]);
        $this->rootMenu->setAttribute("no_ul", true);
    }

    public function addItem($child, array $options = array()) {
        return $this->rootMenu->addChild($child, $options);
    }

    public function trans($id,$catalogue = "admin") {
        return Yii::t($catalogue,$id);
    }
    
    public function render() {
        $matcher = new Matcher();
        $voter = new UriVoter(Yii::$app->getRequest()->url);
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
        $rootMenu = $this->rootMenu;
        $menuDashoard = $rootMenu->addChild($this->trans('Dashboard'), array('uri' => Url::to(['admin/index'])))->setAttribute("icon", "fa fa-th-large");
        $menuDashoard->setAttribute("labelCatalogue","admin");
        
        $menus = [];
        
        $menuGroups = [];
        $menuGroupsDefinition = Yii::$app->controller->module->sidebar->groups;
        foreach(Yii::$app->controller->module->sidebar->items as $i => $menuItem){
            $icon = $menuItem->icon;
            if($menuItem->group === null){
                $menu = $this->rootMenu->addChild($this->trans($menuItem->label),[
                   "uri" => Url::to($menuItem->url),
                ]); 
            }else{
                if(!isset($menuGroups[$menuItem->group])){
                    $menuGroupParameters = [];
                    if(isset($menuGroupsDefinition[$menuItem->group])){
                        $menuGroupParameters = $menuGroupsDefinition[$menuItem->group];
                    }
                    $subMenu = $rootMenu->addChild($menuItem->group, array('uri' => '#',
                    "childrenAttributes" => [
                        "class" => "nav nav-second-level",
                    ],));
                    if(isset($menuGroupParameters["icon"])){
                        $subMenu->setAttribute("icon", $menuGroupParameters["icon"]);
                    }
                    if(isset($menuGroupParameters["tag"])){
                        $subMenu->setAttribute("tag", $menuGroupParameters["tag"]);
                    }
                    $subMenu->setAttribute("labelCatalogue", $menuItem->labelCatalogue);
                    $menuGroups[$subMenu->getName()] = $subMenu;
                }
                $subMenu = $menuGroups[$menuItem->group];
                $menu = $subMenu->addChild($this->trans($menuItem->label),[
                    "uri" => Url::to($menuItem->url),
                ]);
            }
            if($icon !== null){
                $menu->setAttribute("icon", $icon);
            }
            if($menuItem->tag !== null){
                $menu->setAttribute("tag", $menuItem->tag);
            }
            $menu->setAttribute("labelCatalogue", $menuItem->labelCatalogue);
        }
        
       /** 
        $rootMenu->addChild('Comments', array('uri' => '#comments'));
        $rootMenu->addChild('Symfony2', array('uri' => 'http://symfony-reloaded.org/'));
        $subMenu = $rootMenu->addChild('sub Comments', array('uri' => '#comments',
            "childrenAttributes" => [
                "class" => "nav nav-second-level",
            ],));
        $subMenu->addChild('Symfony2', array('uri' => 'http://symfony-reloaded.org/'));
        */
        $renderer = new TwigRenderer($environment, $template, $matcher, [
            "currentClass" => "active"
        ]);
//        var_dump(file_exists("/Users/inhack20/www/freelance/btobrewards/vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views/knp_menu.html.twig"));
//        die;
        return $renderer->render($this->rootMenu);
    }
}
