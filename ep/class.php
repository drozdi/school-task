<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/x/.init.php';
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Main\User;
use App\Main\Group;
use App\Entity\EpGroup;
use App\Entity\EpSubject;

if (!$app->user->isAuthorized()) {
    throw new Exception("Нужно авторизоваться!");
}

switch ((string)$request->get('f')) {
    case 'list':
        if (!$app->user->checkAccess('class', 4) && !$app->user->checkAccess('root', 4)) {
            throw new Exception("Недостаточно прав для загрузки списка!");
        }
        $query = $app->em->createQuery("SELECT g FROM ".EpGroup::class." g JOIN g.parent p WHERE p.code LIKE 'class_%' AND p.parent IS NULL ORDER BY p.sort ASC, g.sort ASC, g.name ASC");
        $result = array();
        foreach ($query->execute() as $group) {
            $result[] = array(
                'id' => $group->getId(),
                'name' => $group->getName(),
                'tutor' => $group->getUser()? sprintf('%s (%s)', $group->getUser()->getAlias(), $group->getUser()->getLogin()): '',
            );
        }
        break;
    case 'load':
        $class = $app->ep->class((array)$request->request->get('class', array()));
        if (!$app->user->cheackUser($class->getUser()) && !$app->user->checkAccess('class', 4)) {
            throw new Exception("Недостаточно прав для загрузки класса!");
        }
        $users = array();
        foreach ($class->getUsers() as $user) {
            $users[] = array(
                'id' => $user->getId(),
                'user_id' => $user->getUserId(),
                'group_id' => $user->getGroupId(),
                'name' => sprintf('%s (%s)', $user->getUserLogin(), $user->getUserAlias())
            );
        }
        $groups = array();
        foreach ($class->getChildren() as $sub) {
            /*$subUsers = array();
            foreach ($sub->getUsers() as $user) {
                $subUsers[] = array(
                    'id' => $user->getId(),
                    'user_id' => $user->getUserId(),
                    'group_id' => $user->getGroupId(),
                    'name' => sprintf('%s (%s)', $user->getUserLogin(), $user->getUserAlias())
                );
            }*/
            $groups[] = array(
                'id' => $sub->getId(),
                'name' => $sub->getName(),
                'parent_id' => $sub->getParent()->getId(),
                'user_id' => $sub->getUser()? $sub->getUser()->getId(): null,
                'subject_id' => $sub->getSubject()? $sub->getSubject()->getId(): null,
                'subject_name' => $sub->getSubject()? $sub->getSubject()->getName(): null,
                //'users' => $subUsers
            );
        }
        $result = array(
            'id' => $class->getId(),
            'name' => $class->getName(),
            'parent_id' => $class->getParent()? $class->getParent()->getId(): null,
            'user_id' => $class->getUser()? $class->getUser()->getId(): null,
        );
        if (!empty($users)) {
            $result['users'] = $users;
        }
        if (!empty($groups)) {
            $result['sub'] = $groups;
        }
        break;
    case 'load-subjects':
        $result = array();
        $dql = "SELECT s FROM ".EpSubject::class." s ORDER BY s.sort ASC, s.name ASC";
        foreach ($app->em->createQuery($dql)->execute() as $subject) {
            $users = array();
            foreach ($subject->getUsers() as $user) {
                $users[] = array(
                    'value' => $user->getId(),
                    'text' => sprintf('%s (%s)', $user->getAlias(), $user->getLogin()),
                );
            }
            $result[] = array(
                'value' => $subject->getId(),
                'text' => $subject->getName(),
                'users' => $users
            );
        }
        break;
    case 'load-parallels':
        $result = array();
        $dql = "SELECT g FROM ".EpGroup::class." g WHERE g.code LIKE 'class_%' AND g.parent IS NULL ORDER BY g.sort ASC, g.name ASC";
        foreach ($app->em->createQuery($dql)->execute() as $group) {
            $result[] = array(
                'value' => $group->getId(),
                'text' => $group->getName(),
            );
        }
        break;
    case 'load-tutors':
        $result = array();
        $dql = "SELECT ug FROM ".User\Group::class." ug JOIN ug.group g WHERE g.code = 'ma_ct'";
        //' ORDER BY u.login ASC';
        foreach ($app->em->createQuery($dql)->execute() as $ug) {
            $result[] = array(
                'value' => $ug->getUserId(),
                'text' => sprintf('%s (%s)', $ug->getUserLogin(), $ug->getUserAlias()),
            );
        }
        break;
    case 'load-pupils':
        $result = array();
        $dql = "SELECT u FROM ".User::class." u JOIN u.ou ou WHERE ou.code = 'pupils' ORDER BY u.login ASC";
        foreach ($app->em->createQuery($dql)->execute() as $user) {
            $result[] = array(
                'value' => $user->getId(),
                'text' => sprintf('%s (%s)', $user->getLogin(), $user->getAlias()),
            );
        }
        break;
    case 'save':
        $arClass = $request->request->get('class', array());
        print_r($arClass);
        $class = $app->ep->class(!empty($arClass['id'])?(int)$arClass['id']:null);
        if (!$app->user->cheackUser($class->getUser()) && !$app->user->checkAccess('class', 2)) {
            throw new Exception("Недостаточно прав для сохранения класса!");
        }
        $arClass['sub'] = (array)(!empty($arClass['sub'])?$arClass['sub']:array());
        $arClass['users'] = (array)(!empty($arClass['users'])?$arClass['users']:array());

        $class = $app->ep->class($class, $arClass);
        $app->em->flush();
        $users = array();
        foreach ($class->getUsers() as $user) {
            $users[] = array(
                'id' => $user->getId(),
                'user_id' => $user->getUserId(),
                'group_id' => $user->getGroupId(),
                'name' => sprintf('%s (%s)', $user->getUserLogin(), $user->getUserAlias())
            );
        }
        $groups = array();
        foreach ($class->getChildren() as $sub) {
            /*$subUsers = array();
            foreach ($sub->getUsers() as $user) {
                $subUsers[] = array(
                    'id' => $user->getId(),
                    'user_id' => $user->getUserId(),
                    'group_id' => $user->getGroupId(),
                    'name' => sprintf('%s (%s)', $user->getUserLogin(), $user->getUserAlias())
                );
            }*/
            $groups[] = array(
                'id' => $sub->getId(),
                'name' => $sub->getName(),
                'parent_id' => $sub->getParent()->getId(),
                'user_id' => $sub->getUser()? $sub->getUser()->getId(): null,
                'subject_id' => $sub->getSubject()? $sub->getSubject()->getId(): null,
                'subject_name' => $sub->getSubject()? $sub->getSubject()->getName(): null,
                //'users' => $subUsers
            );
        }
        $result = array(
            'id' => $class->getId(),
            'name' => $class->getName(),
            'parent_id' => $class->getParent()? $class->getParent()->getId(): null,
            'user_id' => $class->getUser()? $class->getUser()->getId(): null,
        );
        if (!empty($users)) {
            $result['users'] = $users;
        }
        if (!empty($groups)) {
            $result['sub'] = $groups;
        }
        break;
    case 'remove':
        $class = $app->ep->class((int)$request->request->get('id'));
        if (!$app->user->checkAccess('class', 4) && !$app->user->checkAccess('root', 2)) {
            throw new Exception("Недостаточно прав для удаления класса!");
        }
        $app->em->remove($class->getGroup());
        $app->em->remove($class);
        $app->em->flush();
        $result = array(
            'id' => (int)$request->request->get('id')
        );
        break;
}
$result = new JsonResponse($result?: array());
$result->headers->set('Content-Type', 'application/json');
$result->send();
?>