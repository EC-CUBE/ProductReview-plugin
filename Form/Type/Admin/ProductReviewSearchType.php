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
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Master\ProductStatusType;
use Eccube\Form\Type\Master\SexType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('multi', TextType::class, array(
                'label' => '投稿者名・投稿者URL',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['eccube_stext_len'])),
                ),
            ))
            ->add('product_name', TextType::class, array(
                'label' => '商品名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['eccube_stext_len'])),
                ),
            ))
            ->add('product_code', TextType::class, array(
                'label' => '商品コード',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['eccube_stext_len'])),
                ),
            ))
            ->add('sex', SexType::class, array(
                'label' => '性別',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('recommend_level', ChoiceType::class, array(
                'label' => 'おすすめレベル',
                'choices' => array_flip([
                    '5' => '★★★★★',
                    '4' => '★★★★',
                    '3' => '★★★',
                    '2' => '★★',
                    '1' => '★',
                ]),
                'placeholder' => '選択してください',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ))
            // fixme birthdaytypeは誤り
            ->add('review_start', BirthdayType::class, array(
                'label' => '投稿日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'placeholder' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            // fixme birthdaytypeは誤り
            ->add('review_end', BirthdayType::class, array(
                'label' => '投稿日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'placeholder' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            // fixme 商品レビュー用のステータスを作成する
            ->add('status', ProductStatusType::class, array(
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
