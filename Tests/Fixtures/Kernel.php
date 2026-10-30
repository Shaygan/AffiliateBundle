<?php

namespace Shaygan\AffiliateBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;
use Shaygan\AffiliateBundle\ShayganAffiliateBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SecurityBundle();
        yield new TwigBundle();
        yield new DoctrineBundle();
        yield new KnpPaginatorBundle();
        yield new ShayganAffiliateBundle();
    }

    public function getProjectDir(): string { return dirname(__DIR__, 2); }
    public function getCacheDir(): string { return sys_get_temp_dir().'/affiliate-tests/'.md5($this->getProjectDir()).'/cache/'.$this->environment; }
    public function getLogDir(): string { return sys_get_temp_dir().'/affiliate-tests/'.md5($this->getProjectDir()).'/log'; }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'affiliate-test-secret', 'test' => true,
            'http_method_override' => false, 'handle_all_throwables' => true, 'php_errors' => ['log' => true],
            'router' => ['utf8' => true],
            'session' => ['storage_factory_id' => 'session.storage.factory.mock_file', 'cookie_secure' => 'auto', 'cookie_samesite' => 'lax', 'handler_id' => null],
        ]);
        $container->extension('security', [
            'providers' => ['fixture' => ['id' => UserProvider::class]],
            'firewalls' => ['main' => ['provider' => 'fixture', 'http_basic' => []]],
        ]);
        $container->services()->set(UserProvider::class);
        $container->services()->set('logger', \Psr\Log\NullLogger::class);
        $container->extension('twig', ['strict_variables' => true]);
        $container->extension('doctrine', [
            'dbal' => ['url' => getenv('DATABASE_URL') ?: 'sqlite:///%kernel.cache_dir%/database.sqlite'],
            'orm' => ['auto_generate_proxy_classes' => true, 'mappings' => [
                'Affiliate' => ['type' => 'attribute', 'dir' => $this->getProjectDir().'/Entity', 'prefix' => 'Shaygan\\AffiliateBundle\\Entity', 'is_bundle' => false],
            ]],
        ]);
        $container->extension('shaygan_affiliate', ['programs' => ['default' => [
            'type' => 'percentage', 'first_commission_percent' => 15, 'commission_percent' => 10, 'max_count' => 2,
        ]]]);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getProjectDir().'/Resources/config/routing.yml');
    }
}
