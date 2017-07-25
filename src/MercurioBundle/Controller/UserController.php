<?php

namespace MercurioBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MercurioBundle\Form\UserEditType;
use MercurioBundle\Form\UserType;

class UserController extends Controller
{

    /**
     * @Route("/user/{page}", name="user_list", requirements={"page": "\d+"})
     */
    public function listAction($page = 1)
    {
        $users = $this->getDoctrine()->getRepository('MercurioBundle:User')
                ->getAll($page);
        $maxPages = ceil($users->count() / 20);
        return $this->render('user/list.html.twig', compact('users', 'maxPages', 'page'));
    }

    /**
     * @Route("/user/edit/{id}", name="user_edit")
     */
    public function editAction(Request $request, int $id)
    {
        if ($this->getUser()->getId() === $id || $this->isGranted('ROLE_ADMIN')) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('MercurioBundle:User')->findOneById($id);
            $form = $this->createForm(UserEditType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if (!empty($user->getPlainPassword())) {
                    $encoder = $this->get('security.password_encoder');
                    $password = $encoder->encodePassword($user, $user->getPlainPassword());
                    $user->setPassword($password);
                }
                if ($this->getUser()->getRole() === 'ROLE_USER') {
                    $user->setRole('ROLE_USER');
                }
                $em->persist($user);
                $em->flush();
                return $this->render('user/edit.confirm.html.twig');
            }
            return $this->render('user/edit.html.twig', [
                        'user' => $user,
                        'form' => $form->createView()
            ]);
        }
        $this->redirectToRoute('/');
    }

    /**
     * @Route("/user/new", name="user_new")
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = new \MercurioBundle\Entity\User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $encoder = $this->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $em->persist($user);
            $em->flush();
            return $this->render('user/edit.confirm.html.twig');
        }
        return $this->render('user/edit.html.twig', [
                    'user' => $user,
                    'form' => $form->createView()
        ]);
    }

}
