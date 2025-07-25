<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.{_format}', name: 'app_sitemap', requirements: ['_format' => 'html|xml'], format: 'xml')]
    public function index(Request $request): Response
    {
        $hostname = $request->getSchemeAndHttpHost();
        $urls = [];
    
        // ✅ URLs statiques
        $urls[] = [
            'loc' => $hostname . $this->generateUrl('app_home'), 
            'priority' => '1.00'
        ];
        $urls[] = [
            'loc' => $hostname . $this->generateUrl('app_vo'), 
            'priority' => '0.90'
        ];
        $urls[] = [
            'loc' => $hostname . $this->generateUrl('app_services'), 
            'priority' => '0.80'
        ];
        $urls[] = [
            'loc' => $hostname . $this->generateUrl('app_rdv_client'), 
            'priority' => '0.70'
        ];
        $urls[] = [
            'loc' => $hostname . $this->generateUrl('app_devis_client'), 
            'priority' => '0.70'
        ];
        $urls[] = [
            'loc' => $hostname . $this->generateUrl('app_contact'), 
            'priority' => '0.60'
        ];
       
        // ✅ Rendu du template
        $response = $this->render('site_map/sitemap.xml.twig', [
            'urls' => $urls,
            'hostname' => $hostname
        ]);
        
        $response->headers->set('Content-Type', 'text/xml');
        
        return $response;
    }
}