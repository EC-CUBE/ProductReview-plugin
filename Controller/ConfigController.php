<?php

/*
 * This file is part of the FreeDelivery
 *
 * Copyright (C) 2016 lammn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class ConfigController
{

    /**
     * FreeDelivery用設定画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $ProductReviewPlugin = $app['eccube.plugin.product_review.repository.product_review_plugin']->find(1);
        $form = $app['form.factory']->createBuilder('review_config')->getForm();
        $form->setData($ProductReviewPlugin);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $ProductReviewPlugin->setCssSelector($data['css_selector']);
                $app['orm.em']->persist($ProductReviewPlugin);
                $app['orm.em']->flush($ProductReviewPlugin);
                $app->addSuccess('admin.product_review.save.complete', 'admin');
            }
        }

        return $app->render('ProductReview/Resource/template/admin/config.twig', array(
            'form' => $form->createView(),
        ));
    }

}
