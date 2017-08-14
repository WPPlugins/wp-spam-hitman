=== Plugin Name ===
Contributors: shw
Donate link: http://shwsite.org/wp-spam-hitman/
Tags: comments, spam
Requires at least: 2.0.2
Tested up to: 2.1
Stable tag: 0.6

WP Spam Hitman allows you to fight with spam using set of patterns and hitpoints. It also allows you to decide what's going to happen to the spam

== Description ==

WP Spam Hitman is based on set of rules and concept of hitpoint. With right set of rules and hitpoint level you can eliminate most or even all of the spam on you blog.
It works in 3 modes - Delete, Moderation, Spam and is quite customizable.
It allows to set unlimited set of rules - both - simple words and regular expression patterns. It's simple but powerfull tool for fighting with spam.

== Installation ==

1. Download the latest version of WP Spam Hitman
1. Unzip it
1. Upload `wp-spam-hitman` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Set up some options on Options -> WP Spam Hitman section of admin panel

== Frequently Asked Questions ==

= How do I set up rules? =

You simply write rules in WP Spam Hitman options in Rules section.
Every rule in seperate line. You can use words of regular expressions

= Is one rule is one hitpoint =

No - if your rule is for instance 'p0rn' it gets hitpoint every time Spam Hitman finds it in comment. So if the comment if 'p0rn p0rn and one more p0rn' - it gets 3 hitpoints.

= How does the plugin know that I use word/regular expression =

By default - every rule is a word. But if you start (and of course end) a rule with / or # character - it will be treated as regular expression.
Small hint here - word rules work faster - so if you can write a rule as word(s) - do so.

= Do I have to write words several times if they are case-sensitive? =

No - words are case-insensitive so 'p0rn' will find 'p0rn' as well as 'P0rN'.
On the other hand - regular expressions are case sensitive - so you have to use regex modifier (/i) in rule to match both - upper and lower cases.

= How do the modes work? =

* Delete - simply deletes comment when it gets a number of hitpoints you have set up.
* Moderation - puts comment into moderation list but doesn't send you mail so you can check it anytime you want (and not be pissed by new mails comming all the time).
* Spam - it marks message as Spam. It is still in wordpress database but you cannot see it from admin panel.

= I don't the spam message at all - how can I turn it of? =

Go to WP Spam Hitman options and delete the message. If message is blank - WP Spam Hitman will not display it to spammer.

= What are the best rules? =

There are no "the best rules" - it depends on blog. For instance I have my blog written in Polish (well - mostly) so for instance some popular words in spanish - they probably spam.
There are some trends, some popular spammer words/expressions. Most of your visitors will not tell you to 'enlarge your penis' and provide you with a link - so the rules could be 'enlarge' and 'penis'.
If you're not sure your rules works - use options moderation for some time and check if all of the messages it caches are spam.

= Can I use some other plugin with WP Spam Hitman =

The short anwser is - yes. WP Spam Hitman should work well with other plugins - not only with the spam ones. On my blog I use Akismet, WP Gateway and WP Spam Admin - and they work just fine.
If you find one which doesn't work - be sure to tell me about it and I will try to fix it.
But please remember that WP Spam Hitman has high priority so if it will find spam and for instance - delete the comment - it will probably not go to other plugins!

== Changes ==

= 0.7 =

* added - Message '%tags%' - you can place information from comment in your message for spammers
* fixed - bug - now you can change message for spammers
* change - default message for spammers changed to less offensive

= 0.6 =

* fixed - bug with displaying message on modes moderation/spam
* added - version number displayed on the right of plugin name in options
* added - version checker - it checks if you have the newest version available and if not - tells you about it providing link to the newest one

= 0.5 =

First public release.