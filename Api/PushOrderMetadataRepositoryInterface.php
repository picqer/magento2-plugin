<?php

namespace Picqer\Integration\Api;

use Picqer\Integration\Api\Data\PushOrderMetadataInterface;

/**
 * @api
 */
interface PushOrderMetadataRepositoryInterface
{
    /**
     * @param PushOrderMetadataInterface $pushOrderMetadata
     * @return mixed
     */
    function setProcessed(PushOrderMetadataInterface $pushOrderMetadata, $message);

    /**
     * @return PushOrderMetadataInterface[]
     */
    function getUnprocessedList();

    function save(PushOrderMetadataInterface $pushOrderMetadata);
}
