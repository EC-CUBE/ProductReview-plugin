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

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Service\CsvExportService;
use Plugin\ProductReview\Entity\ProductReview;
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Plugin\ProductReview\Repository\ProductReviewRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
     *
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
            $session->set('plugin.product_review.admin.product_review.search.page_no', $pageNo);
        } else {
            if (is_null($pageNo) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('plugin.product_review.admin.product_review.search');
                $session->remove('plugin.product_review.admin.product_review.search.page_no');
                $searchData = array();
            } else {
                // pagingなどの処理
                if (is_null($pageNo)) {
                    $pageNo = intval($session->get('plugin.product_review.admin.product_review.search.page_no'));
                } else {
                    $session->set('plugin.product_review.admin.product_review.search.page_no', $pageNo);
                }

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
            if (isset($searchData['status']) && count($searchData['status']) > 0) {
                $id = array();
                foreach ($searchData['status'] as $status) {
                    $id[] = $status->getId();
                }
                $searchData['status'] = $app['eccube.repository.master.disp']->findBy(array('id' => $id));
            }

            if (isset($searchData['sex']) && count($searchData['sex']) > 0) {
                $id = array();
                foreach ($searchData['sex'] as $sex) {
                    $id[] = $sex->getId();
                }
                $searchData['sex'] = $app['eccube.repository.master.sex']->findBy(array('id' => $id));
            }
            $searchForm->setData($searchData);
        }

        // Get product preview config.
        /* @var $Config ProductReviewConfig */
        $Config = $app['product_review.repository.product_review_config']->find(1);
        $csvType = $Config->getCsvType()->getId();

        return $app->render('ProductReview/Resource/template/admin/index.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_count' => $pageCount,
            'csv_type' => $csvType,
        ));
    }

    /**
     * 編集.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $id
     *
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

        $postedDate = $ProductReview->getCreateDate();
        // formの作成
        /* @var $form FormInterface */
        $form = $app['form.factory']
            ->createBuilder('admin_product_review', $ProductReview)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $status = $app['product_review.repository.product_review']
                ->save($ProductReview);

            log_info('Product review add/edit', array('status' => $status));

            if (!$status) {
                $app->addError('plugin.admin.product_review.save.error', 'admin');
            } else {
                $app->addSuccess('plugin.admin.product_review.save.complete', 'admin');
            }
        }

        return $app->render('ProductReview/Resource/template/admin/edit.twig', array(
            'form' => $form->createView(),
            'Product' => $Product,
            'post_date' => $postedDate,
        ));
    }

    /**
     * Product review delete function.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $id
     *
     * @return RedirectResponse
     */
    public function delete(Application $app, Request $request, $id = null)
    {
        $this->isTokenValid($app);
        $session = $request->getSession();
        $pageNo = intval($session->get('plugin.product_review.admin.product_review.search.page_no'));
        $pageNo = $pageNo ? $pageNo : 1;
        $resume = Constant::ENABLED;
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

        log_info('Product review delete', array('status' => isset($status) ? $status: 0));

        return $app->redirect($app->url('plugin_admin_product_review_page', array('page_no' => $pageNo)).'?resume='.$resume);
    }

    /**
     * 商品レビューCSVの出力.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return StreamedResponse
     */
    public function download(Application $app, Request $request)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request) {
            // Get product preview config.
            /* @var $Config ProductReviewConfig */
            $Config = $app['product_review.repository.product_review_config']->find(1);
            $csvType = $Config->getCsvType()->getId();

            /* @var $csvService CsvExportService */
            $csvService = $app['eccube.service.csv.export'];

            /* @var $repo ProductReviewRepository */
            $repo = $app['product_review.repository.product_review'];

            // CSV種別を元に初期化.
            $csvService->initCsvType($csvType);

            // ヘッダ行の出力.
            $csvService->exportHeader();

            $session = $request->getSession();
            $searchData = array();
            if ($session->has('plugin.product_review.admin.product_review.search')) {
                $searchData = $session->get('plugin.product_review.admin.product_review.search');
                $repo->findDeserializeObjects($searchData);
            }

            $qb = $repo->getQueryBuilderBySearchData($searchData);

            // データ行の出力.
            $csvService->setExportQueryBuilder($qb);
            $csvService->exportData(function ($entity, CsvExportService $csvService) {
                $arrCsv = $csvService->getCsvs();
                $row = array();
                // CSV出力項目と合致するデータを取得.
                foreach ($arrCsv as $csv) {
                    // 受注データを検索.
                    $data = $csvService->getData($csv, $entity);
                    $row[] = $data;
                }
                // 出力.
                $csvService->fputcsv($row);
            });
        });

        $now = new \DateTime();
        $filename = 'product_review_'.$now->format('YmdHis').'.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
        $response->send();

        log_info('商品レビューCSV出力ファイル名', array($filename));

        return $response;
    }
}
