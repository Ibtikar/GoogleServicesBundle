<?php

namespace Ibtikar\GoogleServicesBundle\Service;

use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;
use sngrl\PhpFirebaseCloudMessaging\Notification;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Client;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class FirebaseCloudMessaging
{

    /* @var $fireBaseHTTPClient \sngrl\PhpFirebaseCloudMessaging\Client */
    private $fireBaseHTTPClient;

    /* @var $logger \Monolog\Logger */
    private $logger;

    /**
     * @param string $fireBaseAPIKey
     * @param \Monolog\Logger $logger
     * @throws \Exception
     */
    public function __construct($fireBaseAPIKey, $logger)
    {
        if (!$fireBaseAPIKey) {
            throw new \Exception('You should set "firebase_api_key" parameter under the bundle configuration "ibtikar_google_services"');
        }
        $this->logger = $logger;
        $this->fireBaseHTTPClient = new Client();
        $this->fireBaseHTTPClient->setApiKey($fireBaseAPIKey);
        $this->fireBaseHTTPClient->injectGuzzleHttpClient(new \GuzzleHttp\Client());
    }

    /**
     * @param Device|Topic $reciver
     * @return string
     */
    private function getReciverText($reciver)
    {
        return $reciver instanceof Device ? 'Token: ' . $reciver->getToken() : 'Topic: ' . $reciver->getName();
    }

    /**
     * @param Device|Topic $reciver
     * @param array $data
     * @param string $messagePeriority
     * @return Message
     */
    private function getFirebaseMessage($reciver, array $data = array(), $messagePeriority = 'high')
    {
        $message = new Message();
        $message->setPriority($messagePeriority);
        $message->addRecipient($reciver);
        if (count($data) > 0) {
            $message->setData($data);
        }
        $this->logger->log('info', 'Sending to ' . $this->getReciverText($reciver));
        return $message;
    }

    /**
     * @param Device|Topic $reciver
     * @param array $data
     * @param string $messagePeriority
     * @return boolean
     */
    private function sendMessage($reciver, array $data, $messagePeriority = 'high')
    {
        if (count($data) === 0) {
            throw new \Exception('You must set the data to send.');
        }
        $message = $this->getFirebaseMessage($reciver, $data, $messagePeriority);
        try {
            $sendingStatus = $this->fireBaseHTTPClient->send($message)->getStatusCode() == 200 ? true : false;
        } catch (\Exception $e) {
            $this->logger->log('error', $e->getTraceAsString());
            $sendingStatus = false;
        }
        $this->logger->log('info', 'Sending message to "' . $this->getReciverText($reciver) . '" data: "' . serialize($data) . '" finished with ' . ($sendingStatus ? 'success' : 'error'));
        return $sendingStatus;
    }

    /**
     * @param string $deviceToken
     * @param array $data
     * @param string $messagePeriority
     * @return boolean
     */
    public function sendMessageToDevice($deviceToken, array $data, $messagePeriority = 'high')
    {
        return $this->sendMessage(new Device($deviceToken), $data, $messagePeriority);
    }

    /**
     * @param string $topicId
     * @param array $data
     * @param string $messagePeriority
     * @return boolean
     */
    public function sendMessageToTopic($topicId, array $data, $messagePeriority = 'high')
    {
        return $this->sendMessage(new Topic($topicId), $data, $messagePeriority);
    }

    /**
     * @param Device|Topic $reciver
     * @param string $notificationTitle
     * @param string $notificationBody
     * @param array $data
     * @param int $deviceNotificationsCount
     * @param string $messagePeriority
     * @return boolean
     */
    private function sendNotification($reciver, $notificationTitle, $notificationBody, array $data = array(), $deviceNotificationsCount = null, $messagePeriority = 'high')
    {
        $message = $this->getFirebaseMessage($reciver, $data, $messagePeriority);
        $notification = new Notification($notificationTitle, $notificationBody);
        if ($deviceNotificationsCount) {
            $notification->setBadge($deviceNotificationsCount);
        }
        if(isset($data['sound'])){
            $notification->setSound($data['sound']);
        }else{
            $notification->setSound('default');
        }
        $message->setNotification($notification);
        try {
            $sendingStatus = $this->fireBaseHTTPClient->send($message)->getStatusCode() == 200 ? true : false;
        } catch (\Exception $e) {
            $this->logger->log('error', $e->getTraceAsString());
            $sendingStatus = false;
        }
        $this->logger->log('info', 'Sending notification "' . $notificationTitle . '" to "' . $this->getReciverText($reciver) . '" data: "' . serialize($data) . '" finished with ' . ($sendingStatus ? 'success' : 'error'));
        return $sendingStatus;
    }

    /**
     * @param string $deviceToken
     * @param string $notificationTitle
     * @param string $notificationBody
     * @param array $data
     * @param int $deviceNotificationsCount
     * @param string $messagePeriority
     * @return boolean
     */
    public function sendNotificationToDevice($deviceToken, $notificationTitle, $notificationBody, array $data = array(), $deviceNotificationsCount = null, $messagePeriority = 'high')
    {
        return $this->sendNotification(new Device($deviceToken), $notificationTitle, $notificationBody, $data, $deviceNotificationsCount, $messagePeriority);
    }

    /**
     * @param string $topicId
     * @param string $notificationTitle
     * @param string $notificationBody
     * @param array $data
     * @param int $deviceNotificationsCount
     * @param string $messagePeriority
     * @return boolean
     */
    public function sendNotificationToTopic($topicId, $notificationTitle, $notificationBody, array $data = array(), $deviceNotificationsCount = null, $messagePeriority = 'high')
    {
        return $this->sendNotification(new Topic($topicId), $notificationTitle, $notificationBody, $data, $deviceNotificationsCount, $messagePeriority);
    }

    /**
     * @param string $topicId
     * @param array $devicesTokens
     * @return boolean
     */
    public function subscribeDevicesToTopic($topicId, array $devicesTokens)
    {
        return $this->fireBaseHTTPClient->addTopicSubscription($topicId, $devicesTokens)->getStatusCode() == 200 ? true : false;
    }

    /**
     * @param string $topicId
     * @param array $devicesTokens
     * @return boolean
     */
    public function removeDevicesFromTopic($topicId, array $devicesTokens)
    {
        return $this->fireBaseHTTPClient->removeTopicSubscription($topicId, $devicesTokens)->getStatusCode() == 200 ? true : false;
    }
}
