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
