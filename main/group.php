<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/x/.init.php';
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Main\OU;
use App\Main\User;
use App\Main\Group;
use App\Main\Claimant;

if (!$app->user->checkAccess('root', 7)) {
    throw new Exception("Недостаточно прав!");
}

switch ((string)$request->get('f')) {
    case 'ous':
        $result = array();
        foreach ($app->em->createQuery('SELECT ou FROM '.OU::class.' ou ORDER BY ou.sort ASC, ou.code ASC')->execute() as $ou) {
            $result[] = array(
                'id' => $ou->getId(),
                'code' => $ou->getCode(),
                'description' => $ou->getDescription()
            );
        }
        break;
    case 'list':
        $ou_id = $request->request->get('ou_id');
        if ('null' == $ou_id) {
            $ou_id = null;
        } else {
            $ou_id = (int)$ou_id;
        }
        $query = $app->em->createQueryBuilder();
        $query
            ->select('g')
            ->from(Group::class, 'g')
            ->orderBy('g.sort', 'ASC')
            ->addOrderBy('g.code', 'ASC');
        if ($ou_id > 0) {
            $query
                ->where('g.ou = '.((int)$ou_id));
        } elseif (null === $ou_id) {
            $query
                ->where('g.ou IS NULL');
        }
        $result = array();
        foreach ($query->getQuery()->execute() as $group) {
            $result[] = array(
                'id' => $group->getId(),
                'code' => $group->getCode(),
                'name' => $group->getName(),
                'sort' => $group->getSort(),
                'tutor' => $group->getUser()? sprintf('%s (%s)', $group->getUser()->getLogin(), $group->getUser()->getAlias()): ''
            );
        }
        break;
    case 'load':
        $group = $app->mm->group((array)$request->request->get('group', array()));
        $accesses = array();
        foreach ($group->getAccesses() as $access) {
            $accesses[$access->getId()] = array(
                'id' => $access->getId(),
                'group_id' => $access->getGroup()->getId(),
                'claimant_id' => $access->getClaimant()->getId(),
                'name' => $access->getName(true),
                'access' => $access->getAccess(),
            );
        }
        $users = array();
        foreach ($group->getUsers() as $ug) {
            $users[$ug->getId()] = array(
                'id' => $ug->getId(),
                'user_id' => $ug->getUser()->getId(),
                'group_id' => $ug->getGroup()->getId(),
                'activeFrom' => $ug->getActiveFrom("Y-m-d H:m:s"),
                'activeTo' => $ug->getActiveTo("Y-m-d H:m:s"),
                'name' => sprintf('%s (%s)', $ug->getUserLogin(), $ug->getUserAlias())
            );
        }
        $result = array(
            'id' => $group->getId(),
            'x_timestamp' => $group->getXTimestamp("Y-m-d H:m:s"),
            'user_id' => $group->getUser()? $group->getUser()->getId(): null,
            'ou_id' => $group->getOu()? $group->getOu()->getId(): null,
            'parent_id' => $group->getParent()? $group->getParent()->getId(): null,
            'active' => $group->getActive()? 1: 0,
            'activeFrom' => $group->getActiveFrom("Y-m-d H:m:s"),
            'activeTo' => $group->getActiveTo("Y-m-d H:m:s"),
            'sort' => $group->getSort(),
            'name' => $group->getName(),
            'code' => $group->getCode(),
            'anonymous' => $group->getAnonymous()? 1: 0,
            'description' => $group->getDescription(),
        );
        if (!empty($accesses)) {
            $result['accesses'] = $accesses;
        }
        if (!empty($users)) {
            $result['users'] = $users;
        }
        break;
    case 'load-tutors':
        $dql = 'SELECT u FROM '.User::class.' u JOIN u.ou ou WHERE ou.isTutors = 1 ORDER BY u.login ASC';
        $query = $app->em->createQuery($dql);
        $result = array();
        foreach ($query->execute() as $user) {
            $result[] = array(
                'value' => $user->getId(),
                'text' => sprintf('%s (%s)', $user->getLogin(), $user->getAlias())
            );
        }
        break;
    case 'load-ous':
        $result = array();
        foreach ($app->em->createQuery('SELECT ou FROM '.OU::class.' ou ORDER BY ou.sort ASC, ou.code ASC')->execute() as $ou) {
            $result[] = array(
                'value' => $ou->getId(),
                'text' => sprintf('%s (%s)', $ou->getDescription(), $ou->getCode()),
            );
        }
        break;
    case 'load-groups':
        $ou_id = (int)$request->request->get('ou_id', 0);
        $group_id = (int)$request->request->get('group_id', 0);
        $dql = 'SELECT g FROM '.Group::class.' g WHERE g.id <> '.$group_id;
        if ($ou_id > 0) {
            $dql .= ' AND g.ou = '.$ou_id;
        } else {
            $dql .= ' AND g.ou IS NULL';
        }
        $dql .= ' ORDER BY g.sort ASC, g.code ASC';
        $result = array();
        foreach ($app->em->createQuery($dql)->execute() as $group) {
            $result[] = array(
                'value' => $group->getId(),
                'text' => sprintf('%s (%s)', $group->getName(), $group->getCode()),
            );
        }
        break;
    case 'load-claimants':
        $query = $app->em->createQuery('SELECT c FROM '.Claimant::class.' c ORDER BY c.code ASC');
        $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        $result = array();
        foreach ($query->execute() as $c) {
            $result[] = array(
                'value' => $c['id'],
                'text' => sprintf('%s (%s)', $c['name'], $c['code'])
            );
        }
        break;
    case 'load-users':
        $query = $app->em->createQuery('SELECT u FROM '.User::class.' u');
        $result = array();
        foreach ($query->execute() as $user) {
            $result[] = array(
                'value' => $user->getId(),
                'text' => sprintf('%s (%s)', $user->getLogin(), $user->getAlias())
            );
        }
        break;
    case 'save':
        $arGroup = (array)$request->request->get('group', array());
        $arGroup['active'] = (int)$arGroup['active'];
        $arGroup['anonymous'] = (int)$arGroup['anonymous'];
        $arGroup['sort'] = (int)$arGroup['sort'];
        $arGroup['accesses'] = (array)(!empty($arGroup['accesses'])?$arGroup['accesses']:array());
        $arGroup['users'] = (array)(!empty($arGroup['users'])?$arGroup['users']:array());
        $group = $app->mm->group(!empty($arGroup['id'])?(int)$arGroup['id']:null, $arGroup);
        $app->em->flush();
        $accesses = array();
        foreach ($group->getAccesses() as $access) {
            $accesses[$access->getId()] = array(
                'id' => $access->getId(),
                'group_id' => $access->getGroup()->getId(),
                'claimant_id' => $access->getClaimant()->getId(),
                'name' => $access->getName(true),
                'access' => $access->getAccess(),
            );
        }
        $users = array();
        foreach ($group->getUsers() as $ug) {
            $users[$ug->getId()] = array(
                'id' => $ug->getId(),
                'user_id' => $ug->getUser()->getId(),
                'group_id' => $ug->getGroup()->getId(),
                'activeFrom' => $ug->getActiveFrom("Y-m-d H:m:s"),
                'activeTo' => $ug->getActiveTo("Y-m-d H:m:s"),
                'name' => sprintf('%s (%s)', $ug->getUserLogin(), $ug->getUserAlias())
            );
        }
        $result = array(
            'id' => $group->getId(),
            'x_timestamp' => $group->getXTimestamp("Y-m-d H:m:s"),
            'user_id' => $group->getUser()? $group->getUser()->getId(): null,
            'ou_id' => $group->getOu()? $group->getOu()->getId(): null,
            'parent_id' => $group->getParent()? $group->getParent()->getId(): null,
            'active' => $group->getActive()? 1: 0,
            'activeFrom' => $group->getActiveFrom("Y-m-d H:m:s"),
            'activeTo' => $group->getActiveTo("Y-m-d H:m:s"),
            'sort' => $group->getSort(),
            'name' => $group->getName(),
            'code' => $group->getCode(),
            'anonymous' => $group->getAnonymous()? 1: 0,
            'description' => $group->getDescription()
        );
        if (!empty($accesses)) {
            $result['accesses'] = $accesses;
        }
        if (!empty($users)) {
            $result['users'] = $users;
        }
        break;
    case 'remove':
        $group = $app->mm->group((int)$request->request->get('id'));
        $app->em->remove($group);
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