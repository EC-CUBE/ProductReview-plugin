<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Plugin\ProductReview\Controller;

use Plugin\ProductReview\Form\Type\ProductReviewSearchType;
use Plugin\ProductReview\Form\Type\ProductReviewType;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;

class ProductReviewController extends AbstractController
{
    private $title;

    public function __construct()
    {
        $this->title = '';
    }

    public function index(Application $app, Request $request, $page_no = null)
    {
        $session = $app['session'];

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();
        $page_count = $app['config']['default_page_count'];
        $pagination = null;
        $searchForm = $app['form.factory']
            ->createBuilder('admin_product_review_search')
            ->getForm();
        $searchForm->handleRequest($request);
        if ('POST' === $request->getMethod()) {
            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();
                $qb = $app['eccube.plugin.product_review.repository.product_review']
                    ->getQueryBuilderBySearchData($searchData);

                $page_no = 1;
                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count,
                    array('wrap-queries' => true)
                );

                // sessionのデータ保持
                $session->set('plugin.product_review.admin.product_review.search', $searchData);
            }
        } else {
            if (is_null($page_no)) {
                // sessionを削除
                $session->remove('plugin.product_review.admin.product_review.search');
                $searchData = array();
            } else {
                // pagingなどの処理
                $searchData = $session->get('plugin.product_review.admin.product_review.search');
                if (!is_null($searchData)) {
                    $qb = $app['eccube.plugin.product_review.repository.product_review']
                        ->getQueryBuilderBySearchData($searchData);

                    // 表示件数
                    $pcount = $request->get('page_count');

                    $page_count = empty($pcount) ? $page_count : $pcount;

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count,
                        array('wrap-queries' => true)
                    );
                }
            }
            $searchForm->setData($searchData);
        }

        return $app->render('ProductReview/Resource/template/admin//index.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_count' => $page_count,
        ));
    }

    /**
     * 編集
     * @param Application $app
     * @param Request $request
     * @param unknown $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Application $app, Request $request, $id)
    {
        // IDから商品レビューを取得する
        /** @var $ProductReview \Plugin\ProductReview\Entity\ProductReview */
        $ProductReview = $app['eccube.plugin.product_review.repository.product_review']
            ->findOneById($id);

        if (is_null($ProductReview)) {
            $app->addError('admin.product_review.notfound', 'admin');
            return $app->redirect($app->url('admin_product_review'));
        }

        // formの作成
        $form = $app['form.factory']
            ->createBuilder('admin_product_review', $ProductReview)
            ->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $status = $app['eccube.plugin.product_review.repository.product_review']
                    ->save($ProductReview);
                if (!$status) {
                    $app->addError('admin.product_review.save.error', 'admin');
                } else {
                    $app->addSuccess('admin.product_review.save.complete', 'admin');
                }

                return $app->redirect($app->url('admin_product_review'));
            }
        }

        return $app->render('ProductReview/Resource/template/admin//edit.twig', array(
            'form' => $form->createView(),
            'Product' => $ProductReview->getProduct(),
        ));
    }

    public function delete(Application $app, Request $request, $id = null)
    {
        $this->isTokenValid($app);

        if (!is_null($id)) {
            $repos = $app['eccube.plugin.product_review.repository.product_review'];

            $TargetProductReview = $repos->find($id);

            if (!$TargetProductReview) {
                throw new NotFoundHttpException();
            }

            $status = $repos->delete($TargetProductReview);

            if ($status === true) {
                $app->addSuccess('admin.product_review.delete.complete', 'admin');
            } else {
                $app->addError('admin.product_review.delete.error', 'admin');
            }
        } else {
            $app->addError('admin.product_review.delete.error', 'admin');
        }
        return $app->redirect($app->url('admin_product_review'));
    }

    public function review(Application $app, Request $request, $id)
    {
        /* @var $Product \Eccube\Entity\Product */
        $Product = $app['eccube.repository.product']->get($id);
        if (!$request->getSession()->has('_security_admin') && $Product->getStatus()->getId() !== 1) {
            throw new NotFoundHttpException();
        }
        if (count($Product->getProductClasses()) < 1) {
            throw new NotFoundHttpException();
        }

        $ProductReview = new \Plugin\ProductReview\Entity\ProductReview();

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']
            ->createBuilder('product_review', $ProductReview);
        /* @var $form \Symfony\Component\Form\FormInterface */
            $form = $builder->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                switch ($request->get('mode')) {
                    case 'confirm':
                        $builder->setAttribute('freeze', true);
                        $form = $builder->getForm();
                        $form->handleRequest($request);
                        return $app['twig']->render('ProductReview/Resource/template/default/confirm.twig', array(
                            'form' => $form->createView(),
                            'Product' => $Product
                        ));
                        break;

                    case 'complete':
                        /** @var $ProductReview \Plugin\ProductReview\Entity\ProductReview */
                        $ProductReview = $form->getData();
                        if ($app->isGranted('ROLE_USER')) {
                            $Customer = $app->user();
                            $ProductReview->setCustomer($Customer);
                        }
                        $ProductReview->setProduct($Product);
                        $Disp = $app['eccube.repository.master.disp']
                            ->find(\Eccube\Entity\Master\Disp::DISPLAY_HIDE);

                        $ProductReview->setStatus($Disp);
                        $ProductReview->setDelFlg(Constant::DISABLED);
                        $status = $app['eccube.plugin.product_review.repository.product_review']
                            ->save($ProductReview);

                        if (!$status) {
                            $app->addError('front.product_review.system.error');
                            return $app->redirect($app->url('products_detail_review_error'));
                        } else {
                            return $app->redirect($app->url('products_detail_review_complete', array('id' => $Product->getId())));
                        }
                        break;
                    case 'back':
                        // do nothing
                        break;
                }
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
     * Complete
     *
     * @param  Application $app
     * @return mixed
     */
    public function complete(Application $app)
    {
        return $app['view']->render('ProductReview/Resource/template/default/complete.twig', array(
        ));
    }

    /**
     * 購入エラー画面表示
     */
    public function frontError(Application $app)
    {
        return $app->render('ProductReview/Resource/template/default/error.twig');
    }

}
