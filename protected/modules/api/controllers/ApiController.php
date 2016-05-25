<?php
/**
 * This controller allows to apply into the contest
 */

namespace api\controllers;

use \sectionvideo\models\Video;
use \event\models\Event;

class ApiController extends \Controller
{
    public function actionIndex()
    {
        if (!\Yii::app()->user->isGuest && \Yii::app()->request->isPostRequest) {
            $eventId = $_POST['eventId'];
            $video = new Video();
            $event = Event::model()->findByPk($eventId);

            if (!$event) {
                throw new \CHttpException(404, 'Can not find event with id ' . $eventId);
            }

            switch ($_POST['type']) {
                case 'solo':
                    $type = Video::TYPE_SOLO;
                    break;
                case 'group':
                    $type = Video::TYPE_GROUP;
                    break;
                case 'concert':
                    $type = Video::TYPE_CONCERT;
                    break;
                default:
                    throw new \Exception('Unknown video type: ' . $_POST['type']);
                    break;
            }

            switch ($_POST['status']) {
                case 'published':
                    $status = Video::STATUS_PUBLISHED;
                    break;
                case 'draft':
                    $status = Video::STATUS_DRAFT;
                    break;
                default:
                    throw new \Exception('Unknown video status: ' . $_POST['status']);
                    break;
            }

            $video->attributes = [
                'status' => $status,
                'composition_name' => $_POST['composition'],
                'composition_author' => $_POST['author'],
                'type' => $type,
                'video_url' => $_POST['videoUrl'],
                'title' => $_POST['groupName'],
                'event' => $event->name,
                'event_id' => $event->primaryKey,
            ];

            if (!$video->save()) {
                throw new \CHttpException(500, 'Can not save video');
            }

            header('application/json');
            echo json_encode($video->attributes);
        } else {
            echo 'Hi, i`m Api';
        }
    }
}
