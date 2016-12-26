<?php
/**
 * This file is part of the ProductReview plugin.
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview;

use Eccube\Application;
use Eccube\Event\TemplateEvent;
use Plugin\ProductReview\Util\Version;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class Event.
 */
class Event
{
    /**
     * @var Application
     */
    private $app;

    /**
     * MakerEvent constructor.
     *
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param TemplateEvent $event
     */
    public function onProductDetailRender(TemplateEvent $event)
    {
        $this->app['product_review.event.product_review']->onProductDetailRender($event);
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderProductsDetailBefore(FilterResponseEvent $event)
    {
        if ($this->supportNewHookPoint()) {
            return;
        }

        $this->app['product_review.event.product_review_legacy']->onRenderProductsDetailBefore($event);
    }

    /**
     * @return bool v3.0.9以降のフックポイントに対応しているか？
     */
    private function supportNewHookPoint()
    {
        return Version::isSupportVersion();
    }
}
