<?php

namespace Shaygan\AffiliateBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 *
 * @author Iman Ghasrfakhri <ghasrfakhri@gmail.com>
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('shaygan_affiliate');

        $rootNode
                ->children()
                /**/->scalarNode("referrer_param_name")->defaultValue("ref")->end()
                /**/->scalarNode("session_referral_id_param_name")->defaultValue("pca_affiliate")->end()
                /**/->scalarNode("cookie_referral_id_param_name")->defaultValue("pca_affiliate")->end()
                /**/->scalarNode("cookie_expire_in")->defaultValue("2592000")->end()
                /**/->scalarNode("cookie_path")->defaultValue("/")->end()
                /**/->scalarNode("cookie_secure")->defaultFalse()->end()
                /**/->scalarNode("cookie_httponly")->defaultFalse()->end()
                /**/->arrayNode("purchase")
                /*  */->children()
                /*    */->enumNode("type")
                /*      */->values(array('fixed', 'persent'))->isRequired()
                /*    */->end()
                /*    */->integerNode("amount")->end()
                /*    */->integerNode("persent")->min(0)->max(100)->defaultValue(30)->end()
                /*    */->integerNode("max_count")->min(1)->defaultValue(1)->end()
                /*  */->end()
                /**/->end()
//                /**/->arrayNode("diposit")
//                /*  */->children()
//                /*    */->enumNode("type")
//                /*      */->values(array('fixed', 'persent'))
//                /*    */->end()
//                /*    */->integerNode("amount")->end()
//                /*    */->integerNode("persent")->min(0)->max(100)->end()
//                /*    */->integerNode("max_count")->min(1)->defaultValue(1)->end()
//                /*  */->end()
//                /**/->end()
                ->end()

        ;
        return $treeBuilder;
    }

}
