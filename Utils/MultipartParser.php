<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 11.5.17
 * Time: 11.04
 */

namespace Garant\FilePreviewGeneratorBundle\Utils;

/**
 * Class MultipartParser
 * @todo Multipart Parser will be develop in ReactPHP v0.8.0
 */
class MultipartParser
{
    /**
     * @see http://www.chlab.ch/blog/archives/webdevelopment/manually-parse-raw-http-data-php
     * @see https://gist.github.com/jas-/5c3fdc26fedd11cb9fb5#file-stream-php
     *
     * Parse raw HTTP request data
     *
     * Pass in $a_data as an array. This is done by reference to avoid copying
     * the data around too much.
     *
     * Any files found in the request will be added by their field name to the
     * $data['files'] array.
     *
     * @param   string $input
     * @param   string $content_type
     *
     * @return  array  Associative array of request data
     */
    public static function parse_raw_http_request($input, $content_type)
    {
        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $content_type, $matches);

        $a_data = [];

        // content type is probably regular form-encoded
        if (!count($matches))
        {
            // we expect regular puts to containt a query string containing data
            parse_str(urldecode($input), $a_data);
            return $a_data;
        }

        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block)
        {
            if (empty($block))
                continue;

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== FALSE)
            {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                if(empty($matches[1]) || empty($matches[2])) {
                    continue;
                }
                $a_data['files'][$matches[1]] = $matches[2];
            }
            // parse all other fields
            else
            {
                if (strpos($block, 'filename') !== FALSE)
                {
                    // match "name" and optional value in between newline sequences
                    preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?[\r|\n]$/s', $block, $matches);
                    preg_match('/Content-Type: (.*)?/', $matches[3], $mime);

                    // strip any headers
                    $encoded_body = $matches[3];
                    $encoded_body_sep = "\r\n\r\n"; //CR+LF pair
                    if(($binary_start = strpos($encoded_body, $encoded_body_sep)) !== false) {
                        $encoded_body = substr($encoded_body, $binary_start + strlen($encoded_body_sep));
                    }
                    $encoded_body = rtrim($encoded_body, "\n\r");

                    // get current system path and create tempory file name & path
                    $path = sys_get_temp_dir().'/php'.substr(sha1(rand()), 0, 6);

                    // write temporary file to emulate $_FILES super global
                    $err = file_put_contents($path, $encoded_body);

                    // Did the user use the infamous &lt;input name="array[]" for multiple file uploads?
                    if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) {
                        $key = $tmp[1];
                    } else {
                        $key = $matches[1];
                    }

                    // Create the remainder of the $_FILES super global
                    $a_data[$key]['name'][] = $matches[2];
                    $a_data[$key]['type'][] = $mime[1];
                    $a_data[$key]['tmp_name'][] = $path;
                    $a_data[$key]['error'][] = ($err === FALSE) ? $err : 0;
                    $a_data[$key]['size'][] = filesize($path);
                }
                else
                {
                    // match "name" and optional value in between newline sequences
                    preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?[\r|\n]$/s', $block, $matches);
                    if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) {
                        $a_data[$tmp[1]][] = trim($matches[2]);
                    } else {
                        $a_data[$matches[1]] = trim($matches[2]);
                    }
                }
            }
        }

        return $a_data;
    }
}