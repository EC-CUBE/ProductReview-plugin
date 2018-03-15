<?php

namespace Plugin\ProductReview;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Application;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginManager extends AbstractPluginManager
{
    public function enable($meta = null, Application $app = null, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $ProductReviewConfig = $entityManager->find(ProductReviewConfig::class, 1);
        if (null === $ProductReviewConfig) {
            $ProductReviewConfig = new ProductReviewConfig();
            $ProductReviewConfig->setId(1);
            $ProductReviewConfig->setReviewMax(10);
            $entityManager->persist($ProductReviewConfig);
            $entityManager->flush();
        }
    }
}