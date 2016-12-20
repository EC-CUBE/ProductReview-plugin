<?php
/**
 * This file is part of the ProductReview plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\ProductReview;

use Eccube\Common\Constant;
use Eccube\Event\RenderEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Plugin\ProductReview\EventLegacy;

class Event
{

    private $app;
    private $legacyEvent;

    public function __construct($app)
    {
        $this->app = $app;
        $this->legacyEvent = new EventLegacy($app);
    }

    /**
     * フロント：商品詳細画面に商品レビューを表示します.
     * @param FilterResponseEvent $event
     */
    public function onRenderProductsDetailBefore(FilterResponseEvent $event)
    {
        if ($this->supportNewHookPoint()) {
            return;
        }
        $this->legacyEvent->onRenderProductsDetailBefore($event);
    }

    public function onRouterProductsDetailResponse(FilterResponseEvent $event)
    {
        $this->legacyEvent->onRenderProductsDetailBefore($event);
    }

    /**
     * @return bool v3.0.9以降のフックポイントに対応しているか？
     */
    private function supportNewHookPoint()
    {
        return version_compare('3.0.9', Constant::VERSION, '<=');
    }
}
