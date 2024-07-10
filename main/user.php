<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/x/.init.php';
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Main\OU;
use App\Main\User;
use App\Main\Group;
use App\Main\Claimant;

if (!$app->user->checkAccess('root', 7)) {
    throw new Exception("Недостаточно прав!");
}//*/

switch ((string)$request->get('f')) {
    case 'load-filters':
        $result = array();
        foreach ($app->em->createQuery('SELECT ou FROM '.OU::class.' ou ORDER BY ou.sort ASC, ou.code ASC')->execute() as $ou) {
            $item = array(
                'id' => $ou->getId(),
                'code' => $ou->getCode(),
                'description' => $ou->getDescription()
            );
            $items = array();
            foreach ($app->em->createQuery('SELECT g FROM '.Group::class.' g WHERE g.ou = '.$ou->getId().' ORDER BY g.sort ASC, g.code ASC')->execute() as $group) {
                $items[] = array(
                    'id' => $group->getId(),
                    'ou_id' => $group->getOu()->getId(),
                    'group_id' => $group->getId(),
                    'name' => $group->getName(),
                    'code' => $group->getCode()
                );
            }
            if (!empty($items)) {
                $item['items'] = $items;
            }
            $result[] = $item;
        }
        break;
    case 'list':
        $ou_id = $request->request->get('ou_id');
        if ('null' == $ou_id) {
            $ou_id = null;
        } else {
            $ou_id = (int)$ou_id;
        }
        $group_id = (int)$request->request->get('group_id', 0);
        $arr = array();
        $query = $app->em->createQueryBuilder();
        $query
            ->select('u')
            ->from(User::class, 'u')
            ->orderBy('u.login', 'ASC');
        if ($group_id > 0) {
            $q = $app->em->createQueryBuilder();
            $q
                ->select('gu')
                ->from(User\Group::class, 'gu')
                ->where('gu.group = '.$group_id);
            foreach ($q->getQuery()->execute() as $s) {
                $arr[] = $s->getUser()->getId();
            }
            if (count($arr)) {
                $query->andWhere('u.id IN (' . implode(', ', $arr) . ')');
            }
            $arr[] = -1;
        }
        if (count($arr) == 0 && $ou_id > 0) {
            $query->where('u.ou = '.((int)$ou_id));
        } elseif (count($arr) == 0 && null === $ou_id) {
            $query->where('u.ou IS NULL');
        }
        $result = array();
        foreach ($query->getQuery()->execute() as $user) {
            $result[] = array(
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'alias' => $user->getAlias(),
                'ou' => $user->getOu()? sprintf( '%s', $user->getOu()->getDescription()): '',
                'tutor' => $user->getParent()? sprintf( '%s (%s)', $user->getParent()->getLogin(), $user->getParent()->getAlias()): '',
            );
        }
        break;
    case 'load':
        $user = $app->mm->user((array)$request->request->get('user', array()));
        $accesses = array();
        foreach ($user->getAccesses() as $access) {
            $accesses[$access->getId()] = array(
                'id' => $access->getId(),
                'user_id' => $access->getUser()->getId(),
                'claimant_id' => $access->getClaimant()->getId(),
                'name' => $access->getName(true),
                'access' => $access->getAccess(),
            );
        }
        $groups = array();
        foreach ($user->getGroups() as $ug) {
            $groups[$ug->getId()] = array(
                'id' => $ug->getId(),
                'user_id' => $ug->getUser()->getId(),
                'group_id' => $ug->getGroup()->getId(),
                'activeFrom' => $ug->getActiveFrom("Y-m-d H:m:s"),
                'activeTo' => $ug->getActiveTo("Y-m-d H:m:s"),
                'name' => sprintf('%s (%s)', $ug->getGroupName(), $ug->getGroupCode())
            );
        }
        $result = array(
            'id' => $user->getId(),
            'parent_id' => $user->getParent()? $user->getParent()->getId(): null,
            'x_timestamp' => $user->getXTimestamp("Y-m-d H:m:s"),
            'date_register ' => $user->getDateRegister("Y-m-d H:m:s"),
            'last_login ' => $user->getLastLogin("Y-m-d H:m:s"),
            'last_ip' => $user->getLastIp(),
            'active' => $user->getActive()? 1: 0,
            'activeFrom' => $user->getActiveFrom("Y-m-d H:m:s"),
            'activeTo' => $user->getActiveTo("Y-m-d H:m:s"),
            'loocked ' => $user->getLoocked()? 1: 0,
            'stored_hash' => $user->getStoredHash(),
            'checkword' => $user->getCheckword(),
            'login' => $user->getLogin(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'alias' => $user->getAlias(),
            'first_name' => $user->getFirstName(),
            'second_name' => $user->getSecondName(),
            'patronymic' => $user->getPatronymic(),
            'gender' => $user->getGender(),
            'login_attempts' => $user->getLoginAttempts(),
            'country' => $user->getCountry(),
            'ou_id' => $user->getOu()? $user->getOu()->getId(): null,
            'phone' => $user->getPhone(),
            'description' => $user->getDescription()
        );
        if (!empty($accesses)) {
            $result['accesses'] = $accesses;
        }
        if (!empty($groups)) {
            $result['groups'] = $groups;
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
    case 'load-groups':
        $query = $app->em->createQuery('SELECT g FROM '.Group::class.' g ORDER BY g.sort ASC, g.name ASC');
        $result = array();
        foreach ($query->execute() as $grouo) {
            $result[] = array(
                'value' => $grouo->getId(),
                'text' => sprintf('%s (%s)', $grouo->getName(), $grouo->getCode())
            );
        }
        break;
    case 'save':
        $arUser = (array)$request->request->get('user', array());
        $arUser['active'] = (int)$arUser['active'];
        $arUser['loocked'] = (int)$arUser['loocked'];
        $arUser['accesses'] = (array)(!empty($arUser['accesses'])?$arUser['accesses']:array());
        $arUser['groups'] = (array)(!empty($arUser['groups'])?$arUser['groups']:array());
        $user = $app->mm->user((int)$arUser['id'], $arUser);
        $app->em->flush();
        $accesses = array();
        foreach ($user->getAccesses() as $access) {
            $accesses[$access->getId()] = array(
                'id' => $access->getId(),
                'user_id' => $access->getUser()->getId(),
                'claimant_id' => $access->getClaimant()->getId(),
                'name' => $access->getName(true),
                'access' => $access->getAccess(),
            );
        }
        $groups = array();
        foreach ($user->getGroups() as $ug) {
            $groups[$ug->getId()] = array(
                'id' => $ug->getId(),
                'user_id' => $ug->getUser()->getId(),
                'group_id' => $ug->getGroup()->getId(),
                'activeFrom' => $ug->getActiveFrom("Y-m-d H:m:s"),
                'activeTo' => $ug->getActiveTo("Y-m-d H:m:s"),
                'name' => sprintf('%s (%s)', $ug->getGroupName(), $ug->getGroupCode())
            );
        }
        $result = array(
            'id' => $user->getId(),
            'parent_id' => $user->getParent()? $user->getParent()->getId(): null,
            'x_timestamp' => $user->getXTimestamp("Y-m-d H:m:s"),
            'date_register ' => $user->getDateRegister("Y-m-d H:m:s"),
            'last_login ' => $user->getLastLogin("Y-m-d H:m:s"),
            'last_ip' => $user->getLastIp(),
            'active' => $user->getActive()? 1: 0,
            'activeFrom' => $user->getActiveFrom("Y-m-d H:m:s"),
            'activeTo' => $user->getActiveTo("Y-m-d H:m:s"),
            'loocked ' => $user->getLoocked()? 1: 0,
            'stored_hash' => $user->getStoredHash(),
            'checkword' => $user->getCheckword(),
            'login' => $user->getLogin(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'alias' => $user->getAlias(),
            'first_name' => $user->getFirstName(),
            'second_name' => $user->getSecondName(),
            'patronymic' => $user->getPatronymic(),
            'gender' => $user->getGender(),
            'login_attempts' => $user->getLoginAttempts(),
            'country' => $user->getCountry(),
            'ou_id' => $user->getOu()? $user->getOu()->getId(): null,
            'phone' => $user->getPhone(),
            'description' => $user->getDescription()
        );
        if (!empty($accesses)) {
            $result['accesses'] = $accesses;
        }
        if (!empty($groups)) {
            $result['groups'] = $groups;
        }
        break;
    case 'remove':
        $ou = $app->mm->user((int)$request->request->get('id'));
        $app->em->remove($ou);
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