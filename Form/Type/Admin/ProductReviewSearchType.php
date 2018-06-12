<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Form\Type\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Master\ProductStatusType;
use Eccube\Form\Type\Master\SexType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductReviewSearchType.
 * [商品レビュー]-[レビュー検索]用Form.
 */
class ProductReviewSearchType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ProductReviewSearchType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     * build form method.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->eccubeConfig;
        $builder
            ->add('multi', TextType::class, [
                'label' => 'plugin.admin.product_review.search.inputsearch.placeholder',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => $config['eccube_stext_len']]),
                ],
            ])
            ->add('product_name', TextType::class, [
                'label' => 'plugin.front.product_review.product.name',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => $config['eccube_stext_len']]),
                ],
            ])
            ->add('product_code', TextType::class, [
                'label' => 'plugin.admin.product_review.form.product.code',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => $config['eccube_stext_len']]),
                ],
            ])
            ->add('sex', SexType::class, [
                'label' => 'plugin.admin.product_review.form.sex',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('recommend_level', ChoiceType::class, [
                'label' => 'plugin.admin.product_review.list.level',
                'choices' => array_flip([
                    '5' => '★★★★★',
                    '4' => '★★★★',
                    '3' => '★★★',
                    '2' => '★★',
                    '1' => '★',
                ]),
                'placeholder' => 'plugin.admin.product_review.form.level',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ])
            ->add('review_start', DateType::class, [
                'label' => 'plugin.admin.product_review.list.posted.date',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('review_end', DateType::class, [
                'label' => 'plugin.admin.product_review.list.posted.date',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            // fixme 商品レビュー用のステータスを作成する
            ->add('status', ProductStatusType::class, [
                'label' => 'plugin.admin.product_review.search.multi',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_product_review_search';
    }
}
