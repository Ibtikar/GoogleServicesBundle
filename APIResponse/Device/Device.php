<?php

namespace Ibtikar\GoogleServicesBundle\APIResponse\Device;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class Device
{

    /**
     * @Assert\Type("scalar")
     * @Assert\Length(max=190)
     * @Assert\NotBlank
     */
    public $identifier;

    /**
     * @Assert\Type("scalar")
     */
    public $fcmToken;

}
