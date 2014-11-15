<?php

class Instagram extends SocialMedia
{

    private $app_key;
    private $app_secret;
    private $instagram_id;
    private $endpoint;
    protected $cache_key = "instagram";

    function __construct($app_key, $app_secret, $instagram_id, $endpoint = "users")
    {
        $this->app_key      = $app_key;
        $this->app_secret   = $app_secret;
        $this->instagram_id = $instagram_id;
        $this->endpoint     = $endpoint;

        $this->cache_key = $this->cache_key . $instagram_id . $endpoint;
    }

    public function loadPosts($start = 0, $count = 1)
    {
        $latest_posts = array();
        $url          = "https://api.instagram.com/v1/" . $this->endpoint . "/" . $this->instagram_id . "/media/recent/?client_id=" . $this->app_key . "&count=" . ($start + $count);
        $data         = @json_decode(file_get_contents($url), true);

        if (isset($data["data"])) {

            $i = 0;
            $j = 0;

            foreach ($data["data"] as $item) {
                if ($i >= $start && $j < $count) {

                    $latest_posts[] = array(
                        "date" => $item["created_time"],
                        "text" => isset($item["caption"]) ? $this->addUrls($item["caption"]["text"]) : false,
                        "url" => $item["link"],
                        "picture" => isset($item["images"]) ? $item["images"]["standard_resolution"]["url"] : false
                    );
                    $j++;
                }
                $i++;
            }
        }

        return $latest_posts;
    }

    /*
     * Adds links to usernames but not hashtags
     */
    protected function addUrls($text)
    {
        // The Regular Expression filter
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        // Check if there is a url in the text

        // force http: on www.
        $text = preg_replace("@www\.@", "http://www.", $text);
        // eliminate duplicates after force
        $text = preg_replace("@http://http://www\.@", "http://www.", $text);
        $text = preg_replace("@https://http://www\.@", "https://www.", $text);

        if (preg_match($reg_exUrl, $text, $url)) {
            // make the urls hyper links
            $text = preg_replace($reg_exUrl, '<a href="' . $url[0] . '" rel="nofollow" target="_blank">' . $url[0] . '</a>', $text);
        }

        $text = preg_replace("/@(\w+)/", '<a href="http://instagram.com/$1" target="_blank">@$1</a>', $text);

        return $text;
    }


}