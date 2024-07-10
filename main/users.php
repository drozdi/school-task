<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/x/.init.php';
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\QueryBuilder;

use App\Main\OU;
use App\Main\User;
use App\Main\Group;
use App\Main\Claimant;

/*if (!$app->user->checkAccess('root', 7)) {
    throw new Exception("Недостаточно прав!");
}//*/
$result = array();
switch ((string)$request->get('f')) {
    case 'find':
        $filters = $request->get('filters', array());
        $format = $request->get('format', 'select');
        $result = array();
        foreach ($app->em->getRepository(User::class)->findFilter($filters)->execute() as $user) {
            $result[] = array(
                'value' => $user->getId(),
                'text' => sprintf('%s (%s)', $user->getLogin(), $user->getAlias())
            );
        }
        break;
}

$result = new JsonResponse($result?: array());
$result->headers->set('Content-Type', 'application/json');
$result->send();
?>