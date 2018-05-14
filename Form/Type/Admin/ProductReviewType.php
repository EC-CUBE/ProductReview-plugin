<?php
/**
 * This file is part of the ProductReview plugin.
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Form\Type\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Form\Type\Master\ProductStatusType;
use Eccube\Form\Type\Master\SexType;
use Eccube\Form\Type\Master\StatusType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->add('id', HiddenType::class)
            ->add('create_date', HiddenType::class, array(
                'label' => 'plugin.admin.product_review.list.posted.date',
                'mapped' => false,
            ))
            ->add('status', ProductStatusType::class, array(
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('reviewer_name', TextType::class, array(
                'label' => 'plugin.admin.product_review.form.name.contributor',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => $config['eccube_stext_len'])),
                ),
                'attr' => array(
                    'maxlength' => $config['eccube_stext_len'],
                ),
            ))
            ->add('reviewer_url', TextType::class, array(
                'label' => 'plugin.admin.product_review.form.authorURL',
                'required' => false,
                'constraints' => array(
                    new Assert\Url(),
                    new Assert\Length(array('max' => $config['eccube_stext_len'])),
                ),
                'attr' => array(
                    'maxlength' => $config['eccube_stext_len'],
                ),
            ))
            ->add('sex', SexType::class, array(
                'required' => false,
            ))
            ->add('recommend_level', ChoiceType::class, array(
                'label' => 'plugin.admin.product_review.list.level',
                'choices' => array_flip(array(
                    '5' => '★★★★★',
                    '4' => '★★★★',
                    '3' => '★★★',
                    '2' => '★★',
                    '1' => '★',
                )),
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ))
            ->add('title', TextType::class, array(
                'label' => 'plugin.admin.product_review.form.comment.title',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => $config['eccube_stext_len'])),
                ),
                'attr' => array(
                    'maxlength' => $config['eccube_stext_len'],
                ),
            ))
            ->add('comment', TextareaType::class, array(
                'label' => 'plugin.admin.product_review.form.comment.content',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => $config['eccube_stext_len'])),
                ),
                'attr' => array(
                    'maxlength' => $config['eccube_ltext_len'],
                ),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_product_review';
    }
}
