<?php

namespace Haswalt\SecurityBundle\Controller;

use Haswalt\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\DeserializationContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Haswalt\SecurityBundle\SecurityEvents;
use Haswalt\SecurityBundle\Event\UserEvent;

class UsersController extends ApiController
{
    public function registerAction(Request $request)
    {
        $serializer = $this->get('jms_serializer');
        $validator = $this->get('validator');

        $user = $serializer->deserialize($request->getContent(), 'Haswalt\SecurityBundle\Entity\User', 'json');

        $violations = $validator->validate($user);
        if (count($violations) > 0) {
            return $this->errorResponse($violations);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(
            SecurityEvents::REGISTER,
            new UserEvent($user)
        );

        return $this->locationResponse('haswalt_security_account');
    }

    public function accountAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->jsonResponse($user, 200, ['default']);
    }

    public function updateAccountAction(Request $request)
    {
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        $serializer = $this->get('jms_serializer');
        $validator = $this->get('validator');

        $context = new DeserializationContext();
        $context->setAttribute('target', $currentUser);

        $user = $serializer->deserialize($request->getContent(), 'Haswalt\SecurityBundle\Entity\User', 'json', $context);

        $violations = $validator->validate($user);
        if (count($violations) > 0) {
            return $this->errorResponse($violations);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->jsonResponse($user, 200, ['default']);
    }

    public function forgotPasswordAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (false === isset($data['email'])) {
            return $this->jsonResponse([
                'errors' => [
                    'email' => 'Email is required',
                ]
            ], 400);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HaswaltSecurityBundle:User')
            ->findOneByUsername($data['email']);

        if ($user) {
            $token = base64_encode(random_int(32));
            $user->setForgotToken($token);
            $user->setForgotAt(new \DateTime());
            $em->persist($user);
            $em->flush();

            $this->get('event_dispatcher')->dispatch(
                SecurityEvents::FORGOT_PASSWORD,
                new UserEvent($user)
            );
        }

        return $this->jsonResponse([]);
    }

    public function resetPasswordAction(Request $request, $token)
    {
        $data = json_decode($request->getContent(), true);

        $errors = [];
        if (false === isset($data['email'])) {
            $errors['email'] = 'Email is required';
        }

        if (false === isset($data['password'])) {
            $errors['password'] = 'Password is required';
        }

        if (count($errors) > 0) {
            return $this->jsonResponse($errors, 400);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('HaswaltSecurityBundle:User')
            ->findOneByUsername($data['email']);

        if (!$user || $user->getForgotAt() >= new \DateTime() || $user->getForgotToken() == null || $user->getForgotToken() != $token) {
            throw new AccessDeniedException();
        }

        $user->setPassword($data['password']);
        $user->setForgotToken(null);
        $em->persist($user);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(
            SecurityEvents::RESET_PASSWORD,
            new UserEvent($user)
        );

        return $this->jsonResponse([]);
    }
}
