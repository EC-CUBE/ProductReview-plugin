<?php
/**
 * This file is part of the ProductReview plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\ProductReview\Controller\Admin;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ConfigController.
 */
class ConfigController extends AbstractController
{
    /**
     * @param Application $application
     * @param Request     $request
     * @return Response
     */
    public function index(Application $application, Request $request)
    {

        return $application->render('', array());
    }
}
