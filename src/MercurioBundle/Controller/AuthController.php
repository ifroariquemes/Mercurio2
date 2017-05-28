<?php

namespace MercurioBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MercurioBundle\Entity\User;
use MercurioBundle\Form\UserType;
use Symfony\Component\Routing\Generator\UrlGenerator as UrlGen;

class AuthController extends Controller
{

    /**
     * @Route("/auth/login", name="auth_login")
     */
    public function loginAction(Request $request)
    {
        $helper = $this->get('security.authentication_utils');

        return $this->render(
                        'auth/login.html.twig', [
                    'last_username' => $helper->getLastUsername(),
                    'error' => $helper->getLastAuthenticationError(),
                        ]
        );
    }

    /**
     * @Route("/auth/logout", name="auth_logout")
     */
    public function logoutAction()
    {
        
    }

    /**
     * @Route("/auth/register", name="auth_register")
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRole('ROLE_USER');

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $user->setCode(md5(uniqid()));
            $em->persist($user);
            $em->flush();

            $this->sendEmailRegister($user);

            return $this->render('auth/confirm.html.twig', ['user' => $user]);
        }
        return $this->render('auth/register.html.twig', [
                    'form' => $form->createView(),
        ]);
    }

    private function sendEmailRegister(User &$user)
    {
        $from = $this->container->getParameter('mailer_user');
        $url = $this->generateUrl('auth_confirm', [
            'key' => $user->getCode(),
            'return' => $this->generateUrl('auth_login', [], UrlGen::ABSOLUTE_URL)
                ], UrlGen::ABSOLUTE_URL);
        $email = \Swift_Message::newInstance()
                ->setSubject('Sua nova conta no Mercúrio')
                ->setFrom($from)
                ->setTo($user->getEmail())
                ->setBody(
                $this->render(
                        "auth/email.register.html.twig"
                        , ['user' => $user, 'url' => $url, 'email' => $from]
                ), 'text/html');
        $this->get('mailer')->send($email);
    }

    /**
     * @Route("/auth/confirm", name="auth_confirm")
     */
    public function confirmAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('MercurioBundle:User')->findOneByCode($request->query->get('key'));
        if (!empty($user)) {
            $user->setStatus(true);
            $em->flush();
            return $this->render(
                            'auth/confirmed.html.twig'
                            , ['return' => $request->query->get('return')]
            );
        } else {
            return new \Symfony\Component\HttpFoundation\Response('Chave não reconhecida.');
        }
    }

    /**
     * @Route("/auth/resend", name="auth_resend")
     */
    public function resendAction(Request $request)
    {
        $form = $this->createFormBuilder()
                ->add('email', \Symfony\Component\Form\Extension\Core\Type\EmailType::class, ['label' => 'E-mail'])
                ->getForm();
        $form->handleRequest($request);
        $message = '';
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('MercurioBundle:User')->findOneByEmail($form->get('email')->getData());
            if (!empty($user)) {
                $user->setCode(md5(uniqid()));
                $em->flush();
                $this->sendEmailRegister($user);
                return $this->render('auth/resend.check.html.twig');
            } else {
                $message = 'Não existe conta registrada com este e-mail.';
            }
        }
        return $this->render('auth/resend.html.twig', [
                    'form' => $form->createView(),
                    'message' => $message
        ]);
    }

    /**
     * @Route("/auth/forgot", name="auth_forgot")
     */
    public function forgotAction(Request $request)
    {
        $form = $this->createFormBuilder()
                ->add('email', \Symfony\Component\Form\Extension\Core\Type\EmailType::class, ['label' => 'E-mail'])
                ->getForm();
        $form->handleRequest($request);
        $message = '';
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('MercurioBundle:User')->findOneByEmail($form->get('email')->getData());
            if (!empty($user)) {
                $user->setCode(md5(uniqid()));
                $em->flush();
                $this->sendEmailForgot($user);
                return $this->render('auth/forgot.check.html.twig');
            } else {
                $message = 'Não existe conta registrada com este e-mail.';
            }
        }
        return $this->render('auth/forgot.html.twig', [
                    'form' => $form->createView(),
                    'message' => $message
        ]);
    }

    private function sendEmailForgot(User &$user)
    {
        $from = $this->container->getParameter('mailer_user');
        $url = $this->generateUrl('auth_reset', [
            'key' => $user->getCode(),
            'return' => $this->generateUrl('auth_login', [], UrlGen::ABSOLUTE_URL)
                ], UrlGen::ABSOLUTE_URL);
        $email = \Swift_Message::newInstance()
                ->setSubject('Nova senha no Mercúrio')
                ->setFrom($from)
                ->setTo($user->getEmail())
                ->setBody(
                $this->render(
                        "auth/email.forgot.html.twig"
                        , ['user' => $user, 'url' => $url, 'email' => $from]
                ), 'text/html');
        $this->get('mailer')->send($email);
    }

    /**
     * @Route("/auth/reset", name="auth_reset")
     */
    public function resetAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('MercurioBundle:User')->findOneByCode($request->query->get('key'));
        $form = $this->createFormBuilder($user)
                ->add('plainPassword', \Symfony\Component\Form\Extension\Core\Type\RepeatedType::class, [
                    'type' => \Symfony\Component\Form\Extension\Core\Type\PasswordType::class,
                    'first_options' => ['label' => 'Senha'],
                    'second_options' => ['label' => 'Confirme a senha'],
                    'invalid_message' => 'As senhas precisam ser iguais'])
                ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $em->flush();
            return $this->render('auth/reseted.html.twig');
        }

        if (!empty($user)) {
            return $this->render(
                            'auth/reset.html.twig'
                            , [
                        'user' => $user,
                        'return' => $request->query->get('return'),
                        'form' => $form->createView()
                            ]
            );
        } else {
            return new \Symfony\Component\HttpFoundation\Response('Chave não reconhecida.');
        }
    }

}
