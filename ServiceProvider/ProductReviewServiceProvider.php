<?php
/**
  * This file is part of the ProductReview plugin.
  *
  * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Plugin\ProductReview\ServiceProvider;

use Eccube\Common\Constant;
use Plugin\ProductReview\Event\ProductReviewEvent;
use Plugin\ProductReview\Event\ProductReviewEventLegacy;
use Plugin\ProductReview\Form\Type\Admin\ProductReviewConfigType;
use Plugin\ProductReview\Form\Type\Admin\ProductReviewSearchType;
use Plugin\ProductReview\Form\Type\ProductReviewType;
use Plugin\ProductReview\Form\Type\Admin\ProductReviewType as AdminProductReviewType;
use Plugin\ProductReview\Util\Version;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

// include log functions (for 3.0.0 - 3.0.11)
require_once __DIR__.'/../log.php';

/**
 * Class ProductReviewServiceProvider.
 */
class ProductReviewServiceProvider implements ServiceProviderInterface
{
    /**
     * Register provider.
     *
     * @param BaseApplication $app
     */
    public function register(BaseApplication $app)
    {
        // 商品レビュー用リポジトリ
        $app['product_review.repository.product_review'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductReview\Entity\ProductReview');
        });

        $app['product_review.repository.product_review_config'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductReview\Entity\ProductReviewConfig');
        });

        // Product Review event
        $app['product_review.event.product_review'] = $app->share(function () use ($app) {
            return new ProductReviewEvent($app);
        });

        // Product review legacy event
        $app['product_review.event.product_review_legacy'] = $app->share(function () use ($app) {
            return new ProductReviewEventLegacy($app);
        });

        // フロント画面定義
        $front = $app['controllers_factory'];
        // Admin
        $admin = $app['controllers_factory'];
        // 強制SSL
        if ($app['config']['force_ssl'] == Constant::ENABLED) {
            $admin->requireHttps();
            $front->requireHttps();
        }

        // プラグイン用設定画面
        $admin->match('/plugin/product/review/config', 'Plugin\ProductReview\Controller\Admin\ConfigController::index')->bind('plugin_ProductReview_config');

        // 一覧
        $admin->match('/plugin/product/review/', 'Plugin\ProductReview\Controller\Admin\ProductReviewController::index')
            ->bind('plugin_admin_product_review');
        // 一覧：表示件数変更
        $admin->match('/plugin/product/review/{page_no}', 'Plugin\ProductReview\Controller\Admin\ProductReviewController::index')
            ->assert('page_no', '\d+')
            ->bind('plugin_admin_product_review_page');
        // 一覧: download csv
        $admin->post('/plugin/product/review/download', 'Plugin\ProductReview\Controller\Admin\ProductReviewController::download')
            ->bind('plugin_admin_product_review_download');

        // 編集
        $admin->match('/plugin/product/review/{id}/edit', 'Plugin\ProductReview\Controller\Admin\ProductReviewController::edit')
            ->assert('id', '\d+')
            ->bind('plugin_admin_product_review_edit');

        // 削除
        $admin->delete('/plugin/product/review/{id}/delete', 'Plugin\ProductReview\Controller\Admin\ProductReviewController::delete')
            ->assert('id', '\d+')
            ->bind('plugin_admin_product_review_delete');

        $app->mount('/'.trim($app['config']['admin_route'], '/').'/', $admin);

        // フロント：レビュー入力、確認
        $front->match('/plugin/products/detail/{id}/review', 'Plugin\ProductReview\Controller\ProductReviewController::review')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('plugin_products_detail_review');

        // フロント：レビュー登録完了
        $front->match('/plugin/products/detail/{id}/review/complete', 'Plugin\ProductReview\Controller\ProductReviewController::complete')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('plugin_products_detail_review_complete');

        // フロント：システムエラー
        $front->match('/plugin/products/detail/review/error', 'Plugin\ProductReview\Controller\ProductReviewController::frontError')
            ->bind('plugin_products_detail_review_error');

        $app->mount('', $front);

        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new ProductReviewType($app);
            $types[] = new AdminProductReviewType($app);
            $types[] = new ProductReviewSearchType($app);
            $types[] = new ProductReviewConfigType($app);

            return $types;
        }));

        // メッセージ登録
        $file = __DIR__.'/../Resource/locale/message.'.$app['locale'].'.yml';
        if (file_exists($file)) {
            $app['translator']->addResource('yaml', $file, $app['locale']);
        }

        // メニュー登録
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $addNavi['id'] = 'product_review';
            $addNavi['name'] = 'レビュー管理';
            $addNavi['url'] = 'plugin_admin_product_review';
            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ('product' == $val['id']) {
                    $nav[$key]['child'][] = $addNavi;
                }
            }
            $config['nav'] = $nav;

            return $config;
        }));

        // initialize logger (for 3.0.0 - 3.0.8)
        if (!Version::isSupportMethod()) {
            eccube_log_init($app);
        }
    }

    /**
     * @param BaseApplication $app
     */
    public function boot(BaseApplication $app)
    {
    }
}
