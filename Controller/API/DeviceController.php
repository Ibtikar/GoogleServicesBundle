<?php

namespace Ibtikar\GoogleServicesBundle\Controller\API;

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
        $validationErrorsResponse = $APIOperations->bindAndValidateObjectDataFromRequst($registerDevice, $request);
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
        $validationErrorsResponse = $APIOperations->bindAndValidateObjectDataFromRequst($setIOSBadge, $request);
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
     *  section="Device",
     *  tags={
     *     "stable"="green"
     *  },
     *  input="Ibtikar\GoogleServicesBundle\APIResponse\Device\Device",
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
     * @param Request $request
     * @return JsonResponse
     */
    public function removeDeviceUserAction(Request $request)
    {
        /* @var $APIOperations \Ibtikar\ShareEconomyToolsBundle\Service\APIOperations */
        $APIOperations = $this->get('api_operations');
        $deviceInput = new DeviceResponses\Device();
        $validationErrorsResponse = $APIOperations->bindAndValidateObjectDataFromRequst($deviceInput, $request);
        if ($validationErrorsResponse) {
            return $validationErrorsResponse;
        }
        $em = $this->getDoctrine()->getManager();
        /* @var $device \Ibtikar\GoogleServicesBundle\Entity\Device */
        $device = $em->getRepository('IbtikarGoogleServicesBundle:Device')->findOneByIdentifier($deviceInput->identifier);
        if (!$device) {
            return $APIOperations->getNotFoundErrorJsonResponse();
        }
        $device->setUser(null);
        $em->flush();
        return $APIOperations->getSuccessJsonResponse();
    }
}
