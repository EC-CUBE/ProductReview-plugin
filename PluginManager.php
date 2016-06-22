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

use Eccube\Plugin\AbstractPluginManager;
use Plugin\ProductReview\Entity\ProductReviewPlugin;
class PluginManager extends AbstractPluginManager
{

    public function __construct()
    {
    }

    public function install($config, $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Migration', $config['code']);
    }

    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Migration', $config['code'], 0);
    }

    public function enable($config, $app)
    {
        $this->insertDataPlugin($app);
    }

    public function disable($config, $app)
    {
        $this->removeDataPlugin($app);
    }

    public function update($config, $app)
    {

    }

    private function insertDataPlugin($app) {
        $em = $app['orm.em'];
        $ProductReviewPlugin = new ProductReviewPlugin();
        $ProductReviewPlugin->setCssSelector('#item_detail');
        $ProductReviewPlugin->setId(1);
        $em->persist($ProductReviewPlugin);
        $em->flush($ProductReviewPlugin);
    }

    private function removeDataPlugin($app) {
        $em = $app['orm.em'];
        $ProductReviewPlugin = $app["eccube.plugin.product_review.repository.product_review_plugin"]->find(1);
        $em->remove($ProductReviewPlugin);
        $em->flush($ProductReviewPlugin);
    }

}
