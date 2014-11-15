<?php
/**
 * Created by PhpStorm.
 * User: jamesplayer
 * Date: 2014-09-30
 * Time: 1:26 PM
 */

class Facebook extends SocialMedia {

    private $app_key;
    private $app_secret;
    private $facebook_id;
    protected $cache_key = "facebook";

    function __construct($app_key, $app_secret, $facebook_id)
    {
        $this->app_key     = $app_key;
        $this->app_secret  = $app_secret;
        $this->facebook_id = $facebook_id;

        $this->cache_key = $this->cache_key . $facebook_id;
    }

    public function loadPosts($start = 0, $count = 1)
    {
        $options = "?access_token=" . $this->app_key . "|" . $this->app_secret;
        $json = file_get_contents("https://graph.facebook.com/v2.1/" . $this->facebook_id. "/posts" . $options);

        $fb_data = json_decode($json, true);

        $latest_posts = array();

        // Loop through until we find something with a message, since that means that we actually found something meaningful
        if (isset($fb_data["data"])) {

            $i = 0;
            $j = 0;

            foreach($fb_data["data"] as $item) {
                if (isset($item["message"])) {

                    if ($i >= $start && $j < $count) {
                        $fb_picture = false;

                        if (isset($item["picture"]) && isset($item["object_id"])) {
                            $object_id = $item["object_id"];

                            $json = file_get_contents("https://graph.facebook.com/v2.1/" . $object_id . "/" . $options);
                            $picture_arr = json_decode($json, true);

                            if (isset($picture_arr["images"]) && isset($picture_arr["images"][0])) {
                                $largest_img = $picture_arr["images"][0];
                                $fb_picture = $largest_img["source"];
                            }
                        }

                        $latest_posts[] = array(
                            "date" => strtotime($item["created_time"]),
                            "text" => addFbUrls($item["message"]),
                            "url" => $item["link"],
                            "picture" => $fb_picture
                        );

                        $j++;
                    }
                    $i++;
                }
            }
        }

        return $latest_posts;
    }



} 