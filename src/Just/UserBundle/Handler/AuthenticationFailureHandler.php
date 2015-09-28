<?php
namespace Just\UserBundle\Handler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler {

    /**
     * Translator
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options, LoggerInterface $logger = null, Translator $translator = null) {
    
        parent::__construct($httpKernel, $httpUtils, $options, $logger);

        $this->translator = $translator;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        if($request->isXmlHttpRequest()){
            $message = $exception->getMessageKey();
            $messageTrans = $this->translator->trans($message,array(),'FOSUserBundle');
            if($messageTrans === $message){
                $messageTrans = $this->translator->trans($message,array(),'security');
            }
            $data = array(
                'message' => $messageTrans,
                'success' => false
            );
            $response = new \Symfony\Component\HttpFoundation\JsonResponse($data,400);
            return $response;
        }else{
            return parent::onAuthenticationFailure($request, $exception);
        }
    }
    
    /**
     * Establece el traductor
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    function setTranslator(\Symfony\Component\Translation\TranslatorInterface $translator) {
        $this->translator = $translator;
    }
}