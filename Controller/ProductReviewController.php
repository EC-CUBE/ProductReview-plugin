<?php
/**
 * This file is part of the ProductReview plugin.
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Controller;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\Disp;
use Eccube\Entity\Product;
use Plugin\ProductReview\Entity\ProductReview;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductReviewController front.
 */
class ProductReviewController extends AbstractController
{
    /**
     * @var null
     */
    private $title = null;

    /**
     * Review function.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $id
     *
     * @return RedirectResponse|Response
     */
    public function review(Application $app, Request $request, $id)
    {
        /* @var $Product Product */
        $Product = $app['eccube.repository.product']->get($id);
        if (!$Product) {
            throw new NotFoundHttpException();
        }
        if (!$request->getSession()->has('_security_admin') && $Product->getStatus()->getId() !== Disp::DISPLAY_SHOW) {
            log_info('Product review', array('status' => 'Not permission'));

            throw new NotFoundHttpException();
        }
        if (count($Product->getProductClasses()) < 1) {
            throw new NotFoundHttpException();
        }

        $ProductReview = new ProductReview();

        /* @var $builder FormBuilderInterface */
        $builder = $app['form.factory']
            ->createBuilder('product_review', $ProductReview);
        /* @var $form FormInterface */
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    log_info('Product review config');

                    $builder->setAttribute('freeze', true);
                    $form = $builder->getForm();
                    $form->handleRequest($request);

                    return $app['twig']->render('ProductReview/Resource/template/default/confirm.twig', array(
                        'form' => $form->createView(),
                        'Product' => $Product,
                    ));
                    break;

                case 'complete':
                    log_info('Product review complete');
                    /** @var $ProductReview ProductReview */
                    $ProductReview = $form->getData();
                    if ($app->isGranted('ROLE_USER')) {
                        $Customer = $app->user();
                        $ProductReview->setCustomer($Customer);
                    }
                    $ProductReview->setProduct($Product);
                    $Disp = $app['eccube.repository.master.disp']
                        ->find(Disp::DISPLAY_HIDE);

                    $ProductReview->setStatus($Disp);
                    $ProductReview->setDelFlg(Constant::DISABLED);
                    $status = $app['product_review.repository.product_review']
                        ->save($ProductReview);

                    if (!$status) {
                        $app->addError('plugin.front.product_review.system.error');
                        log_info('Product review complete', array('status' => 'fail'));

                        return $app->redirect($app->url('plugin_products_detail_review_error'));
                    } else {
                        log_info('Product review complete', array('id' => $Product->getId()));

                        return $app->redirect($app->url('plugin_products_detail_review_complete', array('id' => $Product->getId())));
                    }
                    break;

                case 'back':
                default:
                    // do nothing
                    break;
            }
        }

        return $app->render('ProductReview/Resource/template/default/index.twig', array(
            'title' => $this->title,
            'subtitle' => $Product->getName(),
            'form' => $form->createView(),
            'Product' => $Product,
        ));
    }

    /**
     * Complete.
     *
     * @param Application $app
     * @param int         $id
     *
     * @return mixed
     */
    public function complete(Application $app, $id)
    {
        return $app['view']->render('ProductReview/Resource/template/default/complete.twig', array('id' => $id));
    }

    /**
     * 購入エラー画面表示.
     *
     * @param Application $app
     *
     * @return Response
     */
    public function frontError(Application $app)
    {
        return $app->render('ProductReview/Resource/template/default/error.twig');
    }
}
