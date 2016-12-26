<?php
/**
 * This file is part of the ProductReview plugin.
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview\Event;

use Eccube\Application;

/**
 * Class AbstractEvent.
 */
class CommonEvent
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var string target render on the front-end
     */
    protected $pluginTag = '<!--# product-review-plugin-tag #-->';

    /**
     * AbstractEvent constructor.
     *
     * @param \Silex\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Render position.
     *
     * @param string $html    twig code
     * @param string $part    twig code
     * @param string $markTag
     *
     * @return mixed
     */
    protected function renderPosition($html, $part, $markTag = '')
    {
        if (!$markTag) {
            $markTag = $this->pluginTag;
        }
        // for plugin tag
        if (strpos($html, $markTag)) {
            $newHtml = $markTag.$part;
            $html = str_replace($markTag, $newHtml, $html);
        } else {
            // As request, the product review area will append bellow free area section.
            $freeAreaStart = '{% if Product.freearea %}';
            $pos = strpos($html, $freeAreaStart);
            $endPart = substr($html, $pos);

            // End of free area
            $freeAreaEnd = '{% endif %}';
            $from = '/'.preg_quote($freeAreaEnd, '/').'/';
            $newEndPart = preg_replace($from, $freeAreaEnd.$part, $endPart, 1);

            $html = str_replace($endPart, $newEndPart, $html);
        }

        return $html;
    }
}
