<?php

/** generated from /home/tac/g/survos/survos/vendor/survos/maker-bundle/templates/skeleton/bundle/src/Bundle.tpl.php */

namespace Survos\BingNewsBundle;

use Survos\BingNewsBundle\Command\BingNewsListCommand;
use Survos\BingNewsBundle\Controller\BingNewsController;
use Survos\BingNewsBundle\Form\SearchFormType;
use Survos\BingNewsBundle\Service\BingNewsService;
use Survos\BingNewsBundle\Twig\TwigExtension;
use Survos\SimpleDatatables\SurvosSimpleDatatablesBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SurvosBingNewsBundle extends AbstractBundle
{

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // get all bundles https://symfony.com/doc/current/bundles/prepend_extension.html
        $bundles = $builder->getParameter('kernel.bundles');
        $hasSimpleDatatables = in_array(SurvosSimpleDatatablesBundle::class, array_values($bundles));

        $serviceId = 'survos_bing-news.bing-news_service';
        $container->services()->alias(BingNewsService::class, $serviceId);
        $builder->autowire($serviceId, BingNewsService::class)
            ->setArgument('$apiKey', $config['api_key'])
            ->setArgument('$endpoint', $config['endpoint'])
            ->setArgument('$cache', new Reference('cache.app'))
            ->setArgument('$cacheTimeout', $config['cache_timeout'])
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setPublic(true);

        $builder->autowire(SearchFormType::class)
            ->setPublic(true)
            ->setAutowired(true);

        $builder->autowire(BingNewsController::class)
            ->setArgument('$simpleDatatablesInstalled', $hasSimpleDatatables)
            ->addTag('container.service_subscriber')
            ->addTag('controller.service_arguments')
        ;

        foreach ([BingNewsListCommand::class] as $commandName) {
            $builder->autowire($commandName)
                ->setAutoconfigured(true)
                ->addTag('console.command')
            ;
        }
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode();
        $rootNode
            ->children()
                ->scalarNode('api_key')->defaultNull()->end()
                ->scalarNode('endpoint')->defaultValue('https://api.bing.microsoft.com/')->end()
                ->integerNode('cache_timeout')->defaultValue(3600)->end()
//                ->scalarNode('region')->defaultValue(null)->end()
            ->end();

    }

}
