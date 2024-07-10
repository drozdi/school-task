<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/x/.init.php';
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Main\User;
use App\Entity\EpSubject;
use App\Entity\EpGroup;
use App\Entity\EpEvent;

switch ((string)$request->get('f')) {
    //def
    case 'load-info':
        $class = $app->ep->class((int)$request->get('class_id'));
        $result = array(
            //'id' => $class->getId(),
            'name' => $class->getName(),
            'teacher' => $class->getUser()? $class->getUser()->getAlias(): null
        );
        break;
    // Editor
    case 'add-event':
        $arEvent = $request->request->get('event', array());
        $class = $app->ep->class((int)$arEvent['class_id']);
        if (!$app->user->cheackUser($class->getUser())) {
            throw new Exception("Добавить может только классный руководитель!");
        }
        $event = $app->ep->event(null, $arEvent);
        $event->setTitle($event->getGroup()->getSubject()->getName());
        $app->em->flush();
        $result = array(
            'id' => $event->getId(),
            'user_id' => $event->getUser()->getId(),
            'class_id' => $event->getClass()->getId(),
            'group_id' => $event->getGroup()->getId(),
            'name' => $event->getGroup()->getSubject()->getName(),
            'subject_id' => $event->getGroup()->getSubject()->getId(),
            'start' => $event->getStart('Y-m-d H:i:s'),
            'end' => $event->getEnd('Y-m-d H:i:s'),
        );
        break;
    case 'edit-event':
        $arEvent = $request->request->get('event', array());
        $event = $app->ep->event((int)$arEvent['id']);
        if ($app->user->cheackUser($event->getClass()->getUser())) {
            $app->ep->editEvent($event, $arEvent);
            $app->em->flush();
            $result = array(
                'start' => $event->getStart('Y-m-d H:i:s'),
                'end' => $event->getEnd('Y-m-d H:i:s'),
            );
        } else {
            throw new Exception("Изменить может только классный руководитель!");
        }
        break;
    case 'remove-event':
        $arEvent = $request->get('event', array());
        $event = $app->ep->event((int)$arEvent['id']);
        if ($app->user->cheackUser($event->getClass()->getUser())) {
            $app->ep->removeEvent($event, $arEvent);
            $app->em->flush();
            $result = array();
        } else {
            throw new Exception("Удалить может только классный руководитель!");
        }
        break;
    case 'load-sub-groups':
        $dql = sprintf("SELECT g FROM %s g WHERE g.parent = %d", EpGroup::class, (int)$request->get('class_id'));
        $result = array();
        $query = $app->em->createQuery($dql);
        foreach ($query->execute() as $group) {
            $result[] = array(
                'value' => $group->getId(),
                'text' => $group->getName()
            );
        }
        break;
    case 'load-teachers':
        $subject = $app->ep->subject((int)$request->get('subject_id', 0));
        $result = array();
        foreach ($subject->getUsers() as $user) {
            $result[] = array(
                'value' => $user->getId(),
                'text' => $user->getAlias(),
            );
        }
        break;
    case 'load-editor':
        $result = array();
        $dql = sprintf("SELECT e FROM %s e WHERE (e.start BETWEEN '%s' AND '%s') AND (e.end BETWEEN '%s' AND '%s') AND e.class = %d", EpEvent::class,
            str_replace('T', ' ', $request->get('start')),
            str_replace('T', ' ', $request->get('end')),
            str_replace('T', ' ', $request->get('start')),
            str_replace('T', ' ', $request->get('end')),
            $request->get('class_id')
        );
        $query = $app->em->createQuery($dql);
        foreach ($query->execute() as $event) {
            $result[] = array(
                'id' => $event->getId(),
                'name' => $event->getGroup()->getSubject()->getName(),
                'start' => $event->getStart('Y-m-d H:i:s'),
                'end' => $event->getEnd('Y-m-d H:i:s'),
                'color' => "orange",
            );
        }
        break;
    case 'load-editor-detail':
        $event = $app->ep->event((int)$request->get('id', 0));
        $result = array(
            'user_id' => $event->getUser()->getId(),
            'class_id' => $event->getClass()->getId(),
            'group_id' => $event->getGroup()->getId(),
            'subject_id' => $event->getGroup()->getSubject()->getId(),
            'start' => $event->getStart('Y-m-d H:i:s'),
            'end' => $event->getEnd('Y-m-d H:i:s'),
        );
        break;
    // Teacher
    case 'save':
        $arEvent = $request->request->get('event', array());
        $event = $app->ep->event((int)$arEvent['id']);
        if (!$app->user->cheackUser($event->getUser())) {
            throw new Exception("Добавить задание может только учитель!");
        }
        $event = $app->ep->event($event, $arEvent);
        $files = (array)$request->request->get('files', array());
        foreach ($event->getFiles() as $file) {
            if (!in_array($file->getId(), $files)) {
                $event->removeFile($file);
                $app->file->removeObject($file);
            }
        }
        $subDir = $app->ep->translit($event->getClass()->getName()).'/'.$app->ep->translit($event->getGroup()->getSubject()->getName());
        foreach ($app->file->upload('files', 'task', null, $subDir) as $file) {
            $event->addFile($file);
        }
        $app->em->flush();
        $result = array();
        break;
    case 'load-teacher':
        $result = array();
        $strQuery = sprintf("SELECT e FROM %s e WHERE (e.start BETWEEN '%s' AND '%s') AND (e.end BETWEEN '%s' AND '%s') AND e.user = %d", EpEvent::class,
            str_replace('T', ' ', $request->get('start')),
            str_replace('T', ' ', $request->get('end')),
            str_replace('T', ' ', $request->get('start')),
            str_replace('T', ' ', $request->get('end')),
            $app->user->getID()
        );
        $query = $app->em->createQuery($strQuery);
        foreach ($query->execute() as $event) {
            $files = array();
            foreach ($event->getFiles() as $file) {
                $files[$file->getId()] = $file->getOriginalName();
            }
            $result[] = array(
                'id' => $event->getId(),
                'name' => $event->getGroup()->getName(),
                'start' => $event->getStart('Y-m-d H:i'),
                'end' => $event->getEnd('Y-m-d H:i'),
                'color' => !empty($event->getTheme())? "green": "blue",
                'files' => $files
            );
        }
        break;
    case 'load-teacher-detail':
        $event = $app->ep->event((int)$request->get('id', 0));
        $files = array();
        foreach ($event->getFiles() as $file) {
            $files[] = array(
                'id' => $file->getId(),
                'name' => $file->getOriginalName()
            );
        }
        $result = array(
            'theme' => $event->getTheme(),
            'ht' => $event->getHT(),
            'pt' => $event->getPT(),
            'description' => $event->getDescription(),
            'netResource' => $event->getNetResource(),
            'files' => $files
        );
        break;
    //
    case 'list':
        $query = $app->em->createQuery("SELECT g FROM ".EpGroup::class." g JOIN g.parent p WHERE p.code LIKE 'class_%' AND p.parent IS NULL ORDER BY p.sort ASC, g.sort ASC, p.name ASC");
        $result = array();
        foreach ($query->execute() as $group) {
            $result[] = array(
                'id' => $group->getId(),
                'name' => $group->getName(),
            );
        }
        break;
    case 'load':
        $result = array();
        $dql = sprintf("SELECT e FROM %s e WHERE (e.start BETWEEN '%s' AND '%s') AND (e.end BETWEEN '%s' AND '%s') AND e.class = %d", EpEvent::class,
            str_replace('T', ' ', $request->get('start')),
            str_replace('T', ' ', $request->get('end')),
            str_replace('T', ' ', $request->get('start')),
            str_replace('T', ' ', $request->get('end')),
            $request->get('class_id')
        );
        $query = $app->em->createQuery($dql);
        foreach ($query->execute() as $event) {
            $result[] = array(
                'id' => $event->getId(),
                'name' => $event->getGroup()->getSubject()->getName(),
                'start' => $event->getStart('Y-m-d H:i'),
                'end' => $event->getEnd('Y-m-d H:i'),
                'color' => !empty($event->getTheme())? "green": "blue",
            );
        }
        break;
    case 'load-detail':
        $event = $app->ep->event((int)$request->get('id', 0));
        $files = array();
        foreach ($event->getFiles() as $file) {
            $files[$file->getOriginalName()] = $file->getFileSRC();
        }
        $result = array(
            'theme' => $event->getTheme(),
            'teacher' => $event->getUser()->getAlias(),
            'email' => $event->getUser()->getEmail(),
            'ht' => str_replace("\n", "<br />", $event->getHT()),
            'des' => $event->getDescription(),
            'pt' => str_replace("\n", "<br />", $event->getPT()),
            'net' => $event->getNetResource()? explode("\n", $event->getNetResource()?:""): array(),
        );
        if (count($files) > 0) {
            $result['files'] = $files;
        }
        break;
}
$result = new JsonResponse(!empty($result)? $result: array());
$result->headers->set('Content-Type', 'application/json');
$result->send();
?>