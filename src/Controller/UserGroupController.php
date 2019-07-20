<?php

namespace App\Controller;

use App\Entity\UserGroup;
use App\Form\UserGroupType;
use App\Repository\UserGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/usergroup")
 */
class UserGroupController extends AbstractController
{
    /**
     * @Route("/", name="user_group_index", methods={"GET"})
     */
    public function index(UserGroupRepository $userGroupRepository): Response
    {
        return $this->render('user_group/index.html.twig', [
            'user_groups' => $userGroupRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_group_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $userGroup = new UserGroup();
        $form = $this->createForm(UserGroupType::class, $userGroup);
        $form->remove('userGroups');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userGroup);
            $entityManager->flush();

            return $this->redirectToRoute('user_group_index');
        }

        return $this->render('user_group/new.html.twig', [
            'user_group' => $userGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_group_show", methods={"GET"})
     */
    public function show(UserGroup $userGroup): Response
    {
        return $this->render('user_group/show.html.twig', [
            'user_group' => $userGroup,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_group_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UserGroup $userGroup): Response
    {
        $form = $this->createForm(UserGroupType::class, $userGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_group_index');
        }

        return $this->render('user_group/edit.html.twig', [
            'user_group' => $userGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_group_delete", methods={"DELETE"})
     */
    public function delete(Request $request, UserGroup $userGroup): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userGroup->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($userGroup);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_group_index');
    }
}
