<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Controller\Admin;

use Plugin\ProductReview\Form\Type\Admin\ProductReviewConfigType;
use Plugin\ProductReview\Repository\ProductReviewConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ConfigController.
 */
class ConfigController extends \Eccube\Controller\AbstractController
{
    /**
     * @Route("/%eccube_admin_route%/plugin/product/review/config", name="plugin_ProductReview_config")
     * @Template("ProductReview/Resource/template/admin/config.twig")
     *
     * @param Request $request
     * @param ProductReviewConfigRepository $configRepository
     *
     * @return array
     */
    public function index(Request $request, ProductReviewConfigRepository $configRepository)
    {
        $Config = $configRepository->find(1);
        $form = $this->createForm(ProductReviewConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush($Config);

            log_info('Product review config', ['status' => 'Success']);
            $this->addSuccess('plugin.product_review.admin_config.save.complete', 'admin');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
