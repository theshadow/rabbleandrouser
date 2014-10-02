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

/**
 * GET for / which renders the posts
 */
$app->get('/', function () use ($app) {
    /** @var WallService $wallService */
    $wallService = $app['rabbleandrouser.wall.service'];

    // this is a hack it really should be an event hook so that the system automatically populates this and doesn't
    // have to be populated for each action that cares
    $authenticated = false;
    if (is_array($app['session']->get('user'))) {
        $authenticated = true;
    }

    $posts = $wallService->retrieveAll();

    // this is a tad inefficient, should probably create a dependency between the user and post services.
    $userIds = array_reduce($posts, function ($carry, $item) {
        /** @var Post $item */
        $carry[] = $item->getAuthorId();
        return $carry;
    }, array());

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
        $carry[] = array(
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'author' => $users[$post->getAuthorId()]->getUsername(),
        );

        return $carry;
    }, array());

    return $app['twig']->render('index.twig', array(
        'posts' => $posts,
        'authenticated' => $authenticated,
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

    $valid = array_reduce(array($username, $email, $password), function ($carry, $item) {
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
        ->setUsername($username);

    $app['rabbleandrouser.user.service']->create($user, $password);

    return $app->redirect('/');
});

$app->post('/posts', function (Request $request) use($app) {
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

    return $app->redirect('/');
});

$app->run();