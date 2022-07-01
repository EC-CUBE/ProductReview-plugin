<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview42\Controller\Admin;

use Plugin\ProductReview42\Form\Type\Admin\ProductReviewConfigType;
use Plugin\ProductReview42\Repository\ProductReviewConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConfigController.
 */
class ConfigController extends \Eccube\Controller\AbstractController
{
    /**
     * @Route("/%eccube_admin_route%/product_review/config", name="product_review42_admin_config")
     * @Template("@ProductReview42/admin/config.twig")
     *
     * @param Request $request
     * @param ProductReviewConfigRepository $configRepository
     *
     * @return array
     */
    public function index(Request $request, ProductReviewConfigRepository $configRepository)
    {
        $Config = $configRepository->get();
        $form = $this->createForm(ProductReviewConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush($Config);

            log_info('Product review config', ['status' => 'Success']);
            $this->addSuccess('product_review.admin.save.complete', 'admin');

            return $this->redirectToRoute('product_review42_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
