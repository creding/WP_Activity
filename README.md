*WP_Activity


A Plugin in Progress: The purpose is to gather a WordPress site's activity and display it in a linear fashion much like a news feed. Currently I am pulling data from several Custom Post Types for the home page of www.makemedicinebetter.org. This enables users to see where people are active on the site and introduces them to the site as a commuinity without much digging around.

The query selects the posts and comments, converst the post date to a unix timestamp, shortens the content of the post to as many words as you wish, adds comments with a title like "RE:Post Title" then the body of the comment and the content, essentially comments become posts.



