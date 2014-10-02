/**
 * Created by xanderguzman on 10/2/14.
 */
$(function () {


    // I should have used backbone :P and a template engine
    $('form button').bind('click', function (e) {
        var title = $('#title').val();
        var content = $('#content').val();

        // if time permits add a modal
        if (!title.length || !content.length) {
            alert('All fields must be filled');
        }

        $.ajax({
            type: "POST",
            url: "/posts",
            data: {
                title: title,
                content: content
            },
            headers: {
                Accept:"application/json"
            }
        }).success(function (response) {
            // showing only new items would be nice, but we're being lazy here too.
            response = $.parseJSON(response);

            var panel = $('.media.panel-body');

            if (response.length === 0) {
                panel.empty();
                panel.append(
                    $('<div class="media-body"><h4>No posts yet, why not sign up and add one?</h4></div>')
                );
            } else {
                var posts = [];

                // be ye warned, output should be escaped to prevent XSS
                $.each(response, function (k, post) {
                    var link,
                        body,
                        author;

                    if (post.author_email.length) {
                        link = $(
                            '<a class="pull-left" href="#">'
                            + '<img class="media-object" src="' + post.author_gravatar_url + '" alt="' + post.author_email + '" />'
                            + '</a>'
                        );
                    }

                    if (post.author_link.length) {
                        var target = '';
                        if (!post.author_link_is_email) {
                            target = ' target="_blank"';
                        }

                        author = '<a href="' + post.author_link + '"' + target + '>' + post.author + '</a>';
                    } else {
                        author = post.author;
                    }

                    body = $('<div class="media-body"></div>');
                    body.append($('<h4 class="media-heading">' + post.title + '</h4>'));
                    body.append($('<h5 class="media-author">by ' + author + ' on ' + post.created + '</h5>'))
                    body.append('<p>' + post.content + '</p>');

                    link && posts.push(link);
                    posts.push(body);
                });

                panel.empty();
                panel.append(posts);
            }

            $("form input[type=text], textarea").val("");
        });

        e.preventDefault();
        return false;
    });
});