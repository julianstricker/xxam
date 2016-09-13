<?php

namespace Xxam\UserBundle\Handler;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler {

   /**
     * Translator
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        $timezone=$request->get('timezone');
        $token->getUser()->setTimezone($timezone);
        $session=$request->getSession();
        $session->set('timezone',$timezone);
        //date_default_timezone_set($timezone);
            if($request->isXmlHttpRequest()){
            $url = $this->determineTargetUrl($request);
            if(!preg_match('/http/', $url)){
                $url = $request->getBaseUrl().$url;
            }
            
            $data = array(
                'url' => $url,
                'success' => true
            );
            $response = new \Symfony\Component\HttpFoundation\JsonResponse($data);
            return $response;
        }else{
            return parent::onAuthenticationSuccess($request, $token);
        }
    }
    
    /**
     * Builds the target URL according to the defined options.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function determineTargetUrl(Request $request)
    {
        if ($this->options['always_use_default_target_path']) {
            return $this->options['default_target_path'];
        }
        if ($targetUrl = $request->get($this->options['target_path_parameter'], null, true)) {
            return $targetUrl;
        }
        if (null !== $this->providerKey && $targetUrl = $request->getSession()->get('_security.'.$this->providerKey.'.target_path')) {
            $request->getSession()->remove('_security.'.$this->providerKey.'.target_path');
            return $targetUrl;
        }
        if ($this->options['use_referer'] && ($targetUrl = $request->headers->get('Referer')) && $targetUrl !== $this->httpUtils->generateUri($request, $this->options['login_path'])) {
            return $targetUrl;
        }
        return $this->options['default_target_path'];
    }
    
    /**
     * Establece el traductor
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    function setTranslator(\Symfony\Component\Translation\TranslatorInterface $translator) {
        $this->translator = $translator;
    }
}
