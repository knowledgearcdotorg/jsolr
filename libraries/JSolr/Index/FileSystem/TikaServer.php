<?php
/**
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Index\FileSystem;

use \JString as JString;
use \JFactory as JFactory;
use \JLanguageHelper as JLanguageHelper;
use \JArrayHelper as JArrayHelper;

/**
 * A class for extracting metadata, content and other information from a file.
 */
class TikaServer extends Extractor
{
    public function getContentType()
    {
        return $this->getMetadata()->get('Content-Type');
    }

    /**
     * Gets an array of languages associated with this document.
     *
     * In many cases only a two-letter iso language (iso639) is attached to the
     * document. However, JSolr supports both language (iso639) and region
     * (iso3166), E.g. en-AU.
     * This method will attempt to match all the language codes with their
     * language+region counterparts so it is possible that more than one code
     * will be returned for the document.
     *
     * @return array An array of languages associated with the document.
     */
    public function getLanguage()
    {
        $result = $this->getMetadata()->get('language');

        $results = explode("\n", str_replace("\r", "\n", $result));

        $array = array();

        foreach ($results as $value) {
            if ($value) {
                if (JString::strlen($value) == 5) { // assume iso with region
                    $array[] = str_replace('_', '-', $value);
                } elseif (JString::strlen($value) == 2) { // assume iso without region
                    $found = false;
                    $languages = JLanguageHelper::getLanguages();

                    while (($language = current($languages)) && !$found) {
                        $parts = explode('-', $language->lang_code);
                        if ($value == JArrayHelper::getValue($parts, 0)) {
                            if (array_search($language->lang_code, $array) === false) {
                                $array[] = $language->lang_code;
                            }

                            $found = true;
                        }

                        next($languages);
                    }

                    reset($languages);
                }
            }
        }

        // if no languages could be detected, use the system lang.
        if (!count($array)) {
            $array[] = JFactory::getLanguage()->getTag();
        }

        return $array;
    }

    public function getContent()
    {
        return $this->extract('tika');
    }

    public function getMetadata()
    {
        $result = $this->extract('meta', array("Accept"=>"application/json"));
        return new \Joomla\Registry\Registry($result);
    }

    public function getAppPath()
    {
        $appPath = parent::getAppPath();

        if ($appPath) {
            if (substr_compare($appPath, '/', strlen($appPath)- 1) !== 0) {
                $appPath .= '/';
            }
        }

        return $appPath;
    }

    private function extract($endpoint, $headers = array("Accept"=>"text/plain"))
    {
        \JLog::addLogger(array());

        $url = $this->getAppPath().$endpoint;

        \JLog::add('server url: '.$url, \JLog::DEBUG, 'tikaserver');

        $headers = array_merge(array('fileUrl'=>$this->getPathOrUrl()), $headers);

        \JLog::add('server headers: '.print_r($headers, true), \JLog::DEBUG, 'tikaserver');

        $http = \JHttpFactory::getHttp();
        $response = $http->put($url, null, $headers);

        if ($response->code == 200) {
            return $response->body;
        } else {
            throw new \Exception($response->body, $response->code);
        }
    }
}
