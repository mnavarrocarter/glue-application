<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Glue\GlueApplication\Helper;

use Codeception\Lib\Framework;
use Spryker\Client\Session\SessionClient;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilder;
use Spryker\Glue\GlueApplication\Session\Storage\MockArraySessionStorage;
use Spryker\Glue\Kernel\Application;
use Spryker\Glue\Kernel\Plugin\Pimple;
use Symfony\Component\HttpFoundation\Session\Session;

class GlueBootstrap extends Framework
{
    /**
     * @return void
     */
    public function _initialize(): void
    {
        $this->boot();
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $pimplePlugin = new Pimple();
        $pimplePlugin->setApplication(new Application());

        $pimplePlugin->getApplication()['resource_builder'] = new RestResourceBuilder();

        (new SessionClient())->setContainer(
            new Session(
                new MockArraySessionStorage()
            )
        );
    }
}
