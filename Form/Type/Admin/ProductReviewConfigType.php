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
use Plugin\ProductReview\Entity\ProductReviewConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductReviewConfigType.
 */
class ProductReviewConfigType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ProductReviewConfigType constructor.
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
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $min = $this->eccubeConfig['review_regist_min'];
        $max = $this->eccubeConfig['review_regist_max'];

        $builder
            ->add('review_max', IntegerType::class, [
                'required' => true,
                'label' => "レビューの表示件数({$min}～{$max})",
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => $min, 'max' => $max]),
                ],
            ]);
    }

    /**
     * Config.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductReviewConfig::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_product_review_config';
    }
}
