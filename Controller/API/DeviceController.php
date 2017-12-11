<?php

namespace Ibtikar\GoogleServicesBundle\Controller\API;

use AppBundle\APIResponse\ErrorsResponse;
use AppBundle\Service\UserOperations;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Ibtikar\GoogleServicesBundle\APIResponse\Device as DeviceResponses;
use Ibtikar\GoogleServicesBundle\Entity\Device;

class DeviceController extends Controller
{

    /**
     * Register new device
     *
     * @ApiDoc(
     *  section="Device",
     *  tags={
     *     "stable"="green"
     *  },
     *  input="Ibtikar\GoogleServicesBundle\APIResponse\Device\Register",
     *  statusCodes={
     *      200="Returned on success",
     *      403="Returned if the api key is not valid",
     *      404="Returned if the page was not found",
     *      422="Returned if there is a validation error",
     *      500="Returned if there is an internal server error"
     *  },
     *  responseMap = {
     *      200="Ibtikar\ShareEconomyToolsBundle\APIResponse\Success",
     *      403="Ibtikar\ShareEconomyToolsBundle\APIResponse\InvalidAPIKey",
     *      404="Ibtikar\ShareEconomyToolsBundle\APIResponse\NotFound",
     *      422="Ibtikar\ShareEconomyToolsBundle\APIResponse\ValidationErrors",
     *      500="Ibtikar\ShareEconomyToolsBundle\APIResponse\InternalServerError"
     *  }
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Request $request
     * @return JsonResponse
     */
    public function registerAction(Request $request)
    {
        /* @var $APIOperations \Ibtikar\ShareEconomyToolsBundle\Service\APIOperations */
        $APIOperations = $this->get('api_operations');
        $registerDevice = new DeviceResponses\Register();
        $validationErrorsResponse = $APIOperations->bindAndValidateObjectDataFromRequest($registerDevice, $request);
        if ($validationErrorsResponse) {
            return $validationErrorsResponse;
        }
        $em = $this->getDoctrine()->getManager();
        /* @var $device \Ibtikar\GoogleServicesBundle\Entity\Device */
        $device = $em->getRepository('IbtikarGoogleServicesBundle:Device')->findOneByIdentifier($registerDevice->identifier);
        if (!$device) {
            $device = $em->getRepository('IbtikarGoogleServicesBundle:Device')->findOneByToken($registerDevice->token);
            if (!$device) {
                $device = new Device();
                $deviceType = $request->attributes->get('requestFrom');
                if ($deviceType) {
                    $device->setType($deviceType);
                }
                $em->persist($device);
            }
        }
        $APIOperations->bindObjectDataFromObject($device, $registerDevice, true);
        $user = $this->getUser();
        if ($user) {
            $device->setUser($user);
        }
        $em->flush();
        return $APIOperations->getSuccessJsonResponse();
    }

    /**
     * Set the ios device notification count
     *
     * @ApiDoc(
     *  section="Device",
     *  tags={
     *     "stable"="green"
     *  },
     *  input="Ibtikar\GoogleServicesBundle\APIResponse\Device\SetIOSBadge",
     *  statusCodes={
     *      200="Returned on success",
     *      403="Returned if the api key is not valid",
     *      404="Returned if the page was not found",
     *      422="Returned if there is a validation error",
     *      500="Returned if there is an internal server error"
     *  },
     *  responseMap = {
     *      200="Ibtikar\ShareEconomyToolsBundle\APIResponse\Success",
     *      403="Ibtikar\ShareEconomyToolsBundle\APIResponse\InvalidAPIKey",
     *      404="Ibtikar\ShareEconomyToolsBundle\APIResponse\NotFound",
     *      422="Ibtikar\ShareEconomyToolsBundle\APIResponse\ValidationErrors",
     *      500="Ibtikar\ShareEconomyToolsBundle\APIResponse\InternalServerError"
     *  }
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Request $request
     * @return JsonResponse
     */
    public function setIOSBadgeAction(Request $request)
    {
        /* @var $APIOperations \Ibtikar\ShareEconomyToolsBundle\Service\APIOperations */
        $APIOperations = $this->get('api_operations');
        $setIOSBadge = new DeviceResponses\SetIOSBadge();
        $validationErrorsResponse = $APIOperations->bindAndValidateObjectDataFromRequest($setIOSBadge, $request);
        if ($validationErrorsResponse) {
            return $validationErrorsResponse;
        }
        $em = $this->getDoctrine()->getManager();
        /* @var $device \Ibtikar\GoogleServicesBundle\Entity\Device */
        $device = $em->getRepository('IbtikarGoogleServicesBundle:Device')->findOneBy(array('type' => 'ios', 'identifier' => $setIOSBadge->identifier));
        if (!$device) {
            return $APIOperations->getNotFoundErrorJsonResponse('Device not found.');
        }
        $device->setBadgeNumber($setIOSBadge->badgeNumber);
        $em->flush();
        return $APIOperations->getSuccessJsonResponse();
    }

    /**
     * Remove the device relation with the current registered user
     *
     * @ApiDoc(
     *  section="Devices",
     *  statusCodes={
     *      200="Returned on success",
     *      403="Returned if the api key is not valid",
     *      404="Returned if device was not found",
     *      422="Returned if there is a validation error",
     *      500="Returned if there is an internal server error"
     *  },
     *  responseMap = {
     *      200="Ibtikar\ShareEconomyToolsBundle\APIResponse\Success",
     *      403="Ibtikar\ShareEconomyToolsBundle\APIResponse\InvalidAPIKey",
     *      404="Ibtikar\ShareEconomyToolsBundle\APIResponse\NotFound",
     *      422="Ibtikar\ShareEconomyToolsBundle\APIResponse\ValidationErrors",
     *      500="Ibtikar\ShareEconomyToolsBundle\APIResponse\InternalServerError"
     *  }
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * edited by Khaled
     * @param Request $request
     * @return JsonResponse
     */
    public function removeUserDeviceAction(Request $request)
    {
        $user = $this->getUser();

        /* @var $APIOperations \Ibtikar\ShareEconomyToolsBundle\Service\APIOperations */
        $APIOperations = $this->get('api_operations');
        $deviceInput = new DeviceResponses\Device();
        $APIOperations->bindObjectDataFromJsonRequest($deviceInput, $request);
        /* @var $userOperations UserOperations */
        $userOperations = $this->get('user_operations');
        $validationMessages = $userOperations->validateObject($deviceInput);

        if ($user->getId() != $request->get('userID')) {
            $validationMessages[] = new ErrorsResponse('userID', $this->get('translator')->trans('unauthorized_action'));
        }

        $em = $this->getDoctrine()->getManager();

        if($deviceInput->fcmToken) {
            /* @var $device \Ibtikar\GoogleServicesBundle\Entity\Device */
            $device = $em->getRepository('IbtikarGoogleServicesBundle:Device')->findOneBy(['identifier' => $deviceInput->deviceIdentifier,'token' => $deviceInput->fcmToken]);
        } else {
            $device = $em->getRepository('IbtikarGoogleServicesBundle:Device')->findOneByIdentifier($deviceInput->deviceIdentifier);
        }

        if (!$device) {
            $validationMessages[] = new ErrorsResponse('deviceIdentifier', $this->get('translator')->trans('Device not found'));
        }

        if (count($validationMessages)) {
            return $APIOperations->getErrorsJsonResponse($validationMessages);
        }

        $em->remove($device);
        $em->flush();

        return $APIOperations->getSuccessJsonResponse($device);
    }
}
