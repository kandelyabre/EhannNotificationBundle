<?php

namespace Ehann\NotificationBundle\Twig;

use Symfony\Component\HttpFoundation\Session\Session;
use Twig_Extension;
use Twig_Function_Method;

/**
 * Twig Extension for front-end notifications
 *
 */
class NotificationExtension extends Twig_Extension
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'notification' => new Twig_Function_Method($this, 'notification')
        );
    }

    /**
     * @param string $type    The type of notification to display: all|info|error|warning|success
     * @param bool $showIcons Display icons before the notification's text.
     * @param bool $repeat    Show the same message more than once.
     *
     * @return string HTML notification elements.
     *
     * @throws \Exception
     */
    public function notification($type = 'all', $showIcons = false, $repeat = true)
    {
        $notificationTypes = array('info', 'error', 'warning', 'success');

        if (!in_array($type, $notificationTypes) && $type !== 'all') {
            throw new \Exception('Notification type does not exist.');
        }

        if ($type !== 'all') {
            $notificationTypes = array($type);
        }

        $notifications = '';

        // This is used to keep track of repeated messages, with regard to the "repeat" flag
        $repeatedMessages = array_fill_keys($notificationTypes, []);

        foreach ($notificationTypes as $notificationType) {
            $messagesByType = $this->session->getFlashBag()->get('ehann.notice.' . $notificationType, array());
            foreach ($messagesByType as $message) {
                // Do not show duplicate messages if the "repeat" flag is false.
                if ($repeat || !in_array($message, $repeatedMessages[$notificationType])) {
                    $repeatedMessages[$notificationType][] = $message;

                    $notifications.= $this->environment->render(sprintf('EhannNotificationBundle:Alert:%s.html.twig', $notificationType), [
                        'message' => $message,
                        'icon' => $showIcons,
                        'type' => $notificationType,
                    ]);
                }
            }
        }

        return $notifications;
    }

    /**
     * Gets name of this extension
     *
     * @return string Name of extension
     */
    public function getName()
    {
        return 'notification_extension';
    }
}