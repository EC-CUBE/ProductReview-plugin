<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview42\Form\Type;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Master\SexType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductReviewType
 * [商品レビュー]-[レビューフロント]用Form.
 */
class ProductReviewType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ProductReviewType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * build form.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->eccubeConfig;
        $builder
            ->add('reviewer_name', TextType::class, [
                'label' => 'product_review.form.product_review.reviewer_name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $config['eccube_stext_len']]),
                ],
                'attr' => [
                    'maxlength' => $config['eccube_stext_len'],
                ],
            ])
            ->add('reviewer_url', TextType::class, [
                'label' => 'product_review.form.product_review.reviewer_url',
                'required' => false,
                'constraints' => [
                    new Assert\Url(),
                    new Assert\Length(['max' => $config['eccube_mltext_len']]),
                ],
                'attr' => [
                    'maxlength' => $config['eccube_mltext_len'],
                ],
            ])
            ->add('sex', SexType::class, [
                'required' => false,
            ])
            ->add('recommend_level', ChoiceType::class, [
                'label' => 'product_review.form.product_review.recommend_level',
                'choices' => array_flip([
                    '5' => '★★★★★',
                    '4' => '★★★★',
                    '3' => '★★★',
                    '2' => '★★',
                    '1' => '★',
                ]),
                'expanded' => true,
                'multiple' => false,
                'placeholder' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'product_review.form.product_review.title',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 50]),
                ],
                'attr' => [
                    'maxlength' => $config['eccube_stext_len'],
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'product_review.form.product_review.comment',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $config['eccube_ltext_len']]),
                ],
                'attr' => [
                    'maxlength' => $config['eccube_ltext_len'],
                ],
            ]);
    }
}
