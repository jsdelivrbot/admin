<?php

namespace AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AdminExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        //admin menu
        $configs = $this->addMenuItemsByBundles($container, $config);
        $container->setParameter('core.admin_menus', $configs);
        
        //google analytics
        if (isset($config['apis']['google_analytics']['options']['application_name'])) {
            $container->setParameter('google_analytics.application_name', $config['apis']['google_analytics']['options']['application_name']);
        }
        if (isset($config['apis']['google_analytics']['options']['oauth2_client_id'])) {
            $container->setParameter('google_analytics.oauth2_client_id', $config['apis']['google_analytics']['options']['oauth2_client_id']);
        }
        if (isset($config['apis']['google_analytics']['options']['oauth2_client_secret'])) {
            $container->setParameter('google_analytics.oauth2_client_secret', $config['apis']['google_analytics']['options']['oauth2_client_secret']);
        }
        if (isset($config['apis']['google_analytics']['options']['oauth2_redirect_uri'])) {
            $container->setParameter('google_analytics.oauth2_redirect_uri', $config['apis']['google_analytics']['options']['oauth2_redirect_uri']);
        }
        if (isset($config['apis']['google_analytics']['options']['developer_key'])) {
            $container->setParameter('google_analytics.developer_key', $config['apis']['google_analytics']['options']['developer_key']);
        }
        if (isset($config['apis']['google_analytics']['options']['site_name'])) {
            $container->setParameter('google_analytics.site_name', $config['apis']['google_analytics']['options']['site_name']);
        }
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
    }

        
    private function addMenuItemsByBundles($container, $config)
    {
        $bundles = $container->getParameter('kernel.bundles');
        
        if (isset($bundles['CoreBundle'])) {
            $config = $this->arraymap(array(
                'admin_menus' => array(
                    'dashboard' => array(
                        'icon_class' => 'fa fa-dashboard',
                        'label' => 'dashboard',
                        'options' => array(
                            'menuitems' => 'core_menuitem_index',
                            'sliders' => 'core_slider_index',
                        )
                     ),
                    'user' => array(
                        'icon_class' => 'fa fa-users',
                        'label' => 'actor.plural',
                        'options' => array(
                            'actors' => 'core_actor_index',
                            'roles' => 'core_role_index',
                            )
                    ),
                    'design.font' => array(
                        'icon_class' => 'fa fa-paint-brush',
                        'label' => 'design.font',
                        'options' => array(
                            'fontadds' => 'core_font_index',
                            'fontless' => 'core_font_less',    
                        )
                     ),
                    'marketing' => array(
                        'icon_class' => 'fa fa-line-chart',
                        'label' => 'marketing',
                        'options' => array(
                            'reputation.onlie' => 'core_visit_index',        
                            'newsletter' => array(
                                'icon_class' => 'fa fa-envelope-o',
                                'label' => 'newsletter.plural',
                                'options' => array(
                                    'subscriptions' => 'core_newsletter_subscription',
                                    'newsletters' => 'core_newsletter_index',
                                    'shippings' => 'core_newsletter_shipping',
                                ),
                            ),
                        ),
                    ),
                ),
            ),$config);
        }
        if (isset($bundles['AdminBundle'])) {
            $config = $this->arraymap(array(
                'admin_menus' => array(
                    'dashboard' => array(
                        'options' => array(
                            'analytics' => 'admin_default_analytics'
                            )
                        )
                    )
                ),$config);
        }
        if (isset($bundles['BlogBundle'])) {
            $config = $this->arraymap(array(
                'admin_menus' => array(
                    'blog' => array(
                        'icon_class' => 'fa fa-pencil-square-o',
                        'label' => 'blog.singular',
                        'options' => array(
                            'posts' => 'blog_post_index',
                            'postcategories' => 'blog_category_index',
                            'posttags' => 'blog_tag_index',
                            'postcomments' => 'blog_comment_index'
                        )
                    )
                )
            ),$config);
        }
        if (isset($bundles['EcommerceBundle'])) {
            $config = $this->arraymap(array(
                'admin_menus' => array(
                    'ecommerce' => array(
                        'icon_class' => 'fa fa-shopping-cart ',
                        'label' => 'ecommerce',
                        'options' => array(
                            'catalogue' => array(
                                'options' => array(
                                    'products' => 'ecommerce_product_index',
                                    'categories' => 'ecommerce_category_index',
                                    'features' => 'ecommerce_feature_index',
                                    'attributes' => 'ecommerce_attribute_index',
                                    'brands' => 'ecommerce_brand_index',
                                    'models' => 'ecommerce_brandmodel_index',
                                )
                            ),
                            'sales' => array(
                                'options' => array(
                                    'transactions' => 'ecommerce_transaction_index',
                                    'invoices' => 'ecommerce_invoice_index',
                                    'taxes' => 'ecommerce_tax_index',
                                    'paymentserviceproviders' => 'ecommerce_paymentserviceprovider_index',
                                )
                            ),
                            'recurrings' => array(
                                'options' => array(
                                    'contracts' => 'ecommerce_contract_index',
                                    'plans' => 'ecommerce_plan_index',
                                )
                            )
                        )
                    ),
                    'marketing' => array(
                        'options' => array(
                            'advert' => array(
                                'icon_class' => 'fa fa-picture-o',
                                'label' => 'advert.plural',
                                'options' => array(
                                    'adverts' => 'ecommerce_advert_index',
                                    'advertslocated' => 'ecommerce_located_index',
                                )
                            )
                        )
                    ),
                )),$config);
        }
        if (isset($bundles['ElearningBundle'])) {
            $config = $this->arraymap(array(
                'admin_menus' => array(
                    'elearning' => array(
                        'icon_class' => 'fa fa-graduation-cap',
                        'label' => 'elearning',
                        'options' => array(
                            'courses' => 'elearning_course_index',
                            'class' => 'elearning_classes_index',
                            'tests' => 'elearning_test_index',
                            )
                        )
                    )
                ),$config);
        }
        return $config;
    }

    private function arraymap($arr1, $arr2)
    {
        foreach ($arr1 as $key => $menu_item) {
            if(!array_key_exists($key, $arr2)){
                $arr2[$key] = $menu_item;
            }else{
                foreach ($menu_item as $key2 => $menu_item2) {
                    if(!array_key_exists($key2, $arr2[$key])){
                        $arr2[$key][$key2] = $menu_item2;
                    }else{
                        foreach ($menu_item2 as $key3 => $menu_item3) {
                            if(is_array($arr2[$key][$key2][$key3])){
                                $customOptions = $arr2[$key][$key2][$key3];
                                //if we need remove items put values as null
                                $arr2[$key][$key2][$key3] = $this->cleanOptionsNull($customOptions, $menu_item3);
                            }
                        }
                    }
                }
            }
        }
        return $arr2;
    }
    
    private function cleanOptionsNull($customOptions, $oldOptions)
    {
        foreach ($customOptions as $key => $value) {
            if(isset($oldOptions[$key])){
                if(count($value['options'])==0){
                    unset($customOptions[$key]);
                    unset($oldOptions[$key]);
                }
            }
        }
        $merge = array_merge($customOptions, $oldOptions);
        return $merge;
    }
    
}
