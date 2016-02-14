<?php

namespace Cpeter\PhpQkeylmEmailNotification\Parser\Plugins;

use Cpeter\PhpQkeylmEmailNotification\Parser\IParser;

class ParserRegexp implements IParser
{

    /**
     * @param $subject string
     * @param $options array
     * @return mixed
     */
    public function parse(&$subject, &$options)
    {
        // do we qualify for this content

        $regexp = $options['regexp'];
        $version_found = preg_match($regexp, $subject, $match);

        if ($version_found === 1 && !empty($match[1])) {
            return $match[1];
        }

        return false;
    }

    public function isParser($name){
        return $name == str_replace( __NAMESPACE__ .'\\', '', __CLASS__);
    }

}