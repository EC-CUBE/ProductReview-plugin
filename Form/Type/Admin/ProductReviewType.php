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

use Eccube\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductReviewType.
 * [商品レビュー]-[レビュー管理]用Form.
 */
class ProductReviewType extends AbstractType
{
    /**
     * @var Application
     */
    private $app;

    /**
     * ProductReviewType constructor.
     *
     * @param object $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Build form.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->app['config'];
        $builder
            ->add('id', 'hidden')
            ->add('create_date', 'hidden', array(
                'label' => '投稿日',
                'mapped' => false,
            ))
            ->add('status', 'disp', array(
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('reviewer_name', 'text', array(
                'label' => '投稿者名',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
                'attr' => array(
                    'maxlength' => $config['stext_len'],
                ),
            ))
            ->add('reviewer_url', 'text', array(
                'label' => '投稿者URL',
                'required' => false,
                'constraints' => array(
                    new Assert\Url(),
                    new Assert\Length(array('max' => $config['mltext_len'])),
                ),
                'attr' => array(
                    'maxlength' => $config['mltext_len'],
                ),
            ))
            ->add('sex', 'sex', array(
                'required' => false,
            ))
            ->add('recommend_level', 'choice', array(
                'label' => 'おすすめレベル',
                'choices' => array(
                    '5' => '★★★★★',
                    '4' => '★★★★',
                    '3' => '★★★',
                    '2' => '★★',
                    '1' => '★',
                ),
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ))
            ->add('title', 'text', array(
                'label' => 'タイトル',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
                'attr' => array(
                    'maxlength' => $config['stext_len'],
                ),
            ))
            ->add('comment', 'textarea', array(
                'label' => 'コメント',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => $config['ltext_len'])),
                ),
                'attr' => array(
                    'maxlength' => $config['ltext_len'],
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
