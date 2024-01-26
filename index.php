<?php
    declare(strict_types=1);
    require __DIR__ . "/inc/bootstrap.php";

    //variable declarations
    
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode( '/', $uri );

    $settings_json = json_decode(file_get_contents(__DIR__ . '/settings.json'));
    $valid_path = explode(",", $settings_json->path);

    if ((isset($uri[1]) && !(in_array($uri[1], $valid_path)))) {
        header("HTTP/1.1 404 Not Found");
        exit();
        
    }
    elseif (($uri[1] == $valid_path[0]) 
        && ($_SERVER['REQUEST_METHOD'] === "POST"))
    {
        if ($_SERVER["CONTENT_TYPE"] == "application/json") {
            require PROJECT_ROOT_PATH . "/Controller/Api/UserController.php";
            $objUserController = new UserController();
            $objUserController->authenticate($settings_json);
        }
        else 
        {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($settings_json->errors->request_content_type);
        }
        
    }
    elseif ($uri[1] == $valid_path[1])
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") 
        {
            if (($_SERVER["CONTENT_TYPE"] == "application/json") && !(isset($uri[2]))) {

                require PROJECT_ROOT_PATH . "/Controller/Api/FileController.php";
                require PROJECT_ROOT_PATH . "/Controller/Api/UserController.php";
                $objUserController = new UserController();
                $objFileController = new FileController();
                
                if($objUserController->verify_credentials($settings_json)) {
                    $objFileController->getFiles($settings_json);
                }
            }
            elseif (($_SERVER["CONTENT_TYPE"] == "application/json") && (isset($uri[2]))) {
                require PROJECT_ROOT_PATH . "/Controller/Api/FileController.php";
                require PROJECT_ROOT_PATH . "/Controller/Api/UserController.php";
                $objUserController = new UserController();
                $objFileController = new FileController();
                
                if($objUserController->verify_credentials($settings_json)) {
                    $objFileController->getFile($settings_json, $uri[2]);
                }
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === "POST") {
                require PROJECT_ROOT_PATH . "/Controller/Api/FileController.php";
                require PROJECT_ROOT_PATH . "/Controller/Api/UserController.php";
                $objUserController = new UserController();
                $objFileController = new FileController();
                
                if($objUserController->verify_credentials($settings_json)) {
                    $objFileController->uploadFile($settings_json, $_FILES);
                }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === "DELETE") {
            if (($_SERVER["CONTENT_TYPE"] == "application/json") && (isset($uri[2]))) {
                require PROJECT_ROOT_PATH . "/Controller/Api/FileController.php";
                require PROJECT_ROOT_PATH . "/Controller/Api/UserController.php";
                $objUserController = new UserController();
                $objFileController = new FileController();
                
                if($objUserController->verify_credentials($settings_json)) {
                    $objFileController->deleteFile($settings_json, $uri[2]);
                }
            }
        }
        else 
        {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($settings_json->errors->request_content_type);
        }
        
    }
    else 
    {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
?>