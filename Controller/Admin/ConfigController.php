<?php
/**
 * This file is part of the ProductReview plugin.
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Controller\Admin;

use Plugin\ProductReview\Entity\ProductReviewConfig;
use Plugin\ProductReview\Form\Type\Admin\ProductReviewConfigType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ConfigController.
 */
class ConfigController extends AbstractController
{
    /**
     * @param Request $request
     * @param ProductReviewConfig $config
     * @return Response
     *
     * @Route("/plugin/product/review/config")
     */
    public function index(Request $request, ProductReviewConfig $Config)
    {
        $form = $this->createForm(ProductReviewConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($Config);
            $em->flush($Config);

            // todo logの仕様検討
            //log_info('Product review config', array('status' => 'Success'));
            // todo flash messageの仕様検討
            //$app->addSuccess('plugin.admin.product_review_config.save.complete', 'admin');
        }

        // todo @Template使うかどうか検討
        // todo namespaceどうするか検討
        return $this->render('ProductReview/Resource/template/admin/config.twig', [
            'form' => $form->createView(),
        ]);
    }
}
