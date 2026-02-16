<?php
declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    public function getGreeting($request)
    {
        echo '<strong>Route Method:</strong> ' . $request->method() . '<br>';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;<em>For POST method you have to <a href="18-form-security-and-validation.md">Submit Form</a> or use <a href="18-form-security-and-validation.md">API client</a>, Later we will implement this respectively on Chapter <a href="11-response-api-json.md">11. Response API - JSON</a> and Chapter <a href="18-form-security-and-validation.md">18. Form - Security and Validation</a>.</em>' . '<br><br>';
        echo '<strong>Route URI:</strong> ' . $request->uri() . '<br><br>';
        echo '<strong>Route Query String:</strong> <br>' . str_replace('&', '<br>', http_build_query($request->all())) . '<br><br>';
        
        return 'Welcome to the ' . config('app.name');
    }
}
