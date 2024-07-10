<?php
require_once "../x/.init.php";
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\EpGroup;

$res = array();
if ($app->user->checkAccess('root', 7)) {
    $res[] = array(
        'title' => "Main",
        'icon' => "mdi-account-circle",
        'items' => array(
            array(
                'title' => "Подразделения",
                'href' => "/main/ou/",
                'icon' => "mdi-home-group"
            ),
            array(
                'title' => "Пользователи",
                'href' => "/main/user/",
                'icon' => "mdi-account-multiple-outline"
            ),
            array(
                'title' => "Группы",
                'href' => "/main/group/",
                'icon' => "mdi-format-list-group"
            ),
            array(
                'title' => "Права",
                'href' => "/main/claimant/",
                'icon' => "mdi-access-point"
            ),
        )
    );
}
if ($app->user->in('teachers')) {
    $sub = array(
        'title' => "УП",
        'icon' => "mdi-book-account",
        'items' => array()
    );
    if ($app->user->checkAccess('subject', 4) || $app->user->checkAccess('root', 4)) {
        $sub['items'][] = array(
            'title' => "Предметы",
            'href' => "/ep/subject",
            'icon' => "mdi-attachment-minus"
        );
    }
    if ($app->user->checkAccess('class', 4) || $app->user->checkAccess('root', 4)) {
        $sub['items'][] = array(
            'title' => "Классы",
            'href' => "/ep/class",
            'icon' => "mdi-account-group"
        );
    }
    $class = $app->ep->class(array(
        'user' => $app->user->getUser(),
        'level' => 1
    ));
    if (!empty($class) && $class->getId()) {
        $sub['items'][] = array(
            'title' => "Мой класс",
            'icon' => "mdi-account-group",
            'href' => "/ep/class/".$class->getId()
        );
    }
    if (count($sub['items']) > 0) {
        $res[] = $sub;
    }
    $res[] = array(
        'title' => "Расписание",
        'icon' => "mdi-calendar-range",
        'href' => "/event/"
    );
}
if ($app->user->isAuthorized()) {
    $class = $app->ep->class(array(
        'user' => $app->user->getUser(),
        'level' => 1
    ));
    if (!empty($class) && $class->getId()) {
        $res[] = array(
            'title' => "Мой класс",
            'icon' => "mdi-calendar-range",
            'href' => "/event/edit/".$class->getId()
        );
    }
}



$res = new JsonResponse($res);
$res->headers->set('Content-Type', 'application/json');
$res->send();