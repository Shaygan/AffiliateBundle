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
        $treeBuilder = new TreeBuilder('shaygan_affiliate');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
                ->children()
                /**/->scalarNode("referrer_param_name")->defaultValue("ref")->end()
                /**/->arrayNode("referrer_alternative_param_names")
                /*  */->prototype('scalar')->end()->end()
                /**/->scalarNode("session_referral_id_param_name")->defaultValue("pca_affiliate")->end()
                /**/->scalarNode("cookie_referral_id_param_name")->defaultValue("pca_affiliate")->end()
                /**/->scalarNode("cookie_expire_in")->defaultValue("2592000")->end()
                /**/->scalarNode("cookie_path")->defaultValue("/")->end()
                /**/->scalarNode("cookie_secure")->defaultFalse()->end()
                /**/->scalarNode("cookie_httponly")->defaultFalse()->end()
                /**/->arrayNode("programs")->isRequired()->requiresAtLeastOneElement()
                /*  */->useAttributeAsKey('name')
                /*  */->prototype('array')
                /*  */->children()
                /*    */->enumNode("type")
                /*      */->values(array('percentage', 'fixed-amount'))->isRequired()
                /*    */->end()
                /*    */->floatNode("commission_amount")->defaultValue(1)->end()
                /*    */->floatNode("first_commission_amount")->defaultValue(1)->end()
                /*    */->integerNode("commission_percent")->min(0)->max(100)->defaultValue(15)->end()
                /*    */->integerNode("first_commission_percent")->min(0)->max(100)->defaultValue(10)->end()
                /*    */->integerNode("max_count")->min(1)->defaultValue(1)->end()
                /*  */->end()
                /**/->end()
                ->end()

        ;
        return $treeBuilder;
    }

}
