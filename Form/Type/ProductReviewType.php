<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
/*
 * [商品レビュー]-[レビューフロント]用Form
 */

namespace Plugin\ProductReview\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductReviewType extends AbstractType
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->app['config'];
        $builder
            ->add('reviewer_name', 'text', array(
                'label' => '投稿者名',
                'required' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('reviewer_url', 'text', array(
                'label' => '投稿者URL',
                'required' => false,
                'constraints' => array(
                    new Assert\Url(),
                    new Assert\Length(array('max' => $config['mltext_len'])),
                ),
            ))
            ->add('sex', 'sex', array(
                'required' => false,
            ))
            ->add('recommend_level', 'choice', array(
                'required' => false,
                'label' => 'おすすめレベル',
                'choices' => array(
                    '5' => '★★★★★',
                    '4' => '★★★★',
                    '3' => '★★★',
                    '2' => '★★',
                    '1' => '★',
                ),
                'expanded' => true,
                'multiple' => false,
                'placeholder' => false,
            ))
            ->add('title', 'text', array(
                'label' => 'タイトル',
                'required' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('comment', 'textarea', array(
                'label' => 'コメント',
                'required' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => $config['ltext_len'])),
                ),
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'product_review';
    }
}
