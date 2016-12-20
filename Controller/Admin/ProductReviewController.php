<?php
/**
 * This file is part of the ProductReview plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\ProductReview\Controller\Admin;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Plugin\ProductReview\Entity\ProductReview;
use Plugin\ProductReview\Repository\ProductReviewRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductReviewController admin.
 */
class ProductReviewController extends AbstractController
{
    /**
     * Search function.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $page_no
     * @return Response
     */
    public function index(Application $app, Request $request, $page_no = null)
    {
        $pageNo = $page_no;
        $session = $app['session'];

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();
        $pageCount = $app['config']['default_page_count'];
        $pagination = null;
        $searchForm = $app['form.factory']
            ->createBuilder('admin_product_review_search')
            ->getForm();
        /* @var $searchForm FormInterface */
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchData = $searchForm->getData();
            $qb = $app['product_review.repository.product_review']
                ->getQueryBuilderBySearchData($searchData);

            $pageNo = 1;
            $pagination = $app['paginator']()->paginate(
                $qb,
                $pageNo,
                $pageCount,
                array('wrap-queries' => true)
            );

            // sessionのデータ保持
            $session->set('plugin.product_review.admin.product_review.search', $searchData);
        } else {
            if (is_null($pageNo)) {
                // sessionを削除
                $session->remove('plugin.product_review.admin.product_review.search');
                $searchData = array();
            } else {
                // pagingなどの処理
                $searchData = $session->get('plugin.product_review.admin.product_review.search');
                if (!is_null($searchData)) {
                    $qb = $app['product_review.repository.product_review']
                        ->getQueryBuilderBySearchData($searchData);

                    // 表示件数
                    $pcount = $request->get('page_count');

                    $pageCount = empty($pcount) ? $pageCount : $pcount;

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $pageNo,
                        $pageCount,
                        array('wrap-queries' => true)
                    );
                }
            }
            $searchForm->setData($searchData);
        }

        return $app->render('ProductReview/Resource/template/admin/index.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_count' => $pageCount,
        ));
    }

    /**
     * 編集.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $id
     * @return RedirectResponse|Response
     */
    public function edit(Application $app, Request $request, $id)
    {
        // IDから商品レビューを取得する
        /** @var $ProductReview ProductReview */
        $ProductReview = $app['product_review.repository.product_review']
            ->find($id);

        if (!$ProductReview) {
            $app->addError('plugin.admin.product_review.not_found', 'admin');

            return $app->redirect($app->url('plugin_admin_product_review'));
        }

        $Product = $ProductReview->getProduct();
        if (!$Product) {
            $app->addError('admin.product.not_found', 'admin');

            return $app->redirect($app->url('plugin_admin_product_review'));
        }

        // formの作成
        /* @var $form FormInterface */
        $form = $app['form.factory']
            ->createBuilder('admin_product_review', $ProductReview)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $app['product_review.repository.product_review']
                ->save($ProductReview);
            if (!$status) {
                $app->addError('plugin.admin.product_review.save.error', 'admin');
            } else {
                $app->addSuccess('plugin.admin.product_review.save.complete', 'admin');
            }

            return $app->redirect($app->url('plugin_admin_product_review'));
        }

        return $app->render('ProductReview/Resource/template/admin/edit.twig', array(
            'form' => $form->createView(),
            'Product' => $Product,
        ));
    }

    /**
     * Product review delete function.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $id
     * @return RedirectResponse
     */
    public function delete(Application $app, Request $request, $id = null)
    {
        $this->isTokenValid($app);

        if ($id) {
            /* @var $repos ProductReviewRepository */
            $repos = $app['product_review.repository.product_review'];

            $TargetProductReview = $repos->find($id);

            if (!$TargetProductReview) {
                throw new NotFoundHttpException();
            }

            $status = $repos->delete($TargetProductReview);

            if ($status === true) {
                $app->addSuccess('plugin.admin.product_review.delete.complete', 'admin');
            } else {
                $app->addError('plugin.admin.product_review.delete.error', 'admin');
            }
        } else {
            $app->addError('plugin.admin.product_review.delete.error', 'admin');
        }

        return $app->redirect($app->url('plugin_admin_product_review'));
    }
}
