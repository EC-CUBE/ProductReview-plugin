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

namespace Plugin\ProductReview\ServiceProvider;

use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class ProductReviewServiceProvider implements ServiceProviderInterface
{

    public function register(BaseApplication $app)
    {
        // 商品レビュー用リポジトリ
        $app['eccube.plugin.product_review.repository.product_review'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductReview\Entity\ProductReview');
        });
        
        // 一覧
        $app->match('/' . $app["config"]["admin_route"] . '/product/review/', '\\Plugin\\ProductReview\\Controller\\ProductReviewController::index')
            ->bind('admin_product_review');
        // 一覧：表示件数変更
        $app->match('/' . $app["config"]["admin_route"] . '/product/review/{page_no}', '\\Plugin\\ProductReview\\Controller\\ProductReviewController::index')
            ->assert('page_no', '\d+')
            ->bind('admin_product_review_page');

        // 編集
        $app->match('/' . $app["config"]["admin_route"] . '/product/review/edit/{id}', '\\Plugin\\ProductReview\\Controller\\ProductReviewController::edit')
            ->assert('id', '\d+')
            ->bind('admin_product_review_edit');

        // 削除
        $app->match('/' . $app["config"]["admin_route"] . '/product/review/delete/{id}', '\\Plugin\\ProductReview\\Controller\\ProductReviewController::delete')
            ->assert('id', '\d+')
            ->bind('admin_product_review_delete');

        // フロント：レビュー入力、確認
        $app->match('/products/detail/{id}/review', '\\Plugin\\ProductReview\\Controller\\ProductReviewController::review')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('products_detail_review');

        // フロント：レビュー登録完了
        $app->match('/products/detail/{id}/review/complete', '\\Plugin\\ProductReview\\Controller\\ProductReviewController::complete')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('products_detail_review_complete');

        // フロント：システムエラー
        $app->match('/products/detail/review_error', '\\Plugin\\ProductReview\\Controller\\ProductReviewController::frontError')
            ->bind('products_detail_review_error');

        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new \Plugin\ProductReview\Form\Type\ProductReviewType($app);
            $types[] = new \Plugin\ProductReview\Form\Type\Admin\ProductReviewType($app);
            $types[] = new \Plugin\ProductReview\Form\Type\Admin\ProductReviewSerchType($app);
            return $types;
        }));

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());

            $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }

            return $translator;
        }));

        // メニュー登録
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $addNavi['id'] = 'product_review';
            $addNavi['name'] = 'レビュー管理';
            $addNavi['url'] = 'admin_product_review';
            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ('product' == $val['id']) {
                    $nav[$key]['child'][] = $addNavi;
                }
            }
            $config['nav'] = $nav;

            return $config;
        }));
    }

    public function boot(BaseApplication $app)
    {
    }
}
