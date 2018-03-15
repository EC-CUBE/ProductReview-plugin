<?php

// block_templatesのパスを追加する.
$templates = (array)$container->getParameter('eccube_twig_block_templates');
$templates[] = 'ProductReview/Resource/template/default/review.twig';
$container->setParameter('eccube_twig_block_templates', $templates);

// navを追加する
$nav = (array)$container->getParameter('eccube_nav');
foreach ($nav as $key => $value) {
    $add = [
        'id' => 'product_review',
        'name' => 'レビュー管理',
        'url' => 'plugin_admin_product_review',
    ];
}