<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SecurityAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * Вызывается по каждому запросу. Верните те сертификаты, которые вы
     * хотите передать getUser(). Возвращение "null" приведёт к пропуску
     * аутентификатора.
     * @param Request $request
     * @return array
     */
    public function getCredentials(Request $request): array
    {
        if (!$token = $request->headers->get('X-AUTH-TOKEN')) {
            // Нет токена?
            $token = null;
        }

        // То, что вы возвращаете здесь, будет передано getUser() как $credentials
        return array(
            'token' => $token,
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $key = $credentials['token'];

        if (null === $key) {
            return;
        }

        // если null, то аутентификация будет неудачной
        // если объект Пользователя, то вызывается checkCredentials()
        return $userProvider->loadUserByUsername($key);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // проверить сертификаты - например, убедиться, что пароль валидный
        // в этом случае проверка сертификатов не требуется

        // вернуть true, чтобы аутентификация прошла успешно
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // при успехе, позвольте запросу продолжать
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // или, чтобы перевести это сообщение
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Вызывается, когда нужна аутентификация, но не отправляется
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            // вы можете перевести это сообщение
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        // TODO: Implement supports() method.
    }
}
