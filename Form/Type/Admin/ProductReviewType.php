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

namespace Plugin\ProductReview4\Form\Type\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Master\SexType;
use Plugin\ProductReview4\Entity\ProductReviewStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductReviewType.
 * [商品レビュー]-[レビュー管理]用Form.
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
     * Build form.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->eccubeConfig;
        $builder
            ->add('Status', EntityType::class, [
                'class' => ProductReviewStatus::class,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('reviewer_name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $config['eccube_stext_len']]),
                ],
                'attr' => [
                    'maxlength' => $config['eccube_stext_len'],
                ],
            ])
            ->add('reviewer_url', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Url(),
                    new Assert\Length(['max' => $config['eccube_url_len']]),
                ],
                'attr' => [
                    'maxlength' => $config['eccube_url_len'],
                ],
            ])
            ->add('sex', SexType::class, [
                'required' => false,
            ])
            ->add('recommend_level', ChoiceType::class, [
                'choices' => array_flip([
                    '5' => '★★★★★',
                    '4' => '★★★★',
                    '3' => '★★★',
                    '2' => '★★',
                    '1' => '★',
                ]),
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('title', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $config['eccube_stext_len']]),
                ],
                'attr' => [
                    'maxlength' => $config['eccube_stext_len'],
                ],
            ])
            ->add('comment', TextareaType::class, [
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
