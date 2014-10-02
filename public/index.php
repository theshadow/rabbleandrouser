<?php
/**
 * Created by IntelliJ IDEA.
 * User: xanderguzman
 * Date: 10/1/14
 * Time: 5:12 PM
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

use RabbleAndRouser\Auth\Service as AuthService;
use RabbleAndRouser\User\Service as UserService;
use RabbleAndRouser\Wall\Service as WallService;
use RabbleAndRouser\User;
use RabbleAndRouser\Wall\Post;

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__)) . '/..');

require_once APPLICATION_PATH . '/vendor/autoload.php';

$app = new Silex\Application();
$app->register(new TwigServiceProvider(), array(
    'twig.path' => APPLICATION_PATH . '/views',
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => APPLICATION_PATH . '/database.db',
    ),
));

$app->register(new Silex\Provider\SessionServiceProvider());

$app['rabbleandrouser.user.service'] = function () use ($app) {
    $service = new UserService();
    $service->setDb($app['db']);
    return $service;
};

$app['rabbleandrouser.auth.service'] = function () use($app) {
    $service = new AuthService();
    $service->setDb($app['db']);
    return $service;
};

$app['rabbleandrouser.wall.service'] = function () use($app) {
    $service = new WallService();
    $service->setDb($app['db']);
    return $service;
};

// oh god this is an ugly hack I should put it somewhere else
function retrieveAllPosts($app, $sort)
{
    /** @var WallService $wallService */
    $wallService = $app['rabbleandrouser.wall.service'];

    $posts = $wallService->retrieveAll($sort);

    // this is a tad inefficient, should probably create a dependency between the user and post services.
    $userIds = array_reduce($posts, function ($carry, $item) {
        /** @var Post $item */
        $carry[] = $item->getAuthorId();
        return $carry;
    }, array());

    // remove any possible duplicates
    $userIds = array_unique($userIds);

    // request all the users for all the posts
    $users = $app['rabbleandrouser.user.service']->retrieveUsersById($userIds);

    // turn users into an associative array so we can quickly reference them.
    $users = array_reduce($users, function ($carry, $user) {
        /** @var User $user */
        $carry[$user->getId()] = $user;
        return $carry;
    }, array());

    // reduce all of the data into a merged set of data
    $posts = array_reduce($posts, function ($carry, $post) use ($users) {
        /** @var Post $post */
        /** @var User $user */
        $user = $users[$post->getAuthorId()];

        $addressHash = md5(strtolower(trim($user->getEmail())));

        $emailLink = false;
        $authorLink = null;
        if (!is_null($user->getEmail())) {
            $emailLink = true;
            $authorLink = 'mailto:' . $user->getEmail();
        }

        if (!is_null($user->getWebsite())) {
            $authorLink = '//' . $user->getWebsite();
        }

        $dateTime = new \DateTime("@" . $post->getCreated());

        $carry[] = array(
            'created' => $dateTime->format('D, d M y H:i:s'),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'author_link_is_email', $emailLink,
            'author_link' => $authorLink,
            'author_website' => $user->getWebsite(),
            'author_email' => $user->getEmail(),
            'author_gravatar_url' => '//www.gravatar.com/avatar/' . $addressHash . '?d=monsterid&s=64',
            'author' => $user->getUsername(),
        );

        return $carry;
    }, array());

    return $posts;
}

/**
 * GET for / which renders the posts
 */
$app->get('/', function (Request $request) use ($app) {

    $sort = $request->query->get('sort');

    if (is_null($sort) || !in_array($sort, WallService::$VALID_SORT_VALUES)) {
        $sort = WallService::SORT_DESC;
    }

    // this is a hack it really should be an event hook so that the system automatically populates this and doesn't
    // have to be populated for each action that cares
    $authenticated = false;
    if (is_array($app['session']->get('user'))) {
        $authenticated = true;
    }

    $posts = retrieveAllPosts($app, $sort);

    return $app['twig']->render('index.twig', array(
        'posts' => $posts,
        'authenticated' => $authenticated,
        'sort' => $sort,
    ));
});

/**
 * GET for /signup which renders a sign up form
 */
$app->get('/signup', function () use ($app) {
    return $app['twig']->render('signup.twig', array());
});

$app->post('/login', function (Request $request) use ($app) {
    $email = $request->get('email');
    $password = $request->get('password');

    $authenticated = $app['rabbleandrouser.auth.service']->authenticate($email, $password);

    if (!$authenticated) {
        return $app->redirect('/');
    }

    // @todo trigger error if null assume because we authenticated things are fine and dandy
    $user = $app['rabbleandrouser.user.service']->retrieveByEmail($email);

    $app['session']->set('user', array('username' => $user->getUsername()));

    return $app->redirect('/');
});

$app->get('/logout', function () use ($app) {
    if (is_array($app['session']->get('user')) && isset($app['session']->get('user')['username'])) {
        $app['session']->invalidate();
    }
    return $app->redirect('/');
});

/**
 * POST for the /users resource, which creates a new user
 */
$app->post('/users', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');
    $email = $request->get('email');
    $website = $request->get('website');

    $username = empty($username) ? 'Unnamed One' : $username;
    $website = empty($website) ? null : $website;

    $valid = array_reduce(array($email, $password, $website), function ($carry, $item) {
        if (empty($item)) {
            return false;
        }
        return true;
    }, true);

    if ($valid === false) {
        return new Response('Invalid user input', 400);
    }

    $password = $app['rabbleandrouser.auth.service']->hashPassword($password);

    $user = new User();
    $user->setEmail($email)
        ->setUsername($username)
        ->setWebsite($website);

    $app['rabbleandrouser.user.service']->create($user, $password);

    return $app->redirect('/');
});

$app->post('/posts', function (Request $request) use($app) {
    $acceptHeader = $request->headers->get('accept');
    if (!is_array($acceptHeader)) {
        $acceptHeader = array($acceptHeader);
    }

    $sort = $request->query->get('sort');

    if (is_null($sort) || !in_array($sort, WallService::$VALID_SORT_VALUES)) {
        $sort = WallService::SORT_DESC;
    }

    $isAjaxRequest = in_array('application/json', $acceptHeader);

    if (!is_array($app['session']->get('user')) || !isset($app['session']->get('user')['username'])) {
        return new Response('Action is forbidden, must be logged in', 403);
    }

    $title = $request->get('title');
    $content = $request->get('content');
    $username = $app['session']->get('user')['username'];

    /** @var User $user */
    // should check for null value here in the event no user is found
    $user = $app['rabbleandrouser.user.service']->retrieveByUsername($username);

    $post = new Post();
    $post->setTitle($title)
        ->setContent($content)
        ->setAuthorId($user->getId());

    $app['rabbleandrouser.wall.service']->create($post);

    // this isn't an ajax request, return to the home page
    if (!$isAjaxRequest) {
        return $app->redirect('/');
    }

    //otherwise we're lazy bastards and just want to grab them all again to render.
    $posts = retrieveAllPosts($app, $sort);

    $response = new Response();
    $response->headers->set('accept', array('application/json'));
    $response->setContent(json_encode($posts));

    return $response;
});

$app->run();