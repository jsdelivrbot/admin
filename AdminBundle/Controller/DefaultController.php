<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * Returns the DataTables i18n file
     *
     * @return Response
     *
     * @Route("/dataTables.{_format}" , requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function getDataTablesI18nAction()
    {
        $locale = $this->get('request_stack')->getCurrentRequest()->getLocale();
        $format = $this->get('request_stack')->getCurrentRequest()->getRequestFormat();

        return $this->render(
            'AdminBundle:Default/DataTables_i18n:'.$locale.'.txt.'.$format
        );
    }
    
    /**
     * @Route("/admin/")
     * @Template()
     */
    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER') ) {
            return $this->redirect($this->generateUrl('index'));
        }else{
            return $this->redirect($this->generateUrl('admin_default_dashboard'));
        }
        return $this->redirect($this->generateUrl('admin_login'));
    }
    
    /**
     * @Route("/admin/dashboard")
     * @Template("AdminBundle:Default:index.html.twig")
     */
    public function dashboardAction(Request $request)
    {
        return array();
    }
    
    
    /**
     * @Route("/admin/fileupload")
     * @Template("AdminBundle:Default:fileupload.html.twig")
     */
    public function fileuploadAction(Request $request)
    {
        return array();
    }
    
    /**
     * @Route("/admin/analytics/{id}")
     * @Template()
     */
    public function analyticsAction(Request $request, $id=null)
    {
        
        $client = $this->get('google.api.client');

        if ($request->query->get('code') && is_null($this->get('session')->get('access_token'))) {
            $client->authenticate($request->query->get('code'));
            $this->get('session')->set('access_token', $client->getAccessToken());
            header('Location: ' . filter_var($this->generateUrl('admin_default_analytics'), FILTER_SANITIZE_URL));
        }
        
        /************************************************
         Google Analitycs
         If we have an access token, we can make
         requests, else we generate an authentication URL.
        ************************************************/
        $authUrl = null;
        $mainResult = null;
        $reportResult = null;
        $reportDay = null;
        $startDate = null;
        $endDate = null;
        
        if ($this->get('session')->get('access_token')) {
            $client->setAccessToken($this->get('session')->get('access_token'));
            $analytics = $this->get('google.api.analytics');
            if($request->query->get('start') !='' && $request->query->get('end') != ''){
                $startDate = $request->query->get('start');
                $endDate = $request->query->get('end');
            }else{
                $now = new \DateTime();
                $startDate = '2012-01-01';
                $endDate = $now->format('Y-m-d');
            }
         
            if($analytics->hasAccount()){
                list($mainResult, $reportResult, $reportDay) = $analytics->getGoogleAnalitycsData($startDate, $endDate);
            
                return $this->render("AdminBundle:Analytics:analytics.html.twig", array(
                        'authUrl' => $authUrl,
                        'mainResult' => $mainResult,
                        'reportResult'=> $reportResult,
                        'reportDay' => $reportDay,
                        'startDate' => $startDate,
                        'endDate' => $endDate
                    ));
            }
            
            return $this->render("AdminBundle:Analytics:no.analytics.html.twig", array());
            
        } else {
          $authUrl = $client->createAuthUrl();
          return $this->render("AdminBundle:Analytics:auth.analytics.html.twig", array('authUrl' => $authUrl));
        }
        
    }
}
