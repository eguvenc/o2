<?php

namespace Obullo\Utils;

/**
 * Uri Utilities
 * 
 * @category  Utilities
 * @package   Uri
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/utils
 */
Class Uri
{
    /**
     * Container
     *
     * @var object
     */
    public $c;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->uri = $c->load('uri');
    }

    /**
     * Fetch the entire Re-routed URI string
     *
     * @return string
     */
    public function getRoutedUriString()
    {
        return '/' . implode('/', $this->getRoutedSegmentArray()) . '/';
    }

    /**
     * Segment Array
     * 
     * @return array
     */
    public function getSegmentArray()
    {
        return $this->uri->segments;
    }

    /**
     * Total number of segments
     *
     * @return integer
     */
    public function getTotalSegments()
    {
        return sizeof($this->uri->segments);
    }

    /**
     * Routed Segment Array
     *
     * @return array
     */
    public function getRoutedSegmentArray()
    {
        return $this->uri->rsegments;
    }

    /**
     * Total number of routed segments
     *
     * @return integer
     */
    public function getTotalRoutedSegments()
    {
        return sizeof($this->uri->rsegments);
    }

    /**
     * Generate a key value pair from the URI string
     *
     * This function generates and associative array of URI data starting
     * at the supplied segment. For example, if this is your URI:
     *
     *    example.com/user/search/name/joe/location/UK/gender/male
     *
     * You can use this function to generate an array with this prototype:
     *
     * array (
     *            name => joe
     *            location => UK
     *            gender => male
     *         )
     *
     * @param integer $number  the starting segment number
     * @param array   $default an array of default values
     * 
     * @return array
     */
    public function getUriToAssoc($number = 3, $default = array())
    {
        return $this->uriToAssoc($number, $default, 'segment');
    }

    /**
     * Generate a URI string from an associative array
     *
     * @param array $array an associative array of key / values
     * 
     * @return array
     */
    public function getAssocToUri($array)
    {
        $temp = array();
        foreach ((array) $array as $key => $val) {
            $temp[] = $key;
            $temp[] = $val;
        }
        return implode('/', $temp);
    }

    /**
     * Identical to above only it uses the re-routed segment array
     * 
     * @param integer $number  integer
     * @param array   $default array
     * 
     * @return array
     */
    public function getRoutedUriToAssoc($number = 3, $default = array())
    {
        return $this->uriToAssoc($number, $default, 'routedSegment');
    }

    /**
     * Fetch a URI Segment and add a trailing slash
     *
     * @param integer $number number
     * @param string  $where  trailing
     * 
     * @return string
     */
    public function getSlashSegment($number, $where = 'trailing')
    {
        return $this->slashSegment($number, $where, 'segment');
    }

    /**
     * Fetch a URI Segment and add a trailing slash
     *
     * @param integer $number number
     * @param string  $where  trailing
     * 
     * @return string
     */
    public function getSlashRoutedSegment($number, $where = 'trailing')
    {
        return $this->slashSegment($number, $where, 'routedSegment');
    }

    /**
     * Fetch a URI Segment and add a trailing slash - helper function
     * 
     * @param integer $number number
     * @param string  $where  trailing
     * @param string  $which  segment function
     * 
     * @return string
     */
    protected function slashSegment($number, $where = 'trailing', $which = 'segment')
    {
        if ($where == 'trailing') {
            $trailing = '/';
            $leading = '';
        } elseif ($where == 'leading') {
            $leading = '/';
            $trailing = '';
        } else {
            $leading = '/';
            $trailing = '/';
        }
        if ($which == 'segment' OR $which == 'routedSegment') {
            return $leading . $this->uri->$which($number) . $trailing;
        }
        return $leading . $this->$which($number) . $trailing;
    }

    /**
     * Generate a key value pair from the URI string or Re-routed URI string
     *
     * @param integer $number  the starting segment number
     * @param array   $default an array of default values
     * @param string  $which   which array we should use
     * 
     * @return array
     */
    protected function uriToAssoc($number = 3, $default = array(), $which = 'segment')
    {
        if ($which == 'segment') {
            $totalSegments = 'getTotalSegments';
            $segmentArray  = 'getSegmentArray';
        } else {
            $totalSegments = 'getTotalRoutedSegments';
            $segmentArray  = 'getRoutedSegmentArray';
        }
        if ( ! is_numeric($number)) {
            return $default;
        }
        if (isset($this->uri->keyval[$number])) {
            return $this->uri->keyval[$number];
        }
        if ($this->$totalSegments() < $number) {
            if (count($default) == 0) {
                return array();
            }
            $retval = array();
            foreach ($default as $val) {
                $retval[$val] = false;
            }
            return $retval;
        }
        $segments = array_slice($this->$segmentArray(), ($number - 1));
        $i = 0;
        $lastval = '';
        $retval = array();
        foreach ($segments as $seg) {
            if ($i % 2) {
                $retval[$lastval] = $seg;
            } else {
                $retval[$seg] = false;
                $lastval = $seg;
            }
            $i++;
        }
        if (count($default) > 0) {
            foreach ($default as $val) {
                if ( ! array_key_exists($val, $retval)) {
                    $retval[$val] = false;
                }
            }
        }
        $this->uri->keyval[$number] = $retval;  // Cache the array for reuse
        return $retval;
    }

}

// END Uri Class
/* End of file Uri.php

/* Location: .Obullo/Utils/Uri.php */