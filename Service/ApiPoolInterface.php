<?php
/**
 * This file is part of Zepgram\Rest\Service
 *
 * @package    Zepgram\Rest\Service
 * @file       ApiPoolInterface.php
 * @date       28 12 2021 23:15
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Service;

interface ApiPoolInterface
{
    /**
     * @param string $adapterName
     * @param array $rawData
     * @return mixed
     */
    public function execute(string $adapterName, array $rawData = []): mixed;
}
