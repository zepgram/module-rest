<?php
/**
 * This file is part of Zepgram\Rest\Exception\Result
 *
 * @package    Zepgram\Rest\Exception\Result
 * @file       HttpException.php
 * @date       04 11 2021 23:22
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Exception\Result;

use Exception;
use Magento\Framework\Phrase;
use Zepgram\Rest\Exception\ExternalException;

class HttpException extends ExternalException
{
    /**
     * @throws ExternalException
     */
    public function __construct(Phrase $phrase, Exception $cause = null, $code = 0)
    {
        parent::__construct($phrase, $cause, $code);
        if ($cause && $cause->getCode() && ($code === 0 || $code === null)) {
            $code = $cause->getCode();
        }

        throw new ExternalException(__($phrase), $cause, $code);
    }
}
