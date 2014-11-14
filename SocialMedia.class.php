<?php

abstract class SocialMedia
{

    protected $isCache = true;

    protected $cache_key;

    // Seconds in the cache
    private $cache_length = 600;

    /**
     * @param boolean $isCache
     */
    public function setIsCache($isCache)
    {
        $this->isCache = $isCache;
    }

    /**
     * @return boolean
     */
    public function getIsCache()
    {
        return $this->isCache;
    }

    /**
     * @param int $cache_length
     */
    public function setCacheLength($cache_length)
    {
        $this->cache_length = $cache_length;
    }

    /**
     * @return int
     */
    public function getCacheLength()
    {
        return $this->cache_length;
    }

    /**
     * @param string $cache_key
     */
    public function setCacheKey($cache_key)
    {
        $this->cache_key = $cache_key;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cache_key;
    }

    /**
     * The main method for getting posts that should be called from external scripts
     * @param int $start
     * @param int $count
     * @return bool|mixed
     *
     */
    public function getLatestPosts($start = 0, $count = 1)
    {
        $latest_posts = false;
        $apc = (extension_loaded('apc') && ini_get('apc.enabled'));
        $cache_enabled = ($apc && $this->getIsCache());

        if ($cache_enabled) {

            // Look in cache
            $cache_key = $this->cache_key . $start . $count;
            $latest_posts = apc_fetch($cache_key);
        }

        if (!$latest_posts) {

            $latest_posts = $this->loadPosts($start, $count);

            if ($cache_enabled) {
                // Cache for 10 minutes
                apc_store( $cache_key, $latest_posts, $this->getCacheLength() );
            }
        }

        // Add nice date e.g About a minute ago
        foreach ($latest_posts as $index => $post) {
            $latest_posts[$index]["date"]  = $this->timeSince($post["date"]);
        }

        return $latest_posts;

    }

    public abstract function loadPosts($start = 0, $count = 1);

    /**
     * Create a nice string telling the user how long ago this was post was made
     * @param $timestamp
     * @return string
     */
    protected function timeSince($timestamp)
    {
        if (defined("ICL_LANGUAGE_CODE") && ICL_LANGUAGE_CODE == "fr") {
            $timePrefix = "Il y a ";
            $timeSuffix = "";
            // Common time periods as an array of arrays
            $periods = array(
                array(60 * 60 * 24 * 365, 'ans'),
                array(60 * 60 * 24 * 30, 'mois'),
                array(60 * 60 * 24 * 7, 'semaine'),
                array(60 * 60 * 24, 'jour'),
                array(60 * 60, 'heure'),
                array(60, 'minute'),
            );
        } else {
            $timePrefix = "";
            $timeSuffix = " ago";
            // Common time periods as an array of arrays
            $periods = array(
                array(60 * 60 * 24 * 365, 'year'),
                array(60 * 60 * 24 * 30, 'month'),
                array(60 * 60 * 24 * 7, 'week'),
                array(60 * 60 * 24, 'day'),
                array(60 * 60, 'hour'),
                array(60, 'minute'),
            );
        }

        $today = time();
        $since = $today - $timestamp; // Find the difference of time between now and the past

        // Loop around the periods, starting with the biggest
        for ($i = 0, $j = count($periods);$i < $j;$i ++) {
            $seconds = $periods[$i][0];
            $name    = $periods[$i][1];

            // Find the biggest whole period
            if (($count = floor($since / $seconds)) != 0) {
                break;
            }
        }

        if ($count == 1) {
            $output = $timePrefix . '1 ' . $name;
        } else {
            if ($name == "mois" || $name == "ans") {
                //don't add an S for these two french times
                $output = $timePrefix . $count . " " . $name;
            } else {
                $output = $timePrefix . $count . " " . $name . "s";
            }
        }

        if ($i + 1 < $j) {
            // Retrieving the second relevant period
            $seconds2 = $periods[$i + 1][0];
            $name2    = $periods[$i + 1][1];

            // Only show it if it's greater than 0
            if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
                if ($name2 == "mois" || $name2 == "ans") {
                    $output .= ($count2 == 1) ? ', 1 ' . $name2 : ", $count2 {$name2}";
                } else {
                    $output .= ($count2 == 1) ? ', 1 ' . $name2 : ", $count2 {$name2}s";
                }
            }
        }

        $output .= $timeSuffix;

        return $output;
    }

    /**
     * Add links to any urls in the text
     * @param $text
     * @return mixed
     */
    protected function addUrls($text)
    {
        // The Regular Expression filter
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        // Check if there is a url in the text

        // force http: on www.
        $text = preg_replace( "@www\.@", "http://www.", $text );
        // eliminate duplicates after force
        $text = preg_replace( "@http://http://www\.@", "http://www.", $text );
        $text = preg_replace( "@https://http://www\.@", "https://www.", $text );

        if(preg_match($reg_exUrl, $text, $url)) {
            // make the urls hyper links
            $text = preg_replace($reg_exUrl, '<a href="'.$url[0].'" rel="nofollow" target="_blank">'.$url[0].'</a>', $text);
        }

        return $text;
    }

}