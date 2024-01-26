<?php

use Ramsey\Uuid\Uuid;

class FileController extends BaseController
{
    /** 
* "/file/list" Endpoint - Get list of files
*/
    public function getFiles($settings_json) {
        $files = file_get_contents(PROJECT_ROOT_PATH . $settings_json->storage_location . '/db.json');
        if (isset($files)) 
        {
            header('Content-Type: application/json; charset=utf-8');
            echo $files;
        }
        else 
        {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($settings_json->info->files->no_items);
        }
        
    }

    public function getFile($settings_json, $id) {
        $files = json_decode(file_get_contents(PROJECT_ROOT_PATH . $settings_json->storage_location . '/db.json'));
        
        if (property_exists($files, $id)) 
        {
            $filename = PROJECT_ROOT_PATH . $settings_json->storage_location . '/' . $files->$id->file_name;
            if (file_exists($filename))
            {

                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($filename).'"');
                header('Content-Length: ' . filesize($filename));
                header('Content-Transfer-Encoding = binary');
                
                flush();
                readfile($filename);
            }
        }
        
    }

    public function uploadFile($settings_json, $FILE) {
        $db_file_path = PROJECT_ROOT_PATH . $settings_json->storage_location . '/db.json';
        $files = json_decode(file_get_contents($db_file_path));
        $allowed_types = explode(",", $settings_json->allowed_types);

        $file_ext = strtolower(end(explode(".", $FILE['file']['name'])));
        $file_uuid = Uuid::uuid4()->toString();
        $file_name = sha1($file_uuid);
        $file_path = PROJECT_ROOT_PATH . $settings_json->storage_location . '/' . $file_name . '.' . $file_ext;

        if (($FILE['file']['error'] == 0) && isset($file_ext) &&
            (in_array($file_ext, $allowed_types))) 
        {

            foreach ($files as $file)
            {
                if ($file->file_hash == sha1_file($FILE['file']['tmp_name'])) 
                {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($settings_json->errors->files->exists);
                    exit(); 
                }
            }

            if(move_uploaded_file($FILE['file']['tmp_name'], $file_path))
            {
                $file = new stdClass();
                $file->file_hash = sha1_file($file_path);
                $file->file_name = $file_name . '.' . $file_ext;

                $files->$file_uuid = $file;

                file_put_contents($db_file_path, json_encode($files));
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($settings_json->info->files->upload_success);

            }
            else 
            {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($settings_json->errors->files->upload_failed);
            }
            
        }
        else 
        {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($settings_json->errors->files->not_allowed);
        }
    }

    public function deleteFile($settings_json, $id) {
        $db_file_path = PROJECT_ROOT_PATH . $settings_json->storage_location . '/db.json';
        $files = json_decode(file_get_contents($db_file_path));
        
        if (property_exists($files, $id)) 
        {
            $file_name = PROJECT_ROOT_PATH . $settings_json->storage_location . '/' . $files->$id->file_name;
            if (file_exists($file_name))
            {
                if (unlink($file_name)) 
                {
                    unset($files->{$id});
                    file_put_contents($db_file_path, json_encode($files));
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($settings_json->info->files->delete_success);
                }
                else 
                {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($settings_json->info->files->delete_failed);
                }
            }
        }
        else 
        {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($settings_json->errors->files->not_exists);
        }
        
    } 
}