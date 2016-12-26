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

use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ConfigController.
 */
class ConfigController extends AbstractController
{
    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function index(Application $app, Request $request)
    {
        $config = $app['product_review.repository.product_review_config']->find(1);
        if (!$config) {
            throw new NotFoundHttpException();
        }

        /* @var $form FormInterface */
        $form = $app['form.factory']
            ->createBuilder('admin_product_review_config', $config)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /* @var $em EntityManager */
                $em = $app['orm.em'];
                $em->persist($config);
                $em->flush($config);

                log_info('Product review config', array('status' => 'Success'));

                $app->addSuccess('plugin.admin.product_review_config.save.complete', 'admin');
            } catch (\Exception $e) {
                log_info('Product review config', array('status' => $e->getMessage()));

                $app->addError('plugin.admin.product_review_config.save.error', 'admin');
            }
        }

        return $app->render('ProductReview/Resource/template/admin/config.twig', array('form' => $form->createView()));
    }
}
