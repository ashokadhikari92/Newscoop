<?php
/**
 * @package Newscoop\NewscoopBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\NewscoopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Newscoop\NewscoopBundle\Form\Type\ArticleImageType;

class BackendArticleImagesController extends Controller
{
    /**
     * @Route("/admin/articles/{language}/{articleNumber}/images/{imageId}/edit", name="newscoop_newscoop_article_images_edit")
     * @Template()
     */
    public function editAction(Request $request, $language, $articleNumber, $imageId)
    {
        $em = $this->get('em');
        $imageService = $this->container->get('image');
        $articleImage = $imageService->getArticleImage($articleNumber, $imageId);

        $form = $this->container->get('form.factory')->create(new ArticleImageType(), array(
            'number' => $articleImage->getNumber(),
            'caption' => $articleImage->getCaption($language),
            'language' => $language,
            'status' => $articleImage->getImage()->getStatus(),
            'description' => $articleImage->getImage()->getDescription(),
            'photographer' => $articleImage->getImage()->getPhotographer(),
            'photographer_url' => $articleImage->getImage()->getPhotographerUrl(),
            'place' => $articleImage->getImage()->getPlace(),
            'date' => $articleImage->getImage()->getDate(),
        ), array(
            'action' => $this->generateUrl('newscoop_newscoop_article_images_edit', array(
                'language' => $language,
                'articleNumber' => $articleNumber,
                'imageId' => $imageId
            )),
            'method' => 'POST',
        ));

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $data['user'] = $this->container->get('user')->getCurrentUser();
                $imageService->fillImage($articleImage->getImage(), $data);
                $imageService->saveArticleImage($articleImage, $data);

                return new RedirectResponse($this->generateUrl('newscoop_newscoop_article_images_edit', array(
                    'language' => $language,
                    'articleNumber' => $articleNumber,
                    'imageId' => $imageId
                )));
            }
        }

        return array(
            'form' => $form->createView(),
            'imageService' => $imageService,
            'articleImage' => $articleImage,
            'image' => $articleImage->getImage(),
            'caption' => $articleImage->getCaption($language),
            'captions' => $articleImage->getImage()->getCaptions()
        );
    }
}
