<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\GlueApplication\Rest\Request;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\GlueApplication\Rest\RequestConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaginationParametersHttpHttpRequestValidator implements PaginationParametersHttpRequestValidatorInterface
{
    protected const PATTERN_REGEX_PAGE_PARAMETER = '/^\d+$/';

    protected const ERROR_MESSAGE_INVALID_PAGE_PARAMETERS = 'Pagination parameters are invalid.';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Generated\Shared\Transfer\RestErrorMessageTransfer|null
     */
    public function validate(Request $request): ?RestErrorMessageTransfer
    {
        $queryParameters = $request->query->all();
        $queryPage = $queryParameters[RequestConstantsInterface::QUERY_PAGE];

        $offset = $queryPage[RequestConstantsInterface::QUERY_OFFSET] ?? null;
        $limit = $queryPage[RequestConstantsInterface::QUERY_LIMIT] ?? null;

        if ($offset === null && $limit === null) {
            return null;
        }

        if (!$this->arePageParametersValid($offset, $limit)) {
            return (new RestErrorMessageTransfer())
                ->setStatus(Response::HTTP_BAD_REQUEST)
                ->setDetail(static::ERROR_MESSAGE_INVALID_PAGE_PARAMETERS);
        }

        return null;
    }

    /**
     * @param string|null $offset
     * @param string|null $limit
     *
     * @return bool
     */
    protected function arePageParametersValid(?string $offset, ?string $limit): bool
    {
        if ($offset && !preg_match(static::PATTERN_REGEX_PAGE_PARAMETER, $offset)) {
            return false;
        }

        if ($limit && !preg_match(static::PATTERN_REGEX_PAGE_PARAMETER, $limit)) {
            return false;
        }

        if ($limit <= 0) {
            return false;
        }

        return true;
    }
}