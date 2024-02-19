<?php
/**
 * This file is part of Zepgram\Rest\Model
 *
 * @package    Zepgram\Rest\Model
 * @file       HttpClient.php
 * @date       02 18 2024 23:28
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2024 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Model;

class AdapterNameResolver
{
    /**
     * @param string $requestAdapter
     * @return string
     */
    public function getAdapterName(string $requestAdapter): string
    {
        $string = trim($requestAdapter);
        $string = preg_replace('/[^a-zA-Z0-9]/', '_', $string);
        $parts = explode('_', $string);
        $lastPartIndex = count($parts) - 1;
        $parts[$lastPartIndex] = preg_replace('/(?<=\\w)([A-Z])/', '_$1', $parts[$lastPartIndex]);
        $string = implode('_', $parts);
        $string = preg_replace('/_{2,}/', '_', $string);

        return strtolower($string);
    }
}
