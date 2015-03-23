<?php

namespace Obullo\Mailer;

/**
 * Text Class
 * 
 * @category  Text
 * @package   Mailer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/mailer
 */
class Text
{
    /**
     * Newline sign
     * 
     * @var string
     */
    public $newline = '';

    /**
     * Constructor
     * 
     * @param string $newline sign
     */
    public function __construct($newline)
    {
        $this->newline = $newline;
    }

    /**
     * Word Wrap
     *
     * @param string  $str     str
     * @param integer $charlim limit
     * 
     * @return string
     */
    public function wordWrap($str, $charlim = "76")
    {
        $str = preg_replace("| +|", " ", $str);         // Reduce multiple spaces

        // Standardize newlines
        if (strpos($str, "\r") !== false) {
            $str = str_replace(array("\r\n", "\r"), "\n", $str);
        }
        // If the current word is surrounded by {unwrap} tags we'll
        // strip the entire chunk and replace it with a marker.
        $unwrap = array();
        if (preg_match_all("|(\{unwrap\}.+?\{/unwrap\})|s", $str, $matches)) {
            for ($i = 0; $i < count($matches['0']); $i++) {
                $unwrap[] = $matches['1'][$i];
                $str = str_replace($matches['1'][$i], "{{unwrapped" . $i . "}}", $str);
            }
        }
        // Use PHP's native function to do the initial wordwrap.
        // We set the cut flag to false so that any individual words that are
        // too long get left alone.  In the next step we'll deal with them.
        $str = wordwrap($str, $charlim, "\n", false);

        $output = $this->wrapLines($str, $charlim);

        if (count($unwrap) > 0) { // Put our markers back
            foreach ($unwrap as $key => $val) {
                $output = str_replace("{{unwrapped" . $key . "}}", $val, $output);
            }
        }
        return $output;
    }

    /**
     * Wrap lines
     * 
     * @param string  $str     str
     * @param integer $charlim limit
     * 
     * @return void
     */
    protected function wrapLines($str, $charlim)
    {
        // Split the string into individual lines of text and cycle through them
        $output = "";
        foreach (explode("\n", $str) as $line) {
            // Is the line within the allowed character count?
            // If so we'll join it to the output and continue
            if (strlen($line) <= $charlim) {
                $output .= $line . $this->newline;
                continue;
            }
            $temp = '';
            while ((strlen($line)) > $charlim) {
                if (preg_match("!\[url.+\]|://|wwww.!", $line)) {  // If the over-length word is a URL we won't wrap it
                    break;
                }
                $temp .= substr($line, 0, $charlim - 1); // Trim the word down
                $line = substr($line, $charlim - 1);
            }
            // If $temp contains data it means we had to split up an over-length
            // word into smaller chunks so we'll add it back to our current line
            if ($temp != '') {
                $output .= $temp . $this->newline . $line;
            } else {
                $output .= $line;
            }
            $output .= $this->newline;
        }
        return $output;
    }

}

// END Text class

/* End of file Text.php */
/* Location: .Obullo/Mailer/Text.php */