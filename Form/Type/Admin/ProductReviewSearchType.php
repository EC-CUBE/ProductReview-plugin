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
 * Class ProductReviewSearchType.
 * [商品レビュー]-[レビュー検索]用Form.
 */
class ProductReviewSearchType extends AbstractType
{
    /**
     * @var Application
     */
    private $app;

    /**
     * ProductReviewSearchType constructor.
     *
     * @param object $app
     */
    public function __construct($app)
    {
        $this->app = $app;
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
        $config = $this->app['config'];
        $builder
            ->add('multi', 'text', array(
                'label' => '投稿者名・投稿者URL',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['ltext_len'])),
                ),
            ))
            ->add('product_name', 'text', array(
                'label' => '商品名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('product_code', 'text', array(
                'label' => '商品コード',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('sex', 'sex', array(
                'label' => '性別',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
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
                'empty_value' => '選択してください',
                'expanded' => false,
                'multiple' => false,
            ))
            ->add('review_start', 'birthday', array(
                'label' => '投稿日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('review_end', 'birthday', array(
                'label' => '投稿日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('status', 'disp', array(
                'label' => '表示',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_product_review_search';
    }
}
