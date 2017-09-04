<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\FacebookVideo;
use AppBundle\Entity\Page;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Each time you add or update a custom url with an harcorded slug in the controller code, you must update the
 * AppBundle\Entity\Page::URLS constant and reindex algolia's page index.
 */
class PageController extends Controller
{
    /**
     * @Route("/emmanuel-macron", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron")
     * @Method("GET")
     * @Entity("page", expr="repository.findOneBySlug('emmanuel-macron-ce-que-je-suis')")
     */
    public function emmanuelMacronAction(Page $page)
    {
        return $this->render('page/emmanuel-macron/ce-que-je-suis.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/emmanuel-macron/revolution", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron_revolution")
     * @Method("GET")
     * @Entity("page", expr="repository.findOneBySlug('emmanuel-macron-revolution')")
     */
    public function emmanuelMacronRevolutionAction(Page $page)
    {
        return $this->render('page/emmanuel-macron/revolution.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/emmanuel-macron/videos", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron_videos")
     * @Method("GET")
     */
    public function emmanuelMacronVideosAction()
    {
        return $this->render('page/emmanuel-macron/videos.html.twig', [
            'videos' => $this->getDoctrine()->getRepository(FacebookVideo::class)->findPublishedVideos(),
        ]);
    }

    /**
     * @Route("/le-mouvement", defaults={"_enable_campaign_silence"=true}, name="page_le_mouvement")
     * @Method("GET")
     * @Entity("page", expr="repository.findOneBySlug('le-mouvement-nos-valeurs')")
     */
    public function mouvementValeursAction(Page $page)
    {
        return $this->render('page/le-mouvement/nos-valeurs.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/le-mouvement/notre-organisation", defaults={"_enable_campaign_silence"=true}, name="page_le_mouvement_notre_organisation")
     * @Method("GET")
     * @Entity("page", expr="repository.findOneBySlug('le-mouvement-notre-organisation')")
     */
    public function mouvementOrganisationAction(Page $page)
    {
        return $this->render('page/le-mouvement/notre-organisation.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/le-mouvement/legislatives", defaults={"_enable_campaign_silence"=true}, name="page_le_mouvement_legislatives")
     * @Method("GET")
     */
    public function mouvementLegislativesAction()
    {
        return $this->redirect('https://legislatives.en-marche.fr', Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/le-mouvement/les-comites", name="page_le_mouvement_les_comites")
     * @Method("GET")
     * @Entity("page", expr="repository.findOneBySlug('le-mouvement-les-comites')")
     */
    public function mouvementComitesAction(Page $page)
    {
        return $this->render('page/le-mouvement/les-comites.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/le-mouvement/devenez-benevole", name="page_le_mouvement_devenez_benevole")
     * @Method("GET")
     * @Entity("page", expr="repository.findOneBySlug('le-mouvement-devenez-benevole')")
     */
    public function mouvementBenevoleAction(Page $page)
    {
        return $this->render('page/le-mouvement/devenez-benevole.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/mentions-legales", defaults={"_enable_campaign_silence"=true}, name="page_mentions_legales")
     * @Method("GET")
     * @Entity("page", expr="repository.findOneBySlug('mentions-legales')")
     */
    public function mentionsLegalesAction(Page $page)
    {
        return $this->render('page/mentions-legales.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/okcandidatlegislatives", name="legislatives_confirm_newsletter")
     * @Method("GET")
     */
    public function legislativesConfirmNewsletterAction()
    {
        return $this->render('legislative_candidate/confirm_newsletter.html.twig');
    }

    /**
     * @Route("/bot", name="page_bot")
     * @Method("GET")
     */
    public function botAction()
    {
        return $this->render('bot/page.html.twig');
    }

    /**
     * @Route("/elles-marchent", defaults={"_enable_campaign_silence"=true}, name="page_elles_marchent")
     * @Method("GET")
     */
    public function ellesMarchentAction()
    {
        return $this->render('page/elles-marchent.html.twig');
    }
}
