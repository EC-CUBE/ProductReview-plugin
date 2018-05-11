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

use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Repository\Master\ProductStatusRepository;
use Plugin\ProductReview\Entity\ProductReview;
use Plugin\ProductReview\Form\Type\ProductReviewType;
use Plugin\ProductReview\Repository\ProductReviewRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductReviewController front.
 */
class ProductReviewController extends AbstractController
{

    /** @var  ProductStatusRepository */
    private $productStatusRepository;

    /** @var  ProductReviewRepository */
    private $productReviewRepository;

    /**
     * ProductReviewController constructor.
     * @param ProductStatusRepository $productStatusRepository
     * @param ProductReviewRepository $productReviewRepository
     */
    public function __construct(ProductStatusRepository $productStatusRepository, ProductReviewRepository $productReviewRepository){
        $this->productStatusRepository = $productStatusRepository;
        $this->productReviewRepository = $productReviewRepository;
    }


    /**
     * @Route("/plugin/products/detail/{id}/review", name="plugin_products_detail_review", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param Product $Product
     * @return RedirectResponse|Response
     */
    public function review(Request $request, Product $Product)
    {
        if (!$this->session->has('_security_admin') && $Product->getStatus()->getId() !== ProductStatus::DISPLAY_SHOW) {
            log_info('Product review', array('status' => 'Not permission'));

            throw new NotFoundHttpException();
        }

        $ProductReview = new ProductReview();
        $builder = $this->formFactory->createBuilder(ProductReviewType::class, $ProductReview);
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    log_info('Product review config');

                    $builder->setAttribute('freeze', true);
                    $builder->setAttribute('freeze_display_text', true);
                    $form = $builder->getForm();
                    $form->handleRequest($request);

                    return $this->render('ProductReview/Resource/template/default/confirm.twig', array(
                        'form' => $form->createView(),
                        'Product' => $Product,
                    ));
                    break;

                case 'complete':
                    log_info('Product review complete');
                    /** @var $ProductReview ProductReview */
                    $ProductReview = $form->getData();
                    if ($this->isGranted('ROLE_USER')) {
                        $Customer = $this->getUser();;
                        $ProductReview->setCustomer($Customer);
                    }
                    $ProductReview->setProduct($Product);

                    $ProductReview->setEnabled(true);
                    $status = $this->productReviewRepository->save($ProductReview);

                    if (!$status) {
                        $this->addError('plugin.front.product_review.system.error');
                        log_info('Product review complete', array('status' => 'fail'));

                        return $this->redirectToRoute('plugin_products_detail_review_error');
                    } else {
                        log_info('Product review complete', array('id' => $Product->getId()));

                        return $this->redirectToRoute('plugin_products_detail_review_complete', ['id' => $Product->getId()]);
                    }
                    break;

                case 'back':
                default:
                    // do nothing
                    break;
            }
        }

        return $this->render('ProductReview/Resource/template/default/index.twig', array(
            'Product' => $Product,
            'form' => $form->createView(),
        ));
    }

    /**
     * Complete.
     *
     * @Route("/plugin/products/detail/{id}/review/complete", name="plugin_products_detail_review_complete", requirements={"id" = "\d+"})
     *
     * @param int $id
     *
     * @return mixed
     */
    public function complete($id)
    {
        return $this->render('ProductReview/Resource/template/default/complete.twig', array('id' => $id));
    }

    /**
     * @Route("/plugin/product/review/", name="plugin_products_detail_review_error")
     *
     * @return Response
     */
    public function frontError()
    {
        return $this->render('ProductReview/Resource/template/default/error.twig');
    }
}
