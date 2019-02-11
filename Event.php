<?php

namespace Plugin\Push4;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Eccube\Request\Context;
use Eccube\Event\TemplateEvent;
use Eccube\Repository\NewsRepository;

class Event implements EventSubscriberInterface {

    /**
     * @var \Eccube\Request\Context
     */
    protected $requestContext;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface 
     */
    protected $eventDispatcher;

    /**
     * @var \Eccube\Repository\NewsRepository 
     */
    protected $newsRepository;

    public function __construct(
            Context $requestContext,
            EventDispatcherInterface $eventDispatcher,
            NewsRepository $newsRepository
    )
    {
        $this->requestContext = $requestContext;
        $this->eventDispatcher = $eventDispatcher;
        $this->newsRepository = $newsRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => [['onKernelController', 100000000]],
        ];
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        // フロントページでない場合はスルー
        if (!$this->requestContext->isFront()) {
            return;
        }

        if ($event->getRequest()->attributes->has('_template')) {
            $template = $event->getRequest()->attributes->get('_template');
            $this->eventDispatcher->addListener($template->getTemplate(), function (TemplateEvent $templateEvent) {
                $templateEvent->addSnippet('@Push4/default/snippet/push.twig');
                
                $News = $this->newsRepository->getList();
                $templateEvent->setParameter('News', $News->first());
            });
        }
    }

}
