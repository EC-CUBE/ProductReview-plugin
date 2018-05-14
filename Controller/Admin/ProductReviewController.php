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

use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Repository\Master\PageMaxRepository;
use Eccube\Service\CsvExportService;
use Eccube\Util\FormUtil;
use Knp\Component\Pager\PaginatorInterface;
use Plugin\ProductReview\Entity\ProductReview;
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Plugin\ProductReview\Form\Type\Admin\ProductReviewSearchType;
use Plugin\ProductReview\Form\Type\Admin\ProductReviewType;
use Plugin\ProductReview\Repository\ProductReviewConfigRepository;
use Plugin\ProductReview\Repository\ProductReviewRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductReviewController admin.
 */
class ProductReviewController extends AbstractController
{
    /**
     * @var PageMaxRepository
     */
    protected $pageMaxRepository;

    /**
     * @var ProductReviewRepository
     */
    protected $productReviewRepository;

    /**
     * @var ProductReviewConfigRepository
     */
    protected $productReviewConfigRepository;

    /** @var  CsvExportService */
    protected $csvExportService;


    /**
     * ProductReviewController constructor.
     * @param PageMaxRepository $pageMaxRepository
     * @param ProductReviewRepository $productReviewRepository
     * @param ProductReviewConfigRepository $productReviewConfigRepository
     * @param CsvExportService $csvExportService
     */
    public function __construct(
        PageMaxRepository $pageMaxRepository,
        ProductReviewRepository $productReviewRepository,
        ProductReviewConfigRepository $productReviewConfigRepository,
        CsvExportService $csvExportService)
    {
        $this->pageMaxRepository = $pageMaxRepository;
        $this->productReviewRepository = $productReviewRepository;
        $this->productReviewConfigRepository = $productReviewConfigRepository;
        $this->csvExportService = $csvExportService;
    }

    /**
     * Search function.
     *
     * @Route("%eccube_admin_route%/plugin/product/review/", name="plugin_admin_product_review")
     * @Route("/%eccube_admin_route%/plugin/product/page/{page_no}", requirements={"page_no" = "\d+"}, name="plugin_admin_product_review_page")
     * @Template("ProductReview/Resource/template/admin/index.twig")
     *
     * @param Request $request
     * @param null $page_no
     * @return array
     */
    public function index(Request $request, $page_no = null, PaginatorInterface $paginator)
    {
        $pageNo = $page_no;

        $pageMaxis = $this->pageMaxRepository->findAll();
        $pageCount = $this->eccubeConfig->get('eccube_default_page_count');
        $pagination = null;
        $searchForm = $this->createForm(ProductReviewSearchType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchData = $searchForm->getData();
            dump($searchData);
            die;
            $qb = $this->productReviewRepository
                ->getQueryBuilderBySearchData($searchData);

            $pageNo = 1;
            $pagination = $paginator->paginate(
                $qb,
                $pageNo,
                $pageCount,
                array('wrap-queries' => true)
            );

            $searchData = FormUtil::getViewData($searchForm);

            // sessionのデータ保持
            $this->session->set('plugin.product_review.admin.product_review.search', $searchData);
            $this->session->set('plugin.product_review.admin.product_review.search.page_no', $pageNo);
        } else {
            if (is_null($pageNo) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $this->session->remove('plugin.product_review.admin.product_review.search');
                $this->session->remove('plugin.product_review.admin.product_review.search.page_no');
                $searchData = array();
            } else {
                // pagingなどの処理
                if (is_null($pageNo)) {
                    $pageNo = intval($this->session->get('plugin.product_review.admin.product_review.search.page_no'));
                } else {
                    $this->session->set('plugin.product_review.admin.product_review.search.page_no', $pageNo);
                }

                $searchData = $this->session->get('plugin.product_review.admin.product_review.search');
                if (!is_null($searchData)) {
                    $searchData = FormUtil::submitAndGetData($searchForm, $searchData);
                    $qb = $this->productReviewRepository
                        ->getQueryBuilderBySearchData($searchData);

                    // 表示件数
                    $pcount = $request->get('page_count');

                    $pageCount = empty($pcount) ? $pageCount : $pcount;

                    $pagination = $paginator->paginate(
                        $qb,
                        $pageNo,
                        $pageCount,
                        array('wrap-queries' => true)
                    );
                }
            }
        }

        // Get product preview config.
        /* @var $Config ProductReviewConfig */
        $Config = $this->productReviewConfigRepository->find(1);
        $csvType = 1;//$Config->getCsvType()->getId();

        return [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_count' => $pageCount,
            'csv_type' => $csvType,
        ];
    }

    /**
     * 編集.
     * @Route("%eccube_admin_route%/plugin/product/review/{id}/edit", name="plugin_admin_product_review_edit")
     * @Template("ProductReview/Resource/template/admin/edit.twig")
     *
     * @param Request $request
     * @param int $id
     *
     * @return array
     */
    public function edit(Request $request, $id)
    {
        // IDから商品レビューを取得する
        /** @var $ProductReview ProductReview */
        $ProductReview = $this->productReviewRepository->find($id);

        if (!$ProductReview) {
            $this->addError('plugin.admin.product_review.not_found', 'admin');

            return $this->redirectToRoute('plugin_admin_product_review');
        }

        $Product = $ProductReview->getProduct();
        if (!$Product) {
            $this->addError('admin.product.not_found', 'admin');

            return $this->redirectToRoute('plugin_admin_product_review');
        }

        $postedDate = $ProductReview->getCreateDate();
        // formの作成
        $builder = $this->formFactory->createBuilder(ProductReviewType::class, $ProductReview);
        /* @var $form FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ProductReview = $form->getData();
            $status = $this->productReviewRepository->save($ProductReview);

            log_info('Product review add/edit', array('status' => $status));

            if (!$status) {
                $this->addError('plugin.admin.product_review.save.error', 'admin');
            } else {
                $this->addSuccess('plugin.admin.product_review.save.complete', 'admin');
            }
        }

        return [
            'form' => $form->createView(),
            'Product' => $Product,
            'post_date' => $postedDate,
        ];
    }

    /**
     * Product review delete function.
     * @Route("%eccube_admin_route%/plugin/product/review/{id}/delete", name="plugin_admin_product_review_delete")
     *
     * @param Request $request
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, $id = null)
    {
        $this->isTokenValid();
        $session = $request->getSession();
        $pageNo = intval($session->get('plugin.product_review.admin.product_review.search.page_no'));
        $pageNo = $pageNo ? $pageNo : 1;
        $resume = Constant::ENABLED;
        if ($id) {
            $TargetProductReview = $this->productReviewRepository->find($id);

            if (!$TargetProductReview) {
                throw new NotFoundHttpException();
            }

            $status = $this->productReviewRepository->delete($TargetProductReview);

            if ($status === true) {
                $this->addSuccess('plugin.admin.product_review.delete.complete', 'admin');
            } else {
                $this->addError('plugin.admin.product_review.delete.error', 'admin');
            }
        } else {
            $this->addError('plugin.admin.product_review.delete.error', 'admin');
        }

        log_info('Product review delete', array('status' => isset($status) ? $status : 0));

        return $this->redirect($this->generateUrl('plugin_admin_product_review_page', array('page_no' => $pageNo)).'?resume='.$resume);
    }

    /**
     * 商品レビューCSVの出力.
     *
     * @Route("%eccube_admin_route%/plugin/product/review/download", name="plugin_admin_product_review_download")
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function download(Request $request)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $this->entityManager;
        $em->getConfiguration()->setSQLLogger(null);
        $response = new StreamedResponse();
        $response->setCallback(function () use ($request) {
            $Config = $this->productReviewConfigRepository->find(1);
            $csvType = 1;//$Config->getCsvType()->getId();

            /* @var $csvService CsvExportService */
            $csvService = $this->csvExportService;

            /* @var $repo ProductReviewRepository */
            $repo = $this->productReviewRepository;

            // CSV種別を元に初期化.
            $csvService->initCsvType($csvType);

            // ヘッダ行の出力.
            $csvService->exportHeader();

            $session = $request->getSession();

            $searchData = array();
//            if (Version::isSupportNewSession()) {
//                $searchData = $session->get('plugin.product_review.admin.product_review.search');
//                $searchForm = $this->formFactory->createBuilder(ProductReviewSearchType::class, null, array('csrf_protection' => false));
//                $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $searchData);
//            } else {
                /*if ($session->has('plugin.product_review.admin.product_review.search')) {
                    $searchData = $session->get('plugin.product_review.admin.product_review.search');
                    $repo->findDeserializeObjects($searchData);
                }*/
//            }

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
