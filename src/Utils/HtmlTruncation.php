<?php
namespace App\Utils;

class HtmlTruncation
{
    public static function truncate($text, $length = 200, $ending = '...')
    {
        if (strlen(strip_tags($text)) <= $length) {
            return $text;
        }

        $totalLength = strlen($ending);
        $openTags = [];
        $truncate = '';

        preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $tags, PREG_SET_ORDER);
        foreach ($tags as $tag) {
            if (!empty($tag[1])) {
                if (preg_match('/^<\/(\w+)/', $tag[1], $closeTag)) {
                    $pos = array_search($closeTag[1], $openTags);
                    if ($pos !== false) {
                        unset($openTags[$pos]);
                    }
                } elseif (preg_match('/^<(\w+)[^>]*>/', $tag[1], $openTag)) {
                    array_unshift($openTags, strtolower($openTag[1]));
                }
                $truncate .= $tag[1];
            }

            $contentLength = strlen(preg_replace('/&[0-9a-z]{1,6};|&#[0-9]{1,6};/i', ' ', $tag[2]));
            if ($totalLength + $contentLength > $length) {
                $left = $length - $totalLength;
                $entitiesLength = 0;
                if (preg_match_all('/&[0-9a-z]{1,6};|&#[0-9]{1,6};/i', $tag[2], $entities, PREG_OFFSET_CAPTURE)) {
                    foreach ($entities[0] as $entity) {
                        if ($entity[1] + 1 - $entitiesLength <= $left) {
                            $left--;
                            $entitiesLength += strlen($entity[0]);
                        } else {
                            break;
                        }
                    }
                }

                $truncate .= substr($tag[2], 0, $left + $entitiesLength);
                break;
            } else {
                $truncate .= $tag[2];
                $totalLength += $contentLength;
            }

            if ($totalLength >= $length) {
                break;
            }
        }

        $truncate .= $ending;

        foreach ($openTags as $tag) {
            $truncate .= '</' . $tag . '>';
        }

        return $truncate;
    }
}