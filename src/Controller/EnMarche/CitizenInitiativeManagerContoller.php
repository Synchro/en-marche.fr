<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\CitizenInitiative\CitizenInitiativeCommand;
use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\EventRegistration;
use AppBundle\Event\EventContactMembersCommand;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\CitizenInitiativeType;
use AppBundle\Form\ContactMembersType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/initiative_citoyenne/{uuid}/{slug}", requirements={"uuid": "%pattern_uuid%"})
 * @Security("is_granted('EDIT_CITIZEN_INITIATIVE', initiative)")
 * @Entity("citizen_initiative", expr="repository.findOneActiveByUuid(uuid)")
 */
class CitizenInitiativeManagerContoller extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/modifier", name="app_citizen_initiative_edit")
     * @Method("GET|POST")
     */
    public function editAction(Request $request, CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

        $form = $this->createForm(CitizenInitiativeType::class, $command = CitizenInitiativeCommand::createFromCitizenInitiative($initiative));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_initiative.handler')->handleUpdate($initiative, $command);
            $this->addFlash('info', $this->get('translator')->trans('citizen_initiative.update.success'));

            return $this->redirectToRoute('app_citizen_initiative_show', [
                'uuid' => (string) $initiative->getUuid(),
                'slug' => $initiative->getSlug(),
            ]);
        }

        return $this->render('citizen_initiative/edit.html.twig', [
            'initiative' => $initiative,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/annuler", name="app_citizen_initiative_cancel")
     * @Method("GET|POST")
     */
    public function cancelAction(Request $request, CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

        $command = CitizenInitiativeCommand::createFromCitizenInitiative($initiative);

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_initiative.handler')->handleCancel($initiative, $command);
            $this->addFlash('info', $this->get('translator')->trans('citizen_initiative.cancel.success'));

            return $this->redirectToRoute('app_citizen_initiative_show', [
                'uuid' => (string) $initiative->getUuid(),
                'slug' => $initiative->getSlug(),
            ]);
        }

        return $this->render('citizen_initiative/cancel.html.twig', [
            'initiative' => $initiative,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscrits", name="app_citizen_initiative_members")
     * @Method("GET")
     */
    public function membersAction(CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

        $registrations = $this->getDoctrine()->getRepository(EventRegistration::class)->findByEvent($initiative);

        return $this->render('citizen_initiative/members.html.twig', [
            'initiative' => $initiative,
            'registrations' => $registrations,
        ]);
    }

    /**
     * @Route("/inscrits/exporter", name="app_citizen_initiative_export_members")
     * @Method("POST")
     */
    public function exportMembersAction(Request $request, CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

        if (!$this->isCsrfTokenValid('event.export_members', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to export members.');
        }

        $uuids = json_decode($request->request->get('exports'), true);

        if (!$uuids) {
            return $this->redirectToRoute('app_citizen_initiative_members', [
                'uuid' => $initiative->getUuid(),
                'slug' => $initiative->getSlug(),
            ]);
        }

        $repository = $this->getDoctrine()->getRepository(EventRegistration::class);

        try {
            $registrations = $repository->findByUuidAndEvent($initiative, $uuids);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        if (!$registrations) {
            return $this->redirectToRoute('app_citizen_initiative_members', [
                'uuid' => $initiative->getUuid(),
                'slug' => $initiative->getSlug(),
            ]);
        }

        $exported = $this->get('app.event.registration_exporter')->export($registrations);

        return new Response($exported, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inscrits-a-l-evenement.csv"',
        ]);
    }

    /**
     * @Route("/inscrits/contacter", name="app_citizen_initiative_contact_members")
     * @Method("POST")
     */
    public function contactMembersAction(Request $request, CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

        if (!$this->isCsrfTokenValid('event.contact_members', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to contact members.');
        }

        $uuids = json_decode($request->request->get('contacts', '[]'), true);

        if (!$uuids) {
            $this->addFlash('info', $this->get('translator')->trans('citizen_initiative.contact.none'));

            return $this->redirectToRoute('app_citizen_initiative_members', [
                'uuid' => $initiative->getUuid(),
                'slug' => $initiative->getSlug(),
            ]);
        }

        $repository = $this->getDoctrine()->getRepository(EventRegistration::class);

        try {
            $registrations = $repository->findByUuidAndEvent($initiative, $uuids);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        $command = new EventContactMembersCommand($registrations, $this->getUser());

        $form = $this->createForm(ContactMembersType::class, $command, ['csrf_token_id' => 'event.contact_members'])
            ->add('submit', SubmitType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.contact_members_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('citizen_initiative.contact.success'));

            return $this->redirectToRoute('app_citizen_initiative_members', [
                'uuid' => $initiative->getUuid(),
                'slug' => $initiative->getSlug(),
            ]);
        }

        $uuids = array_map(function (EventRegistration $registration) {
            return $registration->getUuid()->toString();
        }, $registrations);

        return $this->render('citizen_initiative/contact_members.html.twig', [
            'initiative' => $initiative,
            'contacts' => $uuids,
            'form' => $form->createView(),
        ]);
    }
}
