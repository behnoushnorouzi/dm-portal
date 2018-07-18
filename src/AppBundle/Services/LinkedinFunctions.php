<?php declare(strict_types=1);

namespace AppBundle\Services;

use Happyr\LinkedIn\LinkedIn;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\Translator;

final class LinkedinFunctions
{
    private $linkedinApiId;
    private $linkedinApiSecret;
    private $linkedinCompanyId;
    private $dmPortalProd;
    private $redirectUrl;
    private $translator;
    private $session;
    private $router;

    private const VISIBILITY = 'anyone';

    /**
     * LinkedinFunctions constructor.
     * @param string $linkedinApiId
     * @param string $linkedinApiSecret
     * @param string $linkedinCompanyId
     * @param string $redirectUrl
     * @param Router $router
     * @param Session $session
     * @param string $dmPortalProd
     * @param Translator $translator
     */
    public function __construct(
        string $linkedinApiId,
        string $linkedinApiSecret,
        string $linkedinCompanyId,
        string $redirectUrl,
        Router $router,
        Session $session,
        string $dmPortalProd,
        Translator $translator
    )
    {
        $this->linkedinApiId = $linkedinApiId;
        $this->linkedinApiSecret = $linkedinApiSecret;
        $this->linkedinCompanyId = $linkedinCompanyId;
        $this->redirectUrl = $redirectUrl;
        $this->session = $session;
        $this->router = $router;
        $this->dmPortalProd = $dmPortalProd;
        $this->translator = $translator;

        $this->linkedin = new LinkedIn($this->linkedinApiId, $this->linkedinApiSecret);
    }

    /**
     * @return string
     */
    public function getLinkedinLogin(): string
    {
        if ($this->linkedin->getError()) {
            throw new Exception($this->translator->trans('service.linkedin.login.error'));
        }

        return $this->linkedin->getLoginUrl([
            'redirect_uri' => $this->router->generate($this->redirectUrl, array(), UrlGeneratorInterface::ABSOLUTE_URL)
        ]);
    }

    /**
     * @return string
     */
    public function LinkedinCallback(): string
    {
        try {
            $accessToken = $this->linkedin->getAccessToken();
        } catch (Exception $exception) {
            return $exception->getMessage();
        }

        if (!$accessToken) {
            $response = new Response();
            if ($this->linkdin->hasError()) {
                $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
                return "Error: " . $this->linkedin->getError() . "\n";
            }
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->send();
        }

        $this->session->set('sessionToken', $accessToken->getToken());

        return $this->session->get('sessionToken');
    }

    /**
     * @param string $comment
     * @param string|null $imageUrl
     * @param string $link
     *
     * @return mixed
     */
    public function postMessageOnLinkedin(string $comment, string $imageUrl = null, string $link = '#')
    {
        $options = [
            'json' => [
                'comment' => $comment,
                'content' => [
                    'submitted-url' =>  $link,
                    'submitted-image-url' => ($imageUrl) ? $this->dmPortalProd.trim($imageUrl, '/app/web') : $this->dmPortalProd.'/img/logo_darkmira.png',
                ],
                'visibility' => [
                    'code' => self::VISIBILITY,
                ]
            ]
        ];

        return $this->linkedin->post('v1/companies/'.$this->linkedinCompanyId.'/shares', $options);
    }
}
