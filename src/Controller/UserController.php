<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserGroup;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->remove('userGroups');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $plain_password = $user->getPassword();
            $user->setPassword(
                $this->encoder->encodePassword($user, $plain_password)
            );
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->remove('password');
        $form->remove('userGroups');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain_password = $user->getPassword();
            $user->setPassword(
                $this->encoder->encodePassword($user, $plain_password)
            );
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
    /**
     * @Route("/{id}/user_groupadd", name="user_groupadd", methods={"GET","POST"})
     */
    public function user_groupadd(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $em = $this->getDoctrine()->getManager();
        $userGroups[]=$em->getRepository(UserGroup::class)->findGroupsNotJoined($user->getId());
        $form->handleRequest($request);

        if($request->isMethod('post')){
            $group_id = $request->request->get('_group');
            $group = $em->getRepository(UserGroup::class)->find($group_id);
            $user->getUserGroups()->add($group);
            $em->persist($group);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/useraddgroup.html.twig', [
            'user'=>$user,
            'userGroups' => $userGroups,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/{id}/user_groupremove", name="user_groupremove", methods={"GET","POST"})
     */
    public function user_groupremove(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $em = $this->getDoctrine()->getManager();
        $userGroups[]=$em->getRepository(UserGroup::class)->findGroupsJoined($user->getId());

        $form->handleRequest($request);

        if($request->isMethod('post')){
            $data = $request->request;
            var_dump($data);
            exit(0);
            $group_id = $request->request->get('_group');
            $group = $em->getRepository(UserGroup::class)->find($group_id);
            $user->removeUserGroup($group);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/userremovegroup.html.twig', [
            'user'=>$user,
            'userGroups' => $userGroups,
            'form' => $form->createView(),
        ]);
    }
}
