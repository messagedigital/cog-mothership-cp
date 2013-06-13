<?php

namespace Message\Mothership\ControlPanel;

use Message\User\AnonymousUser;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\HTTP\RedirectResponse;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Event listener for the Mothership Control Panel
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			KernelEvents::EXCEPTION => array(
				array('sendToLogin')
			),
		);
	}

	/**
	 * Send the user to the login page if the exception relates to a HTTP 403
	 * status code and the user is not currently logged in.
	 *
	 * @param GetResponseForExceptonEvent $event The event object
	 */
	public function sendToLogin(GetResponseForExceptionEvent $event)
	{
		// Skip if the user is logged in
		if (!($this->_services['user.current'] instanceof AnonymousUser)) {
			return false;
		}

		// If it's an access denied exception, send the user to the login page
		if ($event->getException() instanceof HttpException
		 && 403 === $event->getException()->getStatusCode()) {
			$event->setResponse(new RedirectResponse(
				$this->_services['routing.generator']->generate('ms.cp.login')
			));
		}
	}
}