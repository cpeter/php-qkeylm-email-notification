<?php

namespace Cpeter\PhpQkeylmEmailNotification\Parser;

interface IParser {
    /**
     * @param $subject string
     * @param $options array
     * @return mixed
     */
    public function parse(&$subject, &$options);

    /**
     * @param $name string
     * @return booleans
     */
    public function isParser($name);
}