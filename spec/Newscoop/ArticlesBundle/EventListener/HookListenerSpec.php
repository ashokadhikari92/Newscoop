<?php

namespace spec\Newscoop\ArticlesBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Bundle\DoctrineBundle\Registry;

class HookListenerSpec extends ObjectBehavior
{
    /**
     * @param \Doctrine\ORM\EntityManager                                           $em
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface            $templating
     * @param \Newscoop\Entity\Article                                              $article
     * @param \Newscoop\ArticlesBundle\Entity\Repository\EditorialCommentRepository $repository
     */
    public function let($die, $em, $templating, $article, $repository, Registry $doctrine)
    {
        $doctrine->getManager()->willReturn($em);
        $em->getRepository(Argument::exact('Newscoop\ArticlesBundle\Entity\EditorialComment'))->willReturn($repository);
        $repository->getAllByArticleNumber(Argument::any(), Argument::any())->willReturn(Argument::any());
        $article->getNumber()->willReturn(1);
        $this->beConstructedWith($em, $templating);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Newscoop\ArticlesBundle\EventListener\HookListener');
    }

    /**
     * @param \Newscoop\EventDispatcher\Events\PluginHooksEvent $event
     * @param \Symfony\Component\HttpFoundation\Response        $response
     */
    public function it_will_display_list_of_editorial_comments_for_article($event, $response, $article, $em, $repository, $templating)
    {
        $event->getArgument('articleNumber')->willReturn($article->getNumber()->willReturn(1));
        $repository->getAllByArticleNumber(1)->willReturn(Argument::type('array'));

        $templating->renderResponse(
            'NewscoopArticlesBundle:Hook:editorialComments.html.twig',
            array(
                'articleNumber' => 1,
                'editorialComments' => [0 => array(
                    'id' => 1,
                    'articleNumber' => 1
                )]
            )
        )->willReturn($response);

        $event->addHookResponse($response);

        $this->listEditorialComments($event)->shouldReturn(null);
    }
}
