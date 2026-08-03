<?php
namespace App\Controllers;

use Exception;

class ErrorController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLayout('error');
    }
    
    public function notFound(): void
    {
        http_response_code(HTTP_NOT_FOUND);
        $this->render('404', [
            'title' => 'Page Not Found - ' . APP_NAME,
            'message' => 'The page you are looking for could not be found.'
        ]);
    }
    
    public function forbidden(): void
    {
        http_response_code(HTTP_FORBIDDEN);
        $this->render('403', [
            'title' => 'Access Denied - ' . APP_NAME,
            'message' => 'You do not have permission to access this page.'
        ]);
    }
    
    public function serverError(): void
    {
        http_response_code(HTTP_INTERNAL_SERVER_ERROR);
        $this->render('500', [
            'title' => 'Server Error - ' . APP_NAME,
            'message' => 'An unexpected error occurred. Please try again later.'
        ]);
    }
    
    public function maintenance(): void
    {
        http_response_code(HTTP_SERVICE_UNAVAILABLE);
        $this->render('maintenance', [
            'title' => 'Under Maintenance - ' . APP_NAME,
            'message' => MAINTENANCE_MESSAGE ?? 'The system is currently undergoing maintenance.'
        ]);
    }
}