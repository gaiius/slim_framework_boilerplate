<?php

namespace Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class Home {

    private $view;
    private $flash;
 

    public function __construct($view, $flash) {
        $this->view = $view;
        $this->flash = $flash;
  
    }

    public function index(Request $request, Response $response, $args) {
            
               $this->view->render($response, 'hello_2.twig', ['data' => $m_sampling, 'defect' => $x]);
                return $response;
               
          }

    public function dispatch(Request $request, Response $response, $args) {
       
        $result = $this->view->render($response, 'login.twig');
        return $response;
      
    }

  
}
