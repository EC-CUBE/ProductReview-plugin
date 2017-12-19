<?php

use Plugin\ProductReview\Form\Type\Admin\ProductReviewConfigType;

// block_templatesのパスを追加する.
$templates = (array)$container->getParameter('eccube.twig.block.templates');
$templates[] = 'ProductReview/Resource/template/default/hello.twig';

$container->setParameter('eccube.twig.block.templates', $templates);

// サービス定義を追加する
$container
    ->register(ProductReviewConfigType::class)
    ->setBindings([
        '$min' => 1,
        '$max' => 30,
    ]);
