<?php

namespace Obullo\Mailer\Transport;

trait MailerTrait
{
    /**
     * Set RFC 822 Date
     *
     * @param string $newDate set custom date
     * 
     * @return string
     */
    public function setDate($newDate = null)
    {
        if ( ! is_null($newDate)) {
            $this->setHeader('Date', $newDate);
            return $newDate;
        }
        $timezone = date("Z");
        $operator = (strncmp($timezone, '-', 1) == 0) ? '-' : '+';
        $abs = abs($timezone);
        $floorTimezone = floor($abs / 3600) * 100 + ($abs % 3600 ) / 60;
        $date = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $operator, $floorTimezone);
        $this->setHeader('Date', $date);
        return $date;
    }

    /**
     * Convert a String to an Array
     *
     * @param mixed $email email data
     * 
     * @return array
     */
    public function strToArray($email)
    {
        if ( ! is_array($email)) {
            if (strpos($email, ',') !== false) {
                $email = preg_split('/[\s,]/', $email, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $email = trim($email);
                settype($email, "array");
            }
        }
        return $email;
    }

    /**
     * Clean Extended Email Address: Joe Smith <joe@smith.com>
     *
     * @param string $email address
     * 
     * @return string
     */
    public function formatEmail($email)
    {
        if ( ! is_array($email)) {
            if (strpos($email, '>') > 0 AND preg_match('/(?<name>.*?)\<(?<email>.*)\>/', $email, $match)) {
                return array('email' => $match['email'], 'name' => $match['name']);
            } else {
                return array('email' => $email, 'name' => null);
            }
        }
        $formatted = array();
        foreach ($email as $address) {
            if (strpos($address, '>') > 0 AND preg_match('/(?<name>.*?)\<(?<email>.*)\>/', $address, $match)) {
                $formatted[] = array('email' => $match['email'], 'name' => $match['name']);
            } else {
                $formatted[] = array('email' => $address, 'name' => null);
            }
        }
        return $formatted;
    }

    /**
     * Clean Extended Email Address: Joe Smith <joe@smith.com>
     *
     * @param string $email address
     * 
     * @return string
     */
    public function cleanEmail($email)
    {
        if ( ! is_array($email)) {
            if (preg_match('/\<(.*)\>/', $email, $match)) {
                return $match['1'];
            } else {
                return $email;
            }
        }
        $cleanEmail = array();
        foreach ($email as $addy) {
            if (preg_match('/\<(.*)\>/', $addy, $match)) {
                $cleanEmail[] = $match['1'];
            } else {
                $cleanEmail[] = $addy;
            }
        }
        return $cleanEmail;
    }

    /**
     * Prep Quoted Printable
     *
     * Prepares string for Quoted-Printable Content-Transfer-Encoding
     * Refer to RFC 2045 http://www.ietf.org/rfc/rfc2045.txt
     *
     * @param string  $str     str
     * @param integer $charlim limit
     * 
     * @return string
     */
    public function prepQuotedPrintable($str, $charlim = '')
    {
        // Set the character limit
        // Don't allow over 76, as that will make servers and MUAs barf
        // all over quoted-printable data
        if ($charlim == '' OR $charlim > '76') {
            $charlim = '76';
        }
        $str = preg_replace("| +|", " ", $str); // Reduce multiple spaces
        $str = preg_replace('/\x00+/', '', $str);         // kill nulls

        if (strpos($str, "\r") !== false) {         // Standardize newlines
            $str = str_replace(array("\r\n", "\r"), "\n", $str);
        }
        // We are intentionally wrapping so mail servers will encode characters
        // properly and MUAs will behave, so {unwrap} must go!
        $str = str_replace(array('{unwrap}', '{/unwrap}'), '', $str);
        $lines = explode("\n", $str);         // Break into an array of lines
        $escape = '=';
        $output = '';
        foreach ($lines as $line) {
            $length = strlen($line);
            $temp = '';
            // Loop through each character in the line to add soft-wrap
            // characters at the end of a line " =\r\n" and add the newly
            // processed line(s) to the output (see comment on $crlf class property)
            for ($i = 0; $i < $length; $i++) {   // Grab the next character
                $char = substr($line, $i, 1);
                $ascii = ord($char);
                // Convert spaces and tabs but only if it's the end of the line
                if ($i == ($length - 1)) {
                    $char = ($ascii == '32' OR $ascii == '9') ? $escape . sprintf('%02s', dechex($ascii)) : $char;
                }
                if ($ascii == '61') {  // encode = signs
                    $char = $escape . strtoupper(sprintf('%02s', dechex($ascii)));  // =3D
                }
                // If we're at the character limit, add the line to the output,
                // reset our temp variable, and keep on chuggin'
                if ((strlen($temp) + strlen($char)) >= $charlim) {
                    $output .= $temp . $escape . $this->crlf;
                    $temp = '';
                }
                // Add the character to our temporary line
                $temp .= $char;
            }
            // Add our completed line to the output
            $output .= $temp . $this->crlf;
        }
        // get rid of extra CRLF tacked onto the end
        $output = substr($output, 0, strlen($this->crlf) * -1);
        return $output;
    }

    /**
     * Prep Q Encoding
     *
     * Performs "Q Encoding" on a string for use in email headers.  It's related
     * but not identical to quoted-printable, so it has its own method
     *
     * @param str  $str  string
     * @param bool $from set to true for processing From: headers
     * 
     * @return string
     */
    public function prepQencoding($str, $from = false)
    {
        $str = str_replace(array("\r", "\n"), array('', ''), $str);
        // Line length must not exceed 76 characters, so we adjust for
        // a space, 7 extra characters =??Q??=, and the charset that we will add to each line
        $limit = 75 - 7 - strlen($this->charset);
        // these special characters must be converted too
        $convert = array('_', '=', '?');
        if ($from === true) {
            $convert[] = ',';
            $convert[] = ';';
        }
        $output = '';
        $temp = '';
        for ($i = 0, $length = strlen($str); $i < $length; $i++) {
            $char = substr($str, $i, 1); // Grab the next character
            $ascii = ord($char);
            if ($ascii < 32 OR $ascii > 126 OR in_array($char, $convert)) { // convert ALL non-printable ASCII characters and our specials
                $char = '=' . dechex($ascii);
            }
            if ($ascii == 32) { // handle regular spaces a bit more compactly than =20
                $char = '_';
            }
            // If we're at the character limit, add the line to the output,
            // reset our temp variable, and keep on chuggin'
            if ((strlen($temp) + strlen($char)) >= $limit) {
                $output .= $temp . $this->crlf;
                $temp = '';
            }
            $temp .= $char; // Add the character to our temporary line
        }
        $str = $output . $temp;
        // wrap each line with the shebang, charset, and transfer encoding
        // the preceding space on successive lines is required for header "folding"
        $str = trim(preg_replace('/^(.*)$/m', ' =?' . $this->charset . '?Q?$1?=', $str));
        return $str;
    }

}

// END MailerTrait
/* End of file MailerTrait.php

/* Location: .Obullo/Mailer/Transport/MailerTrait.php */