Thanks
============

Plugin for [CMF Cotonti](https://www.cotonti.com) that let users thank each other .

Authors: [Vladimir Sibirov](https://github.com/trustmaster) ([Trustmaster](https://www.cotonti.com/users/Trustmaster)), [Alexey Kalnov](https://github.com/Alex300) ([Alex300](https://www.cotonti.com/users/Alex300)), Cotonti Team

Plugin page:  
https://www.cotonti.com/extensions/community-social/thanks-plugin

## Features
- Just a simple "Thank you!" button, no negative karmas and karma wars, no offense.
- Users can thank each other for pages, comments and forum posts.
- Tags to output thanks count for a user. A page listing all thanks received by a user.
- The number of thanks per user and per day are limited.

## Installation

- Requires Cotonti version 0.9.26 beta or higher
- Download and unpack the plugin to plugins/thanks.
- Install and configure the plugin in Administration panel.
- Add the necessary tags to your page.tpl, forums.posts.tpl, users.details.tpl and other templates, see example below.

Example tags in page.tpl:
```
<!-- Adds a thank you button -->
{PHP|thanks_itemAddThankWidget('page', {PAGE_ID})}
 
<!-- Adds a widget with a list of those who thanked for the page -->
{PHP|thanks_itemWhoThankedWidget('page', {PAGE_ID})}
```

Example tags in forums.posts.tpl:
```
<!-- Number of thanks to the user -->
{PHP|thanks_userCountWidget({FORUMS_POSTS_ROW_USER_ID}, {FORUMS_POSTS_ROW_USER_THANKS})}
 
<!-- Thank you button -->
{PHP|thanks_itemAddThankWidget('forumPost', {FORUMS_POSTS_ROW_ID})}
 
<!-- Widget with a list of those who thanked for the post --> 
{PHP|thanks_itemWhoThankedWidget('forumPost', {FORUMS_POSTS_ROW_ID})}
```

Example tags in comments.tpl:
```
<!-- Number of thanks to the user -->
{PHP|thanks_userCountWidget({COMMENTS_ROW_AUTHOR_ID}, {COMMENTS_ROW_AUTHOR_THANKS})}
 
<!-- Thank you button -->
{PHP|thanks_itemAddThankWidget('comment', {COMMENTS_ROW_ID})}
 
<!-- Widget with a list of those who thanked for the comment --> 
{PHP|thanks_itemWhoThankedWidget('comment', {COMMENTS_ROW_ID})}
```

Example tags in users.details.tpl:
```
<tr>
    <td>{PHP.L.thanks_thanked}:</td><br>
    <td><a href="{USERS_DETAILS_THANKS_URL}" title="{PHP.L.thanks_for_user}">{USERS_DETAILS_THANKS_TIMES}</a></td>
</tr>
```

You can also use `{*THANKS}`, `{*THANKS_URL}` and `{*THANKS_TIMES}` tags wherever all user-specific tags are available (* stands for a tag prefix specific to that area or block).
