#PHP Social Media Posts

##Requirements
Succesfully tested on PHP 5.4 & 5.5. It should work on 5.3 but it's not tested.

[APC](http://php.net/manual/en/book.apc.php) is required for caching to work (optional).

##Installation
Include `SocialMedia.class.php` somewhere in your code, then include `Facebook.class.php`, `Twitter.class.php` and `Instagram.class.php`.

##Usage

There are three social media classes that each extend a base `SocialMedia` class. These are `Facebook`, `Twitter` and `Instagram`.

Each class has the same interface for getting posts: `getLatestPosts($start = 0, $count = 1)` which returns a collection of arrays.

Each array contains `text`, `url`, `picture` and `date` properties.

As long as [APC](http://php.net/manual/en/book.apc.php) is enabled then the responses from the social media channels will be cached. The default cache time is 10 mins.

###Examples:
```
// Get latest Facebook post
$facebook = new Facebook([app_key], [app_secret], [facebook_id]);
$facebook_posts = $facebook->getLatestPosts();

// Get last two Twitter posts
$twitter = new Twitter([consumer_key], [consumer_secret], [user_token], [user_secret], [screen_name]);
$twitter_posts = $twitter->getLatestPosts(0,2);

// Get the second-to-last Instagram post
$instagram = new Instagram([app_key], [app_secret], [instagram_id]);
$instagram_posts = $instagram->getLatestPosts(1,1);
```
